<?php

namespace App\Http\Middleware;

use App\Language;
use Closure;

/**
 * Replaces SetLangMiddleware for routes that skip the session middleware to
 * be Cloudflare-cacheable (see routes/web.php). SetLangMiddleware's
 * Accept-Language auto-detect would make a cached page's language depend on
 * whoever's browser first populated the cache, served identically to every
 * other visitor regardless of their own browser language — so that part is
 * never used here.
 *
 * The site_lang cookie (set by FrontendController::changeLanguage) IS
 * honored, unlike a session, because it's readable without starting a
 * session at all. A visitor who explicitly switched language sends that
 * cookie on every request; Cloudflare's Cache Rule bypasses cache for any
 * request carrying it, so they always hit origin and get their real
 * choice — everyone else (no cookie, the overwhelming majority) gets the
 * fast cached page in the site's default language.
 */
class ForceDefaultLocale
{
    public function handle($request, Closure $next)
    {
        $lang = null;
        if ($request->hasCookie('site_lang')) {
            $lang = Language::where('code', $request->cookie('site_lang'))->where('status', 1)->first();
        }
        if (!$lang) {
            $lang = Language::where('is_default', 1)->first();
        }
        if ($lang) {
            app()->setLocale($lang->code);
        }

        return $next($request);
    }
}
