<?php

namespace App\Http\Controllers\Admin;

use App\BasicExtended;
use App\BasicExtra;
use App\BasicSetting;
use App\Home;
use App\Http\Controllers\Controller;
use App\Language;
use App\Service;
use App\Timezone;
use Artisan;
use Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Validator;

class BasicController extends Controller
{
    public function fileManager()
    {
        return view('admin.basic.file-manager');
    }

    public function updateImageSettings(Request $request)
    {
        $request->validate([
            'image_convert_enabled' => 'required|in:0,1',
            'image_convert_format' => 'required|in:webp,avif',
            'image_convert_quality' => 'required|integer|min:1|max:100',
            'image_resize_enabled' => 'required|in:0,1',
            'image_resize_max_width' => 'required|integer|min:50|max:10000',
            'image_resize_max_height' => 'required|integer|min:50|max:10000',
        ]);

        $bexs = BasicExtra::all();
        foreach ($bexs as $bex) {
            $bex->image_convert_enabled = $request->image_convert_enabled;
            $bex->image_convert_format = $request->image_convert_format;
            $bex->image_convert_quality = $request->image_convert_quality;
            $bex->image_resize_enabled = $request->image_resize_enabled;
            $bex->image_resize_max_width = $request->image_resize_max_width;
            $bex->image_resize_max_height = $request->image_resize_max_height;
            $bex->save();
        }

        Session::flash('success', 'Image optimization settings updated successfully!');
        return back();
    }

    public function updateUploadLimits(Request $request)
    {
        $request->validate([
            'lfm_max_image_size_mb' => 'required|integer|min:1|max:500',
            'lfm_max_file_size_mb' => 'required|integer|min:1|max:500',
        ]);

        $bexs = BasicExtra::all();
        foreach ($bexs as $bex) {
            $bex->lfm_max_image_size_mb = $request->lfm_max_image_size_mb;
            $bex->lfm_max_file_size_mb = $request->lfm_max_file_size_mb;
            $bex->save();
        }

        Session::flash('success', 'Upload size limits updated successfully!');
        return back();
    }

    public function updateCloudflareSettings(Request $request)
    {
        $request->validate([
            'cloudflare_zone_id' => 'nullable|string|max:64',
            'cloudflare_api_token' => 'nullable|string|max:255',
        ]);

        $bexs = BasicExtra::all();
        foreach ($bexs as $bex) {
            $bex->cloudflare_zone_id = $request->cloudflare_zone_id;
            $bex->cloudflare_api_token = $request->cloudflare_api_token;
            $bex->save();
        }

        Session::flash('success', 'Cloudflare settings updated successfully!');
        return back();
    }

    public function logo()
    {
        $data['abs'] = BasicSetting::first();

        return view('admin.basic.logo', $data);
    }

