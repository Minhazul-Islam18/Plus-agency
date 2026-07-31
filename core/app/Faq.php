<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\InvalidatesHomeListingCache;

class Faq extends Model
{
    use InvalidatesHomeListingCache;
  public $timestamps = false;

  public function faqCategory()
  {
    return $this->belongsTo('App\FAQCategory', 'category_id', 'id');
  }
}
