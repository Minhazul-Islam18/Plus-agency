<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\InvalidatesHomeListingCache;

class Service extends Model
{
    use InvalidatesHomeListingCache;

  public function scategory()
  {
    return $this->belongsTo('App\Scategory');
  }

  public function portfolios()
  {
    return $this->hasMany('App\Portfolio');
  }

  public function language()
  {
    return $this->belongsTo('App\Language');
  }
}
