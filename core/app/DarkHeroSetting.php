<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DarkHeroSetting extends Model
{
    protected $table = 'dark_hero_settings';
    protected $guarded = [];
    public $timestamps = true;

    public function language()
    {
        return $this->belongsTo(Language::class);
    }
}
