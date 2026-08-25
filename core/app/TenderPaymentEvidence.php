<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Append-only log of every manual payment-validation event (Mark as Paid /
 * Cancel Payment Validation) — one row per event, never updated or deleted.
 * See the migration for why this exists separately from
 * TenderPurchase::admin_proof (which only ever holds the current proof).
 */
class TenderPaymentEvidence extends Model
{
    // "Evidence" is uncountable — Eloquent's pluralizer would guess
    // `tender_payment_evidence`, not the `tender_payment_evidences` table
    // the migration actually creates.
    protected $table = 'tender_payment_evidences';

    protected $fillable = [
        'tender_purchase_id',
        'order_number',
        'action',
        'amount',
        'currency_code',
        'proof_path',
        'proof_original_name',
        'proof_size',
        'admin_id',
        'admin_name',
        'reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function purchase()
    {
        return $this->belongsTo('App\TenderPurchase', 'tender_purchase_id');
    }

    public function admin()
    {
        return $this->belongsTo('App\Admin', 'admin_id');
    }
}
