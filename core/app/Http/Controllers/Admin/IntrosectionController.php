<?php

namespace App\Http\Controllers\Admin;

use App\BasicExtended;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\BasicSetting as BS;
use App\Language;
use Validator;
use Session;

class IntrosectionController extends Controller
{
    public function index(Request $request)
    {
        $lang = Language::where('code', $request->language)->firstOrFail();
        $data['lang_id'] = $lang->id;
        $data['abs'] = $lang->basic_setting;
        $data['abe'] = $lang->basic_extended;

        return view('admin.home.intro-section', $data);
    }

    public function update(Request $request, $langid)
    {
        $image = $request->image;
        $allowedExts = array('jpg', 'png', 'jpeg', 'svg');
        $extImage = pathinfo($image, PATHINFO_EXTENSION);

        $image2 = $request->image_2;
        $extImage2 = pathinfo($image2, PATHINFO_EXTENSION);

        $introSectionBg = $request->intro_section_bg;
        $extIntroSectionBg = pathinfo($introSectionBg, PATHINFO_EXTENSION);

        $rules = [
            'intro_section_title' => 'required|max:25',
            'intro_section_text' => 'required|max:80',
            'intro_section_button_text' => 'nullable|max:15',
            'intro_section_button_url' => 'nullable|max:255',
            'intro_section_video_link' => 'nullable',
            'intro_overlay_color' => 'required',
            'intro_overlay_opacity' => 'required|numeric|max:1|min:0'
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

        if ($request->filled('image_2')) {
            $rules['image_2'] = [
                function ($attribute, $value, $fail) use ($extImage2, $allowedExts) {
                    if (!in_array($extImage2, $allowedExts)) {
                        return $fail("Only png, jpg, jpeg, svg image is allowed");
                    }
                }
            ];
        }

        if ($request->filled('intro_section_bg')) {
            $rules['intro_section_bg'] = [
                function ($attribute, $value, $fail) use ($extIntroSectionBg, $allowedExts) {
                    if (!in_array($extIntroSectionBg, $allowedExts)) {
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
        $bs->intro_section_title = $request->intro_section_title;
        $bs->intro_section_text = $request->intro_section_text;
        $bs->intro_section_button_text = $request->intro_section_button_text;
        $bs->intro_section_button_url = $request->intro_section_button_url;
        $videoLink = $request->intro_section_video_link;
        if (strpos($videoLink, "&") != false) {
            $videoLink = substr($videoLink, 0, strpos($videoLink, "&"));
        }
        $bs->intro_section_video_link = $videoLink;

        // Handle image deletion
        if ($request->delete_image == '1') {
            @unlink(base_path('../assets/front/img/' . $bs->intro_bg));
            $bs->intro_bg = null;
        }

        if ($request->filled('image')) {
            if ($bs->intro_bg) {
                @unlink(base_path('../assets/front/img/' . $bs->intro_bg));
            }
            $filename = uniqid() . '.' . $extImage;
            @copy(base_path('../' . $image), base_path('../assets/front/img/' . $filename));

            $bs->intro_bg = $filename;
        }

        $bs->save();

        $be = BasicExtended::where('language_id', $langid)->firstOrFail();

        // Save overlay color and opacity
        $be->intro_overlay_color = $request->intro_overlay_color;
        $be->intro_overlay_opacity = $request->intro_overlay_opacity;

        // Handle intro section background image upload
        if ($request->filled('intro_section_bg')) {
            // Delete old image if exists
            if ($be->intro_section_bg) {
                @unlink(base_path('../assets/front/img/' . $be->intro_section_bg));
            }

            $filename = uniqid() . '.' . $extIntroSectionBg;

            // Convert to absolute paths
            // assets folder is one level up from core directory
            $sourcePath = base_path('../' . $introSectionBg);
            $destinationPath = base_path('../assets/front/img/' . $filename);

            // Copy the file
            $copyResult = copy($sourcePath, $destinationPath);
            if (!$copyResult) {
                $error = error_get_last();
            }

            $be->intro_section_bg = $filename;
        }
        // Handle intro section background image deletion (only if not uploading new one)
        elseif ($request->delete_intro_section_bg == '1') {
            if ($be->intro_section_bg) {
                @unlink(base_path('../assets/front/img/' . $be->intro_section_bg));
                $be->intro_section_bg = null;
            }
        }

        if ($request->filled('image_2')) {
            @unlink(base_path('../assets/front/img/' . $be->intro_bg2));
            $filename = uniqid() . '.' . $extImage2;
            @copy(base_path('../' . $image2), base_path('../assets/front/img/' . $filename));

            $be->intro_bg2 = $filename;
        }
        $be->save();

        Session::flash('success', 'Informations updated successfully!');
        return redirect()->back();
    }
}
