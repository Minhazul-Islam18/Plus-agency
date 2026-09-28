<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\InvalidatesHomeListingCache;

class Blog extends Model
{
    use InvalidatesHomeListingCache;
    public $timestamps = true;

    public function bcategory() {
      return $this->belongsTo('App\Bcategory');
    }

    public function admin() {
      return $this->belongsTo('App\Admin');
    }

    /**
     * Full name of the admin who published this post — "By ..." byline on
     * the front end. Falls back to a generic label for posts published
     * before admin_id existed, or whose admin account has since been
     * deleted (admin_id set but the row is gone).
     */
    public function getAuthorNameAttribute(): string
    {
      $admin = $this->admin;
      $name = $admin ? trim($admin->first_name . ' ' . $admin->last_name) : '';
      return $name !== '' ? $name : __('Admin');
    }

    public function language() {
      return $this->belongsTo('App\Language');
    }
}
