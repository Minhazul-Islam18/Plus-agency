<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TenderBlacklist extends Model
{
    protected $fillable = [
        'company_registration_no',
        'email',
        'phone_number',
        'company_name',
        'reason',
    ];

    /**
     * Is this company blacklisted? Keyed on the company registration number alone —
     * email and phone are stored on a rule for reference only and are never matched
     * on, so one person may still order for a different, unbanned company.
     * Values are canonicalised, so "ab-12" and "AB12" hit the same rule.
     */
    public static function matches(?string $regNo): bool
    {
        $regNo = TenderPurchase::normalizeRegNo($regNo);
        if ($regNo === '') {
            return false;
        }

        return static::where('company_registration_no', $regNo)->exists();
    }
}
