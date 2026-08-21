<?php

namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\BasicSetting as BS;
use App\BasicExtended as BE;
use App\ContactMessage;
use App\Http\Helpers\KreativMailer;
use Illuminate\Support\Facades\Log;
use App\Slider;
use App\Scategory;
use App\Portfolio;
use App\Feature;
use App\Point;
use App\Statistic;
use App\Testimonial;
use App\Gallery;
use App\GalleryCategory;
use App\Faq;
use App\Page;
use App\Member;
use App\Blog;
use App\Partner;
use App\Service;
use App\DarkHeroSetting;
use App\Archive;
use App\Bcategory;
use App\Subscriber;
use App\Language;
use App\Admin;
use App\Tender;
use App\BasicExtra;
use App\FAQCategory;
use App\Home;
use App\Mail\ContactMail;
use App\OfflineGateway;
use App\Pcategory;
use App\Product;
use Session;
use Validator;
use Config;
use Mail;
use PDF;
use Auth;

class FrontendController extends Controller
{
    public function __construct()
    {
        $bs = BS::first();
        $be = BE::first();

        Config::set('captcha.sitekey', $bs?->google_recaptcha_site_key);
        Config::set('captcha.secret', $bs?->google_recaptcha_secret_key);
    }

    public function index()
    {
        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }
        $data['currentLang'] = $currentLang;

        $be = $currentLang?->basic_extended;
        $bex = $currentLang?->basic_extra;
        $lang_id = $currentLang?->id;

        // Tender card price formatting (same variable name as Front\TenderController).
        $data['bse'] = $bex;

        $data['sliders'] = Slider::where('language_id', $lang_id)->orderBy('serial_number', 'ASC')->get();
        $data['features'] = Feature::where('language_id', $lang_id)->where('status', 1)->orderBy('serial_number', 'ASC')->get();
        $version = $be?->theme_version;

        if ($version == 'dark') {
            $data['darkHero'] = DarkHeroSetting::where('language_id', $lang_id)->first();
        }

        // if home page page builder is disabled
        if ($bex?->home_page_pagebuilder == 0) {
            // Featured listing blocks change only via admin CRUD, not
            // real-time — short TTL keeps the homepage fast without a
            // manual invalidation hook on every one of these models.
            $data += \Illuminate\Support\Facades\Cache::remember("home_listing_blocks:lang:{$lang_id}", now()->addMinutes(15), function () use ($lang_id) {
                $blocks = [
                    'portfolios' => Portfolio::where('language_id', $lang_id)->where('feature', 1)->with('service:id,title')->orderBy('serial_number', 'ASC')->limit(10)->get(),
                    'points' => Point::where('language_id', $lang_id)->orderBy('serial_number', 'ASC')->get(),
                    'statistics' => Statistic::where('language_id', $lang_id)->orderBy('serial_number', 'ASC')->get(),
                    'testimonials' => Testimonial::where('language_id', $lang_id)->orderBy('serial_number', 'ASC')->get(),
                    'faqs' => Faq::orderBy('serial_number', 'ASC')->get(),
                    'members' => Member::where('language_id', $lang_id)->where('feature', 1)->get(),
                    'blogs' => Blog::where('language_id', $lang_id)->orderBy('id', 'DESC')->limit(6)->get(),
                    'partners' => Partner::where('language_id', $lang_id)->where('status', 1)->orderBy('serial_number', 'ASC')->get(),
                    'scategories' => Scategory::where('language_id', $lang_id)->where('feature', 1)->where('status', 1)->orderBy('serial_number', 'ASC')->get(),
                    'tenders' => Tender::where('language_id', $lang_id)->where('is_featured', 1)->where('status', 1)->with('tenderCategory:id,name')->orderBy('id', 'DESC')->limit(10)->get(),
                ];

                if (!serviceCategory()) {
                    $blocks['services'] = Service::where('language_id', $lang_id)->where('feature', 1)->orderBy('serial_number', 'ASC')->get();
                }

                return $blocks;
            });
        }
        // if home page page builder is disabled
        else {
            $data['home'] = Home::where('theme', $be->theme_version)->where('language_id', $currentLang->id)->first();
        }

