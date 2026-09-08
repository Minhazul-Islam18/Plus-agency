<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Marks an already session/CSRF-free response (see $cfCacheableExcept in
 * routes/web.php — this middleware is always chained right after
 * ->withoutMiddleware($cfCacheableExcept), never applied on its own) as safe
 * to cache, both at Cloudflare's edge and at the origin's own LiteSpeed
 * LSCache layer.
 *
 * Neither of those actually happens for free just by dropping the session
 * middleware: with no explicit Cache-Control, LiteSpeed's own default for a
 * dynamic PHP response is a conservative "Cache-Control: no-cache, private"
 * (confirmed live via curl — present even on routes that provably never run
 * StartSession, checked via ->excludedMiddleware() in tinker), which
 * Cloudflare then just passes through as-is (cf-cache-status: DYNAMIC).
 *
 * s-maxage (shared/CDN caches — Cloudflare) is set longer than max-age
 * (browsers) so a stale value is corrected quickly for a visitor who reloads,
 * while the edge still absorbs the bulk of repeat traffic. Matches the
 * app's own AppServiceProvider::globalViewData TTL (1500-1800s) so both
 * layers go stale on roughly the same schedule.
 *
 * X-LiteSpeed-Cache-Control is LiteSpeed's own opt-in signal — it takes
 * priority over the server's default private/no-cache behavior for the
 * exact same response, letting the origin's own LSCache layer cache the
 * page too, not just Cloudflare's edge.
 *
 * No purge-on-save hook exists yet for either cache — an admin edit to a
 * cached page's content becomes visible only after the TTL below lapses,
 * same eventual-consistency tradeoff the app already accepts for its own
 * flexible() view-data cache. Worth a follow-up (Cloudflare purge API call
 * from BasicController/PortfolioController etc.) if same-second consistency
 * ever matters more than it does today.
 */
class SetPublicCacheHeaders
{
    private const BROWSER_MAX_AGE = 300;
    private const EDGE_MAX_AGE = 1800;

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only cache real success responses to real GET/HEAD requests — never
        // a redirect, a validation error, or a 404 that slipped through this
        // route (e.g. a slug lookup miss inside the controller). HEAD gets
        // the same treatment as GET (RFC-correct — same cacheable resource,
        // same validators) so a HEAD-based cache warmer/prefetch doesn't see
        // different caching behavior than a real page load.
        if ($request->isMethodCacheable() && method_exists($response, 'header') && $response->isSuccessful()) {
            $response->headers->set(
                'Cache-Control',
                'public, max-age=' . self::BROWSER_MAX_AGE . ', s-maxage=' . self::EDGE_MAX_AGE
            );
            $response->headers->set(
                'X-LiteSpeed-Cache-Control',
                'public,max-age=' . self::EDGE_MAX_AGE
            );
        }

        return $response;
    }
}
