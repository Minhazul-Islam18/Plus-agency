<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * One row of the carousel's right-side "icon highlights" panel (points
 * forts) — icon (Font Awesome class, picked from the same icon-picker
 * library used by the Evidence of Competence blocks) + a one-line label.
 * Always fully replaced on save (see PortfolioController::storeHighlights),
 * same pattern as PortfolioImage/PortfolioDocument.
 */
class PortfolioHighlight extends Model
{
    public $timestamps = false;

    protected $fillable = ['portfolio_id', 'icon', 'label', 'serial_number'];

    public function portfolio()
    {
        return $this->belongsTo('App\Portfolio');
    }
}