    public function updatelogo(Request $request)
    {
        $logo = $request->logo;
        $emailLogo = $request->email_logo;
        $favicon = $request->favicon;
        $breadcrumb = $request->breadcrumb;

        $allowedExts = allowed_image_extensions();
        // Email clients (Outlook especially) largely don't render inline SVG
        // regardless of markup — the email logo must be a raster format.
        $allowedEmailExts = array('jpg', 'png', 'jpeg');
        $extLogo = pathinfo($logo, PATHINFO_EXTENSION);
        $extEmailLogo = pathinfo($emailLogo, PATHINFO_EXTENSION);
        $extFav = pathinfo($favicon, PATHINFO_EXTENSION);
        $extBread = pathinfo($breadcrumb, PATHINFO_EXTENSION);

        $rules = [];

        if ($request->filled('logo')) {
            $rules['logo'] = [
                function ($attribute, $value, $fail) use ($extLogo, $allowedExts) {
                    if (!in_array($extLogo, $allowedExts)) {
                        return $fail("Only png, jpg, jpeg, svg image is allowed");
                    }
                }
            ];
        }

        if ($request->filled('email_logo')) {
            $rules['email_logo'] = [
                function ($attribute, $value, $fail) use ($extEmailLogo, $allowedEmailExts) {
                    if (!in_array($extEmailLogo, $allowedEmailExts)) {
                        return $fail("Only png, jpg, jpeg image is allowed (SVG is not supported by most email clients)");
                    }
                }
            ];
        }

        if ($request->filled('favicon')) {
            $rules['favicon'] = [
                function ($attribute, $value, $fail) use ($extFav, $allowedExts) {
                    if (!in_array($extFav, $allowedExts)) {
                        return $fail("Only png, jpg, jpeg, svg image is allowed");
                    }
                },
            ];
        }

        if ($request->filled('breadcrumb')) {
            $rules['breadcrumb'] = [
                function ($attribute, $value, $fail) use ($extBread, $allowedExts) {
                    if (!in_array($extBread, $allowedExts)) {
                        return $fail("Only png, jpg, jpeg, svg image is allowed");
                    }
                },
            ];
        }

        $request->validate($rules);

        if ($request->filled('logo')) {

            $bss = BasicSetting::all();
            // only remove the the previous image, if it is not the same as default image or the first image is being updated

            $optimize = $request->boolean('optimize_logo', true);
            foreach ($bss as $key => $bs) {
                @unlink(FRONT_IMG_PATH . $bs->logo);
                $filename = uniqid() . '.' . $extLogo;
                $dest = FRONT_IMG_PATH . $filename;
                // Recommended 300x80 — 600x160 covers 2x retina headers.
                $optimize ? copy_resized_image($logo, $dest, 600, 160) : @copy($logo, $dest);

                $bs->logo = $filename;
                $bs->save();
            }
        }

        if ($request->filled('email_logo')) {

            $bss = BasicSetting::all();

            $optimize = $request->boolean('optimize_email_logo', true);
            foreach ($bss as $key => $bs) {
                @unlink(FRONT_IMG_PATH . $bs->email_logo);
                $filename = uniqid() . '.' . $extEmailLogo;
                $dest = FRONT_IMG_PATH . $filename;
                $optimize ? copy_resized_image($emailLogo, $dest, 600, 160) : @copy($emailLogo, $dest);

                $bs->email_logo = $filename;
                $bs->save();
            }
        }

        if ($request->filled('favicon')) {

            $bss = BasicSetting::all();
            // only remove the the previous image, if it is not the same as default image or the first image is being updated

            $optimize = $request->boolean('optimize_favicon', true);
            foreach ($bss as $key => $bs) {
                @unlink(FRONT_IMG_PATH . $bs->favicon);
                $filename = uniqid() . '.' . $extFav;
                $dest = FRONT_IMG_PATH . $filename;
                // Recommended 40x40 — 128x128 gives real retina/apple-touch-icon headroom.
                $optimize ? copy_resized_image($favicon, $dest, 128, 128) : @copy($favicon, $dest);

                $bs->favicon = $filename;
                $bs->save();
            }
        }

        if ($request->filled('breadcrumb')) {

            $bss = BasicSetting::all();
            // only remove the the previous image, if it is not the same as default image or the first image is being updated

            $optimize = $request->boolean('optimize_breadcrumb', true);
            foreach ($bss as $key => $bs) {
                @unlink(FRONT_IMG_PATH . $bs->breadcrumb);
                $filename = uniqid() . '.' . $extBread;
                $dest = FRONT_IMG_PATH . $filename;
                // 1920x350 is already the actual full-bleed banner size, not
                // just a crop-safe minimum — no retina multiplier needed.
                $optimize ? copy_resized_image($breadcrumb, $dest, 1920, 350) : @copy($breadcrumb, $dest);

                $bs->breadcrumb = $filename;
                $bs->save();
            }
        }

        $request->session()->flash('success', 'Images updated successfully!');
        return back();
    }


