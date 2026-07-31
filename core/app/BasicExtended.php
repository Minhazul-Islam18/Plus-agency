<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\InvalidatesGlobalViewCache;

class BasicExtended extends Model
{
    use InvalidatesGlobalViewCache;
    protected $table = 'basic_settings_extended';
    public $timestamps = false;

    public function language() {
        return $this->belongsTo('App\Language');
    }
}
