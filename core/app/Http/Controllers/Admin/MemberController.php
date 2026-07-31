<?php

namespace App\Http\Controllers\Admin;

use App\BasicExtended;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Member;
use App\Language;
use App\BasicSetting as BS;
use Validator;
use Session;

class MemberController extends Controller
{
    /**
     * Combine the picked dial code with the national number for wa.me,
     * tolerating whatever the admin actually typed in the number field:
     *  - "01917817191"          → trunk "0" dropped: code + 1917817191
     *  - "0033612345678"        → "00" international-access prefix dropped
     *                              first, exposing the re-typed code beneath
     *  - "880 1917817191"       → re-typed code dropped, not duplicated
     *  - "+880 1917-817191"     → dashes/spaces/plus already stripped by the
     *                              picker's own input handler before this runs
     * Leading zeros are stripped both before AND after the re-typed-code
     * check, since removing the code can expose a further trunk "0"
     * underneath (e.g. "880" + "0" + "1917817191" typed as one block).
     * Same de-duplication idea as TenderPurchase::e164Phone().
     */
    private function whatsappValue(Request $request): ?string
    {
        if (!$request->filled('whatsapp_number')) {
            return null;
        }

        $code     = (string) $request->whatsapp_number_code; // e.g. "+880"
        $bareCode = ltrim($code, '+');

        $national = preg_replace('/\D/', '', (string) $request->whatsapp_number);
        $national = ltrim($national, '0');

        // Admin re-typed the country code inside the number field too — drop
        // the duplicate rather than doubling it up.
        if ($bareCode !== '' && strpos($national, $bareCode) === 0) {
            $national = substr($national, strlen($bareCode));
        }

        $national = ltrim($national, '0');

        return $code . $national;
    }

    public function index(Request $request)
    {
        $lang = Language::where('code', $request->language)->firstOrFail();
        $data['lang_id'] = $lang->id;
        $data['abs'] = $lang->basic_setting;
        $data['abe'] = $lang->basic_extended;
        $data['be'] = BS::first();
        $data['bex'] = BasicExtended::first();
        $data['langs'] = Language::where('status', 1)->get();
        $data['members'] = Member::where('language_id', $data['lang_id'])->get();

        return view('admin.home.member.index', $data);
    }

    public function create()
    {
        $data['countries'] = \App\Http\Helpers\Countries::forCheckout();
        return view('admin.home.member.create', $data);
    }

    public function edit($id)
    {
        $data['member']    = Member::findOrFail($id);
        $data['countries'] = \App\Http\Helpers\Countries::forCheckout();

        $digits = preg_replace('/\D/', '', (string) $data['member']->whatsapp);
        $split  = \App\Http\Helpers\Countries::splitDial($digits);
        $data['preWhatsappCode']   = $split['code'];
        $data['preWhatsappNumber'] = $split['number'];
        $data['preWhatsappFlag']   = $split['code'] !== ''
            ? (collect($data['countries'])->firstWhere('dial', $split['code'])['flag'] ?? '')
            : '';

        return view('admin.home.member.edit', $data);
    }