    public function updatethemeversion(Request $request)
    {
        $bes = BasicExtended::all();

        foreach ($bes as $key => $be) {
            $be->theme_version = $request->theme_version;
            $be->save();
        }

        Session::flash('success', "$request->theme_version version activated successfully!");
        return "success";
    }


    public function homeSettings(Request $request)
    {
        $data['abs'] = BasicSetting::first();
        $data['abe'] = BasicExtended::first();
        $data['abex'] = BasicExtra::first();
        $data['themes'] = Home::all();

        return view('admin.themeHome.settings', $data);
    }

    public function updateHomeSettings(Request $request)
    {
        $bss = BasicSetting::all();
        $bes = BasicExtended::all();
        $bexs = BasicExtra::all();

        foreach ($bss as $key => $bs) {
            $bs->home_version = $request->home_version;
            $bs->save();
        }
        foreach ($bes as $key => $be) {
            $be->theme_version = $request->theme_version;
            $be->save();
        }
        foreach ($bexs as $key => $bex) {
            $bex->home_page_pagebuilder = $request->home_page_pagebuilder;
            $bex->save();
        }

        Session::flash('success', "Settings updated successfully!");
        return back();
    }

    public function preloader(Request $request)
    {
        return view('admin.basic.preloader');
    }

    public function updatepreloader(Request $request)
    {
        $preloader = $request->preloader;
        $allowedExts = allowed_image_extensions();
        $extPreloader = pathinfo($preloader, PATHINFO_EXTENSION);

        $rules = [
            'preloader_status' => 'required'
        ];

        if ($request->filled('preloader')) {
            $rules['preloader'] = [
                function ($attribute, $value, $fail) use ($extPreloader, $allowedExts) {
                    if (!in_array($extPreloader, $allowedExts)) {
                        return $fail("Only png, jpg, jpeg, gif, svg images are allowed");
                    }
                }
            ];
        }

        $request->validate($rules);



        if ($request->filled('preloader')) {
            $filename = uniqid() . '.' . $extPreloader;
            $dest = FRONT_IMG_PATH . $filename;
            if ($request->boolean('optimize_preloader', true)) {
                // Displayed at 44x44 — 120x120 covers ~2.7x retina. Animated
                // GIFs keep their animation (see copy_resized_image()).
                copy_resized_image($preloader, $dest, 120, 120);
            } else {
                @copy($preloader, $dest);
            }
        }

        $bexs = BasicExtra::all();
        foreach ($bexs as $key => $bex) {
            if ($request->filled('preloader')) {
                @unlink(FRONT_IMG_PATH . $bex->preloader);
                $bex->preloader = $filename;
            }

            $bex->preloader_status = $request->preloader_status;
            $bex->save();
        }
        Session::flash('success', 'Preloader updated successfully.');
        return back();
    }


    public function basicinfo(Request $request)
    {
        $data['abs'] = BasicSetting::first();
        $data['abe'] = BasicExtended::first();
        $data['abx'] = BasicExtra::first();
        $data['timezones'] = Timezone::all();

        return view('admin.basic.basicinfo', $data);
    }

