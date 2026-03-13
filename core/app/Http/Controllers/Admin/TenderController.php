<?php

namespace App\Http\Controllers\Admin;

use App\BasicExtra;
use App\Tender;
use App\TenderCategory;
use App\TenderPurchase;
use App\Exports\TenderEnrollExport;
use App\Http\Controllers\Controller;
use App\Language;
use App\OfflineGateway;
use App\PaymentGateway;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class TenderController extends Controller
{
    private function countries()
    {
        return [
            'Afghanistan', 'Albania', 'Algeria', 'Andorra', 'Angola', 'Argentina', 'Armenia', 'Australia',
            'Austria', 'Azerbaijan', 'Bahamas', 'Bahrain', 'Bangladesh', 'Belarus', 'Belgium', 'Belize',
            'Benin', 'Bhutan', 'Bolivia', 'Bosnia and Herzegovina', 'Botswana', 'Brazil', 'Brunei',
            'Bulgaria', 'Burkina Faso', 'Burundi', 'Cambodia', 'Cameroon', 'Canada', 'Cape Verde',
            'Central African Republic', 'Chad', 'Chile', 'China', 'Colombia', 'Comoros', 'Congo',
            'Costa Rica', "Côte d'Ivoire", 'Croatia', 'Cuba', 'Cyprus', 'Czech Republic', 'Denmark',
            'Djibouti', 'Dominican Republic', 'DR Congo', 'Ecuador', 'Egypt', 'El Salvador',
            'Equatorial Guinea', 'Eritrea', 'Estonia', 'Eswatini', 'Ethiopia', 'Fiji', 'Finland',
            'France', 'Gabon', 'Gambia', 'Georgia', 'Germany', 'Ghana', 'Greece', 'Guatemala', 'Guinea',
            'Guinea-Bissau', 'Guyana', 'Haiti', 'Honduras', 'Hungary', 'Iceland', 'India', 'Indonesia',
            'Iran', 'Iraq', 'Ireland', 'Israel', 'Italy', 'Jamaica', 'Japan', 'Jordan', 'Kazakhstan',
            'Kenya', 'Kosovo', 'Kuwait', 'Kyrgyzstan', 'Laos', 'Latvia', 'Lebanon', 'Lesotho', 'Liberia',
            'Libya', 'Liechtenstein', 'Lithuania', 'Luxembourg', 'Madagascar', 'Malawi', 'Malaysia',
            'Maldives', 'Mali', 'Malta', 'Mauritania', 'Mauritius', 'Mexico', 'Moldova', 'Monaco',
            'Mongolia', 'Montenegro', 'Morocco', 'Mozambique', 'Myanmar', 'Namibia', 'Nepal',
            'Netherlands', 'New Zealand', 'Nicaragua', 'Niger', 'Nigeria', 'North Macedonia', 'Norway',
            'Oman', 'Pakistan', 'Panama', 'Papua New Guinea', 'Paraguay', 'Peru', 'Philippines', 'Poland',
            'Portugal', 'Qatar', 'Romania', 'Russia', 'Rwanda', 'Saudi Arabia', 'Senegal', 'Serbia',
            'Sierra Leone', 'Singapore', 'Slovakia', 'Slovenia', 'Somalia', 'South Africa', 'South Sudan',
            'Spain', 'Sri Lanka', 'Sudan', 'Suriname', 'Sweden', 'Switzerland', 'Syria', 'Taiwan',
            'Tajikistan', 'Tanzania', 'Thailand', 'Timor-Leste', 'Togo', 'Trinidad and Tobago', 'Tunisia',
            'Turkey', 'Turkmenistan', 'Uganda', 'Ukraine', 'United Arab Emirates', 'United Kingdom',
            'United States', 'Uruguay', 'Uzbekistan', 'Venezuela', 'Vietnam', 'Yemen', 'Zambia', 'Zimbabwe',
        ];
    }

    public function index(Request $request)
    {
        $language = Language::where('code', $request->language)->first();
        $language_id = $language->id;

        $tenders = Tender::where('language_id', $language_id)
            ->orderBy('id', 'desc')
            ->get();

        $countries = $this->countries();

        return view('admin.tender.tender.index', compact('tenders', 'countries'));
    }

    public function create(Request $request)
    {
        $countries = $this->countries();

        $language = Language::where('code', $request->language)->first();
        $tender_categories = $language
            ? TenderCategory::where('language_id', $language->id)->where('status', 1)->orderBy('serial_number')->get()
            : collect([]);

        return view('admin.tender.tender.create', compact('countries', 'tender_categories', 'language'));
    }

    public function getCategories($langId)
    {
        $tender_categories = TenderCategory::where('language_id', $langId)
            ->where('status', 1)
            ->get();

        return $tender_categories;
    }

    public function store(Request $request)
    {
        $slug = slug_create($request->title);
        $image    = $request->tender_image;
        $expImage = $request->expert_image;
        $allowedExts = ['jpg', 'png', 'jpeg', 'svg'];
        $extImage    = pathinfo($image, PATHINFO_EXTENSION);
        $extExpImage = pathinfo($expImage, PATHINFO_EXTENSION);

        $rules = [
            'language_id'         => 'required',
            'tender_category_id'  => 'required',
            'country'             => 'required',
            'tender_code'         => 'required',
            'title'               => [
                'required',
                'max:255',
                function ($attribute, $value, $fail) use ($slug) {
                    $exists = Tender::whereRaw('LOWER(slug) = ?', [strtolower($slug)])->exists();
                    if ($exists) {
                        $fail('The title field must be unique.');
                    }
                }
            ],
            'submission_deadline' => 'required|after_or_equal:today',
            'overview'            => 'required',
            'expert_name'         => 'required',
            'expert_position'     => 'required',
            'expert_details'      => 'required',
            'expert_whatsapp'     => 'required',
            'expert_phone'        => 'required',
            'tender_image'        => 'required',
            'expert_image'        => 'required',
        ];

        if ($request->filled('tender_image')) {
            $rules['tender_image'] = [
                function ($attribute, $value, $fail) use ($extImage, $allowedExts) {
                    if (!in_array($extImage, $allowedExts)) {
                        $fail('Only jpg, png, jpeg, svg image is allowed.');
                    }
                }
            ];
        }

        if ($request->filled('expert_image')) {
            $rules['expert_image'] = [
                function ($attribute, $value, $fail) use ($extExpImage, $allowedExts) {
                    if (!in_array($extExpImage, $allowedExts)) {
                        $fail('Only jpg, png, jpeg, svg image is allowed.');
                    }
                }
            ];
        }

        if ($request->filled('video_link')) {
            $rules['video_link'] = [
                function ($attribute, $value, $fail) {
                    $pattern = '/^(https?:\/\/)?(www\.)?(youtube\.com\/(watch\?v=|embed\/|v\/|shorts\/)|youtu\.be\/)[a-zA-Z0-9_\-]{5,}/i';
                    if (!preg_match($pattern, $value)) {
                        $fail('The video link must be a valid YouTube URL.');
                    }
                }
            ];
        }

        $messages = [
            'language_id.required'              => 'The language field is required.',
            'tender_category_id.required'       => 'The category field is required.',
            'submission_deadline.after_or_equal' => 'The submission deadline cannot be a past date.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $validator->getMessageBag()->add('error', 'true');
            return response()->json($validator->errors());
        }

        $tender = new Tender;
        $tender->language_id        = $request->language_id;
        $tender->tender_category_id = $request->tender_category_id;
        $tender->country            = $request->country;
        $tender->tender_code        = $request->tender_code;
        $tender->title              = $request->title;
        $tender->slug               = $slug;
        $tender->submission_deadline = $request->submission_deadline;
        $tender->current_price      = $request->current_price;
        $tender->previous_price     = $request->previous_price;
        $tender->summary            = $request->summary;

        if ($request->filled('tender_image')) {
            $filename = uniqid() . '.' . $extImage;
            $dir = 'assets/front/img/tenders/';
            @mkdir($dir, 0775, true);
            @copy($image, $dir . $filename);
            $tender->tender_image = $filename;
        }

        if ($request->filled('expert_image')) {
            $filename = uniqid() . '.' . $extExpImage;
            $dir = 'assets/front/img/tender_experts/';
            @mkdir($dir, 0775, true);
            @copy($expImage, $dir . $filename);
            $tender->expert_image = $filename;
        }

        $link = $request->video_link;
        if (!empty($link) && strpos($link, '&') !== false) {
            $tender->video_link = substr($link, 0, strpos($link, '&'));
        } else {
            $tender->video_link = $link;
        }

        $tender->overview        = $request->overview;
        $tender->expert_name     = $request->expert_name;
        $tender->expert_position = $request->expert_position;
        $tender->expert_details  = $request->expert_details;
        $tender->expert_whatsapp = $request->expert_whatsapp;
        $tender->expert_phone    = $request->expert_phone;
        $tender->save();

        Session::flash('success', 'Tender Added Successfully');

        return 'success';
    }

    public function edit($id)
    {
        $tender = Tender::findOrFail($id);
        $tender_categories = TenderCategory::where('language_id', $tender->language_id)
            ->where('status', 1)
            ->orderBy('serial_number', 'asc')
            ->get();

        $countries = $this->countries();

        return view('admin.tender.tender.edit', compact('tender', 'tender_categories', 'countries'));
    }

    public function update(Request $request)
    {
        $tenderId = $request->tender_id;
        $tender   = Tender::findOrFail($tenderId);
        $slug     = slug_create($request->title);

        $image    = $request->tender_image;
        $expImage = $request->expert_image;
        $allowedExts = ['jpg', 'png', 'jpeg', 'svg'];
        $extImage    = pathinfo($image, PATHINFO_EXTENSION);
        $extExpImage = pathinfo($expImage, PATHINFO_EXTENSION);

        $rules = [
            'country'             => 'required',
            'tender_code'         => 'required',
            'title'               => 'required|max:255',
            'submission_deadline' => 'required|after_or_equal:today',
            'overview'            => 'required',
            'expert_name'         => 'required',
            'expert_position'     => 'required',
            'expert_details'      => 'required',
            'expert_whatsapp'     => 'required',
            'expert_phone'        => 'required',
        ];

        if ($request->filled('tender_image')) {
            $rules['tender_image'] = [
                function ($attribute, $value, $fail) use ($extImage, $allowedExts) {
                    if (!in_array($extImage, $allowedExts)) {
                        $fail('Only jpg, png, jpeg, svg image is allowed.');
                    }
                }
            ];
        }

        if ($request->filled('expert_image')) {
            $rules['expert_image'] = [
                function ($attribute, $value, $fail) use ($extExpImage, $allowedExts) {
                    if (!in_array($extExpImage, $allowedExts)) {
                        $fail('Only jpg, png, jpeg, svg image is allowed.');
                    }
                }
            ];
        }

        if ($request->filled('video_link')) {
            $rules['video_link'] = [
                function ($attribute, $value, $fail) {
                    $pattern = '/^(https?:\/\/)?(www\.)?(youtube\.com\/(watch\?v=|embed\/|v\/|shorts\/)|youtu\.be\/)[a-zA-Z0-9_\-]{5,}/i';
                    if (!preg_match($pattern, $value)) {
                        $fail('The video link must be a valid YouTube URL.');
                    }
                }
            ];
        }

        $messages = [
            'submission_deadline.after_or_equal' => 'The submission deadline cannot be a past date.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $validator->getMessageBag()->add('error', 'true');
            return response()->json($validator->errors());
        }

        $tender->country            = $request->country;
        $tender->tender_code        = $request->tender_code;
        $tender->title              = $request->title;
        $tender->slug               = $slug;
        $tender->submission_deadline = $request->submission_deadline;
        $tender->current_price      = $request->current_price;
        $tender->previous_price     = $request->previous_price;
        $tender->summary            = $request->summary;

        if ($request->filled('tender_image')) {
            $filename = uniqid() . '.' . $extImage;
            $dir = 'assets/front/img/tenders/';
            @mkdir($dir, 0775, true);
            @copy($image, $dir . $filename);
            $tender->tender_image = $filename;
        }

        if ($request->filled('expert_image')) {
            $filename = uniqid() . '.' . $extExpImage;
            $dir = 'assets/front/img/tender_experts/';
            @mkdir($dir, 0775, true);
            @copy($expImage, $dir . $filename);
            $tender->expert_image = $filename;
        }

        $link = $request->video_link;
        if (!empty($link) && strpos($link, '&') !== false) {
            $tender->video_link = substr($link, 0, strpos($link, '&'));
        } else {
            $tender->video_link = $link;
        }

        $tender->overview        = $request->overview;
        $tender->expert_name     = $request->expert_name;
        $tender->expert_position = $request->expert_position;
        $tender->expert_details  = $request->expert_details;
        $tender->expert_whatsapp = $request->expert_whatsapp;
        $tender->expert_phone    = $request->expert_phone;
        $tender->save();

        Session::flash('success', 'Tender Updated Successfully');

        return 'success';
    }

    public function delete(Request $request)
    {
        $tender = Tender::findOrFail($request->tender_id);

        if (!empty($tender->tender_image)) {
            @unlink('assets/front/img/tenders/' . $tender->tender_image);
        }
        if (!empty($tender->expert_image)) {
            @unlink('assets/front/img/tender_experts/' . $tender->expert_image);
        }

        $tender->delete();

        Session::flash('success', 'Tender Deleted Successfully');

        return back();
    }

    public function bulkDelete(Request $request)
    {
        foreach ($request->ids as $id) {
            $tender = Tender::findOrFail($id);

            if (!empty($tender->tender_image)) {
                @unlink('assets/front/img/tenders/' . $tender->tender_image);
            }
            if (!empty($tender->expert_image)) {
                @unlink('assets/front/img/tender_experts/' . $tender->expert_image);
            }

            $tender->delete();
        }

        Session::flash('success', 'Tenders Deleted Successfully');

        return 'success';
    }

    public function featured(Request $request)
    {
        $tender = Tender::findOrFail($request->tender_id);
        $tender->is_featured = $request->is_featured;
        $tender->save();

        return 'success';
    }

    public function purchaseLog(Request $request)
    {
        $orderNum = $request->order_number;
        $langCode = $request->language;

        $purchases = TenderPurchase::with('tender')
            ->when($orderNum, fn($q) => $q->where('order_number', $orderNum))
            ->when($langCode, function ($q) use ($langCode) {
                $language = Language::where('code', $langCode)->first();
                if ($language) {
                    $tenderIds = Tender::where('language_id', $language->id)->pluck('id');
                    $q->whereIn('tender_id', $tenderIds);
                }
            })
            ->orderBy('id', 'DESC')
            ->paginate(10);

        return view('admin.tender.tender.purchase', compact('purchases'));
    }

    public function purchasePaymentStatus(Request $request)
    {
        $purchase = TenderPurchase::findOrFail($request->purchase_id);
        $purchase->payment_status = $request->payment_status;
        $purchase->save();

        Session::flash('success', 'Payment status changed successfully!');
        return back();
    }

    public function purchaseDelete(Request $request)
    {
        $purchase = TenderPurchase::findOrFail($request->purchase_id);
        if (!empty($purchase->receipt)) {
            @unlink('assets/front/receipt/' . $purchase->receipt);
        }
        $purchase->delete();

        Session::flash('success', 'Deleted successfully!');
        return back();
    }

    public function purchaseBulkOrderDelete(Request $request)
    {
        foreach ($request->ids as $id) {
            $purchase = TenderPurchase::findOrFail($id);
            if (!empty($purchase->receipt)) {
                @unlink('assets/front/receipt/' . $purchase->receipt);
            }
            $purchase->delete();
        }

        Session::flash('success', 'Deleted successfully!');
        return 'success';
    }

    public function settings()
    {
        $abex = BasicExtra::first();
        return view('admin.tender.settings', compact('abex'));
    }

    public function updateSettings(Request $request)
    {
        $bexs = BasicExtra::all();
        foreach ($bexs as $bex) {
            $bex->is_tender = $request->is_tender;
            $bex->save();
        }

        Session::flash('success', 'Tender Settings Updated Successfully');

        return back();
    }

    public function report(Request $request)
    {
        $fromDate      = $request->from_date;
        $toDate        = $request->to_date;
        $paymentStatus = $request->payment_status;
        $paymentMethod = $request->payment_method;

        if (!empty($fromDate) && !empty($toDate)) {
            $query = TenderPurchase::with(['tender', 'user'])
                ->when($fromDate,      fn($q) => $q->whereDate('created_at', '>=', Carbon::parse($fromDate)))
                ->when($toDate,        fn($q) => $q->whereDate('created_at', '<=', Carbon::parse($toDate)))
                ->when($paymentMethod, fn($q) => $q->where('payment_method', $paymentMethod))
                ->when($paymentStatus, fn($q) => $q->where('payment_status', $paymentStatus))
                ->orderBy('id', 'DESC');

            Session::put('tender_enroll_report', $query->get());
            $enrolls = $query->paginate(10);
        } else {
            Session::put('tender_enroll_report', []);
            $enrolls = collect([]);
        }

        $onPms  = PaymentGateway::where('status', 1)->get();
        $offPms = OfflineGateway::all();

        return view('admin.tender.tender.report', compact('enrolls', 'onPms', 'offPms'));
    }

    public function exportReport()
    {
        $enrolls = Session::get('tender_enroll_report');

        if (empty($enrolls) || count($enrolls) == 0) {
            Session::flash('warning', 'There are no enrollments to export.');
            return back();
        }

        return Excel::download(new TenderEnrollExport(collect($enrolls)), 'tender-enrollments.csv');
    }
}
