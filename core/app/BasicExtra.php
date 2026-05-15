<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BasicExtra extends Model
{
  protected $table = 'basic_settings_extra';

  public $timestamps = false;

  public function language()
  {
    return $this->belongsTo('App\Language');
  }

  protected $fillable = [
    'faq_category_status',
    'gallery_category_status',
    'package_category_status',
    'twilio_status',
    'twilio_account_sid',
    'twilio_auth_token',
    'twilio_from_number',
    'tender_breadcrumb_bg',
    'tender_breadcrumb_overlay_color',
    'tender_breadcrumb_overlay_opacity',
  ];
}
