<?php

namespace App\Http\Controllers\Front;

use App\BasicExtra;
use App\Http\Controllers\Controller;
use App\Language;
use App\OfflineGateway;
use App\PaymentGateway;
use App\Tender;
use App\TenderCategory;
use App\TenderModule;
use App\TenderPurchase;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use App\Http\Helpers\KreativMailer;

class TenderController extends Controller
{
    private function getCurrentLang()
    {
        if (session()->has('lang')) {
            return Language::where('code', session()->get('lang'))->first();
        }
        return Language::where('is_default', 1)->first();
    }

    private function getVersionData($currentLang)
    {
        $be      = $currentLang->basic_extended;
        $version = $be->theme_version === 'dark' ? 'default' : $be->theme_version;
        return $version;
    }

    public function tenders(Request $request)
    {
        $currentLang = $this->getCurrentLang();
        $bex         = BasicExtra::first();

        if ($bex->is_tender == 0) {
            return back();
        }

        // Only the columns the listing cards render — never the heavy
        // longText/overview/expert_* fields. Eager-load the category name and
        // module ids so the cards' `->tenderCategory->name` and
        // `->tenderModules->count()` don't fire a query per row (N+1).
        $listCols = [
            'id',
            'language_id',
            'tender_category_id',
            'country',
            'tender_code',
            'title',
            'slug',
            'submission_deadline',
            'current_price',
            'previous_price',
            'tender_image',
        ];
        $listWith = ['tenderCategory:id,name', 'tenderModules:id,tender_id'];

        $data['featured_tenders'] = Tender::where('language_id', $currentLang->id)
            ->where('is_featured', 1)
            ->select($listCols)
            ->with($listWith)
            ->orderBy('id', 'desc')
            ->get();

        $data['tender_categories'] = TenderCategory::where('language_id', $currentLang->id)
            ->where('status', 1)
            ->select(['id', 'name'])
            ->orderBy('id', 'desc')
            ->get();

        $data['tenderCount'] = Tender::where('language_id', $currentLang->id)->count();

        // All unique countries for filtering
        $data['countries'] = Tender::where('language_id', $currentLang->id)
            ->distinct()
            ->pluck('country')
            ->filter()
            ->sort()
            ->values();

        $searchKey  = $request->search;
        $categoryId = $request->category_id;
        $countryFilter = $request->country;
        $checked    = $request->checked_value;
        $minPrice   = $request->minValue;
        $maxPrice   = $request->maxValue;
        $filterKey  = $request->filterValue;

        $data['tenders'] = Tender::where('language_id', $currentLang->id)
            ->select($listCols)
            ->with($listWith)
            ->when($searchKey, fn($q) => $q->where(function ($q) use ($searchKey) {
                $q->where('title', 'like', '%' . $searchKey . '%')
                    ->orWhere('tender_code', 'like', '%' . $searchKey . '%');
            }))
            ->when($categoryId, fn($q) => $q->where('tender_category_id', $categoryId))
            ->when($countryFilter, fn($q) => $q->where('country', $countryFilter))
            ->when($checked, function ($q) use ($checked) {
                if ($checked == 'free') {
                    return $q->whereNull('current_price');
                } elseif ($checked == 'premium') {
                    return $q->whereNotNull('current_price');
                }
            })
            ->when($minPrice, fn($q) => $q->where('current_price', '>=', $minPrice))
            ->when($maxPrice, fn($q) => $q->where('current_price', '<=', $maxPrice))
            ->when($filterKey, function ($q) use ($filterKey) {
                if ($filterKey == 'new') {
                    return $q->orderBy('id', 'desc');
                } elseif ($filterKey == 'old') {
                    return $q->orderBy('id', 'asc');
                } elseif ($filterKey == 'deadline_asc') {
                    return $q->whereNotNull('submission_deadline')->orderBy('submission_deadline', 'asc');
                } elseif ($filterKey == 'high-to-low') {
                    return $q->whereNotNull('current_price')->orderBy('current_price', 'desc');
                } elseif ($filterKey == 'low-to-high') {
                    return $q->whereNotNull('current_price')->orderBy('current_price', 'asc');
                }
            })
            ->when(!$filterKey, fn($q) => $q->orderBy('id', 'desc'))
            ->paginate(9);

        // Price-slider bounds — one aggregate query instead of two run in the view.
        $bounds = Tender::where('language_id', $currentLang->id)
            ->selectRaw('MIN(current_price) AS mn, MAX(current_price) AS mx')
            ->first();
        $data['minPrice'] = (float) ($bounds->mn ?? 0);
        $data['maxPrice'] = (float) ($bounds->mx ?? 0);

        $data['bse']         = $currentLang->basic_extra;
        $data['currentLang'] = $currentLang;
        $data['version']     = $this->getVersionData($currentLang);

        return view('front.tender.tenders', $data);
    }

