<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TenderCategory extends Model
{
  public function tenders()
  {
    return $this->hasMany('App\Tender');
  }
}