    public function updatebasicinfo(Request $request)
    {
        $rules = [
            'website_title' => 'required',
            'base_color' => 'required',
            'secondary_base_color' => 'required',
            'hero_area_overlay_color' => 'required',
            'hero_area_overlay_opacity' => 'required|numeric|max:1|min:0',
            'breadcrumb_area_overlay_color' => 'required',
            'breadcrumb_area_overlay_opacity' => 'required|numeric|max:1|min:0',
            'base_currency_symbol' => 'required',
            'base_currency_symbol_position' => 'required',
            'base_currency_text' => 'required',
            'base_currency_text_position' => 'required',
            'base_currency_rate' => 'required|numeric',
        ];

        $be = BasicExtended::first();

        $request->validate($rules);

        $bss = BasicSetting::all();
        foreach ($bss as $key => $bs) {
            $bs->website_title = $request->website_title;
            $bs->base_color = $request->base_color;
            $bs->secondary_base_color = $request->secondary_base_color;

            $bs->save();
        }


        $bes = BasicExtended::all();
        foreach ($bes as $key => $be) {
            $be->hero_overlay_color = $request->hero_area_overlay_color;
            $be->hero_overlay_opacity = $request->hero_area_overlay_opacity;


            $be->breadcrumb_overlay_color = $request->breadcrumb_area_overlay_color;
            $be->breadcrumb_overlay_opacity = $request->breadcrumb_area_overlay_opacity;
            $be->save();
        }


        $bexs = BasicExtra::all();
        foreach ($bexs as $key => $bex) {
            $bex->base_currency_symbol = $request->base_currency_symbol;
            $bex->base_currency_symbol_position = $request->base_currency_symbol_position;
            $bex->base_currency_text = $request->base_currency_text;
            $bex->base_currency_text_position = $request->base_currency_text_position;
            $bex->base_currency_rate = $request->base_currency_rate;
            $bex->timezone = $request->timezone;
            $bex->save();
        }

        // set timezone in .env
        if ($request->has('timezone') && $request->filled('timezone')) {
            $arr = ['TIMEZONE' => $request->timezone];
            setEnvironmentValue($arr);
            \Artisan::call('config:clear');
        }

        Session::flash('success', 'Basic informations updated successfully!');
        return back();
    }

    public function seo(Request $request)
    {
        $lang = Language::where('code', $request->language)->firstOrFail();
        $data['lang_id'] = $lang->id;
        $data['abe'] = $lang->basic_extended;
        // Named seoBs/seoBex (not bs/bex) on purpose: AppServiceProvider's
        // view()->composer('*', ...) injects its own $bs/$bex, keyed off the
        // admin's current URL locale, into EVERY view at render time — after
        // this controller's data is set, so a same-named key here would get
        // silently overwritten by that global one instead of overriding it.
        $data['seoBs'] = $lang->basic_setting;
        $data['seoBex'] = $lang->basic_extra;

        return view('admin.basic.seo', $data);
    }

