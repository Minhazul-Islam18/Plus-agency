<?php

namespace App\Http\Controllers\Admin;

use App\BasicExtra;
use App\SecureToken;
use App\Tender;
use App\TenderCategory;
use App\TenderPurchase;
use App\Exports\TenderEnrollExport;
use App\Http\Controllers\Controller;
use App\Http\Helpers\KreativMailer;
use App\Language;
use App\OfflineGateway;
use App\PaymentGateway;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use PHPMailer\PHPMailer\PHPMailer;

class TenderController extends Controller
{
    private const TENDER_SUBDIR = 'tenders/';
    private const EXPERT_SUBDIR = 'tender_experts/';
    private function countries()
    {
        return [
            'Afghanistan',
            'Albania',
            'Algeria',
            'Andorra',
            'Angola',
            'Argentina',
            'Armenia',
            'Australia',
            'Austria',
            'Azerbaijan',
            'Bahamas',
            'Bahrain',
            'Bangladesh',
            'Belarus',
            'Belgium',
            'Belize',
            'Benin',
            'Bhutan',
            'Bolivia',
            'Bosnia and Herzegovina',
            'Botswana',
            'Brazil',
            'Brunei',
            'Bulgaria',
            'Burkina Faso',
            'Burundi',
            'Cambodia',
            'Cameroon',
            'Canada',
            'Cape Verde',
            'Central African Republic',
            'Chad',
            'Chile',
            'China',
            'Colombia',
            'Comoros',
            'Congo',
            'Costa Rica',
            "Côte d'Ivoire",
            'Croatia',
            'Cuba',
            'Cyprus',
            'Czech Republic',
            'Denmark',
            'Djibouti',
            'Dominican Republic',
            'DR Congo',
            'Ecuador',
            'Egypt',
            'El Salvador',
            'Equatorial Guinea',
            'Eritrea',
            'Estonia',
            'Eswatini',
            'Ethiopia',
            'Fiji',
            'Finland',
            'France',
            'Gabon',
            'Gambia',
            'Georgia',
            'Germany',
            'Ghana',
            'Greece',
            'Guatemala',
            'Guinea',
            'Guinea-Bissau',
            'Guyana',
            'Haiti',
            'Honduras',
            'Hungary',
            'Iceland',
            'India',
            'Indonesia',
            'Iran',
            'Iraq',
            'Ireland',
            'Israel',
            'Italy',
            'Jamaica',
            'Japan',
            'Jordan',
            'Kazakhstan',
            'Kenya',
            'Kosovo',
            'Kuwait',
            'Kyrgyzstan',
            'Laos',
            'Latvia',
            'Lebanon',
            'Lesotho',
            'Liberia',
            'Libya',
            'Liechtenstein',
            'Lithuania',
            'Luxembourg',
            'Madagascar',
            'Malawi',
            'Malaysia',
            'Maldives',
            'Mali',
            'Malta',
            'Mauritania',
            'Mauritius',
            'Mexico',
            'Moldova',
            'Monaco',
            'Mongolia',
            'Montenegro',
            'Morocco',
            'Mozambique',
            'Myanmar',
            'Namibia',
            'Nepal',
            'Netherlands',
            'New Zealand',
            'Nicaragua',
            'Niger',
            'Nigeria',
            'North Macedonia',
            'Norway',
            'Oman',
            'Pakistan',
            'Panama',
            'Papua New Guinea',
            'Paraguay',
            'Peru',
            'Philippines',
            'Poland',
            'Portugal',
            'Qatar',
            'Romania',
            'Russia',
            'Rwanda',
            'Saudi Arabia',
            'Senegal',
            'Serbia',
            'Sierra Leone',
            'Singapore',
            'Slovakia',
            'Slovenia',
            'Somalia',
            'South Africa',
            'South Sudan',
            'Spain',
            'Sri Lanka',
            'Sudan',
            'Suriname',
            'Sweden',
            'Switzerland',
            'Syria',
            'Taiwan',
            'Tajikistan',
            'Tanzania',
            'Thailand',
            'Timor-Leste',
            'Togo',
            'Trinidad and Tobago',
            'Tunisia',
            'Turkey',
            'Turkmenistan',
            'Uganda',
            'Ukraine',
            'United Arab Emirates',
            'United Kingdom',
            'United States',
            'Uruguay',
            'Uzbekistan',
            'Venezuela',
            'Vietnam',
            'Yemen',
            'Zambia',
            'Zimbabwe',
        ];
    }