    public function tenderDetails($slug)
    {
        $currentLang = $this->getCurrentLang();
        $bex         = BasicExtra::first();

        if ($bex->is_tender == 0) {
            return back();
        }

        $data['tender'] = Tender::where('language_id', $currentLang->id)
            ->where('slug', $slug)
            ->firstOrFail();

        $tender = $data['tender'];

        $data['modules']         = TenderModule::where('tender_id', $tender->id)
            ->where('status', 1)
            ->with('sections')
            ->get();

        $data['paymentGateways'] = PaymentGateway::where('status', 1)
            ->whereIn('keyword', ['stripe', 'razorpay', 'moneroo'])
            ->orderBy('name', 'asc')
            ->get();

        $data['offlineGateways'] = OfflineGateway::where('language_id', $currentLang->id)
            ->where('course_checkout_status', 1)
            ->orderBy('serial_number', 'asc')
            ->get();

        // Terms & Conditions page for the current language (Page Type = Terms).
        $termsPage = \App\Page::forType('terms', $currentLang->id);
        $data['termsUrl'] = $termsPage ? route('front.dynamicPage', $termsPage->slug) : null;

        // Checkout country + phone dialling-code pickers (no free typing).
        // Static list, not a query — only the two fields the pickers render.
        $data['countries'] = \App\Http\Helpers\Countries::forCheckout();

        // Related tenders: active (not past deadline) only. Same category first,
        // fall back to latest active others.
        $activeOnly = function ($q) {
            $q->whereNull('submission_deadline')
                ->orWhereDate('submission_deadline', '>=', now()->toDateString());
        };
        $relatedCols = [
            'id',
            'language_id',
            'tender_category_id',
            'country',
            'tender_code',
            'title',
            'slug',
            'submission_deadline',
            'current_price',
            'previous_price',
            'tender_image',
        ];
        $relatedWith = ['tenderCategory:id,name', 'tenderModules:id,tender_id'];

        $related = Tender::where('language_id', $currentLang->id)
            ->where('id', '!=', $tender->id)
            ->where($activeOnly)
            ->when($tender->tender_category_id, fn($q) => $q->where('tender_category_id', $tender->tender_category_id))
            ->select($relatedCols)
            ->with($relatedWith)
            ->orderBy('id', 'desc')
            ->take(8)
            ->get();
        if ($related->isEmpty()) {
            $related = Tender::where('language_id', $currentLang->id)
                ->where('id', '!=', $tender->id)
                ->where($activeOnly)
                ->select($relatedCols)
                ->with($relatedWith)
                ->orderBy('id', 'desc')
                ->take(8)
                ->get();
        }
        $data['relatedTenders'] = $related;

        $data['bse']         = $currentLang->basic_extra;
        $data['currentLang'] = $currentLang;
        $data['version']     = $this->getVersionData($currentLang);

        return view('front.tender.tender_details', $data);
    }

