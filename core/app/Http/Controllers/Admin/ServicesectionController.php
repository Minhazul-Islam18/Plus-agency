<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\BasicSetting as BS;
use App\BasicExtended;
use App\Language;
use Validator;
use Session;

class ServicesectionController extends Controller
{
    public function index(Request $request)
    {
        $lang = Language::where('code', $request->language)->firstOrFail();
        $data['lang_id'] = $lang->id;
        $data['abs'] = $lang->basic_setting;
        $data['abe'] = $lang->basic_extended;

        return view('admin.home.service-section', $data);
    }

    public function update(Request $request, $langid)
    {
        $serviceSectionBg = $request->service_section_bg;
        $allowedExts = array('jpg', 'png', 'jpeg', 'svg', 'webp', 'avif');
        $extServiceSectionBg = pathinfo($serviceSectionBg, PATHINFO_EXTENSION);

        $rules = [
            'service_section_subtitle' => 'required|max:80',
            'service_section_title' => 'required|max:25',
            'service_overlay_color' => 'required',
            'service_overlay_opacity' => 'required|numeric|max:1|min:0'
        ];

        if ($request->filled('service_section_bg')) {
            $rules['service_section_bg'] = [
                function ($attribute, $value, $fail) use ($extServiceSectionBg, $allowedExts) {
                    if (!in_array($extServiceSectionBg, $allowedExts)) {
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
        $bs->service_section_subtitle = $request->service_section_subtitle;
        $bs->service_section_title = $request->service_section_title;
        $bs->save();

        $be = BasicExtended::where('language_id', $langid)->firstOrFail();

        // Save overlay color and opacity
        $be->service_overlay_color = $request->service_overlay_color;
        $be->service_overlay_opacity = $request->service_overlay_opacity;

        // Handle service section background image upload
        if ($request->filled('service_section_bg')) {
            // Delete old image if exists
            if ($be->service_section_bg) {
                @unlink(base_path(FRONT_IMG_DIR . $be->service_section_bg));
            }

            $filename = uniqid() . '.' . $extServiceSectionBg;

            // Convert to absolute paths
            // assets folder is one level up from core directory
            $sourcePath = base_path('../' . $serviceSectionBg);
            $destinationPath = base_path(FRONT_IMG_DIR . $filename);

            // Copy the file
            $copyResult = copy($sourcePath, $destinationPath);

            $be->service_section_bg = $filename;
        }
        // Handle service section background image deletion (only if not uploading new one)
        elseif ($request->delete_service_section_bg == '1') {
            if ($be->service_section_bg) {
                @unlink(base_path(FRONT_IMG_DIR . $be->service_section_bg));
                $be->service_section_bg = null;
            }
        }

        $be->save();

        Session::flash('success', 'Informations updated successfully!');
        return redirect()->back();
    }
}
