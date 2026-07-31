<?php

namespace App\Traits;

use App\Providers\AppServiceProvider;

/**
 * bs/be/bex/langs/menus are cached per-language in AppServiceProvider's view
 * composer (key: global_view_data:lang:{id}, plus global_socials/
 * global_languages_active). Any model backing that bundle uses this trait so
 * a save/delete from ANY code path invalidates it automatically, instead of
 * relying on every admin controller method remembering to call
 * AppServiceProvider::flushGlobalViewCache() itself.
 */
trait InvalidatesGlobalViewCache
{
    protected static function bootInvalidatesGlobalViewCache()
    {
        static::saved(function ($model) {
            AppServiceProvider::flushGlobalViewCache();
        });

        static::deleted(function ($model) {
            AppServiceProvider::flushGlobalViewCache();
        });
    }
}
