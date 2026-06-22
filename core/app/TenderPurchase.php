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
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    public function tender()
    {
        return $this->hasOne('App\Tender', 'id', 'tender_id');
    }

    /**
     * Module names this email has already paid for on a tender.
     * purchased_modules stores {name, cost} (no ids), so paid-state is keyed by name.
     */
    public static function paidModuleNames(string $email, int $tenderId): array
    {
        return static::where('tender_id', $tenderId)
            ->where('payment_status', 'Completed')
            ->whereRaw('LOWER(TRIM(email)) = ?', [strtolower(trim($email))])
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
