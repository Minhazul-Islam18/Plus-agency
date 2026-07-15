<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;

class LoginController extends Controller
{
    public function login(){
      return view('admin.login');
    }

    public function authenticate(Request $request){
      // return $request->username . ' ' . $request->password;
      $this->validate($request, [
        'username'   => 'required',
        'password' => 'required'
      ]);
      if (Auth::guard('admin')->attempt(['username' => $request->username,'password' => $request->password]))
      {
          // Return to the page the admin was on before the session expired
          // (stored as url.intended by the guest redirect), falling back to
          // the dashboard on a fresh login.
          return redirect()->intended(route('admin.dashboard'));
      }
      return redirect()->back()->with('alert','Username and Password Not Matched');
    }

    public function logout() {
      Auth::guard('admin')->logout();
      return redirect()->route('admin.login');
    }
}
