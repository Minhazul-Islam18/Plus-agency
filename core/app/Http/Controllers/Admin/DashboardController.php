<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Language;
use App\Quote;
use App\TenderPurchase;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $data['quotes'] = Quote::orderBy('id', 'DESC')->limit(10)->get();
        $data['tpurchases'] = TenderPurchase::with('tender')->orderBy('id', 'DESC')->limit(10)->get();
        $data['default'] = Language::where('is_default', 1)->first();
        return view('admin.dashboard', $data);
    }
}
