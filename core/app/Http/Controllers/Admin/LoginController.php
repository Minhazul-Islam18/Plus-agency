<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Admin;
use App\AdminPanelSetting;
use App\Language;
use App\Http\Helpers\KreativMailer;
use Auth;
use Hash;
use Session;

class LoginController extends Controller
{
    /**
     * Lockout has no stored expiry column — it's derived on every check from
     * locked_at + the current lockout_duration_minutes setting, so changing
     * the setting later doesn't retroactively change an in-progress lock's
     * length in a confusing way (only future locks use the new duration).
     */
    private function lockoutExpiresAt(Admin $target)
    {
        // locked_at isn't cast to Carbon on the Admin model, so parse it
        // explicitly rather than assuming a Carbon instance.
        return \Carbon\Carbon::parse($target->locked_at)->addMinutes(AdminPanelSetting::lockoutDurationMinutes());
    }

    private function lockedMessage($unlockAt): string
    {
        return __('Your account has been locked because you have exceeded the maximum number of login attempts. You can try again after :time.', [
            'time' => $unlockAt->format('d M Y, H:i'),
        ]);
    }

    private function sendLockedEmail(Admin $target, $unlockAt): void
    {
        $language = Language::where('is_default', 1)->first();
        $bs = $language->basic_setting;

        try {
            (new KreativMailer)->mailFromAdmin([
                'toMail' => $target->email,
                'toName' => $target->username,
                'admin_name' => $target->username,
                'admin_email' => $target->email,
                'locked_until' => $unlockAt->format('d M Y, H:i'),
                'login_url' => route('admin.login'),
                'website_title' => $bs->website_title,
                'logo_path' => KreativMailer::resolveAssetPath($bs->email_logo ?: $bs->logo),
                'templateType' => 'admin_account_locked',
            ]);
        } catch (\Throwable $e) {
            \Log::error('[LoginController] Account-locked email failed', ['error' => $e->getMessage()]);
        }
    }

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

      // Accept either username or email in the one field — the guard's
      // provider only matches the `username` column, so attempt() below
      // always authenticates against $target->username, never the raw
      // input (which may actually be an email).
      $target = Admin::where('username', $request->username)
          ->orWhere('email', $request->username)
          ->first();

      // Already locked (from a previous run of failed attempts) — block
      // outright, before even trying the password. Super admin is exempt.
      if ($target && !$target->isSuperAdmin() && $target->locked_at) {
          $unlockAt = $this->lockoutExpiresAt($target);

          if (now()->lt($unlockAt)) {
              return redirect()->back()->with('alert', $this->lockedMessage($unlockAt));
          }

          // Cooldown has elapsed — auto-unlock before this attempt proceeds.
          $target->locked_at = null;
          $target->failed_login_attempts = 0;
          $target->save();
      }

      if ($target && Auth::guard('admin')->attempt(['username' => $target->username, 'password' => $request->password], $request->boolean('remember')))
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
          $max = AdminPanelSetting::maxLoginAttempts();

          if ($target->failed_login_attempts >= $max) {
              $target->locked_at = now();
              $target->save();

              $unlockAt = $this->lockoutExpiresAt($target);
              $this->sendLockedEmail($target, $unlockAt);

              return redirect()->back()->with('alert', $this->lockedMessage($unlockAt));
          }

          // Warn before it happens, not just after — an admin silently
          // approaching lockout (e.g. from a stale saved password) has no
          // other signal that the next wrong attempt locks them out.
          $remaining = $max - $target->failed_login_attempts;
          return redirect()->back()->with('alert', trans_choice(
              'Username and Password Not Matched. :count attempt remaining before your account is locked.|Username and Password Not Matched. :count attempts remaining before your account is locked.',
              $remaining,
              ['count' => $remaining]
          ));
      }

      return redirect()->back()->with('alert', __('Username and Password Not Matched'));
    }

    public function logout() {
      Auth::guard('admin')->logout();
      return redirect()->route('admin.login');
    }
}
