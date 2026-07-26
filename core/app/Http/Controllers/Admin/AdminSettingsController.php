<?php

namespace App\Http\Controllers\Admin;

use App\AdminPanelSetting;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;

class AdminSettingsController extends Controller
{
    public function loginBranding()
    {
        $data['aps'] = AdminPanelSetting::singleton();
        return view('admin.admin_settings.login-branding', $data);
    }

    public function updateLoginBranding(Request $request)
    {
        $logo = $request->login_logo;
        $bgImage = $request->login_bg_image;
        $allowedExts = array('jpg', 'png', 'jpeg', 'svg');
        $extLogo = pathinfo($logo, PATHINFO_EXTENSION);
        $extBg = pathinfo($bgImage, PATHINFO_EXTENSION);

        $rules = [
            'platform_name' => 'nullable|max:255',
            'tagline' => 'required|max:255',
            'copyright_text' => 'nullable|max:255',
            'features' => 'required|array|size:4',
            'features.*.icon' => 'required|max:255',
            'features.*.title' => 'required|max:255',
            'features.*.desc' => 'nullable|max:255',
        ];

        if ($request->filled('login_logo')) {
            $rules['login_logo'] = [
                function ($attribute, $value, $fail) use ($extLogo, $allowedExts) {
                    if (!in_array($extLogo, $allowedExts)) {
                        return $fail("Only png, jpg, jpeg, svg image is allowed");
                    }
                }
            ];
        }

        if ($request->filled('login_bg_image')) {
            $rules['login_bg_image'] = [
                function ($attribute, $value, $fail) use ($extBg, $allowedExts) {
                    if (!in_array($extBg, $allowedExts)) {
                        return $fail("Only png, jpg, jpeg, svg image is allowed");
                    }
                }
            ];
        }

        $request->validate($rules);

        $aps = AdminPanelSetting::singleton();
        $aps->platform_name = $request->platform_name;
        $aps->tagline = $request->tagline;
        $aps->copyright_text = $request->copyright_text;
        $aps->features = $request->features;

        if ($request->filled('login_logo')) {
            @unlink('assets/front/img/' . $aps->login_logo);
            $filename = uniqid() . '.' . $extLogo;
            @copy($logo, 'assets/front/img/' . $filename);
            $aps->login_logo = $filename;
        } elseif ($request->boolean('clear_login_logo')) {
            @unlink('assets/front/img/' . $aps->login_logo);
            $aps->login_logo = null;
        }

        if ($request->filled('login_bg_image')) {
            @unlink('assets/front/img/' . $aps->login_bg_image);
            $filename = uniqid() . '.' . $extBg;
            @copy($bgImage, 'assets/front/img/' . $filename);
            $aps->login_bg_image = $filename;
        } elseif ($request->boolean('clear_login_bg_image')) {
            @unlink('assets/front/img/' . $aps->login_bg_image);
            $aps->login_bg_image = null;
        }

        $aps->save();

        Session::flash('success', 'Login branding updated successfully!');
        return back();
    }

    public function security()
    {
        $data['aps'] = AdminPanelSetting::singleton();
        $data['adminPrefix'] = config('app.admin_prefix', 'admin');
        return view('admin.admin_settings.security', $data);
    }

    public function updateSecurity(Request $request)
    {
        $request->validate([
            'max_login_attempts' => 'required|integer|min:1|max:20',
        ]);

        $aps = AdminPanelSetting::singleton();
        $aps->max_login_attempts = $request->max_login_attempts;
        $aps->save();

        Session::flash('success', 'Security settings updated successfully!');
        return back();
    }
}
