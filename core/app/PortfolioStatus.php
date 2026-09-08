<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Manageable per-language "Statut" list (Ongoing/Pending/Completed, or
 * whatever the admin wants) — replaces the old hardcoded 3-value `status`
 * string enum on Portfolio. Mirrors PortfolioSector exactly.
 */
class PortfolioStatus extends Model
{
    public $timestamps = false;

    protected $fillable = ['language_id', 'name', 'status', 'serial_number'];

    // Explicit FK required — see the identical fix/comment on
    // PortfolioSector::portfolios(); the real column is `status_id`, not
    // Eloquent's default guess of `portfolio_status_id`.
    public function portfolios()
    {
        return $this->hasMany('App\Portfolio', 'status_id');
    }

    public function language()
    {
        return $this->belongsTo('App\Language');
    }
}
