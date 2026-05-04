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
        'receipt',
        'invoice',
        'payment_reference',
    ];

    public function tender()
    {
        return $this->hasOne('App\Tender', 'id', 'tender_id');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
