<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TenderPurchase extends Model
{
    protected $fillable = [
        'tender_id',
        'user_id',
        'order_number',
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'country',
        'city',
        'company_name',
        'company_address',
        'company_registration_no',
        'qty',
        'technical_proposal_fee',
        'financial_proposal_fee',
        'summary_fee',
        'tender_notice_publication_fee',
        'currency_code',
        'payment_method',
        'gateway_type',
        'payment_status',
        'paid_at',
        'receipt',
        'invoice',
        'payment_reference',
        'access_status',
        'suspend_reason',
        'suspended_at',
    ];

    protected $casts = [
        'paid_at'      => 'datetime',
        'suspended_at' => 'datetime',
    ];

    public function tender()
    {
        return $this->hasOne('App\Tender', 'id', 'tender_id');
    }

    /**
     * Admin has suspended this transaction: no link issuing, no download.
     */
    public function isSuspended(): bool
    {
        return $this->access_status === 'suspended';
    }

    /**
     * Canonical form of a company registration number: uppercase, alphanumeric only.
     * Duplicate detection compares canonical values, so "ab-12 " and "AB12" collide.
     */
    public static function normalizeRegNo(?string $regNo): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $regNo));
    }

    /**
     * Module names already paid for on a tender under a given company registration
     * number. purchased_modules stores {name, cost} (no ids), so paid-state is keyed
     * by name. Registration number is the sole buyer identity for the duplicate guard:
     * the same company cannot re-pay regardless of which email is used. An empty /
     * missing registration number never matches, so it never blocks a purchase.
     */
    public static function paidModuleNamesForReg(int $tenderId, ?string $regNo): array
    {
        $regNo = static::normalizeRegNo($regNo);
        if ($regNo === '') {
            return [];
        }

        return static::where('tender_id', $tenderId)
            ->where('payment_status', 'Completed')
            ->where('company_registration_no', $regNo)
            ->get()
            ->flatMap(function ($p) {
                $mods = json_decode($p->purchased_modules, true) ?: [];
                return collect($mods)->pluck('name');
            })
            ->map(fn($n) => trim((string) $n))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
