<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PortfolioSector extends Model
{
    public $timestamps = false;

    protected $fillable = ['language_id', 'parent_id', 'name', 'icon', 'status', 'serial_number'];

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

    /** Portfolios tagged at THIS row specifically as their subsector (only meaningful on a subsector row). */
    public function portfoliosAsSubsector()
    {
        return $this->hasMany('App\Portfolio', 'subsector_id');
    }

    public function language()
    {
        return $this->belongsTo('App\Language');
    }

    /** Self-referencing hierarchy — a subsector's own top-level sector. Null on a top-level sector row. */
    public function parent()
    {
        return $this->belongsTo('App\PortfolioSector', 'parent_id');
    }

    /** A sector's own subsectors (rows with parent_id = this row's id). Empty on a subsector row. */
    public function subsectors()
    {
        return $this->hasMany('App\PortfolioSector', 'parent_id')->orderBy('serial_number', 'asc');
    }

    /** Top-level sectors only — what the left panel / Portfolio form's "Sector" select show. */
    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }
}
