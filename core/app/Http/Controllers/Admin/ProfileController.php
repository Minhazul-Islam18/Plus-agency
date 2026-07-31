<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Auth;
use Session;
use Hash;
use Validator;
use App\Admin;
use App\AdminPanelSetting;
use App\Language;
use App\Http\Helpers\KreativMailer;

class ProfileController extends Controller
{
    public function changePass() {
      return view('admin.profile.changepass');
    }

    public function forcedChangePassword() {
      $currentLang = Language::where('code', app()->getLocale())->first() ?: Language::where('is_default', 1)->first();
      return view('admin.profile.forced-changepass', ['aps' => AdminPanelSetting::forLanguage($currentLang->id)]);
    }

    public function updateForcedPassword(Request $request) {
      $validator = Validator::make($request->all(), [
          'password' => 'required|min:8|confirmed',
      ], [
          'password.confirmed' => "Password doesn't match",
      ]);

      if ($validator->fails()) {
          return redirect()->route('admin.forcedChangePassword')->withErrors($validator);
      }

      $admin = Admin::findOrFail(Auth::guard('admin')->user()->id);
      $admin->password = bcrypt($request->password);
      $admin->temp_password = null;
      $admin->temp_password_expires_at = null;
      $admin->must_change_password = false;
      $admin->save();

      $this->sendPasswordChangedEmail($admin);

      Session::flash('success', 'Password changed successfully!');
      return redirect()->route('admin.dashboard');
    }

    private function sendPasswordChangedEmail(Admin $admin)
    {
      $language = Language::where('is_default', 1)->first();
      $bs = $language->basic_setting;

      try {
        (new KreativMailer)->mailFromAdmin([
          'toMail' => $admin->email,
          'toName' => $admin->username,
          'admin_name' => $admin->username,
          'admin_email' => $admin->email,
          'changed_at' => now()->format('d M Y, H:i'),
          'login_url' => route('admin.login'),
          'website_title' => $bs->website_title,
          'logo_path' => KreativMailer::resolveAssetPath($bs->email_logo ?: $bs->logo),
          'templateType' => 'admin_password_changed',
        ]);
      } catch (\Throwable $e) {
        \Log::error('[ProfileController] Password-changed email failed', ['error' => $e->getMessage()]);
      }
    }

    public function editProfile() {
      $admin = Admin::findOrFail(Auth::guard('admin')->user()->id);
      return view('admin.profile.editprofile', ['admin' => $admin]);
    }

    public function updatePropic(Request $request) {
      $img = $request->file('file');
      $allowedExts = array('jpg', 'png', 'jpeg', 'webp');

      $rules = [
        'file' => [
          function($attribute, $value, $fail) use ($img, $allowedExts) {
            if (!empty($img)) {
              $ext = $img->getClientOriginalExtension();
              if(!in_array($ext, $allowedExts)) {
                  return $fail("Only png, jpg, jpeg image is allowed");
              }
            }
          },
        ],
      ];

      $validator = Validator::make($request->all(), $rules);
      if ($validator->fails()) {
        $validator->getMessageBag()->add('error', 'true');
        return response()->json(['errors' => $validator->errors(), 'id' => 'image']);
      }

      @unlink("assets/admin/img/propics/".Auth::guard('admin')->user()->image);
      $fileName = uniqid() . '.jpg';
      $request->file('file')->move('assets/admin/img/propics/', $fileName);
      $admin = Admin::findOrFail(Auth::guard('admin')->user()->id);
      $admin->image = $fileName;
      $admin->save();
      return response()->json(['status' => "success", 'image' => 'image']);
    }

    public function updateProfile(Request $request) {
      $admin = Admin::findOrFail(Auth::guard('admin')->user()->id);

      $validatedData = $request->validate([
        'username' => [
            'required',
            'max:255',
            Rule::unique('admins')->ignore($admin->id)
        ],
        'email' => 'required|email|max:255',
        'first_name' => 'required|max:255',
        'last_name' => 'required|max:255',
      ]);


      $admin->username = $request->username;
      $admin->email = $request->email;
      $admin->first_name = $request->first_name;
      $admin->last_name = $request->last_name;
      $admin->save();

      Session::flash('success', 'Profile updated successfully!');

      return redirect()->back();
    }

    public function updatePassword(Request $request) {
      $messages = [
          'password.required' => 'The new password field is required',
          'password.confirmed' => "Password does'nt match"
      ];
      $validator = Validator::make($request->all(), [
          'old_password' => 'required',
          'password' => 'required|confirmed'
      ], $messages);
      // if given old password matches with the password of this authenticated user...
      if(Hash::check($request->old_password, Auth::guard('admin')->user()->password)) {
          $oldPassMatch = 'matched';
      } else {
          $oldPassMatch = 'not_matched';
      }
      if ($validator->fails() || $oldPassMatch=='not_matched') {
          if($oldPassMatch == 'not_matched') {
            $validator->errors()->add('oldPassMatch', true);
          }
          return redirect()->route('admin.changePass')
                      ->withErrors($validator);
      }

      // updating password in database...
      $user = Admin::findOrFail(Auth::guard('admin')->user()->id);
      $user->password = bcrypt($request->password);
      $user->save();

      $this->sendPasswordChangedEmail($user);

      Session::flash('success', 'Password changed successfully!');

      return redirect()->back();
    }
}
