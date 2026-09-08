<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PortfolioSector extends Model
{
    public $timestamps = false;

    protected $fillable = ['language_id', 'name', 'status', 'serial_number'];

    // Explicit FK required — Eloquent's default guess for a bare
    // hasMany('App\Portfolio') would be `portfolio_sector_id` (snake-case
    // of this class's own name), but the real column on `portfolios` is
    // `sector_id`. Without this, ->portfolios()->count() 500s (caught
    // live: the delete-guard in PortfolioSectorController@delete never
    // actually worked for a sector genuinely in use).
    public function portfolios()
    {
        return $this->hasMany('App\Portfolio', 'sector_id');
    }

    public function language()
    {
        return $this->belongsTo('App\Language');
    }
}
