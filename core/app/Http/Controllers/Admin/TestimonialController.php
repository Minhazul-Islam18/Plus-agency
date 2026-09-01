<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Language;
use App\Testimonial;
use App\BasicSetting as BS;
use App\BasicExtended;
use Validator;
use Session;

class TestimonialController extends Controller
{
    private const IMG_SUBDIR = 'testimonials/';
    public function index(Request $request)
    {
        $lang = Language::where('code', $request->language)->firstOrFail();
        $data['lang_id'] = $lang->id;
        $data['abs'] = $lang->basic_setting;
        $data['abe'] = $lang->basic_extended;
        $data['testimonials'] = Testimonial::where('language_id', $data['lang_id'])->orderBy('id', 'DESC')->get();

        return view('admin.home.testimonial.index', $data);
    }

    public function edit($id)
    {
        $data['testimonial'] = Testimonial::findOrFail($id);
        return view('admin.home.testimonial.edit', $data);
    }

    public function store(Request $request)
    {
        $image = $request->image;
        $allowedExts = array('jpg', 'png', 'jpeg', 'svg', 'webp', 'avif');
        $extImage = pathinfo($image, PATHINFO_EXTENSION);

        $companyLogo = $request->company_logo;
        $extCompanyLogo = pathinfo($companyLogo, PATHINFO_EXTENSION);

        $messages = [
            'language_id.required' => 'The language field is required'
        ];

        $rules = [
            'language_id' => 'required',
            'image' => 'required',
            'comment' => 'required',
            'name' => 'required|max:50',
            'rank' => 'required|max:50',
            'serial_number' => 'required|integer',
            'company_url' => 'nullable|url|max:255',
        ];

        if ($request->filled('image')) {
            $rules['image'] = [
                function ($attribute, $value, $fail) use ($extImage, $allowedExts) {
                    if (!in_array($extImage, $allowedExts)) {
                        return $fail("Only png, jpg, jpeg, svg image is allowed");
                    }
                }
            ];
        }

        if ($request->filled('company_logo')) {
            $rules['company_logo'] = [
                function ($attribute, $value, $fail) use ($extCompanyLogo, $allowedExts) {
                    if (!in_array($extCompanyLogo, $allowedExts)) {
                        return $fail("Only png, jpg, jpeg, svg image is allowed");
                    }
                }
            ];
        }

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            $errmsgs = $validator->getMessageBag()->add('error', 'true');
            return response()->json($validator->errors());
        }

        $testimonial = new Testimonial;
        $testimonial->language_id = $request->language_id;
        $testimonial->comment = $request->comment;
        $testimonial->name = $request->name;
        $testimonial->rank = $request->rank;
        $testimonial->image = $request->testimonial_image;
        $testimonial->serial_number = $request->serial_number;
        $testimonial->company_url = $request->filled('company_url') ? trim($request->company_url) : null;

        if ($request->filled('image')) {
            $filename = uniqid() .'.'. $extImage;
            @copy($image, FRONT_IMG_PATH . self::IMG_SUBDIR . $filename);
            $testimonial->image = $filename;
        }

        if ($request->filled('company_logo')) {
            $filename = uniqid() . '.' . $extCompanyLogo;
            @copy($companyLogo, FRONT_IMG_PATH . self::IMG_SUBDIR . $filename);
            $testimonial->company_logo = $filename;
        }

        $testimonial->save();

