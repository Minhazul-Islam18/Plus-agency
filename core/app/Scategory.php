<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\InvalidatesHomeListingCache;

class Scategory extends Model
{
    use InvalidatesHomeListingCache;
  public $timestamps = false;

  public function services()
  {
    return $this->hasMany('App\Service');
  }

  public function language()
  {
    return $this->belongsTo('App\Language');
  }
}
