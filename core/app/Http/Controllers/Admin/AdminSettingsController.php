<?php

namespace App\Http\Controllers\Admin;

use App\AdminPanelSetting;
use App\Language;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;

class AdminSettingsController extends Controller
{
    private function resolveLanguage(Request $request): Language
    {
        $lang = $request->filled('language')
            ? Language::where('code', $request->input('language'))->first()
            : null;

        return $lang ?: Language::where('is_default', 1)->first();
    }

    public function loginBranding(Request $request)
    {
        $selLang = $this->resolveLanguage($request);

        $data['aps'] = AdminPanelSetting::forLanguage($selLang->id);
        $data['langs'] = Language::all();
        $data['selLang'] = $selLang;

        return view('admin.admin_settings.login-branding', $data);
    }

    public function updateLoginBranding(Request $request)
    {
        $logo = $request->login_logo;
        $bgImage = $request->login_bg_image;
        $allowedExts = allowed_image_extensions();
        $extLogo = pathinfo($logo, PATHINFO_EXTENSION);
        $extBg = pathinfo($bgImage, PATHINFO_EXTENSION);

        $rules = [
            'language_id' => 'required|exists:languages,id',
            'platform_name' => 'nullable|max:255',
            'tagline' => 'required|max:255',
            'copyright_text' => 'nullable|max:255',
            // Variable length on purpose — different languages can have a
            // different number of feature highlights (e.g. 4 in English,
            // 3 in French), not a fixed set of slots.
            'features' => 'required|array|min:1|max:8',
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

        // Re-index features (the form can submit sparse/gappy keys after
        // rows are removed client-side) and drop the row's client-only id.
        $features = array_values(array_map(function ($f) {
            return [
                'icon' => $f['icon'],
                'title' => $f['title'],
                'desc' => $f['desc'] ?? '',
            ];
        }, $request->features));

        $aps = AdminPanelSetting::forLanguage($request->language_id);
        $aps->platform_name = $request->platform_name;
        $aps->tagline = $request->tagline;
        $aps->copyright_text = $request->copyright_text;
        $aps->features = $features;

        if ($request->filled('login_logo')) {
            @unlink(FRONT_IMG_PATH . $aps->login_logo);
            $filename = uniqid() . '.' . $extLogo;
            @copy($logo, FRONT_IMG_PATH . $filename);
            $aps->login_logo = $filename;
        } elseif ($request->boolean('clear_login_logo')) {
            @unlink(FRONT_IMG_PATH . $aps->login_logo);
            $aps->login_logo = null;
        }

        if ($request->filled('login_bg_image')) {
            @unlink(FRONT_IMG_PATH . $aps->login_bg_image);
            $filename = uniqid() . '.' . $extBg;
            @copy($bgImage, FRONT_IMG_PATH . $filename);
            $aps->login_bg_image = $filename;
        } elseif ($request->boolean('clear_login_bg_image')) {
            @unlink(FRONT_IMG_PATH . $aps->login_bg_image);
            $aps->login_bg_image = null;
        }

        $aps->save();

        Session::flash('success', 'Login branding updated successfully!');
        return back();
    }

    public function security()
    {
        // Global in effect (a lockout threshold has no per-language variant),
        // so this always targets the default language's row regardless of
        // any ?language= — no language switcher on this sub-page.
        $defaultLang = Language::where('is_default', 1)->first();

        $data['aps'] = AdminPanelSetting::forLanguage($defaultLang->id);
        $data['adminPrefix'] = config('app.admin_prefix', 'admin');
        return view('admin.admin_settings.security', $data);
    }

    public function updateSecurity(Request $request)
    {
        $request->validate([
            'max_login_attempts' => 'required|integer|min:1|max:20',
            'lockout_duration_minutes' => 'required|integer|min:1|max:10080', // up to 7 days
        ]);

        $defaultLang = Language::where('is_default', 1)->first();

        $aps = AdminPanelSetting::forLanguage($defaultLang->id);
        $aps->max_login_attempts = $request->max_login_attempts;
        $aps->lockout_duration_minutes = $request->lockout_duration_minutes;
        $aps->save();

        Session::flash('success', 'Security settings updated successfully!');
        return back();
    }
}
