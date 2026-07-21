<?php

namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use App\BasicSetting as BS;
use App\BasicExtended as BE;
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
use App\Archive;
use App\Bcategory;
use App\Subscriber;
use App\Language;
use App\Admin;
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

        $data['sliders'] = Slider::where('language_id', $lang_id)->orderBy('serial_number', 'ASC')->get();
        $data['features'] = Feature::where('language_id', $lang_id)->where('status', 1)->orderBy('serial_number', 'ASC')->get();
        $version = $be?->theme_version;

        // if home page page builder is disabled
        if ($bex?->home_page_pagebuilder == 0) {
            $data['portfolios'] = Portfolio::where('language_id', $lang_id)->where('feature', 1)->orderBy('serial_number', 'ASC')->limit(10)->get();
            $data['points'] = Point::where('language_id', $lang_id)->orderBy('serial_number', 'ASC')->get();
            $data['statistics'] = Statistic::where('language_id', $lang_id)->orderBy('serial_number', 'ASC')->get();
            $data['testimonials'] = Testimonial::where('language_id', $lang_id)->orderBy('serial_number', 'ASC')->get();
            $data['faqs'] = Faq::orderBy('serial_number', 'ASC')->get();
            $data['members'] = Member::where('language_id', $lang_id)->where('feature', 1)->get();
            $data['blogs'] = Blog::where('language_id', $lang_id)->orderBy('id', 'DESC')->limit(6)->get();
            $data['partners'] = Partner::where('language_id', $lang_id)->orderBy('serial_number', 'ASC')->get();
            $data['scategories'] = Scategory::where('language_id', $lang_id)->where('feature', 1)->where('status', 1)->orderBy('serial_number', 'ASC')->get();
            if (!serviceCategory()) {
                $data['services'] = Service::where('language_id', $lang_id)->where('feature', 1)->orderBy('serial_number', 'ASC')->get();
            }
        }
        // if home page page builder is disabled
        else {
            $data['home'] = Home::where('theme', $be->theme_version)->where('language_id', $currentLang->id)->first();
        }

        if ($version == 'gym') {
            if ($bex->home_page_pagebuilder == 1) {
                return view('front.gym.index', $data);
            } else {
                return view('front.gym.index1', $data);
            }
        } elseif ($version == 'car') {
            if ($bex->home_page_pagebuilder == 1) {
                return view('front.car.index', $data);
            } else {
                return view('front.car.index1', $data);
            }
        } elseif ($version == 'cleaning') {
            if ($bex->home_page_pagebuilder == 1) {
                return view('front.cleaning.index', $data);
            } else {
                return view('front.cleaning.index1', $data);
            }
        } elseif ($version == 'construction') {
            if ($bex->home_page_pagebuilder == 1) {
                return view('front.construction.index', $data);
            } else {
                return view('front.construction.index1', $data);
            }
        } elseif ($version == 'logistic') {
            if ($bex->home_page_pagebuilder == 1) {
                return view('front.logistic.index', $data);
            } else {
                return view('front.logistic.index1', $data);
            }
        } elseif ($version == 'lawyer') {
            if ($bex->home_page_pagebuilder == 1) {
                return view('front.lawyer.index', $data);
            } else {
                return view('front.lawyer.index1', $data);
            }
        } elseif ($version == 'ecommerce') {
            $data['fcategories'] = Pcategory::where('status', 1)->where('language_id', $currentLang->id)->where('is_feature', 1)->get();
            $data['hcategories'] = Pcategory::where('status', 1)->where('language_id', $currentLang->id)->where('products_in_home', 1)->get();
            $data['fproducts'] = Product::where('status', 1)->where('is_feature', 1)->where('language_id', $currentLang->id)->orderBy('id', 'DESC')->limit(10)->get();
            $data['products'] = Product::where('status', 1)->where('language_id', $currentLang->id)->orderBy('id', 'DESC')->limit(10)->get();
            if ($bex->home_page_pagebuilder == 1) {
                return view('front.ecommerce.index', $data);
            } else {
                return view('front.ecommerce.index1', $data);
            }
        } elseif ($version == 'default' || $version == 'dark') {
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


        $version = $be->theme_version;

        if ($version == 'gym') {
            return view('front.gym.services', $data);
        } elseif ($version == 'car') {
            return view('front.car.services', $data);
        } elseif ($version == 'cleaning') {
            return view('front.cleaning.services', $data);
        } elseif ($version == 'construction') {
            return view('front.construction.services', $data);
        } elseif ($version == 'logistic') {
            return view('front.logistic.services', $data);
        } elseif ($version == 'lawyer') {
            return view('front.lawyer.services', $data);
        } elseif ($version == 'default' || $version == 'dark' || $version == 'ecommerce') {
            $data['version'] = $version == 'dark' ? 'default' : $version;
            return view('front.services', $data);
        }
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

        $data['portfolios'] = Portfolio::when($category, function ($query, $category) {
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

        if ($version == 'gym') {
            $data['portfolios'] = $data['portfolios']->get();
            return view('front.gym.portfolios', $data);
        } elseif ($version == 'car') {
            $data['portfolios'] = $data['portfolios']->get();
            return view('front.car.portfolios', $data);
        } elseif ($version == 'cleaning') {
            $data['portfolios'] = $data['portfolios']->get();
            return view('front.cleaning.portfolios', $data);
        } elseif ($version == 'construction') {
            $data['portfolios'] = $data['portfolios']->get();
            return view('front.construction.portfolios', $data);
        } elseif ($version == 'logistic') {
            $data['portfolios'] = $data['portfolios']->get();
            return view('front.logistic.portfolios', $data);
        } elseif ($version == 'lawyer') {
            $data['portfolios'] = $data['portfolios']->get();
            return view('front.lawyer.portfolios', $data);
        } elseif ($version == 'default' || $version == 'dark' || $version == 'ecommerce') {
            $data['version'] = $version == 'dark' ? 'default' : $version;
            $data['portfolios'] = $data['portfolios']->get();
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

        $request->validate($rules, $messages);

        $be =  BE::firstOrFail();
        $from = $request->email;
        $to = $be->to_mail;
        $subject = $request->subject;
        $message = $request->message;

        try {

            $mail = new PHPMailer(true);
            $mail->setFrom($from, $request->name);
            $mail->addAddress($to);     // Add a recipient

            // Content
            $mail->isHTML(true);  // Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body    = $message;

            $mail->send();
        } catch (\Exception $e) {
            // die($e->getMessage());
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
        $subsc->save();

        return "success";
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

        if ($version == 'gym') {
            return view('front.gym.team', $data);
        } elseif ($version == 'car') {
            return view('front.car.team', $data);
        } elseif ($version == 'cleaning') {
            return view('front.cleaning.team', $data);
        } elseif ($version == 'construction') {
            return view('front.construction.team', $data);
        } elseif ($version == 'logistic') {
            return view('front.logistic.team', $data);
        } elseif ($version == 'lawyer') {
            return view('front.lawyer.team', $data);
        } elseif ($version == 'default' || $version == 'dark' || $version == 'ecommerce') {
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
