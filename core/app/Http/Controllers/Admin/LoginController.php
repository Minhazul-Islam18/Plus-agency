<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Admin;
use App\AdminPanelSetting;
use Auth;
use Session;

class LoginController extends Controller
{
    const LOCKED_MESSAGE = 'Your account has been locked because you have exceeded the maximum number of login attempts. To unlock your account, please contact support.';

    public function login(){
      return view('admin.login', ['aps' => AdminPanelSetting::singleton()]);
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
          return redirect()->back()->with('alert', self::LOCKED_MESSAGE);
      }

      if (Auth::guard('admin')->attempt(['username' => $request->username,'password' => $request->password], $request->boolean('remember')))
      {
          $admin = Auth::guard('admin')->user();

          if (!$admin->isSuperAdmin() && $admin->failed_login_attempts > 0) {
              $admin->failed_login_attempts = 0;
              $admin->save();
          }

          // A temp password (from the forgot-password flow) is only valid for
          // a limited window, even if bcrypt still matches it.
          if ($admin->temp_password_expires_at && now()->gt($admin->temp_password_expires_at)) {
              Auth::guard('admin')->logout();
              Session::flash('alert', 'Your temporary password has expired. Please request a new one.');
              return redirect()->route('admin.forget.form');
          }

          // Return to the page the admin was on before the session expired
          // (stored as url.intended by the guest redirect), falling back to
          // the dashboard on a fresh login. If a password change is required
          // (fresh temp password), the forcepasswordchange middleware will
          // redirect from here to the change-password page.
          return redirect()->intended(route('admin.dashboard'));
      }

      if ($target && !$target->isSuperAdmin()) {
          $target->increment('failed_login_attempts');
          if ($target->failed_login_attempts >= AdminPanelSetting::maxLoginAttempts()) {
              $target->locked_at = now();
              $target->save();
              return redirect()->back()->with('alert', self::LOCKED_MESSAGE);
          }
      }

      return redirect()->back()->with('alert','Username and Password Not Matched');
    }

    public function logout() {
      Auth::guard('admin')->logout();
      return redirect()->route('admin.login');
    }
}
