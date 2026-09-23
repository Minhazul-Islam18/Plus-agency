<?php

namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\BasicSetting as BS;
use App\BasicExtended as BE;
use App\ContactMessage;
use App\Jobs\SendAdminMail;
use App\Jobs\SendContactAdminNotifyMail;
use Illuminate\Support\Facades\Log;
use App\Slider;
use App\Scategory;
use App\Portfolio;
use App\PortfolioSector;
use Illuminate\Support\Str;
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
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
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
        $currentLang = currentLang();
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
                    'portfolios' => Portfolio::where('language_id', $lang_id)->where('feature', 1)->where('is_archived', 0)->where('is_published', 1)->with('service:id,title')->orderBy('serial_number', 'ASC')->limit(10)->get(),
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
        $currentLang = currentLang();
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
        $currentLang = currentLang();

        $data['currentLang'] = $currentLang;
        $be = $currentLang->basic_extended;
        $data['countryNames'] = array_column(\App\Http\Helpers\Countries::all(), 'name', 'iso');

        // Filtering moved from Service Category (an indirect two-hop join:
        // portfolio -> service -> scategory) to Portfolio's own Sector —
        // every portfolio already carries a direct sector_id, and that's
        // the classification that actually describes the portfolio itself.
        $sectorId = $request->sector;

        if (!empty($sectorId)) {
            $data['sector'] = PortfolioSector::where('language_id', $currentLang->id)
                ->whereNull('parent_id')
                ->findOrFail($sectorId);
        }

        // Only sectors with at least one published project — with 15+
        // sector names already, the list only gets taller as more get
        // added, most with nothing behind them yet.
        $data['sectors'] = PortfolioSector::where('language_id', $currentLang->id)
            ->whereNull('parent_id')
            ->where('status', 1)
            ->whereHas('portfolios', function ($query) {
                $query->where('is_published', 1)->where('is_archived', 0);
            })
            ->orderBy('serial_number', 'ASC')
            ->get();

        // Per-sector project counts for the sidebar's count badges — always
        // the real total per sector, regardless of which sector is
        // currently filtered (so "All sectors" keeps showing the grand
        // total, not just whatever's on screen right now).
        $data['sectorCounts'] = Portfolio::where('language_id', $currentLang->id)
            ->where('is_archived', 0)->where('is_published', 1)
            ->whereNotNull('sector_id')
            ->selectRaw('sector_id, count(*) as cnt')
            ->groupBy('sector_id')
            ->pluck('cnt', 'sector_id');
        // NOT sectorCounts->sum() — a portfolio without a sector_id (legacy/
        // unassigned) still shows under "All sectors" itself, so that total
        // must count every published portfolio, not just the ones grouped
        // into a sector above.
        $data['totalPortfoliosCount'] = Portfolio::where('language_id', $currentLang->id)
            ->where('is_archived', 0)->where('is_published', 1)
            ->count();

        $search = trim((string) $request->search);
        $data['search'] = $search;

        $year = $request->year;
        $data['year'] = $year;

        $data['years'] = Portfolio::where('language_id', $currentLang->id)
            ->where('is_archived', 0)->where('is_published', 1)
            ->whereNotNull('year')
            ->distinct()
            ->orderBy('year', 'DESC')
            ->pluck('year');

        $baseQuery = Portfolio::with('service.scategory', 'sector', 'statusInfo')
            ->when($sectorId, function ($query) use ($sectorId) {
                return $query->where('sector_id', $sectorId);
            })
            ->when($search !== '', function ($query) use ($search) {
                return $query->where('title', 'like', '%' . $search . '%');
            })
            ->when($year, function ($query) use ($year) {
                return $query->where('year', $year);
            })
            ->when($currentLang, function ($query, $currentLang) {
                return $query->where('language_id', $currentLang->id);
            })
            ->where('is_archived', 0)->where('is_published', 1);

        // Status names are open, admin-managed free text (see the same
        // keyword matching in Portfolio admin's own status badge colors) —
        // there's no fixed id to group by, so bucket by keyword instead.
        $data['statusCounts'] = (clone $baseQuery)->get()->groupBy(function ($portfolio) {
            $name = mb_strtolower(convertUtf8(optional($portfolio->statusInfo)->name ?? ''));
            if (Str::contains($name, ['complet', 'finish', 'réalisé', 'realise', 'done'])) {
                return 'completed';
            }
            if (Str::contains($name, ['progress', 'cours', 'ongoing'])) {
                return 'in_progress';
            }
            if (Str::contains($name, ['pending', 'attente'])) {
                return 'pending';
            }
            return 'other';
        })->map->count();

        $data['portfolios'] = $baseQuery->orderBy('serial_number', 'ASC');

        $version = $be->theme_version;

        if ($version == 'default' || $version == 'dark') {
            $data['version'] = $version == 'dark' ? 'default' : $version;
            $data['portfolios'] = $data['portfolios']->paginate(9);
            return view('front.portfolios', $data);
        }
    }

    public function portfoliodetails($slug)
    {
        $currentLang = currentLang();

        $portfolio = Portfolio::with(['sector', 'subsector', 'statusInfo', 'service', 'portfolio_images', 'documents', 'highlights', 'partnerRefs'])
            ->where('slug', $slug)->where('language_id', $currentLang->id)
            ->where('is_archived', 0)->where('is_published', 1)
            ->first();

        if (!$portfolio) {
            // Editable-slug feature: this slug may just be an OLD one — an
            // admin shortened it since this link was shared/indexed. 301
            // (not 302) so search engines transfer the old URL's ranking
            // to the new one instead of treating them as two pages.
            $target = resolve_slug_redirect('portfolio', $slug);
            if ($target && $target->is_published && !$target->is_archived) {
                return redirect()->route('front.portfoliodetails', $target->slug, 301);
            }
            abort(404);
        }

        $data['portfolio'] = $portfolio;

        $portfolio = $data['portfolio'];

        if ($portfolio->country) {
            foreach (\App\Http\Helpers\Countries::all() as $c) {
                if ($c['iso'] === $portfolio->country) {
                    $data['pdCountryName'] = $c['name'];
                    break;
                }
            }
        }

        $data['documentList'] = $portfolio->documents->map(fn ($pd) => [
            'name' => $pd->original_name ?: $pd->file,
            'url' => url(FRONT_IMG_PATH . 'portfolios/documents/' . $pd->file),
            'size' => $pd->size,
        ])->all();

        // Latest published projects (excluding this one) — no popularity
        // metric exists to track (no view/click count column on
        // portfolios), so "latest" is the honest stand-in for that.
        // Previously filtered to same sector/service only, which could
        // come back empty for a project with neither set; newest-first
        // always has something to show.
        $data['similarProjects'] = Portfolio::where('language_id', $currentLang->id)
            ->where('id', '!=', $portfolio->id)
            ->where('is_archived', 0)
            ->where('is_published', 1)
            ->with('sector', 'statusInfo')
            ->orderBy('id', 'DESC')
            ->limit(8)
            ->get();

        // ISO -> full name map for the similar-project cards' country line
        // (they store the same 2-letter code as $portfolio->country above).
        $data['countryNames'] = array_column(\App\Http\Helpers\Countries::all(), 'name', 'iso');

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

        $currentLang = currentLang();

        $data['service'] = Service::where('slug', $slug)->where('language_id', $currentLang->id)->firstOrFail();

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
        $currentLang = currentLang();
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
        $currentLang = currentLang();

        $lang_id = $currentLang->id;

        $blog = Blog::where('slug', $slug)->where('language_id', $lang_id)->first();

        if (!$blog) {
            // Editable-slug feature — see portfoliodetails() above for the
            // full explanation. 301 preserves the old URL's SEO ranking.
            $target = resolve_slug_redirect('blog', $slug);
            if ($target) {
                return redirect()->route('front.blogdetails', $target->slug, 301);
            }
            abort(404);
        }

        $data['blog'] = $blog;

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
        $currentLang = currentLang();
        $be = $currentLang->basic_extended;
        $version = $be->theme_version;

        if ($version == 'dark') {
            $version = 'default';
        }

        $data['version'] = $version;

        $data['langg'] = currentLang();

        return view('front.contact', $data);
    }

    public function sendmail(Request $request)
    {
        $currentLang = currentLang();
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

        // 1) Notify the site owner.
        try {
            SendContactAdminNotifyMail::dispatch([
                'toMail'          => $to,
                'toName'          => $be->from_name ?: $bs->website_title,
                'customer_name'   => $safeName,
                'contact_email'   => $safeEmail,
                'contact_subject' => $safeSubject,
                'contact_message' => $safeMessage,
                'website_title'   => $bs->website_title,
                'templateType'    => 'contact_admin_notify',
                'type'            => 'contactAdminNotify',
            ], $contactMessage->id);
        } catch (\Exception $e) {
            Log::error('[Contact] Admin notification email failed', [
                'contact_message_id' => $contactMessage->id,
                'error' => $e->getMessage(),
            ]);
        }

        // 2) Auto-reply to the visitor confirming receipt.
        try {
            SendAdminMail::dispatch([
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

        $currentLang = currentLang();

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
        $currentLang = currentLang();

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
        $currentLang = currentLang();

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
        $currentLang = currentLang();

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
        $currentLang = currentLang();

        // Page rows have per-language slugs with no cross-language link
        // (e.g. "Notre-histoire" only exists as a French row) — without
        // this filter, /en/Notre-histoire silently returned the French
        // row regardless of URL locale (chrome showed English, content
        // stayed French). Filtering here means visiting a page's slug
        // under the wrong locale correctly 404s instead of showing
        // mismatched content — there's no data to resolve it to the
        // "equivalent" English page.
        $data['page'] = Page::where('slug', $slug)->where('language_id', $currentLang->id)->firstOrFail();

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
        // URL-based routing (see SetLocaleFromUrl) now carries the locale
        // in every URL segment, so this no longer needs to set a cookie —
        // it just redirects to the equivalent page under the new locale.
        $target = \App\Language::where('code', $lang)->where('status', 1)->first();
        if (!$target) {
            return redirect()->back();
        }

        // Route name + params come from the switcher link's query string
        // (set in layout.blade.php from the page that rendered it), not
        // from re-parsing a Referer header — the layout already knows
        // exactly which route/params generated the current page. Only
        // permalink-driven routes (shared slug across languages) are
        // swapped in place; front.dynamicPage (per-language Page slugs,
        // no cross-language link) falls through to the target homepage.
        $routeName = request()->query('_route');
        $params = request()->query();
        unset($params['_route']);

        if ($routeName && $routeName !== 'front.dynamicPage' && \Illuminate\Support\Facades\Route::has($routeName)) {
            $params['locale'] = $target->code;
            try {
                return redirect()->route($routeName, $params);
            } catch (\Exception) {
                // param mismatch for this route — fall through to homepage
            }
        }

        return redirect()->route('front.index', ['locale' => $target->code]);
    }

}
