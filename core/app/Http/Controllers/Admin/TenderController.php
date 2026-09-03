<?php

namespace App\Http\Controllers\Admin;

use App\BasicExtra;
use App\Member;
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
use Illuminate\Support\Facades\Auth;
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
        $members = $language
            ? Member::where('language_id', $language->id)->orderBy('name')->get()
            : collect([]);

        return view('admin.tender.tender.create', compact('countries', 'tender_categories', 'members', 'language'));
    }

    public function getCategories($langId)
    {
        $tender_categories = TenderCategory::where('language_id', $langId)
            ->where('status', 1)
            ->get();

        return $tender_categories;
    }

    public function getMembers($langId)
    {
        return Member::where('language_id', $langId)->orderBy('name')->get();
    }

    public function store(Request $request)
    {
        $slug = slug_create($request->title);
        $image    = $request->tender_image;
        $expImage = $request->expert_image;
        $allowedExts = allowed_image_extensions();
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
            'expert_source'       => 'required|in:custom,member',
            'expert_member_id'    => 'required_if:expert_source,member|nullable|exists:members,id',
            'expert_name'         => 'required',
            'expert_position'     => 'required',
            'expert_details'      => 'required',
            'expert_whatsapp'     => 'required',
            'expert_email'        => 'required|email',
            'tender_image'        => 'required',
        ];

        // Expert image: required for a custom expert (no photo to fall back on);
        // optional for a team member since store() copies the member's own photo.
        if ($request->expert_source !== 'member') {
            $rules['expert_image'] = 'required';
        }

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
        } elseif ($request->expert_source === 'member') {
            $member = Member::find($request->expert_member_id);
            if ($member && !empty($member->image)) {
                $srcExt   = pathinfo($member->image, PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $srcExt;
                $dir = FRONT_IMG_PATH . self::EXPERT_SUBDIR;
                @mkdir($dir, 0775, true);
                @copy(FRONT_IMG_PATH . 'members/' . $member->image, $dir . $filename);
                $tender->expert_image = $filename;
            }
        }

        $link = $request->video_link;
        if (!empty($link) && strpos($link, '&') !== false) {
            $tender->video_link = substr($link, 0, strpos($link, '&'));
        } else {
            $tender->video_link = $link;
        }

        $tender->overview          = clean($request->overview);
        $tender->expert_member_id  = $request->expert_source === 'member' ? $request->expert_member_id : null;
        $tender->expert_name       = $request->expert_name;
        $tender->expert_position   = $request->expert_position;
        $tender->expert_details    = clean($request->expert_details);
        $tender->expert_whatsapp   = $request->expert_whatsapp;
        $tender->expert_email      = $request->expert_email;
        $tender->save();

        // A new tender doesn't change any cached front-end page's content by
        // itself (nothing links to it yet), but the listing pages' cached
        // HTML doesn't know it exists either — purge so it appears immediately.
        CloudflareController::purge();

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
        $members = Member::where('language_id', $tender->language_id)->orderBy('name')->get();

        return view('admin.tender.tender.edit', compact('tender', 'tender_categories', 'members', 'countries'));
    }

    public function update(Request $request)
    {
        $tenderId = $request->tender_id;
        $tender   = Tender::findOrFail($tenderId);
        $slug     = slug_create($request->title);

        $image    = $request->tender_image;
        $expImage = $request->expert_image;
        $allowedExts = allowed_image_extensions();
        $extImage    = pathinfo($image, PATHINFO_EXTENSION);
        $extExpImage = pathinfo($expImage, PATHINFO_EXTENSION);

        $rules = [
            'language_id'         => 'required',
            'tender_category_id'  => 'required',
            'country'             => 'required',
            'tender_code'         => 'required',
            'title'               => 'required|max:255',
            'submission_deadline' => 'required|after_or_equal:today',
            'overview'            => 'required',
            'expert_source'       => 'required|in:custom,member',
            'expert_member_id'    => 'required_if:expert_source,member|nullable|exists:members,id',
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
            'language_id.required'              => 'The language field is required.',
            'tender_category_id.required'       => 'The category field is required.',
            'submission_deadline.after_or_equal' => 'The submission deadline cannot be a past date.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $validator->getMessageBag()->add('error', 'true');
            return response()->json($validator->errors());
        }

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
        } elseif ($request->expert_source === 'member' && (int) $request->expert_member_id !== (int) $tender->expert_member_id) {
            $member = Member::find($request->expert_member_id);
            if ($member && !empty($member->image)) {
                $srcExt   = pathinfo($member->image, PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $srcExt;
                $dir = FRONT_IMG_PATH . self::EXPERT_SUBDIR;
                @mkdir($dir, 0775, true);
                @copy(FRONT_IMG_PATH . 'members/' . $member->image, $dir . $filename);
                $tender->expert_image = $filename;
            }
        }

        $link = $request->video_link;
        if (!empty($link) && strpos($link, '&') !== false) {
            $tender->video_link = substr($link, 0, strpos($link, '&'));
        } else {
            $tender->video_link = $link;
        }

        $tender->overview          = clean($request->overview);
        $tender->expert_member_id  = $request->expert_source === 'member' ? $request->expert_member_id : null;
        $tender->expert_name       = $request->expert_name;
        $tender->expert_position   = $request->expert_position;
        $tender->expert_details    = clean($request->expert_details);
        $tender->expert_whatsapp   = $request->expert_whatsapp;
        $tender->expert_email      = $request->expert_email;
        $tender->save();

        // Front-end listing + detail pages are cached at Cloudflare's edge —
        // without this, an edit (including a language change moving the
        // tender to a different locale's list) wouldn't show up until the
        // Cache Rule's TTL naturally expired.
        CloudflareController::purge();

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

        // The front-end listing page is cached at Cloudflare's edge — without
        // this, a deleted tender keeps appearing there (with its detail page
        // now 404ing) until the Cache Rule's TTL naturally expires.
        CloudflareController::purge();

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

        CloudflareController::purge();

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

        // Toggling status changes whether it should appear in the cached
        // front-end listing at all.
        CloudflareController::purge();

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
        $completing      = $request->payment_status === 'Completed' && $previousStatus !== 'Completed';
        $reversing       = $request->payment_status !== 'Completed' && $previousStatus === 'Completed';

        // Reversal only makes sense for a validation an admin actually made —
        // it flips a DB flag and revokes access, it doesn't refund anything.
        // A real gateway payment (validated_by_admin_id empty — it reached
        // Completed via TenderPaymentHelper::completePurchase(), the
        // callback path this endpoint has nothing to do with) genuinely
        // charged the buyer through Moneroo/Stripe/Razorpay; "reversing" it
        // here would revoke access from someone who legitimately paid, with
        // no corresponding refund ever happening on the gateway's side.
        if ($reversing && empty($purchase->validated_by_admin_id)) {
            Session::flash('error', 'This order was paid through a real payment gateway, not a manual admin validation — it can\'t be reversed here.');
            return back();
        }

        // Manually marking an order Completed is a sensitive action (it
        // grants download access without any real payment having been
        // verified by a gateway) — gated behind its own permission, separate
        // from and in addition to the general Tender Management access this
        // whole route group already requires.
        if ($completing && !Auth::guard('admin')->user()->hasPermission('Manual Payment Completion')) {
            Session::flash('error', 'You don\'t have permission to manually complete a payment. Contact an administrator.');
            return back();
        }

        // Manually completing a Pending order (offline receipt or an
        // abandoned/failed online payment) requires proof — the buyer's own
        // uploaded receipt only covers the offline-checkout path, not a
        // gateway payment an admin is confirming happened outside the app.
        // Every completion reaching this endpoint (as opposed to
        // TenderPaymentHelper::completePurchase(), the real gateway-callback
        // path) is by definition an admin manual validation — recorded below
        // so the receipt can say so instead of showing a stale gateway name.
        if ($completing) {
            // Proof now comes from LFM (a URL to an already-uploaded file),
            // not a raw multipart upload — same "copy from LFM's URL into our
            // own storage" pattern as the invoice branding images on
            // admin/tender/settings.
            $request->validate([
                'proof' => 'required|string',
            ], [
                'proof.required' => 'A payment proof (image or file) is required to mark this order as paid.',
            ]);

            $url      = $request->input('proof');
            $ext      = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
            $filename = uniqid('proof_') . '.' . ($ext ?: 'dat');
            $dir      = 'assets/front/tender_proofs/';
            if (!is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }
            @copy($url, $dir . $filename);
            $purchase->admin_proof = $filename;

            // The buyer's original payment_method still held whatever
            // gateway their earlier failed/abandoned attempt used (or
            // whichever offline gateway they picked at checkout) — neither
            // is how this order actually got marked paid.
            $purchase->payment_method = 'Manual';

            $admin = Auth::guard('admin')->user();
            $purchase->validated_by_admin_id   = $admin->id ?? null;
            // admins table has first_name/last_name, no `name` column/accessor.
            $purchase->validated_by_admin_name = $admin ? trim($admin->first_name . ' ' . $admin->last_name) : null;

            // Find My Files' "Payment Reference" recovery method looks the
            // order up by whereNotNull('payment_reference') — a manually
            // completed order has no real gateway reference to put here
            // (that's the whole point of this flow), so without this,
            // payment_reference stays null forever and that recovery method
            // can never find this order at all. The order number is already
            // unique and known to the buyer (it's on every email/receipt),
            // so it doubles as a usable reference here. Never overwrite an
            // admin-entered reference (purchaseUpdateReference) if one's
            // already set.
            if (empty($purchase->payment_reference)) {
                $purchase->payment_reference = $purchase->order_number;
            }
        }

        // Reversing a manual validation (Completed → anything else) revokes
        // download access immediately rather than waiting for the next
        // download attempt to notice — an already-open tab or a cached
        // direct link should stop working the moment this happens, not just
        // future ones. The download routes also re-check payment_status
        // live (defense in depth), but this makes existing tokens dead on
        // arrival instead of merely dormant.
        $revokedTokens = 0;
        if ($reversing) {
            $request->validate([
                'reason' => 'required|string|max:500',
            ], [
                'reason.required' => 'A reason is required to cancel this payment validation.',
            ]);

            $purchase->reversal_reason          = trim($request->input('reason'));
            $purchase->validated_by_admin_id    = null;
            $purchase->validated_by_admin_name  = null;

            $revokedTokens = SecureToken::where('order_id', $purchase->order_number)
                ->where('status', 'active')
                ->update(['status' => 'revoked']);
        }

        $purchase->payment_status = $request->payment_status;
        // Match the gateway path (TenderPaymentHelper::completePurchase) — stamp
        // paid_at the first time this order is marked Completed, so it's not
        // left null for manually-approved (e.g. offline) payments.
        if ($completing && empty($purchase->paid_at)) {
            $purchase->paid_at = now();
        }
        $purchase->save();

        \App\TenderAuditLog::record('payment_status_changed', $purchase,
            "Payment status {$previousStatus} → {$purchase->payment_status}",
            [
                'from' => $previousStatus,
                'to' => $purchase->payment_status,
                'tokens_revoked' => $revokedTokens,
                'reversal_reason' => $reversing ? $purchase->reversal_reason : null,
            ]);

        // Immutable evidence log — one row per validate/cancel event, own
        // proof copy, so a Completed → Reversed → Completed cycle keeps
        // full history even though tender_purchases.admin_proof only ever
        // holds the CURRENT proof.
        if ($completing || $reversing) {
            $moduleTotal = collect(json_decode($purchase->purchased_modules, true) ?: [])->sum('cost');
            \App\TenderPaymentEvidence::create([
                'tender_purchase_id'   => $purchase->id,
                'order_number'         => $purchase->order_number,
                'action'               => $completing ? 'validated' : 'canceled',
                'amount'               => $moduleTotal,
                'currency_code'        => $purchase->currency_code,
                'proof_path'           => $purchase->admin_proof,
                'proof_original_name'  => $completing ? basename(parse_url($url, PHP_URL_PATH)) : null,
                'proof_size'           => $completing && isset($dir, $filename) && file_exists($dir . $filename) ? filesize($dir . $filename) : null,
                'admin_id'             => Auth::guard('admin')->id(),
                'admin_name'           => trim((Auth::guard('admin')->user()->first_name ?? '') . ' ' . (Auth::guard('admin')->user()->last_name ?? '')),
                'reason'               => $reversing ? $purchase->reversal_reason : null,
            ]);
        }

        if ($completing) {
            $this->sendPurchaseApprovedEmail($purchase);
        }

        if ($reversing) {
            $this->sendPaymentReversedEmail($purchase);
        }

        Session::flash('success', 'Payment status changed successfully!');
        return back();
    }

    /**
     * Emailed when an admin reverses a manual "Mark as Paid" validation.
     * Issues a fresh resume token (same mechanism as
     * TenderPaymentHelper::handleFailedPayment) so the buyer can pay again
     * through any available gateway without re-entering their details.
     */
    private function sendPaymentReversedEmail(TenderPurchase $purchase): void
    {
        try {
            $rawToken = hash_hmac('sha256', implode('|', [
                $purchase->id,
                $purchase->order_number,
                now()->timestamp,
                Str::random(16),
            ]), config('app.key'));

            $purchase->resume_token_hash = hash('sha256', $rawToken);
            $purchase->resume_token_issued_at = now();
            $purchase->save();

            $language = $purchase->tender->language ?? Language::where('is_default', 1)->first();
            $bs       = $language->basic_setting;

            (new KreativMailer)->mailFromAdmin([
                'toMail'        => $purchase->email,
                'toName'        => $purchase->first_name,
                'customer_name' => $purchase->first_name,
                'tender_name'   => $purchase->tender->title ?? 'Tender Document',
                'order_number'  => $purchase->order_number,
                'resume_url'    => route('tender.purchase.resume', ['token' => $rawToken]),
                'website_title' => $bs->website_title,
                'templateType'  => 'tender_payment_reversed',
                'type'          => 'tenderPaymentReversed',
            ]);
        } catch (\Exception $e) {
            Log::error('[Tender] Payment-reversed email failed', [
                'order' => $purchase->order_number,
                'error' => $e->getMessage(),
            ]);
        }
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

        // Admin-configurable open-limit (admin/tender/settings), matching the
        // gateway-payment path (TenderPaymentHelper::dispatchTenderDownloadLink)
        // — this was hardcoded to 3 here, silently ignoring the Opens Allowed
        // Per Link setting for every manually-completed order.
        $maxDownloads = (int) optional(BasicExtra::first())->tender_max_downloads;
        if ($maxDownloads < 1) {
            $maxDownloads = 3;
        }

        SecureToken::create([
            'order_id'       => $purchase->order_number,
            'email_hash'     => hash('sha256', strtolower(trim($purchase->email))),
            'token_hash'     => $tokenHash,
            'issued_at'      => now(),
            'expires_at'     => now()->addHours(24),
            'max_downloads'  => $maxDownloads,
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
                'max_downloads'  => $maxDownloads,
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
            // min:3 is the system default, not an arbitrary floor — these
            // are security caps (download opens / recovery-link reissuance),
            // and an admin should be able to raise them but never weaken
            // them below what the system ships with.
            'tender_max_downloads'              => 'nullable|integer|min:3|max:20',
            'tender_max_regen_per_day'          => 'nullable|integer|min:3|max:20',
            'tender_regen_cap_enabled'          => 'nullable|in:0,1',
            'tender_regen_cap_order_number'     => 'nullable|in:0,1',
            'tender_regen_cap_otp'              => 'nullable|in:0,1',
            'tender_regen_cap_payref'           => 'nullable|in:0,1',
            'tender_regen_cap_regenerate'       => 'nullable|in:0,1',
            'tender_payment_session_timeout_minutes' => 'nullable|integer|min:1|max:1440',
            'tender_payment_link_expiry_hours'       => 'nullable|integer|min:1|max:720',
            'tender_max_devices_per_order'       => 'nullable|integer|min:1|max:20',
        ], [
            'tender_pdf_password.required_if'   => 'A password is required when PDF encryption is active.',
            'tender_max_downloads.min'          => 'Opens Allowed Per Link can\'t be set below the system default (3).',
            'tender_max_regen_per_day.min'       => 'Recovery Requests Per Order can\'t be set below the system default (3).',
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

            $bex->invoice_footer_address = clean($request->invoice_footer_address);

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

            // Abandoned-payment sweep (global) — see NotifyIncompleteTenderPayments.
            $bex->tender_payment_session_timeout_minutes = $request->filled('tender_payment_session_timeout_minutes')
                ? (int) $request->tender_payment_session_timeout_minutes
                : 5;
            // Resume-link lifetime (global) — see TenderController::resumePurchase().
            $bex->tender_payment_link_expiry_hours = $request->filled('tender_payment_link_expiry_hours')
                ? (int) $request->tender_payment_link_expiry_hours
                : 24;
            // Max recognized devices/browsers per order before a new one is
            // refused outright — see FindMyFilesController::deviceAccessState().
            $bex->tender_max_devices_per_order = $request->filled('tender_max_devices_per_order')
                ? (int) $request->tender_max_devices_per_order
                : 5;

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
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'avif'])) {
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
