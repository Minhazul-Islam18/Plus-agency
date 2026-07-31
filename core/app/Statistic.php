<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\InvalidatesHomeListingCache;

class Statistic extends Model
{
    use InvalidatesHomeListingCache;
    public $timestamps = false;

    public function language() {
        return $this->belongsTo('App\Language');
    }

}
