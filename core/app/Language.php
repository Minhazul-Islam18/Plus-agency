<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
  protected $fillable = ['id', 'name', 'is_default', 'code', 'rtl', 'status'];

  public function basic_setting()
  {
    return $this->hasOne('App\BasicSetting');
  }

  public function basic_extended()
  {
    return $this->hasOne('App\BasicExtended', 'language_id');
  }

  public function basic_extra()
  {
    return $this->hasOne('App\BasicExtra', 'language_id');
  }

  public function sliders()
  {
    return $this->hasMany('App\Slider');
  }

  public function features()
  {
    return $this->hasMany('App\Feature');
  }

  public function points()
  {
    return $this->hasMany('App\Point');
  }

  public function statistics()
  {
    return $this->hasMany('App\Statistic');
  }

  public function testimonials()
  {
    return $this->hasMany('App\Testimonial');
  }

  public function members()
  {
    return $this->hasMany('App\Member');
  }

  public function partners()
  {
    return $this->hasMany('App\Partner');
  }

  public function ulinks()
  {
    return $this->hasMany('App\Ulink');
  }

  public function pages()
  {
    return $this->hasMany('App\Page');
  }

  public function scategories()
  {
    return $this->hasMany('App\Scategory');
  }

  public function services()
  {
    return $this->hasMany('App\Service');
  }

  public function portfolios()
  {
    return $this->hasMany('App\Portfolio');
  }

  public function galleries()
  {
    return $this->hasMany('App\Gallery');
  }

  public function faqs()
  {
    return $this->hasMany('App\Faq');
  }

  public function bcategories()
  {
    return $this->hasMany('App\Bcategory');
  }

  public function blogs()
  {
    return $this->hasMany('App\Blog');
  }

  public function menus()
  {
    return $this->hasMany('App\Menu');
  }

  public function sitemaps()
  {
    return $this->hasMany('App\Sitemap');
  }
  public function tender_categories()
  {
    return $this->hasMany('App\TenderCategory');
  }

  public function offline_gateways()
  {
    return $this->hasMany('App\OfflineGateway');
  }

  public function homes()
  {
    return $this->hasMany('App\Home');
  }

  public function megamenus()
  {
    return $this->hasMany('App\Megamenu');
  }

  public function faqCategory()
  {
    return $this->hasMany('App\FAQCategory');
  }

  public function galleryCategory()
  {
    return $this->hasMany('App\GalleryCategory');
  }

  public function popups() {
      return $this->hasMany('App\Popup');
  }
}
