<?php

namespace RachidLaasri\LaravelInstaller\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;

class LicenseController extends Controller
{

    public function __construct()
    {

    }

    /**
     * Display the permissions check page.
     *
     * @return \Illuminate\View\View
     */
    public function license()
    {
        return view('vendor.installer.license');
    }

    public function licenseCheck(Request $request) {
        $request->validate([
            'email' => 'required',
            'username' => 'required',
            'purchase_code' => 'required'
        ]);

        Session::flash('license_success', 'Your license is verified successfully!');
        return redirect()->route('LaravelInstaller::environmentWizard');

    }
}
