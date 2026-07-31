<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\InvalidatesHomeListingCache;

class Blog extends Model
{
    use InvalidatesHomeListingCache;
    public $timestamps = true;

    public function bcategory() {
      return $this->belongsTo('App\Bcategory');
    }

    public function language() {
      return $this->belongsTo('App\Language');
    }
}
