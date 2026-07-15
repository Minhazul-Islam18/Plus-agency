<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\TenderBlacklist;
use App\TenderPurchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class TenderBlacklistController extends Controller
{
    /**
     * Rules for a blacklist entry. The company registration number is the required
     * identifier — it is what the checkout duplicate/blacklist guard keys on. Email,
     * phone and company name are optional extra identifiers.
     */
    private function rules(bool $withId = false): array
    {
        $rules = [
            'company_registration_no' => 'required|string|max:100|regex:/^[A-Z0-9]+$/',
            'email'                   => 'nullable|email|max:255',
            'phone_number'            => 'nullable|string|max:50',
            'company_name'            => 'nullable|string|max:255',
            'reason'                  => 'nullable|string|max:255',
        ];

        if ($withId) {
            $rules['id'] = 'required|exists:tender_blacklists,id';
        }

        return $rules;
    }

    private function messages(): array
    {
        return [
            'company_registration_no.required' => 'Company Registration No. is required.',
            'company_registration_no.regex'    => 'Company Registration No. may contain only letters and numbers — no spaces or special characters.',
        ];
    }

    /** Payload for create/update, with the registration number canonicalised. */
    private function payload(Request $request): array
    {
        return [
            'company_registration_no' => TenderPurchase::normalizeRegNo($request->company_registration_no),
            'email'                   => $request->email ? strtolower(trim($request->email)) : null,
            'phone_number'            => $request->phone_number ?: null,
            'company_name'            => $request->company_name ?: null,
            'reason'                  => $request->reason ?: null,
        ];
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        $entries = TenderBlacklist::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where('company_registration_no', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone_number', 'like', "%{$q}%")
                    ->orWhere('company_name', 'like', "%{$q}%");
            })
            ->orderBy('id', 'DESC')
            ->paginate(15);

        return view('admin.tender.blacklist.index', compact('entries', 'q'));
    }

    public function store(Request $request)
    {
        // Uppercase first, so a lowercase entry is accepted and stored canonically.
        $request->merge([
            'company_registration_no' => strtoupper(trim((string) $request->company_registration_no)),
        ]);
        $request->validate($this->rules(), $this->messages());

        TenderBlacklist::create($this->payload($request));

        Session::flash('success', 'Buyer added to the blacklist.');
        return back();
    }

    /**
     * Quick "blacklist this company" from a purchase row. The rule is keyed on the
     * company registration number alone — the buyer's email and phone are deliberately
     * not copied, so one person may still order for a different, unbanned company.
     */
    public function storeFromPurchase(Request $request)
    {
        $purchase = TenderPurchase::findOrFail($request->purchase_id);

        $regNo = TenderPurchase::normalizeRegNo($purchase->company_registration_no);
        if ($regNo === '') {
            Session::flash('error', 'This order has no Company Registration No., so it cannot be blacklisted. Add the rule manually.');
            return back();
        }

        TenderBlacklist::create([
            'company_registration_no' => $regNo,
            'company_name'            => $purchase->company_name ?: null,
            'reason'                  => trim($request->input('reason', '')) ?: null,
        ]);

        Session::flash('success', 'Company added to the blacklist.');
        return back();
    }

    public function update(Request $request)
    {
        $request->merge([
            'company_registration_no' => strtoupper(trim((string) $request->company_registration_no)),
        ]);
        $request->validate($this->rules(true), $this->messages());

        TenderBlacklist::findOrFail($request->id)->update($this->payload($request));

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
