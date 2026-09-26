<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\InvalidatesHomeListingCache;

class Faq extends Model
{
    use InvalidatesHomeListingCache;
  public $timestamps = false;

  protected $casts = [
    'is_frequent' => 'boolean',
    'promoted_at' => 'datetime',
  ];

  public function faqCategory()
  {
    return $this->belongsTo('App\FAQCategory', 'category_id', 'id');
  }
}
