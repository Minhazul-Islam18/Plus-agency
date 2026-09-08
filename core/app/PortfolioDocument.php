<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PortfolioDocument extends Model
{
    protected $fillable = ['portfolio_id', 'file', 'original_name', 'size'];

    public function portfolio()
    {
        return $this->belongsTo('App\Portfolio');
    }
}
