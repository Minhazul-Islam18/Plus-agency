<?php

namespace App\Http\Controllers\Admin;

use App\BasicExtended as BE;
use App\BasicExtra;
use App\BasicSetting as BS;
use App\Http\Controllers\Controller;
use App\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class LanguageController extends Controller
{
    public function index($lang = false)
    {
        $data['languages'] = Language::all();
        return view('admin.language.index', $data);
    }


    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|max:255',
            'code' => [
                'required',
                'max:255',
                'unique:languages'
            ],
            'direction' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errmsgs = $validator->getMessageBag()->add('error', 'true');
            return response()->json($validator->errors());
        }

        $data = file_get_contents(resource_path('lang/') . 'default.json');
        $json_file = trim(strtolower($request->code)) . '.json';
        $path = resource_path('lang/') . $json_file;

        File::put($path, $data);

        $in['name'] = $request->name;
        $in['code'] = $request->code;
        $in['rtl'] = $request->direction;
        if (Language::where('is_default', 1)->count() > 0) {
            $in['is_default'] = 0;
        } else {
            $in['is_default'] = 1;
        }
        $lang = Language::create($in);

        // duplicate First row of basic_settings for current language
        $dbs = Language::where('is_default', 1)->first()->basic_setting;
        $cols = json_decode($dbs, true);
        $bs = new BS;

        // Define language-specific text fields that should be empty for new languages
        $languageSpecificFields = [
            'website_title',
            'footer_text',
            'newsletter_text',
            'copyright_text',
            'hero_section_title',
            'hero_section_bold_text',
            'hero_section_text',
            'hero_section_button_text',
            'hero_section_button_url',
            'hero_section_video_link',
            'intro_section_title',
            'intro_section_text',
            'intro_section_button_text',
            'intro_section_button_url',
            'intro_section_video_link',
            'service_section_title',
            'service_section_subtitle',
            'approach_title',
            'approach_subtitle',
            'approach_button_text',
            'approach_button_url',
            'cta_section_text',
            'cta_section_button_text',
            'cta_section_button_url',
            'portfolio_section_title',
            'portfolio_section_text',
            'team_section_title',
            'team_section_subtitle',
            'contact_form_title',
            'contact_form_subtitle',
            'quote_title',
            'quote_subtitle',
            'maintainance_text',
            'service_title',
            'service_subtitle',
            'portfolio_title',
            'portfolio_subtitle',
            'testimonial_title',
            'testimonial_subtitle',
            'blog_section_title',
            'blog_section_subtitle',
            'faq_title',
            'faq_subtitle',
            'blog_title',
            'blog_subtitle',
            'service_details_title',
            'portfolio_details_title',
            'blog_details_title',
            'gallery_title',
            'gallery_subtitle',
            'team_title',
            'team_subtitle',
            'contact_title',
            'contact_subtitle',
            'error_title',
            'error_subtitle',
            'event_title',
            'event_subtitle',
            'event_details_title',
            'cause_title',
            'cause_subtitle',
            'cause_details_title'
        ];

        foreach ($cols as $key => $value) {
            // if the column is 'id' [primary key] then skip it
            if ($key == 'id') {
                continue;
            }

            // if the field is language-specific, set it to NULL instead of copying
            if (in_array($key, $languageSpecificFields)) {
                $bs[$key] = null;
                continue;
            }


            // create favicon image using default language image & save unique name in database
            if ($key == 'favicon') {
                // take default lang image
                $dimg = url('/assets/front/img/') . '/' . $dbs->favicon;

                // copy paste the default language image with different unique name
                $filename = uniqid();
                if (($pos = strpos($dbs->favicon, ".")) !== FALSE) {
                    $ext = substr($dbs->favicon, $pos + 1);
                }
                $newImgName = $filename . '.' . $ext;

                @copy($dimg, 'assets/front/img/' . $newImgName);

                // save the unique name in database
                $bs[$key] = $newImgName;

                // continue the loop
                continue;
            }

            // create logo image using default language image & save unique name in database
            if ($key == 'logo') {
                // take default lang image
                $dimg = url('/assets/front/img/') . '/' . $dbs->logo;

                // copy paste the default language image with different unique name
                $filename = uniqid();
                if (($pos = strpos($dbs->logo, ".")) !== FALSE) {
                    $ext = substr($dbs->logo, $pos + 1);
                }
                $newImgName = $filename . '.' . $ext;

                @copy($dimg, 'assets/front/img/' . $newImgName);

                // save the unique name in database
                $bs[$key] = $newImgName;

                // continue the loop
                continue;
            }

            // create breadcrumb image using default language image & save unique name in database
            if ($key == 'breadcrumb') {
                // take default lang image
                $dimg = url('/assets/front/img/') . '/' . $dbs->breadcrumb;

                // copy paste the default language image with different unique name
                $filename = uniqid();
                if (($pos = strpos($dbs->breadcrumb, ".")) !== FALSE) {
                    $ext = substr($dbs->breadcrumb, $pos + 1);
                }
                $newImgName = $filename . '.' . $ext;

                @copy($dimg, 'assets/front/img/' . $newImgName);

                // save the unique name in database
                $bs[$key] = $newImgName;

                // continue the loop
                continue;
            }

            // create footer_logo image using default language image & save unique name in database
            if ($key == 'footer_logo') {
                // take default lang image
                $dimg = url('/assets/front/img/') . '/' . $dbs->footer_logo;

                // copy paste the default language image with different unique name
                $filename = uniqid();
                if (($pos = strpos($dbs->footer_logo, ".")) !== FALSE) {
                    $ext = substr($dbs->footer_logo, $pos + 1);
                }
                $newImgName = $filename . '.' . $ext;

                @copy($dimg, 'assets/front/img/' . $newImgName);

                // save the unique name in database
                $bs[$key] = $newImgName;

                // continue the loop
                continue;
            }

            // create hero_bg image using default language image & save unique name in database
            if ($key == 'hero_bg') {
                // take default lang image
                $dimg = url('/assets/front/img/') . '/' . $dbs->hero_bg;

                // copy paste the default language image with different unique name
                $filename = uniqid();
                if (($pos = strpos($dbs->hero_bg, ".")) !== FALSE) {
                    $ext = substr($dbs->hero_bg, $pos + 1);
                }
                $newImgName = $filename . '.' . $ext;

                @copy($dimg, 'assets/front/img/' . $newImgName);

                // save the unique name in database
                $bs[$key] = $newImgName;

                // continue the loop
                continue;
            }

            // create intro_bg image using default language image & save unique name in database
            if ($key == 'intro_bg') {
                // take default lang image
                $dimg = url('/assets/front/img/') . '/' . $dbs->intro_bg;

                // copy paste the default language image with different unique name
                $filename = uniqid();
                if (($pos = strpos($dbs->intro_bg, ".")) !== FALSE) {
                    $ext = substr($dbs->intro_bg, $pos + 1);
                }
                $newImgName = $filename . '.' . $ext;

                @copy($dimg, 'assets/front/img/' . $newImgName);

                // save the unique name in database
                $bs[$key] = $newImgName;

                // continue the loop
                continue;
            }

            // create cta_bg image using default language image & save unique name in database
            if ($key == 'cta_bg') {
                // take default lang image
                $dimg = url('/assets/front/img/') . '/' . $dbs->cta_bg;

                // copy paste the default language image with different unique name
                $filename = uniqid();
                if (($pos = strpos($dbs->cta_bg, ".")) !== FALSE) {
                    $ext = substr($dbs->cta_bg, $pos + 1);
                }
                $newImgName = $filename . '.' . $ext;

                @copy($dimg, 'assets/front/img/' . $newImgName);

                // save the unique name in database
                $bs[$key] = $newImgName;

                // continue the loop
                continue;
            }

            // create team_bg image using default language image & save unique name in database
            if ($key == 'team_bg') {
                // take default lang image
                $dimg = url('/assets/front/img/') . '/' . $dbs->team_bg;

                // copy paste the default language image with different unique name
                $filename = uniqid();
                if (($pos = strpos($dbs->team_bg, ".")) !== FALSE) {
                    $ext = substr($dbs->team_bg, $pos + 1);
                }
                $newImgName = $filename . '.' . $ext;

                @copy($dimg, 'assets/front/img/' . $newImgName);

                // save the unique name in database
                $bs[$key] = $newImgName;

                // continue the loop
                continue;
            }

            // create announcement image using default language image & save unique name in database
            if ($key == 'announcement') {
                // take default lang image
                $dimg = url('/assets/front/img/') . '/' . $dbs->announcement;

                // copy paste the default language image with different unique name
                $filename = uniqid();
                if (($pos = strpos($dbs->announcement, ".")) !== FALSE) {
                    $ext = substr($dbs->announcement, $pos + 1);
                }
                $newImgName = $filename . '.' . $ext;

                @copy($dimg, 'assets/front/img/' . $newImgName);

                // save the unique name in database
                $bs[$key] = $newImgName;

                // continue the loop
                continue;
            }

            $bs[$key] = $value;
        }
        $bs['language_id'] = $lang->id;
        $bs->save();

        // duplicate First row of basic_settings_extended for current language
        $be = BE::firstOrFail();
        $cols = json_decode($be, true);
        $be = new BE;

        // Define language-specific text fields for basic_settings_extended
        $extendedLanguageSpecificFields = [
            'cookie_alert_text',
            'cookie_alert_button_text',
            'home_meta_keywords',
            'home_meta_description',
            'services_meta_keywords',
            'services_meta_description',
            'portfolios_meta_keywords',
            'portfolios_meta_description',
            'team_meta_keywords',
            'team_meta_description',
            'calendar_meta_keywords',
            'calendar_meta_description',
            'gallery_meta_keywords',
            'gallery_meta_description',
            'faq_meta_keywords',
            'faq_meta_description',
            'blogs_meta_keywords',
            'blogs_meta_description',
            'contact_meta_keywords',
            'contact_meta_description',
            'quote_meta_keywords',
            'quote_meta_description',
            'cart_title',
            'cart_subtitle',
            'checkout_title',
            'checkout_subtitle',
            'popular_tags',
            'about_title',
            'about_bold_text',
            'about_summary',
            'about_signature',
            'about_phone',
            'cart_meta_keywords',
            'cart_meta_description',
            'checkout_meta_keywords',
            'checkout_meta_description',
            'login_meta_keywords',
            'login_meta_description',
            'register_meta_keywords',
            'register_meta_description',
            'forgot_meta_keywords',
            'forgot_meta_description',
        ];

        foreach ($cols as $key => $value) {
            // if the column is 'id' [primary key] then skip it
            if ($key == 'id') {
                continue;
            }

            // if the field is language-specific, set it to NULL instead of copying
            if (in_array($key, $extendedLanguageSpecificFields)) {
                $be[$key] = null;
                continue;
            }

            $be[$key] = $value;
        }
        $be['language_id'] = $lang->id;
        $be->save();


        // duplicate First row of basic_settings_extra for current language
        $bex = BasicExtra::firstOrFail();
        $cols = json_decode($bex, true);
        $bex = new BasicExtra;

        // Define language-specific text fields for basic_settings_extra
        $extraLanguageSpecificFields = [
            'client_feedback_title',
            'client_feedback_subtitle',
            'whatsapp_header_title',
            'whatsapp_popup_message'
        ];

        foreach ($cols as $key => $value) {
            // if the column is 'id' [primary key] then skip it
            if ($key == 'id') {
                continue;
            }

            // if the field is language-specific, set it to NULL instead of copying
            if (in_array($key, $extraLanguageSpecificFields)) {
                $bex[$key] = null;
                continue;
            }

            $bex[$key] = $value;
        }
        $bex['language_id'] = $lang->id;
        $bex->save();


        Session::flash('success', 'Language added successfully!');
        return "success";
    }

    public function edit($id)
    {
        if ($id > 0) {
            $data['language'] = Language::findOrFail($id);
        }
        $data['id'] = $id;

        return view('admin.language.edit', $data);
    }


    public function update(Request $request)
    {
        $language = Language::findOrFail($request->language_id);

        $rules = [
            'name' => 'required|max:255',
            'code' => [
                'required',
                'max:255',
                Rule::unique('languages')->ignore($language->id),
            ],
            'direction' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errmsgs = $validator->getMessageBag()->add('error', 'true');
            return response()->json($validator->errors());
        }

        $language->name = $request->name;
        $language->code = $request->code;
        $language->rtl = $request->direction;
        $language->save();

        Session::flash('success', 'Language updated successfully!');
        return "success";
    }

    public function editKeyword($id)
    {
        if ($id > 0) {
            $la = Language::findOrFail($id);
            $json = file_get_contents(resource_path('lang/') . $la->code . '.json');
            $list_lang = Language::all();

            if (empty($json)) {
                return back()->with('alert', 'File Not Found.');
            }

            return view('admin.language.edit-keyword', compact('json', 'la'));
        } else if ($id == 0) {
            $json = file_get_contents(resource_path('lang/') . 'default.json');

            if (empty($json)) {
                return back()->with('alert', 'File Not Found.');
            }

            return view('admin.language.edit-keyword', compact('json'));
        }
    }

    public function updateKeyword(Request $request, $id)
    {
        $lang = Language::findOrFail($id);
        $content = json_encode($request->keys);
        if ($content === 'null') {
            return back()->with('alert', 'At Least One Field Should Be Fill-up');
        }
        file_put_contents(resource_path('lang/') . $lang->code . '.json', $content);
        return back()->with('success', 'Updated Successfully');
    }

    public function delete($id)
    {
        $la = Language::findOrFail($id);
        if ($la->is_default == 1) {
            return back()->with('warning', 'Default language cannot be deleted!');
        }
        @unlink('assets/front/img/languages/' . $la->icon);
        @unlink(resource_path('lang/') . $la->code . '.json');
        if (session()->get('lang') == $la->code) {
            session()->forget('lang');
        }

        // deleting basic_settings and basic_extended for corresponding language
        $bs = $la->basic_setting;
        if (!empty($bs)) {

            @unlink('assets/front/img/' . $bs->favicon);

            @unlink('assets/front/img/' . $bs->logo);

            @unlink('assets/front/img/' . $bs->breadcrumb);

            @unlink('assets/front/img/' . $bs->announcement);

            @unlink('assets/front/img/' . $bs->hero_bg);

            @unlink('assets/front/img/' . $bs->intro_bg);

            @unlink('assets/front/img/' . $bs->cta_bg);

            @unlink('assets/front/img/' . $bs->team_bg);

            @unlink('assets/front/img/' . $bs->footer_logo);

            $bs->delete();
        }
        $be = $la->basic_extended;
        if (!empty($be)) {
            $be->delete();
        }

        $bex = $la->basic_extra;
        if (!empty($bex)) {
            $bex->delete();
        }

        // deleting pages for corresponding language
        if (!empty($la->pages)) {
            $la->pages()->delete();
        }

        // deleting sliders for corresponding language
        if (!empty($la->sliders)) {
            $sliders = $la->sliders;
            foreach ($sliders as $slider) {
                @unlink('assets/front/img/sliders/' . $slider->image);
                $slider->delete();
            }
        }

        // deleting testimonials for corresponding language
        if (!empty($la->testimonials)) {
            $testimonials = $la->testimonials;
            foreach ($testimonials as $testimonial) {
                @unlink('assets/front/img/testimonials/' . $testimonial->image);
                $testimonial->delete();
            }
        }
        // deleting pages for corresponding language
        if (!empty($la->pages)) {
            $la->pages()->delete();
        }


        // deleting members for corresponding language
        if (!empty($la->members)) {
            $members = $la->members;
            foreach ($members as $member) {
                @unlink('assets/front/img/members/' . $member->image);
                $member->delete();
            }
        }

        // deleting partners for corresponding language
        if (!empty($la->partners)) {
            $partners = $la->partners;
            foreach ($partners as $partner) {
                @unlink('assets/front/img/partners/' . $partner->image);
                $partner->delete();
            }
        }

        // deleting service categories for corresponding language
        if (!empty($la->scategories)) {
            $scategories = $la->scategories;
            foreach ($scategories as $scategory) {
                @unlink('assets/front/img/service_category_icons/' . $scategory->image);
                $scategory->delete();
            }
        }

        // deleting services for corresponding language
        if (!empty($la->services)) {
            $services = $la->services;
            foreach ($services as $service) {
                @unlink('assets/front/img/services/' . $service->main_image);
                $service->delete();
            }
        }

        // deleting job categories for corresponding language
        if (!empty($la->jcategories)) {
            $jcategories = $la->jcategories;
            foreach ($jcategories as $jcategory) {
                $jcategory->delete();
            }
        }


        // deleting jobs for corresponding language
        if (!empty($la->jobs)) {
            $jobs = $la->jobs;
            foreach ($jobs as $job) {
                $job->delete();
            }
        }

        // deleting feature for corresponding language
        if (!empty($la->features)) {
            $features = $la->features;
            foreach ($features as $feature) {
                $feature->delete();
            }
        }

        // deleting gallery categories for corresponding language
        if (!empty($la->galleryCategory)) {
            $la->galleryCategory()->delete();
        }

        // deleting gallery images for corresponding language
        if (!empty($la->galleries)) {
            $galleries = $la->galleries;
            foreach ($galleries as $gallery) {
                @unlink('assets/front/img/gallery/' . $gallery->image);
                $gallery->delete();
            }
        }

        // deleting portfolios for corresponding language
        if (!empty($la->portfolios)) {
            $portfolios = $la->portfolios;
            foreach ($portfolios as $portfolio) {
                @unlink('assets/front/img/portfolios/featured/' . $portfolio->featured_image);

                // deleting slider images of the specific portfolio
                $pis = $portfolio->portfolio_images;
                foreach ($pis as $pi) {
                    @unlink('assets/front/img/portfolios/sliders/' . $pi->image);
                    $pi->delete();
                }
                $portfolio->delete();
            }
        }


        // deleting services for corresponding language
        if (!empty($la->blogs)) {
            $blogs = $la->blogs;
            foreach ($blogs as $blog) {
                @unlink('assets/front/img/blogs/' . $blog->main_image);
                $blog->delete();
            }
        }

        // deleting blog categories for corresponding language
        if (!empty($la->bcategories)) {
            $bcategories = $la->bcategories;
            foreach ($bcategories as $bcat) {
                $bcat->delete();
            }
        }

        // deleting points for corresponding language
        if (!empty($la->points)) {
            $la->points()->delete();
        }

        // deleting packages for corresponding language
        if (!empty($la->statistics)) {
            $la->statistics()->delete();
        }

        // deleting useful links for corresponding language
        if (!empty($la->ulinks)) {
            $la->ulinks()->delete();
        }
        // deleting statistics for corresponding language
        if (!empty($la->statistics)) {
            $la->statistics()->delete();
        }

        // deleting faq categories for corresponding language
        if (!empty($la->faqCategory)) {
            $la->faqCategory()->delete();
        }

        // deleting faqs for corresponding language
        if (!empty($la->faqs)) {
            $la->faqs()->delete();
        }

        // deleting event calendars for corresponding language
        if (!empty($la->calendars)) {
            $la->calendars()->delete();
        }

        // deleting menus for corresponding language
        if (!empty($la->menus)) {
            $la->menus()->delete();
        }

        // deleting offline gateways for corresponding language
        if (!empty($la->offline_gateways)) {
            $la->offline_gateways()->delete();
        }


        $la->delete();
        return back()->with('success', 'Delete Successfully');
    }


    public function default(Request $request, $id)
    {
        Language::where('is_default', 1)->update(['is_default' => 0]);
        $lang = Language::find($id);
        $lang->is_default = 1;
        $lang->save();
        return back()->with('success', $lang->name . ' laguage is set as defualt.');
    }

    public function status(Request $request)
    {
        $language = Language::findOrFail($request->id);

        // Prevent disabling default language
        if ($language->is_default == 1 && $request->status == 0) {
            return response()->json(['success' => false, 'message' => 'Default language cannot be deactivated!'], 400);
        }

        $language->status = $request->status;
        $language->save();

        return response()->json(['success' => true]);
    }

    public function rtlcheck($langid)
    {
        if ($langid > 0) {
            $lang = Language::find($langid);
        } else {
            return 0;
        }

        return $lang->rtl;
    }
}
