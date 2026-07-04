<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Adds baseline security response headers to every web response.
 *
 * Content-Security-Policy is intentionally left out here — the theme relies on
 * inline scripts/styles, so a CSP needs per-page tuning and is tracked
 * separately. HSTS is only emitted over HTTPS so it never strands a plain-HTTP
 * environment.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Some responses (streamed file downloads) don't expose a headers bag
        // the same way; guard before writing.
        if (!method_exists($response, 'header')) {
            return $response;
        }

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-XSS-Protection', '0');
        $response->headers->set('Permissions-Policy', 'geolocation=(), camera=(), microphone=()');

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
