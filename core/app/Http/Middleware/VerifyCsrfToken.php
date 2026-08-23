<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
  /**
   * Indicates whether the XSRF-TOKEN cookie should be set on the response.
   *
   * @var bool
   */
  protected $addHttpCookie = true;

  /**
   * The URIs that should be excluded from CSRF verification.
   *
   * @var array
   */
  protected $except = [
    // The newsletter form lives in the shared footer, so it's on every
    // page. Once pages are Cloudflare-cached, the CSRF token baked into
    // that cached HTML belongs to whoever's request generated the cache
    // entry — every other visitor's submission would 419. Exempted here,
    // compensated by throttle:5,10 on the route instead (see routes/web.php).
    // Wildcard: subscribe now lives under /{locale}/subscribe.
    '*/subscribe',
    // Web push subscription registration (common-main.js, fires from any
    // page including the now session-less cacheable ones — same reasoning
    // as 'subscribe' above). Low-stakes (just registers a push endpoint),
    // compensated by throttle:10,1 on the route. Stays unprefixed — /push
    // was deliberately left outside the /{locale} group.
    'push',
  ];
}
