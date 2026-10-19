<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HeroVideoSetting extends Model
{
    protected $table = 'hero_video_settings';
    protected $guarded = [];
    public $timestamps = true;
    protected $casts = [
        'titles' => 'array',
    ];

    public function language()
    {
        return $this->belongsTo(Language::class);
    }
}