        Session::flash('success', 'Testimonial added successfully!');
        return "success";
    }

    public function update(Request $request)
    {
        $image = $request->image;
        $allowedExts = array('jpg', 'png', 'jpeg', 'svg', 'webp', 'avif');
        $extImage = pathinfo($image, PATHINFO_EXTENSION);

        $companyLogo = $request->company_logo;
        $extCompanyLogo = pathinfo($companyLogo, PATHINFO_EXTENSION);

        $rules = [
            'comment' => 'required',
            'name' => 'required|max:50',
            'rank' => 'required|max:50',
            'serial_number' => 'required|integer',
            'company_url' => 'nullable|url|max:255',
        ];

        if ($request->filled('image')) {
            $rules['image'] = [
                function ($attribute, $value, $fail) use ($extImage, $allowedExts) {
                    if (!in_array($extImage, $allowedExts)) {
                        return $fail("Only png, jpg, jpeg, svg image is allowed");
                    }
                }
            ];
        }

        if ($request->filled('company_logo')) {
            $rules['company_logo'] = [
                function ($attribute, $value, $fail) use ($extCompanyLogo, $allowedExts) {
                    if (!in_array($extCompanyLogo, $allowedExts)) {
                        return $fail("Only png, jpg, jpeg, svg image is allowed");
                    }
                }
            ];
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errmsgs = $validator->getMessageBag()->add('error', 'true');
            return response()->json($validator->errors());
        }

        $testimonial = Testimonial::findOrFail($request->testimonial_id);
        $testimonial->comment = $request->comment;
        $testimonial->name = $request->name;
        $testimonial->rank = $request->rank;
        $testimonial->serial_number = $request->serial_number;
        $testimonial->company_url = $request->filled('company_url') ? trim($request->company_url) : null;

        if ($request->filled('image')) {
            @unlink(FRONT_IMG_PATH . self::IMG_SUBDIR . $testimonial->image);
            $filename = uniqid() .'.'. $extImage;
            @copy($image, FRONT_IMG_PATH . self::IMG_SUBDIR . $filename);
            $testimonial->image = $filename;
        }

        if ($request->filled('company_logo')) {
            if ($testimonial->company_logo) {
                @unlink(FRONT_IMG_PATH . self::IMG_SUBDIR . $testimonial->company_logo);
            }
            $filename = uniqid() . '.' . $extCompanyLogo;
            @copy($companyLogo, FRONT_IMG_PATH . self::IMG_SUBDIR . $filename);
            $testimonial->company_logo = $filename;
        }

        $testimonial->save();

        Session::flash('success', 'Testimonial updated successfully!');
        return "success";
    }

    public function textupdate(Request $request, $langid)
    {
        $testimonialSectionBg = $request->testimonial_section_bg;
        $allowedExts = array('jpg', 'png', 'jpeg', 'svg', 'webp', 'avif');
        $extTestimonialSectionBg = pathinfo($testimonialSectionBg, PATHINFO_EXTENSION);

        $rules = [
            'testimonial_section_title' => 'required|max:25',
            'testimonial_section_subtitle' => 'required|max:80',
            'testimonial_overlay_color' => 'required',
            'testimonial_overlay_opacity' => 'required|numeric|max:1|min:0'
        ];

        if ($request->filled('testimonial_section_bg')) {
            $rules['testimonial_section_bg'] = [
                function ($attribute, $value, $fail) use ($extTestimonialSectionBg, $allowedExts) {
                    if (!in_array($extTestimonialSectionBg, $allowedExts)) {
                        return $fail("Only png, jpg, jpeg, svg image is allowed");
                    }
                }
            ];
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errmsgs = $validator->getMessageBag()->add('error', 'true');
            return response()->json($validator->errors());
        }

        $bs = BS::where('language_id', $langid)->firstOrFail();
        $bs->testimonial_title = $request->testimonial_section_title;
        $bs->testimonial_subtitle = $request->testimonial_section_subtitle;
        $bs->save();

        $be = BasicExtended::where('language_id', $langid)->firstOrFail();

        // Save overlay color and opacity
        $be->testimonial_overlay_color = $request->testimonial_overlay_color;
        $be->testimonial_overlay_opacity = $request->testimonial_overlay_opacity;

        // Handle testimonial section background image upload
        if ($request->filled('testimonial_section_bg')) {
            // Delete old image if exists
            if ($be->testimonial_section_bg) {
                @unlink(base_path(FRONT_IMG_DIR . $be->testimonial_section_bg));
            }

            $filename = uniqid() . '.' . $extTestimonialSectionBg;

            // Convert to absolute paths
            // assets folder is one level up from core directory
            $sourcePath = base_path('../' . $testimonialSectionBg);
            $destinationPath = base_path(FRONT_IMG_DIR . $filename);

            // Copy the file
            copy($sourcePath, $destinationPath);

            $be->testimonial_section_bg = $filename;
        }
        // Handle testimonial section background image deletion (only if not uploading new one)
        elseif ($request->delete_testimonial_section_bg == '1') {
            if ($be->testimonial_section_bg) {
                @unlink(base_path(FRONT_IMG_DIR . $be->testimonial_section_bg));
                $be->testimonial_section_bg = null;
            }
        }

        $be->save();

        Session::flash('success', 'Informations updated successfully!');
        return redirect()->back();
    }

    public function delete(Request $request)
    {
        $testimonial = Testimonial::findOrFail($request->testimonial_id);
        @unlink(FRONT_IMG_PATH . self::IMG_SUBDIR . $testimonial->image);
        if ($testimonial->company_logo) {
            @unlink(FRONT_IMG_PATH . self::IMG_SUBDIR . $testimonial->company_logo);
        }
        $testimonial->delete();

        Session::flash('success', 'Testimonial deleted successfully!');
        return back();
    }
}
