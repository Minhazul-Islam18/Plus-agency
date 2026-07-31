<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

/**
 * Homepage's non-pagebuilder listing blocks (portfolios/points/statistics/
 * testimonials/faqs/members/blogs/partners/scategories/services) are cached
 * per-language in FrontendController@index (key: home_listing_blocks:lang:{id}).
 * Any model contributing to that block uses this trait so a save/delete from
 * ANY code path — controller, tinker, seeder, queue job — invalidates it,
 * instead of relying on every controller method remembering to call
 * Cache::forget itself.
 */
trait InvalidatesHomeListingCache
{
    protected static function bootInvalidatesHomeListingCache()
    {
        static::saved(function ($model) {
            static::flushHomeListingCache($model);
        });

        static::deleted(function ($model) {
            static::flushHomeListingCache($model);
        });
    }

    private static function flushHomeListingCache($model): void
    {
        if (!empty($model->language_id)) {
            Cache::forget("home_listing_blocks:lang:{$model->language_id}");
        }
    }
}
