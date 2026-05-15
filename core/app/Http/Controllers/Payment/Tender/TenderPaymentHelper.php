<?php

namespace App\Http\Controllers\Payment\Tender;

use App\BasicExtra;
use App\Http\Helpers\KreativMailer;
use App\Language;
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
        if (session()->has('lang')) {
            return Language::where('code', session()->get('lang'))->first()
                ?? Language::where('is_default', 1)->first();
        }
        return Language::where('is_default', 1)->first();
    }

    protected function getVersion(): string
    {
        $lang = $this->getLang();
        $version = optional($lang->basic_extended)->theme_version ?? 'default';
        return $version === 'dark' ? 'default' : $version;
    }

    protected function createPendingPurchase(Request $request, string $gateway): TenderPurchase
    {
        $bse = $this->getLang()->basic_extra ?? BasicExtra::first();

        $selectedIds = array_filter(array_map('intval', (array) $request->input('selected_module_ids', [])));
        $moduleQuery = TenderModule::where('tender_id', $request->tender_id);
        if (!empty($selectedIds)) {
            $moduleQuery->whereIn('id', $selectedIds);
        }
        $modules = $moduleQuery->get(['id', 'name', 'cost']);

        $purchase                    = new TenderPurchase;
        $purchase->tender_id         = $request->tender_id;
        $purchase->user_id           = Auth::check() ? Auth::id() : null;
        $purchase->order_number      = strtoupper(Str::random(10));
        $purchase->first_name        = $request->first_name;
        $purchase->last_name         = $request->last_name;
        $purchase->email             = $request->email;
        $purchase->phone_number      = $request->phone_number ?? '';
        $purchase->country           = $request->country ?? '';
        $purchase->city              = $request->city ?? '';
        $purchase->company_name      = $request->company_name ?? null;
        $purchase->company_address   = $request->company_address ?? null;
        $purchase->currency_code     = $bse->base_currency_text;
        $purchase->payment_method    = $gateway;
        $purchase->gateway_type      = 'online';
        $purchase->payment_status    = 'Pending';
        $purchase->purchased_modules = $modules->map(fn($m) => [
            'name' => $m->name,
            'cost' => (float) $m->cost,
        ])->values()->toJson();

        $purchase->save();
        return $purchase;
    }

    protected function completePurchase(int $purchaseId): TenderPurchase
    {
        $purchase = TenderPurchase::findOrFail($purchaseId);
        $purchase->payment_status = 'Completed';
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

            $mailer = new KreativMailer;
            $mailer->mailFromAdmin([
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

        return $purchase;
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
                base_path('public/assets/front/img/' . $bs->logo),
                base_path('../assets/front/img/' . $bs->logo),
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
}
