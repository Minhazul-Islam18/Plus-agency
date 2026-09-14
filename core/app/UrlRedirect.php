<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Old-slug -> current-item lookup for 301 redirects, backing the editable
 * Slug field on Portfolio/Blog/Tender (see record_slug_redirect() /
 * clear_slug_redirect() in Helper.php and each module's front controller).
 */
class UrlRedirect extends Model
{
    public $timestamps = false;
    protected $fillable = ['module', 'old_slug', 'target_id'];
}
