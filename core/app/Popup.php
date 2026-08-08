<?php

namespace App;

use App\Traits\InvalidatesGlobalViewCache;
use Illuminate\Database\Eloquent\Model;

class Popup extends Model
{
    use InvalidatesGlobalViewCache;

    public function language() {
        return $this->belongsTo('App\Language');
    }
}
