<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenderCompany extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_name',
        'country',
        'company_registration_no',
        'email',
        'phone_number',
        'first_purchase_at',
        'last_purchase_at',
    ];

    protected $casts = [
        'first_purchase_at' => 'datetime',
        'last_purchase_at'  => 'datetime',
    ];

    /**
     * Number of Completed-payment purchases for this company. Not cached on
     * the row — computed live so it can never drift from tender_purchases.
     */
    public function purchasedTendersCount(): int
    {
        return TenderPurchase::where('company_registration_no', $this->company_registration_no)
            ->where('payment_status', 'Completed')
            ->count();
    }

    public function isBlacklisted(): bool
    {
        return TenderBlacklist::matches($this->company_registration_no);
    }

    /**
     * Keep the company identity record in sync with a purchase attempt
     * (paid or not — the doc counts "attempted to purchase" as a user too).
     * Skips purchases with no registration number, since that's the sole
     * identity key. Restores a previously soft-deleted company on a new
     * purchase — a live order means they're active again.
     */
    public static function syncFromPurchase(TenderPurchase $purchase): void
    {
        $regNo = TenderPurchase::normalizeRegNo($purchase->company_registration_no);
        if ($regNo === '') {
            return;
        }

        $company = static::withTrashed()->where('company_registration_no', $regNo)->first();

        if (!$company) {
            $company = new static(['company_registration_no' => $regNo]);
            $company->first_purchase_at = $purchase->created_at ?? now();
        } elseif ($company->trashed()) {
            $company->restore();
        }

        $company->company_name    = $purchase->company_name;
        $company->country         = $purchase->country;
        $company->email           = $purchase->email;
        $company->phone_number    = $purchase->phone_number;
        $company->last_purchase_at = $purchase->created_at ?? now();
        $company->save();
    }
}
