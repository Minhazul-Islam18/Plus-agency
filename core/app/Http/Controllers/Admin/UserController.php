<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use App\Admin;
use App\Role;
use App\Language;
use App\Http\Helpers\KreativMailer;
use Illuminate\Support\Str;
use Validator;
use Session;
use Auth;

class UserController extends Controller
{
    private const IMG_SUBDIR = 'propics/';
    public function index()
    {
        $data['users'] = Admin::all();
        $data['roles'] = Role::all();
        return view('admin.user.index', $data);
    }

    public function edit($id)
    {
        // Owner's own record is only ever editable by the owner themself —
        // via Edit Profile, not this Admins Management CRUD. Block any other
        // admin from even opening the edit form for it.
        if ($id == 1 && Auth::guard('admin')->user()->id != 1) {
            Session::flash('warning', "You cannot edit the owner's account!");
            return redirect()->route('admin.user.index');
        }

        $data['user'] = Admin::findOrFail($id);
        $data['roles'] = Role::all();
        return view('admin.user.edit', $data);
    }

    public function store(Request $request)
    {
        $image = $request->image;
        $allowedExts = allowed_image_extensions();
        $extImage = pathinfo($image, PATHINFO_EXTENSION);

        $rules = [
            'image' => 'required',
            'username' => 'required|max:255|unique:admins',
            'email' => 'required|email|max:255|unique:admins',
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'role' => 'required',
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

        $user = new Admin;
        $user->role_id = $request->role;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->status = $request->filled('status') ? $request->status : 1;
        $user->password = null;

        if ($request->filled('image')) {
            $filename = uniqid() .'.'. $extImage;
            @copy($image, FRONT_ADMIN_IMG_PATH . self::IMG_SUBDIR . $filename);
            $user->image = $filename;
        }

        // No password is set at creation time — an activation email carries a
        // one-time link the new admin uses to choose their own password.
        $plainToken = Str::random(48);
        $user->activation_token_hash = hash_hmac('sha256', $plainToken, config('app.key'));
        $user->activation_expires_at = now()->addHours(24);

        $user->save();

        $this->sendActivationEmail($user, $plainToken);

        Session::flash('success', 'User created successfully! An activation email has been sent.');
        return "success";
    }

    private function sendActivationEmail(Admin $user, $plainToken)
    {
        $language = Language::where('is_default', 1)->first();
        $bs = $language->basic_setting;

        try {
            (new KreativMailer)->mailFromAdmin([
                'toMail' => $user->email,
                'toName' => $user->first_name . ' ' . $user->last_name,
                'admin_name' => $user->first_name,
                'activation_link' => route('admin.activate', $plainToken),
                'website_title' => $bs->website_title,
                'logo_path' => KreativMailer::resolveAssetPath($bs->email_logo ?: $bs->logo),
                'templateType' => 'admin_account_activation',
            ]);
        } catch (\Throwable $e) {
            \Log::error('[UserController] Activation email failed', ['error' => $e->getMessage()]);
        }
    }

    public function activate($token)
    {
        $tokenHash = hash_hmac('sha256', $token, config('app.key'));
        $user = Admin::where('activation_token_hash', $tokenHash)->first();

        $valid = $user
            && $user->activation_expires_at
            && now()->lt($user->activation_expires_at)
            && hash_equals($user->activation_token_hash, $tokenHash);

        return view('admin.activate', ['token' => $token, 'valid' => $valid]);
    }

    public function activateStore(Request $request, $token)
    {
        $tokenHash = hash_hmac('sha256', $token, config('app.key'));
        $user = Admin::where('activation_token_hash', $tokenHash)->first();

        $valid = $user
            && $user->activation_expires_at
            && now()->lt($user->activation_expires_at)
            && hash_equals($user->activation_token_hash, $tokenHash);

        if (!$valid) {
            return view('admin.activate', ['token' => $token, 'valid' => false]);
        }

        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        $user->password = bcrypt($request->password);
        $user->activation_token_hash = null;
        $user->activation_expires_at = null;
        $user->save();

        Auth::guard('admin')->login($user);

        Session::flash('success', 'Password created successfully! Welcome aboard.');
        return redirect()->route('admin.dashboard');
    }


    public function update(Request $request)
    {
        // Same protection as edit() — belt and suspenders in case this is
        // ever hit directly, bypassing the edit form.
        if ($request->user_id == 1 && Auth::guard('admin')->user()->id != 1) {
            Session::flash('warning', "You cannot edit the owner's account!");
            return "forbidden";
        }

        $user = Admin::findOrFail($request->user_id);
        $image = $request->image;
        $allowedExts = allowed_image_extensions();
        $extImage = pathinfo($image, PATHINFO_EXTENSION);

        $rules = [
            'username' => [
                'required',
                'max:255',
                Rule::unique('admins')->ignore($user->id),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('admins')->ignore($user->id),
            ],
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'role' => 'required',
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

        $user->username = $request->username;
        $user->email = $request->email;
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->status = $request->status;
        $user->role_id = $request->role;

        if ($request->filled('image')) {
            @unlink(FRONT_ADMIN_IMG_PATH . self::IMG_SUBDIR . $user->image);
            $filename = uniqid() .'.'. $extImage;
            @copy($image, FRONT_ADMIN_IMG_PATH . self::IMG_SUBDIR . $filename);
            $user->image = $filename;
        }

        $user->save();

        Session::flash('success', 'User updated successfully!');
        return "success";
    }

    public function delete(Request $request)
    {
        if ($request->user_id == 1) {
            Session::flash('warning', 'You cannot delete the owner!');
            return back();
        }

        $user = Admin::findOrFail($request->user_id);
        @unlink(FRONT_ADMIN_IMG_PATH . self::IMG_SUBDIR . $user->image);
        $user->delete();

        Session::flash('success', 'User deleted successfully!');
        return back();
    }

    public function unlock(Request $request)
    {
        abort_unless(Auth::guard('admin')->user()->isSuperAdmin(), 403);

        $user = Admin::findOrFail($request->user_id);
        $user->locked_at = null;
        $user->failed_login_attempts = 0;
        $user->save();

        Session::flash('success', 'Account unlocked successfully!');
        return back();
    }

    public function managePermissions($id)
    {
        $data['user'] = Admin::find($id);
        return view('admin.user.permission.manage', $data);
    }

    public function updatePermissions(Request $request)
    {
        $permissions = json_encode($request->permissions);
        $user = Admin::find($request->user_id);
        $user->permissions = $permissions;
        $user->save();

        Session::flash('success', "Permissions updated successfully for '$user->name' user");
        return back();
    }
}
