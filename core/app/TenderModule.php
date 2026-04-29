<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TenderModule extends Model
{
    protected $guarded = ['id'];
    public function tender()
    {
        return $this->belongsTo('App\Tender');
    }

    public function sections()
    {
        return $this->hasMany('App\TenderSection');
    }
}