    public function index(Request $request)
    {
        $language = Language::where('code', $request->language)->first();
        $language_id = $language->id;

        // Client-side DataTable renders the full set, so no server paging here —
        // but only fetch the columns the list + module modal actually use, and
        // eager-load modules to avoid a query per row (N+1).
        $tenders = Tender::where('language_id', $language_id)
            ->select(['id', 'language_id', 'country', 'title', 'submission_deadline', 'tender_image', 'is_featured', 'status'])
            ->with('tenderModules:id,tender_id,name,cost')
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
            'expert_email'        => 'required|email',
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
        $tender->previous_price     = $request->previous_price;
        $tender->summary            = $request->summary;

        if ($request->filled('tender_image')) {
            $filename = uniqid() . '.' . $extImage;
            $dir = FRONT_IMG_PATH . self::TENDER_SUBDIR;
            @mkdir($dir, 0775, true);
            @copy($image, $dir . $filename);
            $tender->tender_image = $filename;
        }

        if ($request->filled('expert_image')) {
            $filename = uniqid() . '.' . $extExpImage;
            $dir = FRONT_IMG_PATH . self::EXPERT_SUBDIR;
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
        $tender->expert_email    = $request->expert_email;
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
            'expert_email'        => 'required|email',
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
        $tender->previous_price     = $request->previous_price;
        $tender->summary            = $request->summary;

        if ($request->filled('tender_image')) {
            $filename = uniqid() . '.' . $extImage;
            $dir = FRONT_IMG_PATH . self::TENDER_SUBDIR;
            @mkdir($dir, 0775, true);
            @copy($image, $dir . $filename);
            $tender->tender_image = $filename;
        }

        if ($request->filled('expert_image')) {
            $filename = uniqid() . '.' . $extExpImage;
            $dir = FRONT_IMG_PATH . self::EXPERT_SUBDIR;
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
        $tender->expert_email    = $request->expert_email;
        $tender->save();

        Session::flash('success', 'Tender Updated Successfully');

        return 'success';
    }

    public function delete(Request $request)
    {
        $tender = Tender::findOrFail($request->tender_id);

        if (!empty($tender->tender_image)) {
            @unlink(FRONT_IMG_PATH . self::TENDER_SUBDIR . $tender->tender_image);
        }
        if (!empty($tender->expert_image)) {
            @unlink(FRONT_IMG_PATH . self::EXPERT_SUBDIR . $tender->expert_image);
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
                @unlink(FRONT_IMG_PATH . self::TENDER_SUBDIR . $tender->tender_image);
            }
            if (!empty($tender->expert_image)) {
                @unlink(FRONT_IMG_PATH . self::EXPERT_SUBDIR . $tender->expert_image);
            }

            $tender->delete();
        }

        Session::flash('success', 'Tenders Deleted Successfully');

        return 'success';
    }

    public function featured(Request $request)
    {
        $result = rescue(function () use ($request) {
            $tender = Tender::findOrFail($request->tender_id);
            $tender->is_featured = $request->is_featured;
            $tender->save();
        }, false);

        if ($result === false) {
            return redirect()->back()->with('error', 'Failed to update featured status.');
        }

        return redirect()->back()->with('success', 'Featured status updated successfully.');
    }

    public function status(Request $request)
    {
        $tender = Tender::findOrFail($request->id);
        $tender->status = $request->status;
        $tender->save();

        return response()->json(['success' => true]);
    }

    public function purchaseLog(Request $request)
    {
        $orderNum = $request->order_number;
        $langCode = $request->language;
        $regNo    = $request->registration_no;

        $purchases = TenderPurchase::with([
                'tender',
                'tender.tenderModules' => fn($q) => $q->where('status', 1)->orderBy('id'),
            ])
            ->when($orderNum, fn($q) => $q->where('order_number', $orderNum))
            ->when($regNo, fn($q) => $q->where('company_registration_no', TenderPurchase::normalizeRegNo($regNo)))
            ->when($langCode, function ($q) use ($langCode) {
                $language = Language::where('code', $langCode)->first();
                if ($language) {
                    $tenderIds = Tender::where('language_id', $language->id)->pluck('id');
                    $q->whereIn('tender_id', $tenderIds);
                }
            })
            ->orderBy('id', 'DESC')
            ->paginate(10)
            ->appends($request->query());

        return view('admin.tender.tender.purchase', compact('purchases', 'orderNum', 'langCode', 'regNo'));
    }

    public function purchasePaymentStatus(Request $request)
    {
        $purchase        = TenderPurchase::findOrFail($request->purchase_id);
        $previousStatus  = $purchase->payment_status;
        $purchase->payment_status = $request->payment_status;
        // Match the gateway path (TenderPaymentHelper::completePurchase) — stamp
        // paid_at the first time this order is marked Completed, so it's not
        // left null for manually-approved (e.g. offline) payments.
        if ($request->payment_status === 'Completed' && empty($purchase->paid_at)) {
            $purchase->paid_at = now();
        }
        $purchase->save();

        \App\TenderAuditLog::record('payment_status_changed', $purchase,
            "Payment status {$previousStatus} → {$purchase->payment_status}",
            ['from' => $previousStatus, 'to' => $purchase->payment_status]);

        // Send secure download link email when payment is first approved
        if ($request->payment_status === 'Completed' && $previousStatus !== 'Completed') {
            $this->sendPurchaseApprovedEmail($purchase);
        }

        Session::flash('success', 'Payment status changed successfully!');
        return back();
    }

    public function purchaseUpdateReference(Request $request)
    {
        $purchase = TenderPurchase::findOrFail($request->purchase_id);
        $oldRef   = $purchase->payment_reference;
        $purchase->payment_reference = trim($request->input('payment_reference', '')) ?: null;
        $purchase->save();

        \App\TenderAuditLog::record('payment_reference_updated', $purchase,
            'Payment reference updated',
            ['from' => $oldRef, 'to' => $purchase->payment_reference]);

        Session::flash('success', 'Payment reference updated successfully!');
        return back();
    }

    /**
     * Suspend / un-suspend a single transaction.
     * Suspending immediately revokes every active download link for the order,
     * so existing links die and the "find my downloads" lookup is blocked.
     */
    public function purchaseSuspend(Request $request)
    {
        $purchase = TenderPurchase::findOrFail($request->purchase_id);

        if ($purchase->isSuspended()) {
            $purchase->access_status  = 'active';
            $purchase->suspend_reason = null;
            $purchase->suspended_at   = null;
            $purchase->save();

            \App\TenderAuditLog::record('purchase_reactivated', $purchase, 'Transaction reactivated');

            Session::flash('success', 'Transaction reactivated successfully!');
            return back();
        }

        $purchase->access_status  = 'suspended';
        $purchase->suspend_reason = trim($request->input('suspend_reason', '')) ?: null;
        $purchase->suspended_at   = now();
        $purchase->save();

        // Kill any live download links for this order.
        $revoked = SecureToken::where('order_id', $purchase->order_number)
            ->where('status', 'active')
            ->update(['status' => 'revoked']);

        \App\TenderAuditLog::record('purchase_suspended', $purchase,
            'Transaction suspended; download links revoked',
            ['reason' => $purchase->suspend_reason, 'links_revoked' => $revoked]);

        Session::flash('success', 'Transaction suspended. Download links disabled.');
        return back();
    }

    private function generateInvoice(TenderPurchase $purchase): string
    {
        $language = Language::where('is_default', 1)->first();
        $bse      = $language->basic_extra;
        $bs       = $language->basic_setting;

        // Embed logo as base64 so dompdf never needs to fetch a remote URL
        $logoSrc = null;
        if (!empty($bs->logo)) {
            // Try multiple possible logo locations
            $candidates = [
                storage_path('app/public/front/img/' . $bs->logo),
                base_path(FRONT_IMG_PUBLIC_DIR . $bs->logo),
                base_path(FRONT_IMG_DIR . $bs->logo),
            ];
            foreach ($candidates as $abs) {
                if (file_exists($abs)) {
                    $ext     = strtolower(pathinfo($abs, PATHINFO_EXTENSION));
                    $mime    = in_array($ext, ['jpg', 'jpeg']) ? 'image/jpeg' : 'image/' . $ext;
                    $logoSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($abs));
                    break;
                }
            }
        }

        // Eager-load tender so price is available in the view
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

    public function invoiceDownload($id)
    {
        $purchase = TenderPurchase::findOrFail($id);

        if (empty($purchase->invoice)) {
            abort(404, 'Invoice not found.');
        }

        $path = storage_path('app/invoices/tender/' . $purchase->invoice);

        // Fallback: check old assets path for invoices generated before the migration
        if (!file_exists($path)) {
            $legacyPath = base_path(FRONT_TENDER_INVOICE_DIR . $purchase->invoice);
            if (file_exists($legacyPath)) {
                $path = $legacyPath;
            } else {
                abort(404, 'Invoice file not found.');
            }
        }

        return response()->download($path, $purchase->order_number . '_invoice.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function purchaseGenerateInvoice($id)
    {
        $purchase = TenderPurchase::findOrFail($id);

        try {
            $this->generateInvoice($purchase);
            Session::flash('success', 'Invoice generated successfully.');
        } catch (\Exception $e) {
            Log::error('[Tender] Manual invoice generation failed', [
                'order' => $purchase->order_number,
                'error' => $e->getMessage(),
            ]);
            Session::flash('error', 'Invoice generation failed: ' . $e->getMessage());
        }

        return back();
    }

    private function sendPurchaseApprovedEmail(TenderPurchase $purchase): void
    {
        $language = Language::where('is_default', 1)->first();
        $be       = $language->basic_extended;

        // Generate invoice PDF (non-fatal — email always sends even if PDF fails)
        $invoicePath = null;
        try {
            $invoicePath = $this->generateInvoice($purchase);
        } catch (\Exception $e) {
            Log::error('[Tender] Invoice generation failed', [
                'order' => $purchase->order_number,
                'error' => $e->getMessage(),
            ]);
        }

        // Revoke any previous active tokens for this order
        SecureToken::where('order_id', $purchase->order_number)
            ->where('status', 'active')
            ->update(['status' => 'revoked']);

        // Generate signed token
        $payload   = implode('|', [$purchase->order_number, $purchase->email, now()->timestamp, Str::random(16)]);
        $rawToken  = hash_hmac('sha256', $payload, config('app.key'));
        $tokenHash = hash('sha256', $rawToken);

        SecureToken::create([
            'order_id'       => $purchase->order_number,
            'email_hash'     => hash('sha256', strtolower(trim($purchase->email))),
            'token_hash'     => $tokenHash,
            'issued_at'      => now(),
            'expires_at'     => now()->addHours(24),
            'max_downloads'  => 3,
            'download_count' => 0,
            'status'         => 'active',
            'device_hash'    => '',
            'ip'             => request()->ip(),
        ]);

        $downloadUrl = route('find_my_files.download', ['t' => $rawToken]);
        $language    = Language::where('is_default', 1)->first();
        $bs          = $language->basic_setting;

        try {
            $mailer = new KreativMailer;
            $mailer->mailFromAdmin([
                'toMail'         => $purchase->email,
                'toName'         => $purchase->first_name,
                'customer_name'  => $purchase->first_name,
                'order_number'   => $purchase->order_number,
                'download_url'   => $downloadUrl,
                'expires_at'     => now()->addHours(24)->format('d M Y, H:i'),
                'max_downloads'  => 3,
                'website_title'  => $bs->website_title,
                'templateType'   => 'tender_download_link',
                'type'           => 'tenderDownloadLink',
                'attachment'     => $invoicePath,
                'attachmentName' => $purchase->order_number . '_invoice.pdf',
            ]);
        } catch (\Exception $e) {
            Log::error('[Tender] Approval email failed', [
                'order' => $purchase->order_number,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function purchaseDelete(Request $request)
    {
        $purchase = TenderPurchase::findOrFail($request->purchase_id);
        if (!empty($purchase->receipt)) {
            @unlink(FRONT_RECEIPT_PATH . $purchase->receipt);
        }
        if (!empty($purchase->invoice)) {
            @unlink(storage_path('app/invoices/tender/' . $purchase->invoice));
            @unlink(base_path(FRONT_TENDER_INVOICE_DIR . $purchase->invoice));
        }

        \App\TenderAuditLog::record('purchase_deleted', $purchase, 'Transaction deleted',
            ['email' => $purchase->email, 'payment_status' => $purchase->payment_status]);

        $purchase->delete();

        Session::flash('success', 'Deleted successfully!');
        return back();
    }

    public function purchaseBulkOrderDelete(Request $request)
    {
        foreach ($request->ids as $id) {
            $purchase = TenderPurchase::findOrFail($id);
            if (!empty($purchase->receipt)) {
                @unlink(FRONT_RECEIPT_PATH . $purchase->receipt);
            }
            if (!empty($purchase->invoice)) {
                @unlink(storage_path('app/invoices/tender/' . $purchase->invoice));
                @unlink(base_path(FRONT_TENDER_INVOICE_DIR . $purchase->invoice));
            }

            \App\TenderAuditLog::record('purchase_deleted', $purchase, 'Transaction deleted (bulk)',
                ['email' => $purchase->email, 'payment_status' => $purchase->payment_status]);

            $purchase->delete();
        }

        Session::flash('success', 'Deleted successfully!');
        return 'success';
    }

    public function settings(Request $request)
    {
        $language = $request->input('language', '');
        $lang     = $language ? Language::where('code', $language)->first() : null;
        $abex     = $lang ? $lang->basic_extra : BasicExtra::first();
        return view('admin.tender.settings', compact('abex', 'language'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'tender_breadcrumb_overlay_color'   => 'nullable|max:20',
            'tender_breadcrumb_overlay_opacity' => 'nullable|numeric|min:0|max:1',
            'tender_watermark_opacity'          => 'nullable|numeric|min:0.05|max:1',
            'tender_watermark_font_size'        => 'nullable|integer|min:6|max:96',
            'tender_watermark_rotation'         => 'nullable|integer|min:-90|max:90',
            'tender_watermark_color'            => 'nullable|max:20',
            'tender_watermark_template'         => 'nullable|string|max:2000',
            'tender_pdf_encrypt_enabled'        => 'nullable|in:0,1',
            'tender_pdf_password'               => 'nullable|string|max:255|required_if:tender_pdf_encrypt_enabled,1',
            'tender_max_downloads'              => 'nullable|integer|min:1|max:20',
            'tender_max_regen_per_day'          => 'nullable|integer|min:1|max:20',
            'tender_regen_cap_enabled'          => 'nullable|in:0,1',
            'tender_regen_cap_order_number'     => 'nullable|in:0,1',
            'tender_regen_cap_otp'              => 'nullable|in:0,1',
            'tender_regen_cap_payref'           => 'nullable|in:0,1',
            'tender_regen_cap_regenerate'       => 'nullable|in:0,1',
        ], [
            'tender_pdf_password.required_if'   => 'A password is required when PDF encryption is active.',
        ]);

        $invoiceDir = base_path(FRONT_ADMIN_IMG_DIR . 'invoice/');
        if (!is_dir($invoiceDir)) {
            mkdir($invoiceDir, 0775, true);
        }

        // Invoice images + is_tender: update all language rows (global)
        $bexs = BasicExtra::all();
        foreach ($bexs as $bex) {
            $bex->is_tender = $request->is_tender;

            foreach (['invoice_watermark', 'invoice_sign', 'invoice_footer_wavy'] as $field) {
                $clearKey = 'clear_' . $field;
                if ($request->input($clearKey) == '1') {
                    if (!empty($bex->$field) && file_exists($invoiceDir . $bex->$field)) {
                        @unlink($invoiceDir . $bex->$field);
                    }
                    $bex->$field = null;
                } elseif ($request->filled($field)) {
                    $url      = $request->input($field);
                    $ext      = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
                    $filename = uniqid($field . '_') . '.' . $ext;
                    if (!empty($bex->$field) && file_exists($invoiceDir . $bex->$field)) {
                        @unlink($invoiceDir . $bex->$field);
                    }
                    @copy($url, $invoiceDir . $filename);
                    $bex->$field = $filename;
                }
            }

            $bex->invoice_footer_address = $request->invoice_footer_address;

            // Watermark settings (global — same on every language row)
            $bex->tender_watermark_enabled   = $request->input('tender_watermark_enabled', 1);
            $bex->tender_watermark_template  = $request->input('tender_watermark_template');
            $bex->tender_watermark_opacity   = $request->filled('tender_watermark_opacity') ? $request->tender_watermark_opacity : 0.30;
            $bex->tender_watermark_color     = $request->filled('tender_watermark_color') ? ltrim($request->tender_watermark_color, '#') : 'FF0000';
            $bex->tender_watermark_font_size = $request->filled('tender_watermark_font_size') ? $request->tender_watermark_font_size : 24;
            $bex->tender_watermark_rotation  = $request->filled('tender_watermark_rotation') ? $request->tender_watermark_rotation : 45;

            // PDF encryption (global — same on every language row)
            $bex->tender_pdf_encrypt_enabled = $request->input('tender_pdf_encrypt_enabled', 0);
            $bex->tender_pdf_password        = $request->input('tender_pdf_password');

            // Secure download link open-limit (global)
            $bex->tender_max_downloads = $request->filled('tender_max_downloads')
                ? (int) $request->tender_max_downloads
                : 3;

            // Per-order recovery cap (global) — shared across all 4 Find-My-Files
            // methods, with a master on/off plus one on/off per method.
            $bex->tender_max_regen_per_day      = $request->filled('tender_max_regen_per_day')
                ? (int) $request->tender_max_regen_per_day
                : 3;
            $bex->tender_regen_cap_enabled      = $request->input('tender_regen_cap_enabled', 1);
            $bex->tender_regen_cap_order_number = $request->input('tender_regen_cap_order_number', 1);
            $bex->tender_regen_cap_otp          = $request->input('tender_regen_cap_otp', 1);
            $bex->tender_regen_cap_payref       = $request->input('tender_regen_cap_payref', 1);
            $bex->tender_regen_cap_regenerate   = $request->input('tender_regen_cap_regenerate', 1);

            $bex->save();
        }

        // Breadcrumb: update only the current language row
        $langCode = $request->input('language', '');
        $lang     = $langCode ? Language::where('code', $langCode)->first() : null;
        $bex      = $lang ? $lang->basic_extra : BasicExtra::first();

        if ($bex) {
            $bex->tender_breadcrumb_overlay_color   = $request->tender_breadcrumb_overlay_color;
            $bex->tender_breadcrumb_overlay_opacity = $request->tender_breadcrumb_overlay_opacity;

            if ($request->filled('tender_breadcrumb_bg')) {
                $url  = $request->input('tender_breadcrumb_bg');
                $ext  = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                    $bgDir = base_path(FRONT_IMG_DIR);
                    if (!empty($bex->tender_breadcrumb_bg)) {
                        @unlink($bgDir . $bex->tender_breadcrumb_bg);
                    }
                    $filename = uniqid('tender_bg_') . '.' . $ext;
                    @copy($url, $bgDir . $filename);
                    $bex->tender_breadcrumb_bg = $filename;
                }
            }

            $bex->save();
        }

        Session::flash('success', 'Tender Settings Updated Successfully');

        return redirect()->route('admin.tender.settings', ['language' => $langCode]);
    }

    public function deleteTenderBreadcrumbBg(Request $request)
    {
        $langCode = $request->input('language', '');
        $lang     = $langCode ? Language::where('code', $langCode)->first() : null;
        $bex      = $lang ? $lang->basic_extra : BasicExtra::first();

        if ($bex && $bex->tender_breadcrumb_bg) {
            @unlink(base_path(FRONT_IMG_DIR . $bex->tender_breadcrumb_bg));
            $bex->tender_breadcrumb_bg = null;
            $bex->save();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 404);
    }

    /**
     * Browser-based watermark self-test — for hosts without terminal/SSH.
     * Runs the seeder command server-side and renders the download links.
     */
    public function watermarkTest()
    {
        \Illuminate\Support\Facades\Artisan::call('tender:wm-test');
        $output = \Illuminate\Support\Facades\Artisan::output();
        $mode   = 'seed';
        return view('admin.tender.watermark-test', compact('output', 'mode'));
    }

    public function watermarkTestCleanup()
    {
        \Illuminate\Support\Facades\Artisan::call('tender:wm-test', ['--cleanup' => true]);
        $output = \Illuminate\Support\Facades\Artisan::output();
        $mode   = 'cleanup';
        return view('admin.tender.watermark-test', compact('output', 'mode'));
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

        // Match exactly what the tender checkout page actually offers (see
        // Front\TenderController@tenderDetails) — not every globally-enabled
        // gateway, only the ones a buyer could actually have paid with here.
        $onPms  = PaymentGateway::where('status', 1)
            ->whereIn('keyword', ['stripe', 'razorpay', 'moneroo'])
            ->get();
        $offPms = OfflineGateway::where('course_checkout_status', 1)->get();

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