    public function store(Request $request)
    {
        $image = $request->image;
        $allowedExts = array('jpg', 'png', 'jpeg', 'svg', 'webp');
        $extImage = pathinfo($image, PATHINFO_EXTENSION);

        $messages = [
            'language_id.required' => 'The language field is required',
            'whatsapp_number_code.required_with' => 'Select a country code for the WhatsApp number.',
        ];

        $rules = [
            'language_id' => 'required',
            'image' => 'required',
            'name' => 'required|max:50',
            'rank' => 'required|max:50',
            'facebook' => 'nullable|max:50',
            'twitter' => 'nullable|max:50',
            'linkedin' => 'nullable|max:50',
            'whatsapp_number' => 'nullable|max:20',
            'whatsapp_number_code' => 'required_with:whatsapp_number',
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

        $member = new Member;
        $member->language_id = $request->language_id;
        $member->image = $request->member_image;
        $member->name = $request->name;
        $member->rank = $request->rank;
        $member->facebook = $request->facebook;
        $member->twitter = $request->twitter;
        $member->linkedin = $request->linkedin;
        $member->whatsapp = $this->whatsappValue($request);

        if ($request->filled('image')) {
            $filename = uniqid() .'.'. $extImage;
            @copy($image, 'assets/front/img/members/' . $filename);
            $member->image = $filename;
        }

        $member->save();

        Session::flash('success', 'Member added successfully!');
        return "success";
    }

    public function update(Request $request)
    {
        $image = $request->image;
        $allowedExts = array('jpg', 'png', 'jpeg', 'svg', 'webp');
        $extImage = pathinfo($image, PATHINFO_EXTENSION);

        $rules = [
            'name' => 'required|max:50',
            'rank' => 'required|max:50',
            'facebook' => 'nullable|max:50',
            'twitter' => 'nullable|max:50',
            'linkedin' => 'nullable|max:50',
            'whatsapp_number' => 'nullable|max:20',
            'whatsapp_number_code' => 'required_with:whatsapp_number',
        ];

        $messages = [
            'whatsapp_number_code.required_with' => 'Select a country code for the WhatsApp number.',
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

        $member = Member::findOrFail($request->member_id);
        $member->name = $request->name;
        $member->rank = $request->rank;
        $member->facebook = $request->facebook;
        $member->twitter = $request->twitter;
        $member->linkedin = $request->linkedin;
        $member->whatsapp = $this->whatsappValue($request);

        if ($request->filled('image')) {
            @unlink('assets/front/img/members/' . $member->image);
            $filename = uniqid() .'.'. $extImage;
            @copy($image, 'assets/front/img/members/' . $filename);
            $member->image = $filename;
        }

        $member->save();

        Session::flash('success', 'Member updated successfully!');
        return "success";
    }

    public function textupdate(Request $request, $langid)
    {
        $bex = BasicExtended::firstOrFail();
        $version = $bex->theme_version;

        if ($version == 'default' || $version == 'dark') {
            $background = $request->background;
            $allowedExts = array('jpg', 'png', 'jpeg', 'svg', 'webp');
            $extBackground = pathinfo($background, PATHINFO_EXTENSION);
        }

        $rules = [
            'team_section_title' => 'required|max:25',
            'team_section_subtitle' => 'required|max:80',
            'team_overlay_color' => 'required',
            'team_overlay_opacity' => 'required|numeric|max:1|min:0'
        ];

        if (($version == 'default' || $version == 'dark') && $request->filled('background')) {
            $rules['background'] = [
                function ($attribute, $value, $fail) use ($extBackground, $allowedExts) {
                    if (!in_array($extBackground, $allowedExts)) {
                        return $fail("Only png, jpg, jpeg, svg image is allowed");
                    }
                }
            ];
        }

        $request->validate($rules);

        $bs = BS::where('language_id', $langid)->firstOrFail();
        $bs->team_section_title = $request->team_section_title;
        $bs->team_section_subtitle = $request->team_section_subtitle;

        // Handle background image deletion
        if ($request->delete_background == '1') {
            @unlink(base_path('../assets/front/img/' . $bs->team_bg));
            $bs->team_bg = null;
        }

        if (($version == 'default' || $version == 'dark') && $request->filled('background')) {
            if ($bs->team_bg) {
                @unlink(base_path('../assets/front/img/' . $bs->team_bg));
            }
            $filename = uniqid() .'.'. $extBackground;
            @copy(base_path('../' . $background), base_path('../assets/front/img/' . $filename));
            $bs->team_bg = $filename;
        }

        $bs->save();

        $be = BasicExtended::where('language_id', $langid)->firstOrFail();

        // Save overlay color and opacity
        $be->team_overlay_color = $request->team_overlay_color;
        $be->team_overlay_opacity = $request->team_overlay_opacity;

        $be->save();

        Session::flash('success', 'Text & Background updated successfully!');
        return back();
    }

    public function delete(Request $request)
    {

        $member = Member::findOrFail($request->member_id);
        @unlink('assets/front/img/members/' . $member->image);
        $member->delete();

        Session::flash('success', 'Member deleted successfully!');
        return back();
    }

    public function feature(Request $request)
    {
        $member = Member::find($request->member_id);
        $member->feature = $request->feature;
        $member->save();

        if ($request->feature == 1) {
            Session::flash('success', 'Featured successfully!');
        } else {
            Session::flash('success', 'Unfeatured successfully!');
        }

        return back();
    }
}
