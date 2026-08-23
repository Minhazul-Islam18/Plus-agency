<?php

namespace App\Http\Controllers\Payment\Tender;

use App\BasicExtra;
use App\Jobs\SendAdminMail;
use App\Language;
use App\SecureToken;
use App\Tender;
use App\TenderModule;
use App\TenderPurchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PDF;

trait TenderPaymentHelper
{
    protected function getLang(): Language
    {
        return currentLang();
    }

    protected function getVersion(): string
    {
        $lang = $this->getLang();
        $version = optional($lang->basic_extended)->theme_version ?? 'default';
        return $version === 'dark' ? 'default' : $version;
    }

    protected function createPendingPurchase(Request $request, string $gateway): TenderPurchase
    {
        // Terms & conditions must be accepted.
        if (!$request->boolean('agree_terms')) {
            throw new \RuntimeException(__('You must accept the terms and conditions to proceed.'));
        }

        // Country and phone dialling code must come from the canonical list, and the
        // number itself must be digits only. The online path bypasses the checkout
        // FormRequest, so these are validated here too.
        $country = (string) $request->country;
        if (!in_array($country, \App\Http\Helpers\Countries::names(), true)) {
            throw new \RuntimeException(__('Please select a country from the list.'));
        }

        $phoneCode = (string) $request->phone_code;
        if (!in_array($phoneCode, \App\Http\Helpers\Countries::dialCodes(), true)) {
            throw new \RuntimeException(__('Please select a valid phone country code.'));
        }

        $nationalNumber = preg_replace('/\D/', '', (string) $request->phone_number);
        if (strlen($nationalNumber) < 4 || strlen($nationalNumber) > 14) {
            throw new \RuntimeException(__('Enter a valid phone number (4–14 digits, without the country code).'));
        }

        // Stored as one E.164 string, matching the offline checkout path.
        $fullPhone = $phoneCode . $nationalNumber;

        // Company registration number is the sole duplicate-purchase key: required,
        // uppercase alphanumeric only. The online path bypasses the checkout
        // FormRequest, so it is validated here too.
        $regNo = TenderPurchase::normalizeRegNo($request->company_registration_no);
        if ($regNo === '' || !preg_match('/^[A-Z0-9]+$/', $regNo)) {
            throw new \RuntimeException(__('A valid Company Registration No. (letters and numbers only) is required.'));
        }

        // Blacklisted companies (by registration no.) cannot place a new order.
        if (\App\TenderBlacklist::matches($regNo)) {
            throw new \RuntimeException(__('This order cannot be processed. Please contact ICA support.'));
        }

        $bse = $this->getLang()->basic_extra ?? BasicExtra::first();

        // The buyer pays per module, so at least one must be selected. The online path
        // bypasses the checkout FormRequest, so this is enforced here too.
        $selectedIds = array_filter(array_map('intval', (array) $request->input('selected_module_ids', [])));
        if (empty($selectedIds)) {
            throw new \RuntimeException(__('Please select at least one module to continue.'));
        }

        $allModules = TenderModule::where('tender_id', $request->tender_id)
            ->where('status', 1)
            ->get(['id', 'name', 'cost']);

        // Free modules (no cost) always ship with the tender → always on the receipt.
        $freeModules = $allModules->filter(fn($m) => is_null($m->cost))->values();

        // Paid modules being purchased — only the ones explicitly selected.
        $paidModules = $allModules->filter(fn($m) => !is_null($m->cost))
            ->whereIn('id', $selectedIds)
            ->values();

        // Duplicate-payment guard: drop paid modules this company already owns,
        // keyed solely on company registration number.
        $paidNames = TenderPurchase::paidModuleNamesForReg(
            (int) $request->tender_id,
            $regNo
        );
        if (!empty($paidNames)) {
            $paidModules = $paidModules->reject(fn($m) => in_array(trim($m->name), $paidNames, true))->values();
        }

        // Nothing left to pay for → block (free items never require payment).
        if ($paidModules->isEmpty()) {
            throw new \RuntimeException(__('You have already paid for the selected module(s). No further payment is required.'));
        }

        // Persist paid + free; free modules carry cost 0 so the receipt total is unchanged.
        $modules = $paidModules->concat($freeModules);

        // Resuming a previously-failed attempt (see resumePurchase()) updates
        // the SAME Pending row instead of creating a new one — otherwise every
        // retry after a declined card would leave another orphaned Pending
        // purchase behind. Guarded to the same tender and still-Pending, so a
        // stale/tampered resume_purchase_id can't hijack an unrelated order.
        $purchase = null;
        if ($request->filled('resume_purchase_id')) {
            $purchase = TenderPurchase::where('id', $request->input('resume_purchase_id'))
                ->where('tender_id', $request->tender_id)
                ->where('payment_status', 'Pending')
                ->first();
        }
        if (!$purchase) {
            $purchase = new TenderPurchase;
            $purchase->order_number = strtoupper(Str::random(10));
        }

        $purchase->tender_id         = $request->tender_id;
        $purchase->user_id           = Auth::check() ? Auth::id() : null;
        $purchase->first_name        = $request->first_name;
        $purchase->last_name         = $request->last_name;
        $purchase->email             = $request->email;
        $purchase->phone_number      = $fullPhone;
        $purchase->country           = $country;
        $purchase->city              = $request->city ?? '';
        $purchase->company_name      = $request->company_name ?? null;
        $purchase->company_address   = $request->company_address ?? null;
        $purchase->company_registration_no = $regNo;
        $purchase->currency_code     = $bse->base_currency_text;
        $purchase->payment_method    = $gateway;
        $purchase->gateway_type      = 'online';
        $purchase->payment_status    = 'Pending';
        $purchase->purchased_modules = $modules->map(fn($m) => [
            'name' => $m->name,
            'cost' => (float) $m->cost,
        ])->values()->toJson();

        $purchase->save();
        \App\TenderCompany::syncFromPurchase($purchase);
        return $purchase;
    }

