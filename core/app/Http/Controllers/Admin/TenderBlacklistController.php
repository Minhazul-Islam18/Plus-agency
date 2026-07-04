<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\TenderBlacklist;
use App\TenderPurchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class TenderBlacklistController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        $entries = TenderBlacklist::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where('email', 'like', "%{$q}%")
                    ->orWhere('phone_number', 'like', "%{$q}%")
                    ->orWhere('ip_address', 'like', "%{$q}%")
                    ->orWhere('company_name', 'like', "%{$q}%");
            })
            ->orderBy('id', 'DESC')
            ->paginate(15);

        return view('admin.tender.blacklist.index', compact('entries', 'q'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'email'        => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:50',
            'ip_address'   => 'nullable|string|max:45',
            'company_name' => 'nullable|string|max:255',
            'reason'       => 'nullable|string|max:255',
        ]);

        // At least one identifier is required for a usable rule.
        if (!$request->filled('email') && !$request->filled('phone_number') && !$request->filled('ip_address')) {
            Session::flash('error', 'Provide at least an email, phone number, or IP address.');
            return back();
        }

        TenderBlacklist::create([
            'email'        => $request->email ? strtolower(trim($request->email)) : null,
            'phone_number' => $request->phone_number ?: null,
            'ip_address'   => $request->ip_address ?: null,
            'company_name' => $request->company_name ?: null,
            'reason'       => $request->reason ?: null,
        ]);

        Session::flash('success', 'Buyer added to the blacklist.');
        return back();
    }

    /**
     * Quick "blacklist this buyer" from a purchase row.
     */
    public function storeFromPurchase(Request $request)
    {
        $purchase = TenderPurchase::findOrFail($request->purchase_id);

        TenderBlacklist::create([
            'email'        => $purchase->email ? strtolower(trim($purchase->email)) : null,
            'phone_number' => $purchase->phone_number ?: null,
            'ip_address'   => null,
            'company_name' => $purchase->company_name ?: null,
            'reason'       => trim($request->input('reason', '')) ?: null,
        ]);

        Session::flash('success', 'Buyer added to the blacklist.');
        return back();
    }

    public function update(Request $request)
    {
        $request->validate([
            'id'           => 'required|exists:tender_blacklists,id',
            'email'        => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:50',
            'ip_address'   => 'nullable|string|max:45',
            'company_name' => 'nullable|string|max:255',
            'reason'       => 'nullable|string|max:255',
        ]);

        if (!$request->filled('email') && !$request->filled('phone_number') && !$request->filled('ip_address')) {
            Session::flash('error', 'Provide at least an email, phone number, or IP address.');
            return back();
        }

        $entry = TenderBlacklist::findOrFail($request->id);
        $entry->update([
            'email'        => $request->email ? strtolower(trim($request->email)) : null,
            'phone_number' => $request->phone_number ?: null,
            'ip_address'   => $request->ip_address ?: null,
            'company_name' => $request->company_name ?: null,
            'reason'       => $request->reason ?: null,
        ]);

        Session::flash('success', 'Blacklist entry updated.');
        return back();
    }

    public function destroy(Request $request)
    {
        TenderBlacklist::findOrFail($request->id)->delete();

        Session::flash('success', 'Blacklist entry removed.');
        return back();
    }
}
