<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\BasicSetting as BS;
use App\BasicExtended;
use App\Language;
use Validator;
use Session;

class TendersectionController extends Controller
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
        return view('admin.home.tender-section', $data);
    }

    public function update(Request $request, $langid)
    {
        $tenderSectionBg = $request->tender_section_bg;
        $allowedExts = array('jpg', 'png', 'jpeg', 'svg', 'webp', 'avif');
        $extTenderSectionBg = pathinfo($tenderSectionBg, PATHINFO_EXTENSION);

        $rules = [
            'tender_section_text' => 'required|max:80',
            'tender_section_title' => 'required|max:25',
            'tender_overlay_color' => 'required',
            'tender_overlay_opacity' => 'required|numeric|max:1|min:0'
        ];

        if ($request->filled('tender_section_bg')) {
            $rules['tender_section_bg'] = [
                function ($attribute, $value, $fail) use ($extTenderSectionBg, $allowedExts) {
                    if (!in_array($extTenderSectionBg, $allowedExts)) {
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
        $bs->tender_section_text = $request->tender_section_text;
        $bs->tender_section_title = $request->tender_section_title;
        $bs->save();

        $be = BasicExtended::where('language_id', $langid)->firstOrFail();

        $be->tender_overlay_color = $request->tender_overlay_color;
        $be->tender_overlay_opacity = $request->tender_overlay_opacity;

        if ($request->filled('tender_section_bg')) {
            if ($be->tender_section_bg) {
                @unlink(base_path(FRONT_IMG_DIR . $be->tender_section_bg));
            }

            $filename = uniqid() . '.' . $extTenderSectionBg;
            $sourcePath = base_path('../' . $tenderSectionBg);
            $destinationPath = base_path(FRONT_IMG_DIR . $filename);
            copy($sourcePath, $destinationPath);

            $be->tender_section_bg = $filename;
        } elseif ($request->delete_tender_section_bg == '1') {
            if ($be->tender_section_bg) {
                @unlink(base_path(FRONT_IMG_DIR . $be->tender_section_bg));
                $be->tender_section_bg = null;
            }
        }

        $be->save();

        Session::flash('success', 'Informations updated successfully!');
        return redirect()->back();
    }
}
