<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Language;
use App\TenderPurchase;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $data['tpurchases'] = TenderPurchase::with('tender')->orderBy('id', 'DESC')->limit(10)->get();
        $data['default'] = Language::where('is_default', 1)->first();
        return view('admin.dashboard', $data);
    }
}
