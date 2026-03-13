<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TenderSection extends Model
{
    public function tenderModule()
    {
        return $this->belongsTo('App\TenderModule');
    }
}
