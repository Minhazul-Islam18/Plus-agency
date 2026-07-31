<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\InvalidatesGlobalViewCache;

class BasicSetting extends Model
{
    use InvalidatesGlobalViewCache;
    public $timestamps = false;

    public function language() {
        return $this->belongsTo('App\Language');
    }
}
