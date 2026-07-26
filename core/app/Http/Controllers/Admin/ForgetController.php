<?php

namespace App\Http\Controllers\Admin;

use App\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Language;
use App\Http\Helpers\KreativMailer;
use Illuminate\Support\Str;
use Session;

class ForgetController extends Controller
{
    public function mailForm() {
        return view('admin.forget');
    }

    public function sendmail(Request $request) {
        // check whether the mail exists in database
        $request->validate([
            'email' => [
                'required',
                function ($attribute, $value, $fail) {
                    $count = Admin::where('email', $value)->count();
                    if ($count == 0) {
                        $fail("The email address doesn't exist");
                    }
                }
            ]
        ]);

        // Issue a temporary password valid for a limited window instead of an
        // unlimited one — Str::random is cryptographically strong, unlike the
        // previous uniqid()-based password which is time-derived/predictable.
        $tempPassword = Str::random(12);
        $admin = Admin::where('email', $request->email)->first();
        $expiresAt = now()->addHours(24);

        $admin->password = bcrypt($tempPassword);
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
                'logo_path' => KreativMailer::resolveAssetPath($bs->logo),
                'templateType' => 'admin_temp_password',
            ]);
        } catch (\Throwable $e) {
            \Log::error('[ForgetController] Temp password email failed', ['error' => $e->getMessage()]);
        }

        Session::flash('success', 'A temporary password has been sent to your email.');
        return back();
    }
}