    public function updateseo(Request $request, $langid)
    {
        $bs = BasicSetting::where('language_id', $langid)->firstOrFail();
        $bs->meta_title = $request->meta_title;
        $bs->meta_keywords = $request->meta_keywords;
        $bs->meta_description = $request->meta_description;
        $bs->save();

        // Default Social Share Image is one shared file regardless of
        // which language this form is open on (an image doesn't need
        // translating) — same "write the same value to every language
        // row" convention as home_version/custom_page_pagebuilder
        // elsewhere in this admin. Copied once, applied to every row.
        if ($request->filled('default_social_image')) {
            $ext = pathinfo($request->default_social_image, PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $ext;
            @copy($request->default_social_image, FRONT_IMG_PATH . $filename);
            foreach (BasicSetting::all() as $row) {
                @unlink(FRONT_IMG_PATH . $row->default_social_image);
                $row->default_social_image = $filename;
                $row->save();
            }
        }

        $bex = \App\BasicExtra::where('language_id', $langid)->firstOrFail();
        $bex->tender_meta_keywords = $request->tender_meta_keywords;
        $bex->tender_meta_description = $request->tender_meta_description;
        $bex->tender_meta_title = $request->tender_meta_title;
        if ($request->filled('tender_og_image')) {
            $ext = pathinfo($request->tender_og_image, PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $ext;
            @unlink(FRONT_IMG_PATH . $bex->tender_og_image);
            @copy($request->tender_og_image, FRONT_IMG_PATH . $filename);
            $bex->tender_og_image = $filename;
        }
        $bex->save();

        $be = BasicExtended::where('language_id', $langid)->firstOrFail();
        $be->home_meta_keywords = $request->home_meta_keywords;
        $be->home_meta_description = $request->home_meta_description;
        $be->home_meta_title = $request->home_meta_title;
        if ($request->filled('home_og_image')) {
            $ext = pathinfo($request->home_og_image, PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $ext;
            @unlink(FRONT_IMG_PATH . $be->home_og_image);
            @copy($request->home_og_image, FRONT_IMG_PATH . $filename);
            $be->home_og_image = $filename;
        }
        $be->services_meta_keywords = $request->services_meta_keywords;
        $be->services_meta_description = $request->services_meta_description;
        $be->services_meta_title = $request->services_meta_title;
        if ($request->filled('services_og_image')) {
            $ext = pathinfo($request->services_og_image, PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $ext;
            @unlink(FRONT_IMG_PATH . $be->services_og_image);
            @copy($request->services_og_image, FRONT_IMG_PATH . $filename);
            $be->services_og_image = $filename;
        }
        $be->portfolios_meta_keywords = $request->portfolios_meta_keywords;
        $be->portfolios_meta_description = $request->portfolios_meta_description;
        $be->portfolios_meta_title = $request->portfolios_meta_title;
        if ($request->filled('portfolios_og_image')) {
            $ext = pathinfo($request->portfolios_og_image, PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $ext;
            @unlink(FRONT_IMG_PATH . $be->portfolios_og_image);
            @copy($request->portfolios_og_image, FRONT_IMG_PATH . $filename);
            $be->portfolios_og_image = $filename;
        }
        $be->blogs_meta_title = $request->blogs_meta_title;
        if ($request->filled('blogs_og_image')) {
            $ext = pathinfo($request->blogs_og_image, PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $ext;
            @unlink(FRONT_IMG_PATH . $be->blogs_og_image);
            @copy($request->blogs_og_image, FRONT_IMG_PATH . $filename);
            $be->blogs_og_image = $filename;
        }
        $be->team_meta_keywords = $request->team_meta_keywords;
        $be->team_meta_description = $request->team_meta_description;
        $be->gallery_meta_keywords = $request->gallery_meta_keywords;
        $be->gallery_meta_description = $request->gallery_meta_description;
        $be->faq_meta_keywords = $request->faq_meta_keywords;
        $be->faq_meta_description = $request->faq_meta_description;
        $be->blogs_meta_keywords = $request->blogs_meta_keywords;
        $be->blogs_meta_description = $request->blogs_meta_description;
        $be->contact_meta_keywords = $request->contact_meta_keywords;
        $be->contact_meta_description = $request->contact_meta_description;
        $be->save();

        Session::flash('success', 'SEO informations updated successfully!');
        return back();
    }

    public function support(Request $request)
    {
        $lang = Language::where('code', $request->language)->firstOrFail();
        $data['lang_id'] = $lang->id;
        $data['abs'] = $lang->basic_setting;

        return view('admin.basic.support', $data);
    }

    public function updatesupport(Request $request, $langid)
    {
        $request->validate([
            'support_email' => 'required|email|max:100',
            'support_phone' => 'required|max:70',
        ]);

        $bs = BasicSetting::where('language_id', $langid)->firstOrFail();
        $bs->support_email = $request->support_email;
        $bs->support_phone = $request->support_phone;
        $bs->save();

        Session::flash('success', 'Support Informations updated successfully!');
        return back();
    }

    public function heading(Request $request)
    {
        $lang = Language::where('code', $request->language)->firstOrFail();
        $data['lang_id'] = $lang->id;
        $data['abs'] = $lang->basic_setting;
        $data['abe'] = $lang->basic_extended;
        $data['abex'] = $lang->basic_extra;

        return view('admin.basic.headings', $data);
    }

    public function updateheading(Request $request, $langid)
    {
        $request->validate([
            'service_title' => 'nullable|max:70',
            'service_subtitle' => 'nullable|max:80',
            'service_details_title' => 'nullable|max:70',
            'portfolio_title' => 'nullable|max:70',
            'portfolio_subtitle' => 'nullable|max:80',
            'portfolio_details_title' => 'nullable|max:80',
            'blog_details_title' => 'nullable|max:70',
            'contact_title' => 'nullable|max:70',
            'contact_subtitle' => 'nullable|max:80',
            'gallery_title' => 'nullable|max:70',
            'gallery_subtitle' => 'nullable|max:80',
            'team_title' => 'nullable|max:70',
            'team_subtitle' => 'nullable|max:80',
            'faq_title' => 'nullable|max:70',
            'faq_subtitle' => 'nullable|max:80',
            'blog_title' => 'nullable|max:70',
            'blog_subtitle' => 'nullable|max:80',
            'error_title' => 'nullable|max:70',
            'error_subtitle' => 'nullable|max:80',
            'cart_title' => 'nullable|max:70',
            'cart_subtitle' => 'nullable|max:80',
            'checkout_title' => 'nullable|max:70',
            'checkout_subtitle' => 'nullable|max:80',
            'event_title' => 'nullable|max:70',
            'event_subtitle' => 'nullable|max:80',
            'cause_title' => 'nullable|max:70',
            'cause_subtitle' => 'nullable|max:80',
            'client_feedback_title' => 'nullable|max:70',
            'client_feedback_subtitle' => 'nullable|max:70'
        ]);

        $bs = BasicSetting::where('language_id', $langid)->firstOrFail();
        $be = BasicExtended::where('language_id', $langid)->firstOrFail();
        $bex = BasicExtra::where('language_id', $langid)->firstOrFail();

        $bs->service_title = $request->service_title;
        $bs->service_subtitle = $request->service_subtitle;
        $bs->service_details_title = $request->service_details_title;
        $bs->portfolio_title = $request->portfolio_title;
        $bs->portfolio_subtitle = $request->portfolio_subtitle;
        $bs->portfolio_details_title = $request->portfolio_details_title;
        $bs->blog_details_title = $request->blog_details_title;
        $bs->contact_title = $request->contact_title;
        $bs->contact_subtitle = $request->contact_subtitle;
        $bs->gallery_title = $request->gallery_title;
        $bs->gallery_subtitle = $request->gallery_subtitle;
        $bs->team_title = $request->team_title;
        $bs->team_subtitle = $request->team_subtitle;
        $bs->faq_title = $request->faq_title;
        $bs->faq_subtitle = $request->faq_subtitle;
        $bs->blog_title = $request->blog_title;
        $bs->blog_subtitle = $request->blog_subtitle;
        $bs->error_title = $request->error_title;
        $bs->error_subtitle = $request->error_subtitle;
        $bs->event_title = $request->event_title;
        $bs->event_subtitle = $request->event_subtitle;
        $bs->event_details_title = $request->event_details_title;
        $bs->cause_title = $request->cause_title;
        $bs->cause_subtitle = $request->cause_subtitle;
        $bs->cause_details_title = $request->cause_details_title;
        $bs->save();


        $be->cart_title = $request->cart_title;
        $be->cart_subtitle = $request->cart_subtitle;
        $be->checkout_title = $request->checkout_title;
        $be->checkout_subtitle = $request->checkout_subtitle;
        $be->save();

        $bex->client_feedback_title = $request->client_feedback_title;
        $bex->client_feedback_subtitle = $request->client_feedback_subtitle;
        $bex->save();

        Session::flash('success', 'Page title & subtitles updated successfully!');
        return back();
    }

    public function script()
    {
        return view('admin.basic.scripts');
    }

    public function updatescript(Request $request)
    {

        $bss = BasicSetting::all();

        foreach ($bss as $bs) {
            $bs->google_analytics_script = $request->google_analytics_script;
            $bs->is_analytics = $request->is_analytics;
            $bs->google_analytics_measurement_id = $request->google_analytics_measurement_id;
            $bs->gtm_container_id = $request->gtm_container_id;
            $bs->google_site_verification = $request->google_site_verification;
            $bs->bing_site_verification = $request->bing_site_verification;
            $bs->is_recaptcha = $request->is_recaptcha;
            $bs->google_recaptcha_site_key = $request->google_recaptcha_site_key;
            $bs->google_recaptcha_secret_key = $request->google_recaptcha_secret_key;
            $bs->save();
        }


        $bes = BasicExtended::all();
        foreach ($bes as $key => $be) {
            $be->facebook_pexel_script = $request->facebook_pexel_script;
            $be->is_facebook_pexel = $request->is_facebook_pexel;
            $be->facebook_pixel_id = $request->facebook_pixel_id;
            $be->facebook_capi_access_token = $request->facebook_capi_access_token;
            $be->save();
        }

        // New tracking/SEO fields above are read by the global '*' view
        // composer's per-language cache (AppServiceProvider::globalViewData,
        // flexible 25-30min TTL) — flush it so a saved change shows up on
        // the next page load instead of waiting out the stale window.
        \App\Providers\AppServiceProvider::flushGlobalViewCache();

        $bexs = BasicExtra::all();
        foreach ($bexs as $key => $bex) {
            $bex->is_whatsapp = $request->is_whatsapp;
            $bex->whatsapp_number = $request->whatsapp_number;
            $bex->whatsapp_header_title = $request->whatsapp_header_title;
            $bex->whatsapp_popup_message = $request->whatsapp_popup_message;
            $bex->whatsapp_popup = $request->whatsapp_popup;

            $bex->twilio_status = $request->twilio_status ?? 0;
            $bex->twilio_account_sid = $request->twilio_account_sid;
            $bex->twilio_auth_token = $request->twilio_auth_token;
            $bex->twilio_from_number = $request->twilio_from_number;

            $bex->save();
        }
        Session::flash('success', 'Scripts updated successfully!');
        return back();
    }

    public function maintainance(Request $request)
    {
        if ($request->has('language')) {
            $lang = Language::where('code', $request->language)->first();
            if (!$lang) {
                $lang = Language::where('is_default', 1)->first();
            }
        } else {
            $lang = Language::where('is_default', 1)->first();
        }

        $data['lang_id'] = $lang->id;
        $data['abs'] = $lang->basic_setting;

        return view('admin.basic.maintainance', $data);
    }

    public function updatemaintainance(Request $request, $langid)
    {
        $maintenance = $request->maintenance;
        $allowedExts = allowed_image_extensions();
        $extLogo = pathinfo($maintenance, PATHINFO_EXTENSION);

        $rules = [];

        if ($request->filled('maintenance')) {
            $rules['maintenance'] = [
                function ($attribute, $value, $fail) use ($extLogo, $allowedExts) {
                    if (!in_array($extLogo, $allowedExts)) {
                        return $fail("Only png, jpg, jpeg image is allowed");
                    }
                }
            ];
        }

        $request->validate($rules);

        if ($request->filled('maintenance')) {
            @unlink(FRONT_IMG_PATH . 'maintainance.png');
            @copy($maintenance, FRONT_IMG_PATH . 'maintainance.png');
        }

        $bs = BasicSetting::where('language_id', $langid)->firstOrFail();
        $bs->maintainance_text = $request->maintainance_text;

        // Update mode and secret path for ALL languages
        $bss = BasicSetting::all();
        foreach ($bss as $b) {
            $b->maintainance_mode = $request->maintainance_mode;
            $b->secret_path = $request->secret_path;
            $b->save();
        }

        $bs->save();


        $down = "down";
        if ($request->filled('secret_path')) {
            $down .= " --secret=" . $request->secret_path;
        }

        if ($request->maintainance_mode == 1) {
            @unlink('core/storage/framework/down');
            Artisan::call($down);
        } else {
            Artisan::call('up');
        }

        Session::flash('success', 'Maintanance mode & page updated successfully!');
        return back();
    }


    public function sections(Request $request)
    {
        $data['abs'] = BasicSetting::first();
        $data['abe'] = BasicExtended::first();

        return view('admin.home.sections', $data);
    }

    public function updatesections(Request $request)
    {
        $request->validate([
            'tender_carousel_speed'      => 'nullable|integer|min:2000|max:30000',
            'blog_carousel_speed'        => 'nullable|integer|min:2000|max:30000',
            'portfolio_carousel_speed'   => 'nullable|integer|min:2000|max:30000',
            'testimonial_carousel_speed' => 'nullable|integer|min:2000|max:30000',
            'team_carousel_speed'        => 'nullable|integer|min:2000|max:30000',
            'partner_carousel_speed'     => 'nullable|integer|min:2000|max:30000',
            'approach_carousel_speed'    => 'nullable|integer|min:2000|max:30000',
        ]);

        $be = BasicExtended::select('theme_version')->first();
        $bss = BasicSetting::all();

        foreach ($bss as $key => $bs) {
            $bs->feature_section = $request->feature_section;

            $bs->intro_section = $request->intro_section;
            $bs->service_section = $request->service_section;
            $bs->approach_section = $request->approach_section;
            $bs->statistics_section = $request->statistics_section;
            $bs->portfolio_section = $request->portfolio_section;
            $bs->tender_section = $request->tender_section;
            $bs->testimonial_section = $request->testimonial_section;
            $bs->team_section = $request->team_section;
            $bs->call_to_action_section = $request->call_to_action_section;

            $bs->news_section = $request->news_section;
            $bs->partner_section = $request->partner_section;
            $bs->top_footer_section = $request->top_footer_section;
            $bs->copyright_section = $request->copyright_section;
            $bs->save();
        }

        $bes = BasicExtended::all();
        foreach ($bes as $key => $be) {
            $be->tender_carousel_speed = $request->filled('tender_carousel_speed') ? (int) $request->tender_carousel_speed : 4500;
            $be->blog_carousel_speed = $request->filled('blog_carousel_speed') ? (int) $request->blog_carousel_speed : 4500;
            $be->portfolio_carousel_speed = $request->filled('portfolio_carousel_speed') ? (int) $request->portfolio_carousel_speed : 4500;
            $be->testimonial_carousel_speed = $request->filled('testimonial_carousel_speed') ? (int) $request->testimonial_carousel_speed : 4500;
            $be->team_carousel_speed = $request->filled('team_carousel_speed') ? (int) $request->team_carousel_speed : 4500;
            $be->partner_carousel_speed = $request->filled('partner_carousel_speed') ? (int) $request->partner_carousel_speed : 4500;
            $be->approach_carousel_speed = $request->filled('approach_carousel_speed') ? (int) $request->approach_carousel_speed : 4500;
            $be->save();
        }

        // These carousel-speed values render as a static inline <script>
        // block in every page's shared layout (front/default/layout.blade.php)
        // — on a Cloudflare-cached public page that block is baked into the
        // cached HTML too, so a save here otherwise silently does nothing
        // visible until that cache's own TTL expires. Same root cause/fix as
        // Portfolio's own missing purge earlier.
        CloudflareController::purge();

        Session::flash('success', 'Sections customized successfully!');
        return back();
    }

    public function cookiealert(Request $request)
    {
        $lang = Language::where('code', $request->language)->firstOrFail();
        $data['lang_id'] = $lang->id;
        $data['abe'] = $lang->basic_extended;

        return view('admin.basic.cookie', $data);
    }

    public function updatecookie(Request $request, $langid)
    {
        $request->validate([
            'cookie_alert_status' => 'required',
            'cookie_alert_text' => 'required',
            'cookie_alert_button_text' => 'required|max:25',
        ]);

        $be = BasicExtended::where('language_id', $langid)->firstOrFail();
        $be->cookie_alert_status = $request->cookie_alert_status;
        $be->cookie_alert_text = str_replace(url('/') . '/assets/front/img/', "{base_url}/assets/front/img/", $request->cookie_alert_text);
        $be->cookie_alert_button_text = $request->cookie_alert_button_text;
        $be->save();

        Session::flash('success', 'Cookie alert updated successfully!');
        return back();
    }
}
