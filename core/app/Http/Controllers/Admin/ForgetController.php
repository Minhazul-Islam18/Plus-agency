<?php

namespace App\Http\Controllers\Admin;

use App\Admin;
use App\AdminPanelSetting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Language;
use App\Http\Helpers\KreativMailer;
use Illuminate\Support\Str;
use Session;

class ForgetController extends Controller
{
    public function mailForm() {
        $currentLang = Language::where('code', app()->getLocale())->first() ?: Language::where('is_default', 1)->first();
        return view('admin.forget', ['aps' => AdminPanelSetting::forLanguage($currentLang->id)]);
    }

    public function sendmail(Request $request) {
        // check whether the mail exists in database
        $request->validate([
            'email' => [
                'required',
                function ($attribute, $value, $fail) {
                    $count = Admin::where('email', $value)->count();
                    if ($count == 0) {
                        $fail(__("The email address doesn't exist"));
                    }
                }
            ]
        ]);

        // Issue a temporary password valid for a limited window instead of an
        // unlimited one — Str::random is cryptographically strong, unlike the
        // previous uniqid()-based password which is time-derived/predictable.
        // The REAL password is deliberately left untouched here: if the admin
        // never completes the reset (or still remembers their old password),
        // it keeps working right up until they actually finish setting a new
        // one — only LoginController's temp-password check below is time-boxed.
        $tempPassword = Str::random(12);
        $admin = Admin::where('email', $request->email)->first();
        $expiresAt = now()->addHours(24);

        $admin->temp_password = bcrypt($tempPassword);
        $admin->temp_password_expires_at = $expiresAt;
        $admin->must_change_password = true;
        $admin->save();

        $language = Language::where('is_default', 1)->first();
        $bs = $language->basic_setting;

        try {
            (new KreativMailer)->mailFromAdmin([
                'toMail' => $admin->email,
                'toName' => $admin->username,
                'admin_name' => $admin->username,
                'admin_email' => $admin->email,
                'temporary_password' => $tempPassword,
                'expiry_date_time' => $expiresAt->format('d M Y, H:i'),
                'login_url' => route('admin.login'),
                'website_title' => $bs->website_title,
                'logo_path' => KreativMailer::resolveAssetPath($bs->email_logo ?: $bs->logo),
                'templateType' => 'admin_temp_password',
            ]);
        } catch (\Throwable $e) {
            \Log::error('[ForgetController] Temp password email failed', ['error' => $e->getMessage()]);
        }

        // Flashed (not permanent session data) so the success screen only
        // shows once, right after a send — reloading /mail-form later shows
        // the normal request form again instead of a stale "sent!" state.
        Session::flash('reset_sent_email', $admin->email);
        return redirect()->route('admin.forget.form');
    }
}
