<?php

namespace App\Http\Controllers\Admin;

use App\BasicExtended;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\BasicSetting as BS;
use App\Point;
use App\Language;
use Session;
use Validator;

class ApproachController extends Controller
{
    public function index(Request $request)
    {
        $lang = Language::where('code', $request->language)->firstOrFail();
        $data['lang_id'] = $lang->id;
        $data['abs'] = $lang->basic_setting;
        $data['abe'] = $lang->basic_extended;
        $data['points'] = Point::where('language_id', $data['lang_id'])->orderBy('id', 'DESC')->get();

        return view('admin.home.approach.index', $data);
    }

    public function store(Request $request)
    {
        $messages = [
            'language_id.required' => 'The language field is required'
        ];

        $rules = [
            'language_id' => 'required',
            'title' => 'required',
            'short_text' => 'required',
            'serial_number' => 'required|integer',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            $errmsgs = $validator->getMessageBag()->add('error', 'true');
            return response()->json($validator->errors());
        }

        $point = new Point;
        $point->language_id = $request->language_id;
        $point->icon = $request->icon;
        $point->title = $request->title;
        $point->short_text = $request->short_text;
        $point->serial_number = $request->serial_number;
        $point->save();

        Session::flash('success', 'New point added successfully!');
        return "success";
    }

    public function pointedit($id)
    {
        $data['point'] = Point::findOrFail($id);
        return view('admin.home.approach.edit', $data);
    }

    public function update(Request $request, $langid)
    {
        $approachSectionBg = $request->approach_section_bg;
        $allowedExts = array('jpg', 'png', 'jpeg', 'svg', 'webp');
        $extApproachSectionBg = pathinfo($approachSectionBg, PATHINFO_EXTENSION);

        $rules = [
            'approach_section_title' => 'required|max:25',
            'approach_section_subtitle' => 'required|max:80',
            'approach_section_button_text' => 'nullable|max:15',
            'approach_section_button_url' => 'nullable|max:255',
            'approach_overlay_color' => 'required',
            'approach_overlay_opacity' => 'required|numeric|max:1|min:0'
        ];

        if ($request->filled('approach_section_bg')) {
            $rules['approach_section_bg'] = [
                function ($attribute, $value, $fail) use ($extApproachSectionBg, $allowedExts) {
                    if (!in_array($extApproachSectionBg, $allowedExts)) {
                        return $fail("Only png, jpg, jpeg, svg image is allowed");
                    }
                }
            ];
        }

        $request->validate($rules);

        $bs = BS::where('language_id', $langid)->firstOrFail();
        $bs->approach_title = $request->approach_section_title;
        $bs->approach_subtitle = $request->approach_section_subtitle;
        $bs->approach_button_text = $request->approach_section_button_text;
        $bs->approach_button_url = $request->approach_section_button_url;
        $bs->save();

        $be = BasicExtended::where('language_id', $langid)->firstOrFail();

        // Save overlay color and opacity
        $be->approach_overlay_color = $request->approach_overlay_color;
        $be->approach_overlay_opacity = $request->approach_overlay_opacity;

        // Handle approach section background image upload
        if ($request->filled('approach_section_bg')) {
            // Delete old image if exists
            if ($be->approach_section_bg) {
                @unlink(base_path(FRONT_IMG_DIR . $be->approach_section_bg));
            }

            $filename = uniqid() . '.' . $extApproachSectionBg;

            // Convert to absolute paths
            // assets folder is one level up from core directory
            $sourcePath = base_path('../' . $approachSectionBg);
            $destinationPath = base_path(FRONT_IMG_DIR . $filename);

            // Copy the file
            copy($sourcePath, $destinationPath);

            $be->approach_section_bg = $filename;
        }
        // Handle approach section background image deletion (only if not uploading new one)
        elseif ($request->delete_approach_section_bg == '1') {
            if ($be->approach_section_bg) {
                @unlink(base_path(FRONT_IMG_DIR . $be->approach_section_bg));
                $be->approach_section_bg = null;
            }
        }

        $be->save();

        Session::flash('success', 'Informations updated successfully!');
        return back();
    }

    public function pointupdate(Request $request)
    {
        $rules = [
            'title' => 'required',
            'short_text' => 'required',
            'serial_number' => 'required|integer',
        ];

        $request->validate($rules);

        $point = Point::findOrFail($request->pointid);
        $point->icon = $request->icon;
        $point->title = $request->title;
        $point->short_text = $request->short_text;
        $point->serial_number = $request->serial_number;
        $point->save();

        Session::flash('success', 'Point updated successfully!');
        return back();
    }

    public function pointdelete(Request $request)
    {

        $point = Point::findOrFail($request->pointid);
        $point->delete();

        Session::flash('success', 'Point deleted successfully!');
        return back();
    }
}
