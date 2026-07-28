<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Admin;
use App\AdminPanelSetting;
use App\Language;
use Auth;
use Hash;
use Session;

class LoginController extends Controller
{
    const LOCKED_MESSAGE = 'Your account has been locked because you have exceeded the maximum number of login attempts. To unlock your account, please contact support.';

    public function login(){
      $currentLang = Language::where('code', app()->getLocale())->first() ?: Language::where('is_default', 1)->first();
      return view('admin.login', ['aps' => AdminPanelSetting::forLanguage($currentLang->id)]);
    }

    public function authenticate(Request $request){
      // return $request->username . ' ' . $request->password;
      $this->validate($request, [
        'username'   => 'required',
        'password' => 'required'
      ]);

      $target = Admin::where('username', $request->username)->first();

      // Already locked (from a previous run of failed attempts) — block
      // outright, before even trying the password. Super admin is exempt.
      if ($target && !$target->isSuperAdmin() && $target->locked_at) {
          return redirect()->back()->with('alert', __(self::LOCKED_MESSAGE));
      }

      if (Auth::guard('admin')->attempt(['username' => $request->username,'password' => $request->password], $request->boolean('remember')))
      {
          $admin = Auth::guard('admin')->user();

          if (!$admin->isSuperAdmin() && $admin->failed_login_attempts > 0) {
              $admin->failed_login_attempts = 0;
          }

          // Logging in with the REAL password means they didn't need the
          // reset after all — cancel any pending one so they're not forced
          // into a password change for a request they've effectively
          // abandoned by using their original credentials instead.
          if ($admin->temp_password) {
              $admin->must_change_password = false;
              $admin->temp_password = null;
              $admin->temp_password_expires_at = null;
          }
          $admin->save();

          // Return to the page the admin was on before the session expired
          // (stored as url.intended by the guest redirect), falling back to
          // the dashboard on a fresh login. If a password change is required
          // (fresh temp password), the forcepasswordchange middleware will
          // redirect from here to the change-password page.
          return redirect()->intended(route('admin.dashboard'));
      }

      // Real password didn't match — check it against a pending forgot-password
      // temp password instead. The real password stays untouched throughout
      // this whole flow (see ForgetController::sendmail), so it always keeps
      // working right up until the reset is actually completed.
      if ($target && $target->temp_password && Hash::check($request->password, $target->temp_password)) {
          if ($target->temp_password_expires_at && now()->gt($target->temp_password_expires_at)) {
              return redirect()->back()->with('alert', __('This temporary password has expired. Please request a new one.'));
          }

          Auth::guard('admin')->login($target, $request->boolean('remember'));

          if (!$target->isSuperAdmin() && $target->failed_login_attempts > 0) {
              $target->failed_login_attempts = 0;
              $target->save();
          }

          // must_change_password is already true from the reset request;
          // the forcepasswordchange middleware routes them to set a real one.
          return redirect()->route('admin.forcedChangePassword');
      }

      if ($target && !$target->isSuperAdmin()) {
          $target->increment('failed_login_attempts');
          if ($target->failed_login_attempts >= AdminPanelSetting::maxLoginAttempts()) {
              $target->locked_at = now();
              $target->save();
              return redirect()->back()->with('alert', __(self::LOCKED_MESSAGE));
          }
      }

      return redirect()->back()->with('alert', __('Username and Password Not Matched'));
    }

    public function logout() {
      Auth::guard('admin')->logout();
      return redirect()->route('admin.login');
    }
}
