<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\TenderBlacklist;
use App\TenderCompany;
use App\TenderPurchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

/**
 * Admin "Users Management" — companies that purchased or attempted to
 * purchase a tender, derived from tender_purchases via TenderCompany.
 */
class TenderCompanyController extends Controller
{
    public function index(Request $request)
    {
        $term = trim((string) $request->input('term', ''));

        $companies = TenderCompany::query()
            ->when($term !== '', function ($query) use ($term) {
                $query->where('company_name', 'like', "%{$term}%")
                    ->orWhere('company_registration_no', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            })
            ->orderBy('last_purchase_at', 'DESC')
            ->paginate(10);

        return view('admin.tender.company.index', compact('companies'));
    }

    /**
     * Toggle blacklist for a company by registration number. Mirrors
     * TenderBlacklistController::storeFromPurchase (same "reg no only, no
     * email/phone copied" identity rule), but entered from a company row
     * instead of a single purchase row.
     */
    public function toggleBlacklist(Request $request)
    {
        $company = TenderCompany::findOrFail($request->company_id);
        $regNo   = $company->company_registration_no;

        $existing = TenderBlacklist::where('company_registration_no', $regNo)->first();

        if ($existing) {
            $existing->delete();
            Session::flash('success', 'Company removed from the blacklist.');
        } else {
            TenderBlacklist::create([
                'company_registration_no' => $regNo,
                'company_name'            => $company->company_name ?: null,
                'reason'                  => trim((string) $request->input('reason', '')) ?: null,
            ]);
            Session::flash('success', 'Company added to the blacklist.');
        }

        return back();
    }

    /**
     * Soft-delete the company identity record only. tender_purchases rows
     * already carry their own full snapshot of the buyer's details, so
     * purchase/order history is entirely unaffected.
     */
    public function destroy(Request $request)
    {
        TenderCompany::findOrFail($request->company_id)->delete();

        Session::flash('success', 'Company removed from the list.');
        return back();
    }
}
