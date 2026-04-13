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
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

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

        $data['featured_tenders'] = Tender::where('language_id', $currentLang->id)
            ->where('is_featured', 1)
            ->orderBy('id', 'desc')
            ->get();

        $data['tender_categories'] = TenderCategory::where('language_id', $currentLang->id)
            ->where('status', 1)
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

        $data['bse']         = $currentLang->basic_extra;
        $data['currentLang'] = $currentLang;
        $data['version']     = $this->getVersionData($currentLang);

        return view('front.tender.tenders', $data);
    }

    public function tenderDetails($id)
    {
        $currentLang = $this->getCurrentLang();
        $bex         = BasicExtra::first();

        if ($bex->is_tender == 0) {
            return back();
        }

        $data['tender'] = Tender::where('language_id', $currentLang->id)
            ->where('id', $id)
            ->firstOrFail();

        $tender = $data['tender'];

        $data['modules']         = TenderModule::where('tender_id', $tender->id)
            ->where('status', 1)
            ->with('sections')
            ->get();

        $data['paymentGateways'] = PaymentGateway::where('status', 1)
            ->orderBy('name', 'asc')
            ->get();

        $data['offlineGateways'] = OfflineGateway::where('language_id', $currentLang->id)
            ->where('course_checkout_status', 1)
            ->orderBy('serial_number', 'asc')
            ->get();

        $data['bse']         = $currentLang->basic_extra;
        $data['currentLang'] = $currentLang;
        $data['version']     = $this->getVersionData($currentLang);

        return view('front.tender.tender_details', $data);
    }

    public function purchase(Request $request)
    {
        $request->validate([
            'tender_id'    => 'required|exists:tenders,id',
            'first_name'   => 'required|string|max:100',
            'last_name'    => 'required|string|max:100',
            'email'        => 'required|email|max:150',
            'phone_number' => 'required|string|max:30',
            'country'      => 'required|string|max:100',
            'gateway'      => 'required',
        ]);

        $bse = BasicExtra::first();

        $purchase                = new TenderPurchase;
        $purchase->tender_id     = $request->tender_id;
        $purchase->user_id       = Auth::check() ? Auth::id() : null;
        $purchase->order_number  = strtoupper(Str::random(10));
        $purchase->first_name    = $request->first_name;
        $purchase->last_name     = $request->last_name;
        $purchase->email         = $request->email;
        $purchase->phone_number  = $request->phone_number;
        $purchase->country       = $request->country;
        $purchase->city          = $request->city ?? '';
        $purchase->currency_code = $bse->base_currency_text;
        $purchase->payment_method = $request->gateway;
        $purchase->gateway_type  = $request->gateway_type ?? 'offline';
        $purchase->payment_status = 'Pending';

        if ($request->hasFile('receipt')) {
            $file     = $request->file('receipt');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('assets/front/receipt'), $filename);
            $purchase->receipt = $filename;
        }

        $purchase->save();

        return redirect()->route('tender.purchase.complete')
            ->with('fmf_purchase_id', $purchase->id);
    }

    public function purchaseComplete()
    {
        $currentLang = $this->getCurrentLang();

        $purchase = null;
        if (session()->has('fmf_purchase_id')) {
            $purchase = TenderPurchase::with('tender')->find(session('fmf_purchase_id'));
        }

        $data['purchase']    = $purchase;
        $data['version']     = $this->getVersionData($currentLang);
        $data['bse']         = $currentLang->basic_extra;
        $data['currentLang'] = $currentLang;

        return view('front.tender.purchase_complete', $data);
    }
}
