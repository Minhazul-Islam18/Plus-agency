<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\InvalidatesHomeListingCache;

class Portfolio extends Model
{
    use InvalidatesHomeListingCache;
  protected $fillable = [
      'id', 'language_id', 'title', 'slug', 'start_date', 'submission_date', 'client_name', 'tags',
      'featured_image', 'content', 'service_id', 'status', 'serial_number', 'meta_keywords',
      'meta_description', 'website_link', 'cost_of_service',
      // Portfolio module overhaul — "evidence of competence" structured fields
      // (see ~/Documents/15-Portoflios/Recommandation sur Portofolios-*.docx).
      'sector_id', 'country', 'year', 'summary', 'partners',
      'problematique', 'mission_ica', 'expertise_mobilisee', 'solution_approche', 'resultat_statut', 'impact',
      'client_logo', 'is_published', 'is_archived',
      'problematique_icon', 'mission_ica_icon', 'expertise_mobilisee_icon', 'solution_approche_icon',
      'resultat_statut_icon', 'impact_icon',
      // Manageable Status module (like Sector) — replaces the old
      // hardcoded 'status' string enum. That column is left in place
      // (untouched, not written to by new code) rather than dropped.
      'status_id',
  ];

  public function portfolio_images()
  {
    return $this->hasMany('App\PortfolioImage');
  }

  public function documents()
  {
    return $this->hasMany('App\PortfolioDocument');
  }

  public function service()
  {
    return $this->belongsTo('App\Service');
  }

  public function sector()
  {
    return $this->belongsTo('App\PortfolioSector', 'sector_id');
  }

  // Named statusInfo() rather than status() — the old `status` string
  // column still exists on this table (untouched, unused by new code),
  // and a relation method sharing that exact name would be shadowed by
  // Eloquent's attribute lookup (a loaded column always wins over a
  // same-named relation method), so $portfolio->status would silently
  // keep returning the old string instead of ever reaching this relation.
  public function statusInfo()
  {
    return $this->belongsTo('App\PortfolioStatus', 'status_id');
  }

  public function language()
  {
    return $this->belongsTo('App\Language');
  }
}
