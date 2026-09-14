<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\InvalidatesHomeListingCache;

class Portfolio extends Model
{
    use InvalidatesHomeListingCache;
  protected $fillable = [
      'id', 'language_id', 'title', 'slug', 'start_date', 'end_date', 'submission_date', 'client_name', 'tags',
      'featured_image', 'content', 'service_id', 'status', 'serial_number', 'meta_keywords',
      'meta_description', 'website_link', 'cost_of_service',
      // Portfolio module overhaul — "evidence of competence" structured fields
      // (see ~/Documents/15-Portoflios/Recommandation sur Portofolios-*.docx).
      'sector_id', 'subsector_id', 'country', 'year', 'summary', 'partners',
      'problematique', 'mission_ica', 'expertise_mobilisee', 'solution_approche', 'resultat_statut', 'impact',
      'client_logo', 'is_published', 'is_archived',
      'problematique_icon', 'mission_ica_icon', 'expertise_mobilisee_icon', 'solution_approche_icon',
      'resultat_statut_icon', 'impact_icon',
      // Manageable Status module (like Sector) — replaces the old
      // hardcoded 'status' string enum. That column is left in place
      // (untouched, not written to by new code) rather than dropped.
      'status_id',
      // Carousel/hero overlay — see the migration's own comment for why
      // these are separate from title/content rather than reusing them.
      'overlay_title', 'overlay_subtitle', 'overlay_description', 'overlay_color', 'overlay_opacity', 'overlay_bloom_opacity',
  ];

  public function portfolio_images()
  {
    return $this->hasMany('App\PortfolioImage');
  }

  public function documents()
  {
    return $this->hasMany('App\PortfolioDocument');
  }

  public function highlights()
  {
    return $this->hasMany('App\PortfolioHighlight')->orderBy('serial_number');
  }

  // Named partnerRefs() rather than partners() — the old free-text
  // `partners` string column still exists on this table (untouched,
  // unused by new code once this is set), and a relation method sharing
  // that exact name would be shadowed by Eloquent's attribute lookup, same
  // reasoning as statusInfo() below.
  public function partnerRefs()
  {
    return $this->belongsToMany('App\Partner', 'portfolio_partners', 'portfolio_id', 'partner_id');
  }

  // Overlay copy falls back to the portfolio's own title/summary when an
  // admin hasn't filled the dedicated overlay fields in — a banner should
  // never render visually empty just because this new section wasn't
  // touched on an older record.
  public function overlayTitle()
  {
    return $this->overlay_title ?: $this->title;
  }

  public function overlayDescription()
  {
    return $this->overlay_description ?: $this->summary;
  }

  public function service()
  {
    return $this->belongsTo('App\Service');
  }

  public function sector()
  {
    return $this->belongsTo('App\PortfolioSector', 'sector_id');
  }

  public function subsector()
  {
    return $this->belongsTo('App\PortfolioSector', 'subsector_id');
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