    /**
     * Authoritative amount to charge: the sum of the modules actually being purchased
     * (already filtered to the buyer's unpaid set in createPendingPurchase). Never trust
     * the client-sent selected_amount — it can be tampered and is wrong when some modules
     * were dropped by the duplicate-payment guard.
     */
    protected function tenderPayableAmount(TenderPurchase $purchase): float
    {
        $mods = json_decode($purchase->purchased_modules, true) ?: [];
        return (float) array_sum(array_column($mods, 'cost'));
    }

    protected function completePurchase(int $purchaseId, ?string $gatewayRef = null): TenderPurchase
    {
        $purchase = TenderPurchase::findOrFail($purchaseId);
        $purchase->payment_status = 'Completed';
        $purchase->paid_at        = now();
        if (!empty($gatewayRef)) {
            $purchase->payment_reference = $gatewayRef;
        }
        $purchase->save();

        try {
            $this->generateTenderInvoice($purchase);
        } catch (\Exception $e) {
            Log::error('[Tender] Invoice generation failed on payment completion', [
                'order' => $purchase->order_number,
                'error' => $e->getMessage(),
            ]);
        }

        try {
            $lang   = $this->getLang();
            $bs     = $lang->basic_setting;
            $tender = Tender::find($purchase->tender_id);

            SendAdminMail::dispatch([
                'toMail'        => $purchase->email,
                'toName'        => $purchase->first_name,
                'customer_name' => $purchase->first_name,
                'tender_name'   => $tender ? $tender->title : 'Tender Document',
                'order_number'  => $purchase->order_number,
                'website_title' => $bs->website_title,
                'templateType'  => 'tender_purchase',
                'type'          => 'tenderPurchase',
            ]);
        } catch (\Exception $e) {
            Log::error('[Tender] Email failed on payment completion', [
                'order' => $purchase->order_number,
                'error' => $e->getMessage(),
            ]);
        }

        try {
            $downloadUrl = $this->dispatchTenderDownloadLink($purchase);
            // Hand the link to the purchase-complete page so the download can start automatically
            session()->flash('tender_download_url', $downloadUrl);
        } catch (\Exception $e) {
            Log::error('[Tender] Download link dispatch failed on payment completion', [
                'order' => $purchase->order_number,
                'error' => $e->getMessage(),
            ]);
        }

        return $purchase;
    }

