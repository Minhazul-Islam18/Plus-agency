<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tender extends Model
{
    protected $fillable = [
        'language_id',
        'tender_category_id',
        'country',
        'tender_code',
        'title',
        'slug',
        'submission_deadline',
        'current_price',
        'previous_price',
        'summary',
        'tender_image',
        'video_link',
        'overview',
        'expert_name',
        'expert_position',
        'expert_details',
        'expert_whatsapp',
        'expert_phone',
        'expert_image',
        'is_featured',
    ];

    public function tenderCategory()
    {
        return $this->belongsTo('App\TenderCategory');
    }

    public function language()
    {
        return $this->belongsTo('App\Language');
    }

    public function tenderModules()
    {
        return $this->hasMany('App\TenderModule');
    }

    public function tenderPurchase()
    {
        return $this->hasMany('App\TenderPurchase');
    }
}
