<?php

namespace App\Http\Middleware;

use App\Language;
use Closure;
use Illuminate\Support\Facades\URL;

/**
 * Replaces the old cookie/Accept-Language detection (SetLangMiddleware) for
 * every front-end route now living under /{locale}/... — language comes
 * from the URL segment itself, which is what makes it crawlable (Google
 * indexes /en/... and /fr/... as genuinely separate pages) and cache-safe
 * (Cloudflare's URL-based cache key naturally separates them, no more
 * cookie-bypass rule needed).
 *
 * The route-level ->where('locale', $activeCodes) constraint already
 * rejects unrecognized locale codes before this ever runs — this only
 * handles setting the resolved locale for the rest of the request.
 */
class SetLocaleFromUrl
{
    public function handle($request, Closure $next)
    {
        $locale = $request->route('locale');
        $lang = Language::where('code', $locale)->where('status', 1)->first();

        if (!$lang) {
            $lang = Language::where('is_default', 1)->first();
        }

        if ($lang) {
            app()->setLocale($lang->code);
            // Set here, not in a service provider's boot() — boot() runs
            // once at bootstrap, before routing, so app()->getLocale() would
            // still be the config default at that point. This has to happen
            // after the real locale is known, so route()/URL generation
            // during *this* request defaults to the right {locale} segment
            // without every call site needing to pass it explicitly.
            URL::defaults(['locale' => $lang->code]);
        }

        // Laravel binds route parameters to controller method arguments by
        // POSITION, not by name (see ResolvesRouteDependencies::resolveMethodDependencies
        // — it does array_values($parameters) then splices by reflection
        // index only for type-hinted class deps). Every controller method
        // under a /{locale}/... route was written before this migration
        // and expects its ORIGINAL first argument (e.g. changeLanguage($lang),
        // dynamicPage($slug), tender_details($slug)) — with {locale} still
        // in the route's parameter list, it silently shifts in as that
        // first argument instead, and the real value gets dropped (PHP
        // ignores excess positional arguments rather than erroring).
        // Removing it here, once, keeps every existing controller
        // signature correct without touching them individually.
        $request->route()->forgetParameter('locale');

        return $next($request);
    }
}