    /**
     * Issue a secure download token and email the buyer their download link with the
     * invoice (payment receipt) attached. Mirrors the FindMyFiles token scheme so the
     * same /find-my-files/download route validates the link.
     */
    protected function dispatchTenderDownloadLink(TenderPurchase $purchase): string
    {
        $ttlHours     = 24; // matches FindMyFilesController::TOKEN_TTL_HOURS
        // Admin-configurable open-limit (admin/tender/settings), falls back to 3.
        $maxDownloads = (int) optional(BasicExtra::first())->tender_max_downloads;
        if ($maxDownloads < 1) {
            $maxDownloads = 3;
        }

        $emailHash = hash('sha256', strtolower(trim($purchase->email)));

        // Revoke any previous active tokens for this order
        SecureToken::where('order_id', $purchase->order_number)
            ->where('status', 'active')
            ->update(['status' => 'revoked']);

        // Generate & store signed token (same payload shape as FindMyFilesController)
        $rawToken = hash_hmac('sha256', implode('|', [
            $purchase->order_number,
            $emailHash,
            now()->timestamp,
            Str::random(16),
        ]), config('app.key'));

        SecureToken::create([
            'order_id'       => $purchase->order_number,
            'email_hash'     => $emailHash,
            'token_hash'     => hash('sha256', $rawToken),
            'issued_at'      => now(),
            'expires_at'     => now()->addHours($ttlHours),
            'max_downloads'  => $maxDownloads,
            'download_count' => 0,
            'status'         => 'active',
        ]);

        $downloadUrl = route('find_my_files.download', ['t' => $rawToken]);

        $lang = $this->getLang();
        $bs   = $lang->basic_setting;

        $mail = [
            'toMail'        => $purchase->email,
            'toName'        => $purchase->first_name,
            'customer_name' => $purchase->first_name,
            'order_number'  => $purchase->order_number,
            'download_url'  => $downloadUrl,
            'expires_at'    => now()->addHours($ttlHours)->format('d M Y, H:i'),
            'max_downloads' => $maxDownloads,
            'website_title' => $bs->website_title,
            'templateType'  => 'tender_download_link',
            'type'          => 'tenderDownloadLink',
        ];

        // Attach the ICA invoice PDF as the payment receipt.
        // Regenerate it if it is missing so the customer always gets the ICA receipt.
        $invoicePath = $purchase->invoice
            ? storage_path('app/invoices/tender/' . $purchase->invoice)
            : null;

        if (!$invoicePath || !file_exists($invoicePath)) {
            try {
                $invoicePath = $this->generateTenderInvoice($purchase);
            } catch (\Exception $e) {
                Log::error('[Tender] Receipt PDF regeneration failed for download email', [
                    'order' => $purchase->order_number,
                    'error' => $e->getMessage(),
                ]);
                $invoicePath = null;
            }
        }

        if ($invoicePath && file_exists($invoicePath)) {
            $mail['attachment']     = $invoicePath;
            $mail['attachmentName'] = 'Receipt-' . $purchase->order_number . '.pdf';
        }

        SendAdminMail::dispatch($mail);

        // Hand both URLs to the purchase-complete page: the stream URL drives the
        // automatic download (no download-count consumed), the landing URL is the manual link.
        session()->flash('tender_stream_url', route('find_my_files.stream', ['t' => $rawToken]));

        return $downloadUrl;
    }

    protected function generateTenderInvoice(TenderPurchase $purchase): string
    {
        $lang    = $this->getLang();
        $bse     = $lang->basic_extra ?? BasicExtra::first();
        $bs      = $lang->basic_setting;

        $logoSrc = null;
        if (!empty($bs->logo)) {
            foreach ([
                storage_path('app/public/front/img/' . $bs->logo),
                base_path(FRONT_IMG_PUBLIC_DIR . $bs->logo),
                base_path(FRONT_IMG_DIR . $bs->logo),
            ] as $path) {
                if (file_exists($path)) {
                    $ext     = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                    $mime    = in_array($ext, ['jpg', 'jpeg']) ? 'image/jpeg' : 'image/' . $ext;
                    $logoSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
                    break;
                }
            }
        }

        $purchase->load('tender');
        $fileName  = $purchase->order_number . '.pdf';
        $directory = storage_path('app/invoices/tender/');
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        PDF::loadView('pdf.tender', [
            'order'   => $purchase,
            'bse'     => $bse,
            'bs'      => $bs,
            'logoSrc' => $logoSrc,
        ])->setPaper('a4', 'portrait')->save($directory . $fileName);

        $purchase->update(['invoice' => $fileName]);

        return $directory . $fileName;
    }

    protected function redirectToComplete(TenderPurchase $purchase)
    {
        return redirect()->route('tender.purchase.complete')
            ->with('fmf_purchase_id', $purchase->id);
    }

    /**
     * Issue (or reissue) a signed resume token for a still-Pending purchase and
     * email the buyer a link to pick the SAME order back up — same modules, same
     * amount, no re-typing their details. Only the hash is persisted; the raw
     * token lives solely in the emailed URL (mirrors SecureToken's download
     * links). Safe to call repeatedly — each call overwrites the previous hash,
     * silently invalidating any earlier resume email for this order.
     */
    protected function handleFailedPayment(?TenderPurchase $purchase): void
    {
        if (!$purchase || $purchase->payment_status === 'Completed') {
            return;
        }

        try {
            $rawToken = hash_hmac('sha256', implode('|', [
                $purchase->id,
                $purchase->order_number,
                now()->timestamp,
                Str::random(16),
            ]), config('app.key'));

            $purchase->resume_token_hash = hash('sha256', $rawToken);
            $purchase->save();

            $lang   = $this->getLang();
            $bs     = $lang->basic_setting;
            $tender = Tender::find($purchase->tender_id);

            SendAdminMail::dispatch([
                'toMail'        => $purchase->email,
                'toName'        => $purchase->first_name,
                'customer_name' => $purchase->first_name,
                'tender_name'   => $tender ? $tender->title : 'Tender Document',
                'order_number'  => $purchase->order_number,
                'resume_url'    => route('tender.purchase.resume', ['token' => $rawToken]),
                'website_title' => $bs->website_title,
                'templateType'  => 'tender_payment_incomplete',
                'type'          => 'tenderPaymentIncomplete',
            ]);
        } catch (\Exception $e) {
            Log::error('[Tender] Incomplete-payment email failed', [
                'order' => $purchase->order_number,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
