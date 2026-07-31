<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\InvalidatesGlobalViewCache;

class Menu extends Model
{
    use InvalidatesGlobalViewCache;
    public function language() {
        return $this->belongsTo('App\Language');
    }
}
