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
     * Module names a buyer has already paid for on a tender.
     * purchased_modules stores {name, cost} (no ids), so paid-state is keyed by name.
     *
     * Identity: a logged-in buyer is matched by account id (so editing the email
     * field cannot dodge the guard); a guest is matched by email.
     */
    public static function paidModuleNamesForBuyer(?int $userId, ?string $email, int $tenderId): array
    {
        $query = static::where('tender_id', $tenderId)
            ->where('payment_status', 'Completed');

        if (!empty($userId)) {
            $query->where('user_id', $userId);
        } else {
            $query->whereRaw('LOWER(TRIM(email)) = ?', [strtolower(trim((string) $email))]);
        }

        return $query->get()
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

    /**
     * Backwards-compatible email-only lookup. Prefer paidModuleNamesForBuyer().
     */
    public static function paidModuleNames(string $email, int $tenderId): array
    {
        return static::paidModuleNamesForBuyer(null, $email, $tenderId);
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
