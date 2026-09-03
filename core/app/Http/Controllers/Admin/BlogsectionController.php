<?php

namespace App\Http\Controllers\Admin;

use App\Language;
use App\BasicExtended;
use App\BasicSetting as BS;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class BlogsectionController extends Controller
{
    public function index(Request $request)
    {
        if (empty($request->language)) {
            $data['lang_id'] = 0;
            $data['abs'] = BS::firstOrFail();
            $data['abe'] = BasicExtended::firstOrFail();
        } else {
            $lang = Language::where('code', $request->language)->firstOrFail();
            $data['lang_id'] = $lang->id;
            $data['abs'] = $lang->basic_setting;
            $data['abe'] = $lang->basic_extended;
        }
        $data['langs'] = Language::where('status', 1)->get();
        return view('admin.home.blog-section', $data);
    }

    public function update(Request $request, $langid)
    {
        $background = $request->background;
        $allowedExts = allowed_image_extensions();
        $extBackground = pathinfo($background, PATHINFO_EXTENSION);

        $rules = [
            'blog_section_subtitle' => 'required|max:80',
            'blog_section_title' => 'required|max:25',
            'blog_overlay_color' => 'required',
            'blog_overlay_opacity' => 'required|numeric|max:1|min:0'
        ];

        if ($request->filled('background')) {
            $rules['background'] = [
                function ($attribute, $value, $fail) use ($extBackground, $allowedExts) {
                    if (!in_array($extBackground, $allowedExts)) {
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
        $bs->blog_section_subtitle = $request->blog_section_subtitle;
        $bs->blog_section_title = $request->blog_section_title;
        $bs->save();

        $be = BasicExtended::where('language_id', $langid)->firstOrFail();

        // Handle background image deletion
        if ($request->delete_background == '1') {
            @unlink(base_path(FRONT_IMG_DIR . $be->blog_bg));
            $be->blog_bg = null;
        }

        if ($request->filled('background')) {
            if ($be->blog_bg) {
                @unlink(base_path(FRONT_IMG_DIR . $be->blog_bg));
            }
            $filename = uniqid() . '.' . $extBackground;
            @copy(base_path('../' . $background), base_path(FRONT_IMG_DIR . $filename));
            $be->blog_bg = $filename;
        }

        // Save overlay color and opacity
        $be->blog_overlay_color = $request->blog_overlay_color;
        $be->blog_overlay_opacity = $request->blog_overlay_opacity;

        $be->save();

        Session::flash('success', 'Texts & Background updated successfully!');
        return redirect()->back();
    }
}