    public function purchase(\App\Http\Requests\Tender\PurchaseRequest $request)
    {
        // Validation handled by PurchaseRequest (buyer fields + receipt MIME).

        // Country code and national number are separate <select>/input; store and
        // match the phone as one E.164 string (e.g. +880171...).
        $fullPhone = $request->phone_code . $request->phone_number;

        // Blacklisted companies (by registration no.) cannot place a new order.
        if (\App\TenderBlacklist::matches($request->company_registration_no)) {
            return back()->with('error', __('This order cannot be processed. Please contact ICA support.'));
        }

        $bse = BasicExtra::first();

        $purchase                = new TenderPurchase;
        $purchase->tender_id     = $request->tender_id;
        $purchase->user_id       = Auth::check() ? Auth::id() : null;
        $purchase->order_number  = strtoupper(Str::random(10));
        $purchase->first_name    = $request->first_name;
        $purchase->last_name     = $request->last_name;
        $purchase->email         = $request->email;
        $purchase->phone_number  = $fullPhone;
        $purchase->country       = $request->country;
        $purchase->city            = $request->city ?? '';
        $purchase->company_name    = $request->company_name ?? null;
        $purchase->company_address = $request->company_address ?? null;
        $purchase->company_registration_no = TenderPurchase::normalizeRegNo($request->company_registration_no);
        $purchase->currency_code   = $bse->base_currency_text;
        $purchase->payment_method = $request->gateway;
        $purchase->gateway_type  = $request->gateway_type ?? 'offline';
        $purchase->payment_status = 'Pending';

        if ($request->hasFile('receipt')) {
            $file = $request->file('receipt');
            // Content-guessed extension, not the client-supplied string.
            $ext      = $file->extension() ?: 'dat';
            $filename = uniqid('receipt_') . '.' . $ext;
            $file->move('assets/front/receipt', $filename);
            $purchase->receipt = $filename;
        }

        if ($request->filled('payment_reference')) {
            $purchase->payment_reference = strtoupper(trim($request->input('payment_reference')));
        }

        // Save purchased modules (name + cost) as JSON. The buyer pays per module, so
        // the selection is required — an empty one is never read as "buy everything".
        // Free modules always ship with the tender, so they are always recorded on the
        // receipt regardless of selection.
        $selectedIds = array_filter(array_map('intval', (array) $request->input('selected_module_ids', [])));
        if (empty($selectedIds)) {
            return back()->with('error', __('Please select at least one module to continue.'))->withInput();
        }

        $allModules = TenderModule::where('tender_id', $request->tender_id)
            ->where('status', 1)
            ->get(['id', 'name', 'cost']);

        $freeModules = $allModules->filter(fn($m) => is_null($m->cost))->values();

        $paidModules = $allModules->filter(fn($m) => !is_null($m->cost))
            ->whereIn('id', $selectedIds)
            ->values();

        // Duplicate-payment guard: drop paid modules this company already owns,
        // keyed solely on company registration number.
        $paidNames = TenderPurchase::paidModuleNamesForReg(
            (int) $request->tender_id,
            $request->company_registration_no
        );
        if (!empty($paidNames)) {
            $paidModules = $paidModules->reject(fn($m) => in_array(trim($m->name), $paidNames, true))->values();
        }

        // Nothing left to pay for → block (free items never require payment).
        if ($paidModules->isEmpty()) {
            return back()->with('error', __('You have already paid for the selected module(s). No further payment is required.'));
        }

        // Persist paid + free; free modules carry cost 0 so the receipt total is unchanged.
        $modules = $paidModules->concat($freeModules);

        $purchase->purchased_modules = $modules->map(fn($m) => [
            'name' => $m->name,
            'cost' => (float) $m->cost,
        ])->values()->toJson();

        $purchase->save();

        $this->sendOrderReceivedEmail($purchase);

        return redirect()->route('tender.purchase.complete')
            ->with('fmf_purchase_id', $purchase->id);
    }

    /**
     * AJAX: given a company registration number + tender, report which paid modules
     * the company already owns, so the checkout can disable them and prevent
     * duplicate payments.
     */
    public function paidModules(Request $request)
    {
        $request->validate([
            'tender_id'               => 'required|exists:tenders,id',
            'company_registration_no' => 'required|string|max:100',
        ]);

        $paidNames = TenderPurchase::paidModuleNamesForReg(
            (int) $request->tender_id,
            $request->company_registration_no
        );

        $modules = TenderModule::where('tender_id', $request->tender_id)
            ->where('status', 1)
            ->get(['id', 'name', 'cost']);

        // Paid module ids (matched by name)
        $paidIds = $modules->filter(fn($m) => in_array(trim($m->name), $paidNames, true))
            ->pluck('id')
            ->values();

        // "All paid" = every payable (non-free) module is already owned
        $payable = $modules->whereNotNull('cost');
        $allPaid = $payable->count() > 0
            && $payable->every(fn($m) => in_array(trim($m->name), $paidNames, true));

        return response()->json([
            'paid_module_ids' => $paidIds,
            'all_paid'        => $allPaid,
        ]);
    }

    public function purchaseComplete()
    {
        $currentLang = $this->getCurrentLang();

        $purchase = null;
        if (session()->has('fmf_purchase_id')) {
            $purchase = TenderPurchase::with('tender')->find(session('fmf_purchase_id'));
        }

        $data['purchase']    = $purchase;
        $data['downloadUrl'] = session('tender_download_url');
        $data['streamUrl']   = session('tender_stream_url');
        $data['version']     = $this->getVersionData($currentLang);
        $data['bse']         = $currentLang->basic_extra;
        $data['currentLang'] = $currentLang;

        return view('front.tender.purchase_complete', $data);
    }

    private function sendOrderReceivedEmail(TenderPurchase $purchase): void
    {
        $currentLang = $this->getCurrentLang();
        $bs          = $currentLang->basic_setting;
        $tender      = Tender::find($purchase->tender_id);

        try {
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
            Log::error('[Tender] Order received email failed', [
                'order' => $purchase->order_number,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
