<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Adds baseline security response headers to every web response.
 *
 * X-XSS-Protection is omitted entirely — the browsers that used to fall back
 * to a dangerous heuristic when it's absent (old IE/Edge) are no longer in
 * any meaningful use; every current browser has removed the XSS Auditor
 * feature this header controlled, so sending it (even as "0") only adds
 * attack surface for zero benefit. Automated scanners flag its mere
 * presence regardless of value, which is the right call at this point.
 *
 * Content-Security-Policy is shipped in Report-Only mode for now — the theme
 * has pervasive inline <script> blocks (owl carousel init, form validation,
 * etc.) across many Blade views, so a blocking CSP needs a monitoring period
 * against real traffic before it's safe to enforce. Switch the header name
 * to `Content-Security-Policy` (drop `-Report-Only`) once browser console /
 * report endpoint show no unexpected violations.
 *
 * Cross-Origin-Opener-Policy uses `same-origin-allow-popups` rather than the
 * stricter `same-origin` — the site opens payment-gateway checkout popups
 * (Razorpay) that may rely on window.opener; the stricter value can silently
 * break that redirect-back flow. HSTS is only emitted over HTTPS so it never
 * strands a plain-HTTP environment.
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

        // X-Powered-By is added by PHP itself (expose_php ini setting, which
        // is PHP_INI_SYSTEM — can't be changed at runtime or via .htaccess
        // on this shared host, only by the hosting provider). header_remove()
        // operates on PHP's own internal header list rather than Symfony's
        // response header bag, so it strips this one where ini_set() can't.
        header_remove('X-Powered-By');

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->remove('X-XSS-Protection');
        $response->headers->set('Permissions-Policy', 'geolocation=(), camera=(), microphone=()');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin-allow-popups');
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');

        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://code.jquery.com https://checkout.razorpay.com https://app.appzi.io https://maxcdn.bootstrapcdn.com",
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://maxcdn.bootstrapcdn.com https://fonts.googleapis.com",
            "font-src 'self' data: https://fonts.gstatic.com https://maxcdn.bootstrapcdn.com",
            "img-src 'self' data: https:",
            "frame-src 'self' https://maps.google.com https://www.google.com https://checkout.razorpay.com",
            "connect-src 'self' https://checkout.razorpay.com https://app.appzi.io",
            "base-uri 'self'",
            "form-action 'self' https://checkout.razorpay.com",
            "object-src 'none'",
            "frame-ancestors 'self'",
        ]);
        $response->headers->set('Content-Security-Policy-Report-Only', $csp);

        if ($request->isSecure()) {
            // `preload` is required by hstspreload.org's submission checker,
            // not just max-age/includeSubDomains — see DNS/HSTS instructions.
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        return $response;
    }
}
