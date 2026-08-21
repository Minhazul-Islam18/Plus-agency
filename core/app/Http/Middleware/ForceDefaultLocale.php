<?php

namespace App\Http\Middleware;

use App\Language;
use Closure;

/**
 * Replaces SetLangMiddleware for routes that skip the session middleware to
 * be Cloudflare-cacheable (see routes/web.php). SetLangMiddleware picks the
 * response language from session or the visitor's Accept-Language header —
 * either of which would make a cached page's language depend on whoever's
 * request first populated the cache, served identically to every other
 * visitor regardless of their own browser language. Forcing the site's
 * configured default language instead guarantees a cached page's content is
 * consistent for everyone; the language switcher still works normally on
 * every non-cached page (session persists there).
 */
class ForceDefaultLocale
{
    public function handle($request, Closure $next)
    {
        $defaultLang = Language::where('is_default', 1)->first();
        if ($defaultLang) {
            app()->setLocale($defaultLang->code);
        }

        return $next($request);
    }
}
