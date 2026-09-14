<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Partner;
use App\BasicExtended;
use App\Language;
use Validator;
use Session;

class PartnerController extends Controller
{
    private const IMG_SUBDIR = 'partners/';
    public function index(Request $request)
    {
        $lang = Language::where('code', $request->language)->first();

        $lang_id = $lang->id;
        $data['partners'] = Partner::where('language_id', $lang_id)->orderBy('id', 'DESC')->get();
        $data['abs'] = $lang->basic_setting;
        $data['abe'] = $lang->basic_extended;
        $data['langs'] = Language::where('status', 1)->get();
        $data['lang_id'] = $lang_id;

        return view('admin.home.partner.index', $data);
    }

    public function edit($id)
    {
        $data['partner'] = Partner::findOrFail($id);
        return view('admin.home.partner.edit', $data);
    }


    public function store(Request $request)
    {
        $image = $request->image;
        $allowedExts = allowed_image_extensions();
        $extImage = pathinfo($image, PATHINFO_EXTENSION);

        $messages = [
            'language_id.required' => 'The language field is required'
        ];

        $rules = [
            'language_id' => 'required',
            'name' => 'required|max:255',
            'image' => 'required',
            'url' => 'required|max:255',
            'serial_number' => 'required|integer',
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

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            $errmsgs = $validator->getMessageBag()->add('error', 'true');
            return response()->json($validator->errors());
        }

        $partner = new Partner;
        $partner->language_id = $request->language_id;
        $partner->name = $request->name;
        $partner->url = $request->url;
        $partner->serial_number = $request->serial_number;

        if ($request->filled('image')) {
            $filename = uniqid() . '.' . $extImage;
            @copy($image, FRONT_IMG_PATH . self::IMG_SUBDIR . $filename);
            $partner->image = $filename;
        }

        $partner->save();

        Session::flash('success', 'Partner added successfully!');
        return "success";
    }

    public function update(Request $request)
    {
        $image = $request->image;
        $allowedExts = allowed_image_extensions();
        $extImage = pathinfo($image, PATHINFO_EXTENSION);

        $rules = [
            'name' => 'required|max:255',
            'url' => 'required|max:255',
            'serial_number' => 'required|integer',
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

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errmsgs = $validator->getMessageBag()->add('error', 'true');
            return response()->json($validator->errors());
        }

        $partner = Partner::findOrFail($request->partner_id);
        $partner->name = $request->name;
        $partner->url = $request->url;
        $partner->serial_number = $request->serial_number;

        if ($request->filled('image')) {
            @unlink(FRONT_IMG_PATH . self::IMG_SUBDIR . $partner->image);
            $filename = uniqid() . '.' . $extImage;
            @copy($image, FRONT_IMG_PATH . self::IMG_SUBDIR . $filename);
            $partner->image = $filename;
        }

        $partner->save();

        Session::flash('success', 'Partner updated successfully!');
        return "success";
    }

    public function delete(Request $request)
    {

        $partner = Partner::findOrFail($request->partner_id);
        @unlink(FRONT_IMG_PATH . self::IMG_SUBDIR . $partner->image);
        $partner->delete();

        Session::flash('success', 'Partner deleted successfully!');
        return back();
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->ids;

        foreach ($ids as $id) {
            $partner = Partner::findOrFail($id);
            @unlink(FRONT_IMG_PATH . self::IMG_SUBDIR . $partner->image);
            $partner->delete();
        }

        Session::flash('success', 'Partner deleted successfully!');
        return "success";
    }

    public function status(Request $request)
    {
        $partner = Partner::findOrFail($request->id);
        $partner->status = $request->status;
        $partner->save();

        return response()->json(['success' => true]);
    }

    public function sectionUpdate(Request $request, $langid)
    {
        $image = $request->background;
        $allowedExts = allowed_image_extensions();
        $extImage = pathinfo($image, PATHINFO_EXTENSION);

        $rules = [
            'partner_overlay_color' => 'required',
            'partner_overlay_opacity' => 'required|numeric|max:1|min:0'
        ];

        if ($request->filled('background')) {
            $rules['background'] = [
                function ($attribute, $value, $fail) use ($extImage, $allowedExts) {
                    if (!in_array($extImage, $allowedExts)) {
                        return $fail("Only png, jpg, jpeg, svg image is allowed");
                    }
                }
            ];
        }

        $request->validate($rules);

        $be = BasicExtended::where('language_id', $langid)->firstOrFail();

        // Update overlay color and opacity
        $be->partner_overlay_color = $request->partner_overlay_color;
        $be->partner_overlay_opacity = $request->partner_overlay_opacity;

        // Handle background image deletion
        if ($request->delete_background == '1') {
            @unlink(base_path(FRONT_IMG_DIR . $be->partner_bg));
            $be->partner_bg = null;
        }

        // Handle background image upload
        if ($request->filled('background')) {
            if ($be->partner_bg) {
                @unlink(base_path(FRONT_IMG_DIR . $be->partner_bg));
            }
            $filename = uniqid() . '.' . $extImage;
            @copy(base_path('../' . $image), base_path(FRONT_IMG_DIR . $filename));
            $be->partner_bg = $filename;
        }

        $be->save();

        Session::flash('success', 'Partner section updated successfully!');
        return back();
    }

    /** Same shape as PortfolioSectorController::getSectors — feeds the Portfolio form's partners multi-select cascade when the language is switched. */
    public function getPartners($langid)
    {
        return Partner::where('language_id', $langid)->where('status', 1)->orderBy('serial_number', 'asc')->get();
    }
}
