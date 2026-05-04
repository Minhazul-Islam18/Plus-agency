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
use PHPMailer\PHPMailer\PHPMailer;

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
        $purchase->city            = $request->city ?? '';
        $purchase->company_name    = $request->company_name ?? null;
        $purchase->company_address = $request->company_address ?? null;
        $purchase->currency_code   = $bse->base_currency_text;
        $purchase->payment_method = $request->gateway;
        $purchase->gateway_type  = $request->gateway_type ?? 'offline';
        $purchase->payment_status = 'Pending';

        if ($request->hasFile('receipt')) {
            $file     = $request->file('receipt');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move('assets/front/receipt', $filename);
            $purchase->receipt = $filename;
        }

        if ($request->filled('payment_reference')) {
            $purchase->payment_reference = strtoupper(trim($request->input('payment_reference')));
        }

        // Save purchased modules (name + cost) as JSON
        // No selection = full tender purchase = all modules
        $selectedIds = array_filter(array_map('intval', (array) $request->input('selected_module_ids', [])));
        $moduleQuery = TenderModule::where('tender_id', $request->tender_id);
        if (!empty($selectedIds)) {
            $moduleQuery->whereIn('id', $selectedIds);
        }
        $modules = $moduleQuery->get(['id', 'name', 'cost']);
        $purchase->purchased_modules = $modules->map(fn($m) => [
            'name' => $m->name,
            'cost' => (float) $m->cost,
        ])->values()->toJson();

        $purchase->save();

        $this->sendOrderReceivedEmail($purchase);

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

    private function sendOrderReceivedEmail(TenderPurchase $purchase): void
    {
        $currentLang = $this->getCurrentLang();
        $be          = $currentLang->basic_extended;
        $fromName    = $be->from_name ?: config('app.name');
        $tender      = Tender::find($purchase->tender_id);

        $body = view('mail.tender_order_received', [
            'purchase'     => $purchase,
            'tenderTitle'  => $tender ? $tender->title : 'Tender Document',
            'fromName'     => $fromName,
        ])->render();

        $mail = new PHPMailer(true);

        try {
            if ($be->is_smtp == 1) {
                $mail->isSMTP();
                $mail->Host       = $be->smtp_host;
                $mail->SMTPAuth   = true;
                $mail->Username   = $be->smtp_username;
                $mail->Password   = $be->smtp_password;
                $mail->SMTPSecure = $be->encryption;
                $mail->Port       = $be->smtp_port;
            }

            $mail->setFrom($be->from_mail, $fromName);
            $mail->addAddress($purchase->email, trim($purchase->first_name . ' ' . $purchase->last_name));
            $mail->isHTML(true);
            $mail->Subject = 'Order Received — ' . $purchase->order_number;
            $mail->Body    = $body;
            $mail->send();
        } catch (\Exception $e) {
            Log::error('[Tender] Order received email failed', [
                'order' => $purchase->order_number,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