        if ($version == 'default' || $version == 'dark') {
            if ($bex?->home_page_pagebuilder == 1) {
                return view('front.default.index', $data);
            } else {
                return view('front.default.index1', $data);
            }
        }
    }

    public function services(Request $request)
    {
        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }
        $data['currentLang'] = $currentLang;
        $be = $currentLang->basic_extended;


        $category = $request->category;
        $term = $request->term;

        if (!empty($category)) {
            $data['category'] = Scategory::findOrFail($category);
        }

        $data['services'] = Service::when($category, function ($query, $category) {
            return $query->where('scategory_id', $category);
        })->when($term, function ($query, $term) {
            return $query->where('title', 'like', '%' . $term . '%');
        })->when($currentLang, function ($query, $currentLang) {
            return $query->where('language_id', $currentLang->id);
        })->orderBy('serial_number', 'ASC')->paginate(6);

        $data['servicesCount'] = Service::where('language_id', $currentLang->id)->count();

        $version = $be->theme_version;

        if ($version == 'default' || $version == 'dark') {
            $data['version'] = $version == 'dark' ? 'default' : $version;
            return view('front.services', $data);
        }
    }

    public function loadMoreServiceCategories(Request $request)
    {
        $langId = (int) $request->query('lang');
        $offset = max(0, (int) $request->query('offset', 0));
        $perPage = 6;

        $query = Scategory::where('language_id', $langId)
            ->where('feature', 1)
            ->where('status', 1)
            ->orderBy('serial_number', 'ASC');

        $total = $query->count();
        $scategories = $query->skip($offset)->take($perPage)->get();

        $html = view('front.default.partials.dark.service-category-cards', [
            'scategories' => $scategories,
            'startIndex' => $offset,
        ])->render();

        return response()->json([
            'html' => $html,
            'next_offset' => $offset + $scategories->count(),
            'has_more' => ($offset + $scategories->count()) < $total,
            'total' => $total,
        ]);
    }

    public function paymentInstruction(Request $request)
    {
        $offline = OfflineGateway::where('name', $request->name)->select('short_description', 'instructions', 'is_receipt')->first();
        return response()->json(['description' => $offline->short_description, 'instructions' => replaceBaseUrl($offline->instructions), 'is_receipt' => $offline->is_receipt]);
    }
    public function portfolios(Request $request)
    {
        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }

        $data['currentLang'] = $currentLang;
        $be = $currentLang->basic_extended;

        $category = $request->category;

        if (!empty($category)) {
            $data['category'] = Scategory::findOrFail($category);
        }

        $data['portfolios'] = Portfolio::with('service.scategory')->when($category, function ($query, $category) {
            $serviceIdArr = [];
            $serviceids = Service::select('id')->where('scategory_id', $category)->get();
            foreach ($serviceids as $key => $serviceid) {
                $serviceIdArr[] = $serviceid->id;
            }
            return $query->whereIn('service_id', $serviceIdArr);
        })->when($currentLang, function ($query, $currentLang) {
            return $query->where('language_id', $currentLang->id);
        })->orderBy('serial_number', 'ASC');

        $version = $be->theme_version;

        if ($version == 'default' || $version == 'dark') {
            $data['version'] = $version == 'dark' ? 'default' : $version;
            $data['portfolios'] = $data['portfolios']->paginate(9);
            return view('front.portfolios', $data);
        }
    }

    public function portfoliodetails($slug)
    {
        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }

        $data['portfolio'] = Portfolio::where('slug', $slug)->firstOrFail();

        $be = $currentLang->basic_extended;
        $version = $be->theme_version;

        if ($version == 'dark') {
            $version = 'default';
        }

        $data['version'] = $version;

        return view('front.portfolio-details', $data);
    }

    public function servicedetails($slug)
    {

        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }

        $data['service'] = Service::where('slug', $slug)->firstOrFail();

        if ($data['service']->details_page_status == 0) {
            return back();
        }

        $be = $currentLang->basic_extended;
        $version = $be->theme_version;

        if ($version == 'dark') {
            $version = 'default';
        }

        $data['version'] = $version;

        return view('front.service-details', $data);
    }

    public function blogs(Request $request)
    {
        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }
        $data['currentLang'] = $currentLang;

        $lang_id = $currentLang->id;
        $be = $currentLang->basic_extended;

        $category = $request->category;
        $catid = null;
        if (!empty($category)) {
            $data['category'] = Bcategory::where('slug', $category)->firstOrFail();
            $catid = $data['category']->id;
        }
        $term = $request->term;
        $tag = $request->tag;
        $month = $request->month;
        $year = $request->year;
        $data['archives'] = Archive::orderBy('id', 'DESC')->get();
        $data['bcats'] = Bcategory::where('language_id', $lang_id)->where('status', 1)->orderBy('serial_number', 'ASC')->get();
        if (!empty($month) && !empty($year)) {
            $archive = true;
        } else {
            $archive = false;
        }

        $data['blogs'] = Blog::when($catid, function ($query, $catid) {
            return $query->where('bcategory_id', $catid);
        })
            ->when($term, function ($query, $term) {
                return $query->where('title', 'like', '%' . $term . '%');
            })
            ->when($tag, function ($query, $tag) {
                return $query->where('tags', 'like', '%' . $tag . '%');
            })
            ->when($archive, function ($query) use ($month, $year) {
                return $query->whereMonth('created_at', $month)->whereYear('created_at', $year);
            })
            ->when($currentLang, function ($query, $currentLang) {
                return $query->where('language_id', $currentLang->id);
            })->orderBy('serial_number', 'ASC')->paginate(6);

        $version = $be->theme_version;

        if ($version == 'dark') {
            $version = 'default';
        }

        $data['version'] = $version;


        return view('front.blogs', $data);
    }

    public function blogdetails($slug)
    {
        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }

        $lang_id = $currentLang->id;


        $data['blog'] = Blog::where('slug', $slug)->firstOrFail();

        $data['archives'] = Archive::orderBy('id', 'DESC')->get();
        $data['bcats'] = Bcategory::where('status', 1)->where('language_id', $lang_id)->orderBy('serial_number', 'ASC')->get();

        $be = $currentLang->basic_extended;
        $version = $be->theme_version;

        if ($version == 'dark') {
            $version = 'default';
        }

        $data['version'] = $version;

        return view('front.blog-details', $data);
    }

    public function contact()
    {
        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }
        $be = $currentLang->basic_extended;
        $version = $be->theme_version;

        if ($version == 'dark') {
            $version = 'default';
        }

        $data['version'] = $version;

        $data['langg'] = Language::where('code', session('lang'))->first();

        return view('front.contact', $data);
    }

    public function sendmail(Request $request)
    {
        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }
        $bs = $currentLang->basic_setting;

        $messages = [
            'g-recaptcha-response.required' => 'Please verify that you are not a robot.',
            'g-recaptcha-response.captcha' => 'Captcha error! try again later or contact site admin.',
        ];

        $rules = [
            'name' => 'required',
            'email' => 'required|email',
            'subject' => 'required',
            'message' => 'required'
        ];
        if ($bs->is_recaptcha == 1) {
            $rules['g-recaptcha-response'] = 'required|captcha';
        }

        $request->validate($rules, $messages);

        $be =  BE::firstOrFail();
        $from = $request->email;
        $to = $be->to_mail;
        $subject = $request->subject;
        $message = $request->message;

        // Persist first so the submission survives even if the mailer fails —
        // previously a bad `to_mail` setting or SMTP error silently dropped it.
        $contactMessage = ContactMessage::create([
            'name'    => $request->name,
            'email'   => $from,
            'subject' => $subject,
            'message' => $message,
        ]);

        // Escaped copies for injecting into the HTML email templates — the
        // fields below are free-text visitor input, not admin-authored content.
        $safeName    = e($request->name);
        $safeEmail   = e($from);
        $safeSubject = e($subject);
        $safeMessage = nl2br(e($message));

        $mailer = new KreativMailer;

        // 1) Notify the site owner.
        try {
            $adminSent = $mailer->mailFromAdmin([
                'toMail'          => $to,
                'toName'          => $be->from_name ?: $bs->website_title,
                'customer_name'   => $safeName,
                'contact_email'   => $safeEmail,
                'contact_subject' => $safeSubject,
                'contact_message' => $safeMessage,
                'website_title'   => $bs->website_title,
                'templateType'    => 'contact_admin_notify',
                'type'            => 'contactAdminNotify',
            ]);

            if ($adminSent) {
                $contactMessage->mail_sent = 1;
                $contactMessage->save();
            }
        } catch (\Exception $e) {
            Log::error('[Contact] Admin notification email failed', [
                'contact_message_id' => $contactMessage->id,
                'error' => $e->getMessage(),
            ]);
        }

        // 2) Auto-reply to the visitor confirming receipt.
        try {
            $mailer->mailFromAdmin([
                'toMail'          => $from,
                'toName'          => $request->name,
                'customer_name'   => $safeName,
                'contact_subject' => $safeSubject,
                'contact_message' => $safeMessage,
                'website_title'   => $bs->website_title,
                'templateType'    => 'contact_customer_confirm',
                'type'            => 'contactCustomerConfirm',
            ]);
        } catch (\Exception $e) {
            Log::error('[Contact] Customer confirmation email failed', [
                'contact_message_id' => $contactMessage->id,
                'error' => $e->getMessage(),
            ]);
        }

        Session::flash('success', 'Email sent successfully!');
        return back();
    }

    public function subscribe(Request $request)
    {
        $rules = [
            'email' => 'required|email|unique:subscribers'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }

        $subsc = new Subscriber;
        $subsc->email = $request->email;
        $subsc->unsubscribe_token = \Illuminate\Support\Str::random(48);
        $subsc->save();

        return "success";
    }

    /**
     * One-click unsubscribe (token from the newsletter email — no login,
     * no re-entering an email, works "at any time" per the requirement).
     */
    public function unsubscribeByToken($token)
    {
        Subscriber::where('unsubscribe_token', $token)->delete();

        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }

        $version = $currentLang->basic_extended->theme_version;
        if ($version == 'dark') {
            $version = 'default';
        }

        return view('front.unsubscribe', ['version' => $version]);
    }

    /**
     * Fallback for a subscriber who doesn't have a recent email handy:
     * unsubscribe by typing the address back in. Always responds the same
     * way regardless of whether the email was actually found, so this
     * can't be used to probe whether an address is subscribed.
     */
    public function unsubscribeByEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        Subscriber::where('email', $request->email)->delete();

        return back()->with('success', __('If that address was subscribed, it has been removed.'));
    }

    public function team()
    {
        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }

        $data['members'] = Member::when($currentLang, function ($query, $currentLang) {
            return $query->where('language_id', $currentLang->id);
        })->get();

        $be = $currentLang->basic_extended;
        $version = $be->theme_version;

        if ($version == 'default' || $version == 'dark') {
            $data['version'] = $version == 'dark' ? 'default' : $version;
            return view('front.team', $data);
        }
    }

    public function gallery()
    {
        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }

        $lang_id = $currentLang->id;

        $data['categories'] = GalleryCategory::where('language_id', $lang_id)->where('status', 1)
            ->orderBy('serial_number', 'ASC')->get();

        $data['galleries'] = Gallery::with('galleryImgCategory')->where('language_id', $lang_id)
            ->where('status', 1)->orderBy('serial_number', 'ASC')->get();

        $be = $currentLang->basic_extended;
        $version = $be->theme_version;

        if ($version == 'dark') {
            $version = 'default';
        }

        $data['version'] = $version;

        return view('front.gallery', $data);
    }

    public function faq()
    {
        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }

        $lang_id = $currentLang->id;

        $data['categories'] = FAQCategory::where('language_id', $lang_id)->where('status', 1)
            ->orderBy('serial_number', 'ASC')->get();

        $data['faqs'] = Faq::where('language_id', $lang_id)->where('status', 1)->orderBy('serial_number', 'ASC')->get();

        $be = $currentLang->basic_extended;
        $version = $be->theme_version;

        if ($version == 'dark') {
            $version = 'default';
        }

        $data['version'] = $version;

        return view('front.faq', $data);
    }

    public function dynamicPage($slug)
    {
        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }

        $data['page'] = Page::where('slug', $slug)->firstOrFail();

        $be = $currentLang->basic_extended;
        $bex = $currentLang->basic_extra;
        $version = $be->theme_version;

        if ($version == 'dark') {
            $version = 'default';
        }

        $data['version'] = $version;

        if ($bex->custom_page_pagebuilder == 1) {
            return view('front.dynamic', $data);
        } else {
            return view('front.dynamic1', $data);
        }
    }

    public function changeLanguage($lang)
    {
        session()->put('lang', $lang);
        app()->setLocale($lang);

        $be = be::first();
        $version = $be->theme_version;

        return redirect()->route('front.index');
    }

}
