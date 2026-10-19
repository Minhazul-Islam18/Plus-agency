<!DOCTYPE html>
<html lang="{{ $currentLang->code ?? 'en' }}">

<head>
    <!--Start of Google Analytics script-->
    @if ($bs->is_analytics == 1)
        {!! $bs->google_analytics_script !!}
    @endif
    <!--End of Google Analytics script-->

    {{-- GA4 / GTM / Meta Pixel — ID-driven fields, consent-gated.
         Gating has to happen in each visitor's own browser, not server-side
         here: most front-end pages are Cloudflare/LiteSpeed-cached, so a
         PHP-side `if (trackingConsented())` baked into this shared HTML
         would apply one visitor's consent state to everyone hitting that
         cache entry. __activateTracking() re-checks the real cookie in the
         browser that actually requested the page, every time — correct
         even when the HTML itself is shared. Called once on DOMContentLoaded,
         and again immediately from the consent dialog's Allow button (see
         resources/views/vendor/cookie-consent/index.blade.php) so tracking
         starts the same page load a visitor agrees, not just on their next
         navigation. --}}
    <script>
        window.__hasTrackingConsent = function () {
            return document.cookie.split('; ').indexOf('{{ config('cookie-consent.cookie_name', 'laravel_cookie_consent') }}=1') !== -1;
        };

        window.__activateTracking = function () {
            if (window.__trackingActivated || !window.__hasTrackingConsent()) {
                return;
            }
            window.__trackingActivated = true;

            @if (!empty($bs->gtm_container_id))
                (function (w, d, s, l, i) {
                    w[l] = w[l] || [];
                    w[l].push({ 'gtm.start': new Date().getTime(), event: 'gtm.js' });
                    var f = d.getElementsByTagName(s)[0], j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : '';
                    j.async = true;
                    j.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
                    f.parentNode.insertBefore(j, f);
                })(window, document, 'script', 'dataLayer', '{{ $bs->gtm_container_id }}');
            @endif

            @if (!empty($bs->google_analytics_measurement_id))
                var __gaScript = document.createElement('script');
                __gaScript.async = true;
                __gaScript.src = 'https://www.googletagmanager.com/gtag/js?id={{ $bs->google_analytics_measurement_id }}';
                document.head.appendChild(__gaScript);
                window.dataLayer = window.dataLayer || [];
                function gtag() { dataLayer.push(arguments); }
                gtag('js', new Date());
                gtag('config', '{{ $bs->google_analytics_measurement_id }}');
            @endif

            @if (!empty($be->facebook_pixel_id))
                !function (f, b, e, v, n, t, s) {
                    if (f.fbq) return; n = f.fbq = function () {
                        n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments)
                    };
                    if (!f._fbq) f._fbq = n; n.push = n; n.loaded = !0; n.version = '2.0';
                    n.queue = []; t = b.createElement(e); t.async = !0;
                    t.src = v; s = b.getElementsByTagName(e)[0];
                    s.parentNode.insertBefore(t, s)
                }(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');

                fbq('init', '{{ $be->facebook_pixel_id }}');

                // Shared event_id (Meta's own dedup key) generated here in
                // the browser rather than server-side — this script block
                // renders inside the same cached HTML as everyone else, so
                // a server-generated id would be reused for every visitor
                // who hits that cache entry. The matching CAPI PageView
                // dispatch is a real-time fetch, not baked into any cache.
                var __fbEventId = (window.crypto && crypto.randomUUID) ? crypto.randomUUID() :
                    'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
                        var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
                        return v.toString(16);
                    });
                fbq('track', 'PageView', {}, { eventID: __fbEventId });

                fetch('{{ route('front.track.pageview') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    keepalive: true,
                    body: JSON.stringify({ event_id: __fbEventId, url: window.location.href }),
                }).catch(function () {});
            @endif
        };

        document.addEventListener('DOMContentLoaded', window.__activateTracking);
    </script>

    @if (session()->has('capi_event') && !empty($be->facebook_pixel_id))
        {{-- Bridges a server-side conversion (Contact/Purchase) back to the
             client Pixel with the same event_id the CAPI dispatch already
             used — see FrontendController@sendmail / TenderController's
             purchaseComplete(). Session flash, so this never rides along
             in cached HTML; it only exists on the one real request. --}}
        <script>
            // Registered after __activateTracking's own DOMContentLoaded
            // listener (earlier in this file), so multiple listeners on the
            // same event fire in registration order — fbq is already
            // defined by the time this runs.
            document.addEventListener('DOMContentLoaded', function () {
                if (window.__hasTrackingConsent && window.__hasTrackingConsent() && window.fbq) {
                    fbq('track', '{{ session('capi_event')['name'] }}', {}, { eventID: '{{ session('capi_event')['id'] }}' });
                }
            });
        </script>
    @endif

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @php
        // Global fallback chain: a page's own @section wins when set; when
        // it isn't, fall through to the site-wide default ($bs->meta_*,
        // "SEO Informations" page) rather than rendering an empty tag.
        // Computed once here so every tag below (and the OG/Twitter block
        // further down) reads from the same three values.
        $__pageMetaTitle = $__env->yieldContent('pagename');
        $__metaDescription = $__env->yieldContent('meta-description') ?: ($bs->meta_description ?? '');
        $__metaKeywords = $__env->yieldContent('meta-keywords') ?: ($bs->meta_keywords ?? '');
        $__titleSuffix = $__pageMetaTitle ?: ($bs->meta_title ?? '');
    @endphp
    <meta name="description" content="{{ $__metaDescription }}">
    <meta name="keywords" content="{{ $__metaKeywords }}">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $bs->website_title }} {{ $__titleSuffix }}</title>

    @php
        // Dynamic Page-model catch-all (front.dynamicPage) has independent,
        // per-language slugs with no cross-language link in the data — a
        // /{locale} swap on that route's slug would 404 or land on the
        // wrong page, so it's excluded from both hreflang and the language
        // switcher's in-place swap below (falls back to the target
        // homepage instead). Every other named route is permalink-driven
        // and shares one slug across languages, safe to swap directly.
        $__routeName = \Illuminate\Support\Facades\Route::currentRouteName();
        $__routeParams = request()->route() ? request()->route()->parameters() : [];
        unset($__routeParams['locale']);
        $__canSwapLocale = $__routeName && $__routeName !== 'front.dynamicPage' && \Illuminate\Support\Facades\Route::has($__routeName);
        $langSwitchQuery = '?' . http_build_query(array_merge($__routeParams, ['_route' => $__routeName]));
    @endphp
    @if ($__canSwapLocale && !empty($langs) && count($langs) > 1)
        @foreach ($langs as $hrefLang)
            <link rel="alternate" hreflang="{{ $hrefLang->code }}" href="{{ route($__routeName, array_merge($__routeParams, ['locale' => $hrefLang->code])) }}">
        @endforeach
        <link rel="alternate" hreflang="x-default" href="{{ route($__routeName, array_merge($__routeParams, ['locale' => optional($langs->firstWhere('is_default', 1))->code ?? ($currentLang->code ?? 'en')])) }}">
    @else
        <link rel="alternate" hreflang="{{ $currentLang->code ?? 'en' }}" href="{{ url()->current() }}">
    @endif

    {{-- Canonical always self-references the current URL via the same
         route-name/params the hreflang loop above already computed, so the
         two can never disagree about what this page's "real" URL is. --}}
    <link rel="canonical" href="{{ url()->current() }}">

    @php
        $__ogTitle = trim(($bs->website_title ?? '') . ' ' . $__titleSuffix);
        $__ogDescription = $__metaDescription ?: ($bs->website_title ?? '');
        $__ogImagePath = $__env->yieldContent('og-image') ?: ($bs->default_social_image ?? '');
    @endphp
    <meta property="og:site_name" content="{{ $bs->website_title }}">
    <meta property="og:locale" content="{{ $currentLang->code ?? 'en' }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $__ogTitle }}">
    <meta property="og:description" content="{{ $__ogDescription }}">
    @if (!empty($__ogImagePath))
        <meta property="og:image" content="{{ asset('assets/front/img/' . $__ogImagePath) }}">
    @endif
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $__ogTitle }}">
    <meta name="twitter:description" content="{{ $__ogDescription }}">
    @if (!empty($__ogImagePath))
        <meta name="twitter:image" content="{{ asset('assets/front/img/' . $__ogImagePath) }}">
    @endif

    <script type="application/ld+json">
        {!! json_encode([
            '@@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $bs->website_title,
            'url' => url('/'),
            'logo' => !empty($bs->favicon) ? asset('assets/front/img/' . $bs->favicon) : null,
            'sameAs' => $socials->pluck('url')->filter()->values(),
        ], JSON_UNESCAPED_SLASHES) !!}
    </script>

    @php
        $__breadcrumbTitle = $__env->yieldContent('breadcrumb-title');
        $__breadcrumbLink = $__env->yieldContent('breadcrumb-link');
    @endphp
    @if (!empty($__breadcrumbLink))
        <script type="application/ld+json">
            {!! json_encode([
                '@@context' => 'https://schema.org',
                '@@type' => 'BreadcrumbList',
                'itemListElement' => array_values(array_filter([
                    ['@@type' => 'ListItem', 'position' => 1, 'name' => __('Home'), 'item' => url('/')],
                    !empty($__breadcrumbTitle) ? ['@@type' => 'ListItem', 'position' => 2, 'name' => strip_tags($__breadcrumbTitle), 'item' => url()->current()] : null,
                    ['@@type' => 'ListItem', 'position' => !empty($__breadcrumbTitle) ? 3 : 2, 'name' => strip_tags($__breadcrumbLink), 'item' => url()->current()],
                ])),
            ], JSON_UNESCAPED_SLASHES) !!}
        </script>
    @endif

    @if (!empty($bs->google_site_verification))
        <meta name="google-site-verification" content="{{ $bs->google_site_verification }}">
    @endif
    @if (!empty($bs->bing_site_verification))
        <meta name="msvalidate.01" content="{{ $bs->bing_site_verification }}">
    @endif

    <!-- favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/front/img/' . $bs->favicon) }}" type="image/x-icon">
    {{-- Icon webfonts are only discovered once plugin.min.css finishes parsing,
         so they fetch late and swap in after icons already reserved space with
         a fallback glyph — that swap is what's causing the CLS layout shift.
         Preloading fetches them in parallel with the CSS instead. --}}
    <link rel="preload" as="font" type="font/woff2" href="{{ asset('assets/front/fonts/fa-solid-900.woff2') }}" crossorigin>
    <link rel="preload" as="font" type="font/woff2" href="{{ asset('assets/front/fonts/fa-regular-400.woff2') }}" crossorigin>
    <link rel="preload" as="font" type="font/woff2" href="{{ asset('assets/front/fonts/fa-brands-400.woff2') }}" crossorigin>
    <link rel="preload" as="font" type="font/woff2" href="{{ asset('assets/front/fonts/Flaticon.woff2') }}" crossorigin>
    <!-- bootstrap css -->
    <link rel="stylesheet" href="{{ asset('assets/front/css/bootstrap.min.css') }}">
    <!-- plugin css -->
    <link rel="stylesheet" href="{{ asset_v('assets/front/css/plugin.min.css') }}">

    <!-- main css -->
    <link rel="stylesheet" href="{{ asset_v('assets/front/css/style.css') }}">

    <!-- common css -->
    <link rel="stylesheet" href="{{ asset_v('assets/front/css/common-style.css') }}">
    @yield('styles')

    @if ($bex?->is_whatsapp == 1)
        <style>
            .back-to-top.show {
                right: auto;
                left: 20px;
            }
        </style>
        <link rel="stylesheet" href="{{ asset_v('assets/front/css/whatsapp-widget.css') }}">
    @endif
    @if (count($langs) == 0)
        <style media="screen">
            .support-bar-area ul.social-links li:last-child {
                margin-right: 0px;
            }

            .support-bar-area ul.social-links::after {
                display: none;
            }
        </style>
    @endif

    <!-- responsive css -->
    <link rel="stylesheet" href="{{ asset_v('assets/front/css/responsive.css') }}">
    @php
        $commonBaseColorHref = url('/') . '/assets/front/css/common-base-color.php?color=' . $bs->base_color;
        $baseColorHref = url('/') . '/assets/front/css/base-color.php?color=' . $bs->base_color . ($be->theme_version != 'dark' ? '&color1=' . $bs->secondary_base_color : '');
    @endphp
    {{-- Brand-color overlays only tweak accent colors on top of the base theme —
         safe to load non-render-blocking (preload + swap) since a brief moment
         on default theme colors, before load, causes no layout shift. --}}
    <!-- common base color change -->
    <link rel="preload" as="style" href="{{ $commonBaseColorHref }}" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="{{ $commonBaseColorHref }}"></noscript>
    <!-- base color change -->
    <link rel="preload" as="style" href="{{ $baseColorHref }}" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="{{ $baseColorHref }}"></noscript>

    @if ($be->theme_version == 'dark')
        <!-- dark version css -->
        <link rel="stylesheet" href="{{ asset_v('assets/front/css/dark.css') }}">
        <!-- dark version base color change -->
        <link href="{{ url('/') }}/assets/front/css/dark-base-color.php?color={{ $bs->base_color }}"
            rel="stylesheet">
        <!-- dark glass theme: centralized CSS vars driven by admin base/secondary color -->
        <link href="{{ url('/') }}/assets/front/css/dark-glass-vars.php?color={{ $bs->base_color }}&color2={{ $bs->secondary_base_color }}"
            rel="stylesheet">
        <link rel="stylesheet" href="{{ asset_v('assets/front/css/dark-glass.css') }}">
    @endif

    @if ($rtl == 1)
        <!-- RTL css -->
        <link rel="stylesheet" href="{{ asset_v('assets/front/css/rtl.css') }}">
        <link rel="stylesheet" href="{{ asset_v('assets/front/css/pb-rtl.css') }}">
    @endif
    <!-- jquery js -->
    <script src="{{ asset('assets/front/js/jquery-3.3.1.min.js') }}"></script>

    <!-- Start of Facebook Pixel Code -->
    @if ($be->is_facebook_pexel == 1)
        {!! $be->facebook_pexel_script !!}
    @endif
    <!-- End of Facebook Pixel Code -->
</head>



@php
    // The video hero's own mute/pause controls float bottom-right of the
    // (near-full-viewport-height) hero, the same corner the fixed
    // WhatsApp widget always occupies — on this one page they'd otherwise
    // sit directly on top of each other. $darkHeroVideo only exists on
    // the homepage route (set in FrontendController::index()).
    $hasVideoHeroControls = ($be->theme_version ?? null) == 'dark'
        && ($bs->home_version ?? null) == 'video'
        && !empty(($darkHeroVideo ?? null)?->video_file);
@endphp
<body @if ($rtl == 1) dir="rtl" @endif @if ($hasVideoHeroControls) class="has-video-hero-controls" @endif>
    @if (!empty($bs->gtm_container_id))
        {{-- GTM's noscript fallback can't be consent-gated — there's no
             cookie read possible without JS in the first place, and this
             is the one case Google's own install instructions don't gate
             either. Negligible in practice: a visitor with JS disabled
             doesn't run the JS-gated block above either. --}}
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $bs->gtm_container_id }}"
            height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    @endif

    <!--   header area start   -->
    @if ($be->theme_version == 'dark')
    <div class="header-area header-absolute dark-header-shell @yield('no-breadcrumb')">
        <div class="support-bar-area dark-utility-strip">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 support-contact-info d-flex align-items-center">
                        @if (!empty($bs->support_email))
                            <span class="address"><i class="far fa-envelope"></i> <a href="mailto:{{ $bs->support_email }}">{{ $bs->support_email }}</a></span>
                        @endif
                        @if (!empty($bs->support_phone))
                            <span class="phone"><i class="flaticon-chat"></i> <a href="tel:{{ preg_replace('/\s+/', '', $bs->support_phone) }}">{{ $bs->support_phone }}</a></span>
                        @endif
                    </div>
                    <div class="col-lg-6 {{ $rtl == 1 ? 'text-left' : 'text-right' }}">
                        <ul class="social-links">
                            @foreach ($socials as $key => $social)
                                <li><a target="_blank" href="{{ $social->url }}"><i
                                            class="{{ $social->icon }}"></i></a></li>
                            @endforeach
                        </ul>

                        @if (!empty($currentLang) && count($langs) > 1)
                            <div class="language">
                                <a class="language-btn" href="#"><i class="flaticon-worldwide"></i>
                                    {{ convertUtf8($currentLang->name) }}</a>
                                <ul class="language-dropdown">
                                    @foreach ($langs as $key => $lang)
                                        <li><a
                                                href='{{ route('changeLanguage', $lang->code) . $langSwitchQuery }}'>{{ convertUtf8($lang->name) }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="dark-nav-bar">
                @includeIf('front.default.partials.navbar')
            </div>
        </div>
    </div>
    @else
    <div class="header-area header-absolute @yield('no-breadcrumb')">
        <div class="container">
            <div class="support-bar-area">
                <div class="row">
                    <div class="col-lg-6 support-contact-info d-flex align-items-center">
                        <span class="address"><i class="far fa-envelope"></i> {{ $bs->support_email }}</span>
                        <span class="phone"><i class="flaticon-chat"></i> {{ $bs->support_phone }}</span>
                    </div>
                    <div class="col-lg-6 {{ $rtl == 1 ? 'text-left' : 'text-right' }}">
                        <ul class="social-links">
                            @foreach ($socials as $key => $social)
                                <li><a target="_blank" href="{{ $social->url }}"><i
                                            class="{{ $social->icon }}"></i></a></li>
                            @endforeach
                        </ul>

                        @if (!empty($currentLang) && count($langs) > 1)
                            <div class="language">
                                <a class="language-btn" href="#"><i class="flaticon-worldwide"></i>
                                    {{ convertUtf8($currentLang->name) }}</a>
                                <ul class="language-dropdown">
                                    @foreach ($langs as $key => $lang)
                                        <li><a
                                                href='{{ route('changeLanguage', $lang->code) . $langSwitchQuery }}'>{{ convertUtf8($lang->name) }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                    </div>
                </div>
            </div>


            @includeIf('front.default.partials.navbar')

        </div>
    </div>
    @endif
    <!--   header area end   -->


    @if (!request()->routeIs('front.index') && !request()->routeIs('front.packageorder.confirmation') && !request()->routeIs('front.unsubscribe.token') && !($hideBreadcrumb ?? false))
        <!--   breadcrumb area start   -->
        @if ($be->theme_version == 'dark')
            <div class="dark-breadcrumb-hero @yield('breadcrumb-hero-class')">
                <span class="dark-bc-watermark">@yield('breadcrumb-link')</span>
                @hasSection('breadcrumb-art')
                    <div class="dark-bc-art">@yield('breadcrumb-art')</div>
                @endif
                <div class="dark-bc-inner">
                    <div class="dark-bc-text">
                        <span class="dark-bc-eyebrow">@yield('breadcrumb-title')</span>
                        <h1>@yield('breadcrumb-subtitle')</h1>
                        @hasSection('breadcrumb-intro')
                            <p class="dark-bc-intro">@yield('breadcrumb-intro')</p>
                        @endif
                        <ul class="dark-bc-trail">
                            <li><a href="{{ route('front.index') }}">{{ __('Home') }}</a></li>
                            <li class="current">@yield('breadcrumb-link')</li>
                        </ul>
                    </div>
                    @hasSection('breadcrumb-ledger')
                        <div class="dark-bc-ledger">
                            @yield('breadcrumb-ledger')
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="breadcrumb-area cases lazy" data-bg="@yield('breadcrumb-bg', asset('assets/front/img/' . $bs->breadcrumb))"
                style="background-size:cover; background-position: center center;">
                <div class="container">
                    <div class="breadcrumb-txt">
                        <div class="row">
                            <div class="col-xl-7 col-lg-8 col-sm-10">
                                <span>@yield('breadcrumb-title')</span>
                                <h1>@yield('breadcrumb-subtitle')</h1>
                                <ul class="breadcumb">
                                    <li><a href="{{ route('front.index') }}">{{ __('Home') }}</a></li>
                                    <li>@yield('breadcrumb-link')</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="breadcrumb-area-overlay"
                    style="background-color: #@yield('breadcrumb-overlay-color', $be->breadcrumb_overlay_color);opacity: @yield('breadcrumb-overlay-opacity', $be->breadcrumb_overlay_opacity);">
                </div>
            </div>
        @endif
        <!--   breadcrumb area end    -->
    @endif


    @yield('content')


    <!--    footer section start   -->
    <footer class="footer-section @if ($be->theme_version == 'dark') dark-footer-shell @endif">
        <div class="container">
            @if (!($bex?->home_page_pagebuilder == 0 && $bs->top_footer_section == 0))
                @if ($be->theme_version == 'dark')
                    @includeif('front.default.partials.dark.footer')
                @else
                <div class="top-footer-section">
                    <div class="row">
                        <div class="col-lg-4 col-md-12">
                            <div class="footer-logo-wrapper">
                                <a href="{{ route('front.index') }}">
                                    <img class="lazy" data-src="{{ asset('assets/front/img/' . $bs->footer_logo) }}"
                                        alt="">
                                </a>
                            </div>
                            <p class="footer-txt">
                                @if (mb_strlen($bs->footer_text, 'UTF-8') > 194)
                                    <span class="footer-text-short">{{ mb_substr($bs->footer_text, 0, 194, 'UTF-8') }}…</span><span
                                        class="footer-text-full" style="display:none;">{{ $bs->footer_text }}</span>
                                    <span class="footer-text-toggle" style="white-space:nowrap; font-size:11px; text-decoration:underline; cursor:pointer; color:#0d6efd;">{{ __('Show more') }}</span>
                                @else
                                    {{ $bs->footer_text }}
                                @endif
                            </p>
                        </div>
                        <div class="col-lg-2 col-md-3">
                            <h4>{{ __('Useful Links') }}</h4>
                            <ul class="footer-links">
                                @foreach ($ulinks as $key => $ulink)
                                    @php
                                        $ulinkName = convertUtf8($ulink->name);
                                        $ulinkShort = mb_strlen($ulinkName, 'UTF-8') > 36
                                            ? mb_substr($ulinkName, 0, 36, 'UTF-8') . '…'
                                            : null;
                                    @endphp
                                    @if ($ulinkShort)
                                        <li>
                                            <a href="{{ $ulink->url }}" style="display:inline;">
                                                <span class="footer-link-short">{{ $ulinkShort }}</span><span class="footer-link-full" style="display:none;">{{ $ulinkName }}</span>
                                            </a>
                                            <span class="footer-link-toggle" style="white-space:nowrap; font-size:11px; text-decoration:underline; cursor:pointer; color:#0d6efd;">{{ __('Show more') }}</span>
                                        </li>
                                    @else
                                        <li>
                                            <a href="{{ $ulink->url }}">{{ $ulinkName }}</a>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                        <div class="col-lg-3 col-md-4">
                            <h4>{{ __('Newsletter') }}</h4>
                            {{-- No @csrf: /subscribe is CSRF-exempt (see
                                 VerifyCsrfToken::$except) — this form appears
                                 on every page via the shared layout, and
                                 rendering @csrf here would need an active
                                 session, which cacheable pages deliberately
                                 skip. Rate-limited instead (throttle:5,10). --}}
                            <form class="footer-newsletter" id="footerSubscribeForm"
                                action="{{ route('front.subscribe') }}" method="post">
                                <p>{{ convertUtf8($bs->newsletter_text) }}</p>
                                <input type="email" name="email" value=""
                                    placeholder="{{ __('Enter Email Address') }}" />
                                <p id="erremail" class="text-danger mb-0 err-email"></p>
                                <button type="submit">{{ __('Subscribe') }}</button>
                            </form>
                        </div>
                        <div class="col-lg-3 col-md-5">
                            <h4>{{ __('Contact Us') }}</h4>
                            <div class="footer-contact-info">
                                <ul>
                                    <li><i class="fa fa-home"></i>
                                        @php
                                            $addresses = explode(PHP_EOL, $bex?->contact_addresses);
                                        @endphp
                                        <span>
                                            @foreach ($addresses as $address)
                                                {{ $address }}
                                                @if (!$loop->last)
                                                    |
                                                @endif
                                            @endforeach
                                        </span>
                                    </li>

                                    <li><i class="fa fa-phone"></i>
                                        @php
                                            $phones = explode(',', $bex?->contact_numbers);
                                        @endphp
                                        <span>
                                            @foreach ($phones as $phone)
                                                {{ $phone }}
                                                @if (!$loop->last)
                                                    ,
                                                @endif
                                            @endforeach
                                        </span>
                                    </li>
                                    <li><i class="far fa-envelope"></i>
                                        @php
                                            $mails = explode(',', $bex?->contact_mails);
                                        @endphp
                                        <span>
                                            @foreach ($mails as $mail)
                                                <a href="mailto:{{ trim($mail) }}">{{ $mail }}</a>
                                                @if (!$loop->last)
                                                    ,
                                                @endif
                                            @endforeach
                                        </span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            @endif

            @if (!($bex?->home_page_pagebuilder == 0 && $bs->copyright_section == 0))
                @if ($be->theme_version == 'dark')
                    @php
                        // page_type (language-agnostic marker), not slug — a
                        // page's slug is per-language translated text, so
                        // matching hardcoded French slugs only ever found the
                        // French rows and silently produced zero results
                        // (no links, no error) for every other language.
                        // Fixed display order (privacy, terms, legal notice, cookie
                        // policy) regardless of DB/insertion order — sortBy keyed on
                        // this array's own position, missing pages just drop out.
                        $legalPageOrder = ['privacy', 'terms', 'legal_notice', 'cookie_policy'];
                        $legalPages = \App\Page::where('language_id', $currentLang->id ?? null)
                            ->whereIn('page_type', $legalPageOrder)
                            ->where('status', 1)
                            ->get(['id', 'title', 'slug', 'page_type'])
                            ->sortBy(fn($page) => array_search($page->page_type, $legalPageOrder))
                            ->values();
                    @endphp
                    <div class="copyright-section dark-copyright-section">
                        <span class="dark-copyright-text">{!! replaceBaseUrl(convertUtf8($bs->copyright_text)) !!}</span>
                        @if ($legalPages->count())
                            <div class="dark-copyright-links">
                                @foreach ($legalPages as $key => $page)
                                    <a href="{{ route('front.dynamicPage', [$page->slug]) }}">{{ !empty($page->title) ? convertUtf8($page->title) : str_replace('-', ' ', $page->slug) }}</a>
                                    @if (!$loop->last)<span class="dark-copyright-dot"></span>@endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                @else
                <div class="copyright-section">
                    <div class="row">
                        <div class="col-sm-12 text-center">
                            {!! replaceBaseUrl(convertUtf8($bs->copyright_text)) !!}
                        </div>
                    </div>
                </div>
                @endif
            @endif
        </div>
    </footer>
    <!--    footer section end   -->


    {{-- WhatsApp Chat Button — custom widget, not the floatingWhatsApp
         plugin (generic look, no theming). Same admin fields underneath:
         whatsapp_number, whatsapp_header_title, whatsapp_popup_message,
         whatsapp_popup (show-popup toggle). Approved design:
         https://claude.ai/artifact/K6ehDRg6TeHyiSbS4naHZT --}}
    @if ($bex?->is_whatsapp == 1)
        @php
            $waIcon = '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 00-8.6 15L2 22l5.2-1.4A10 10 0 1012 2zm5.6 14.3c-.3.8-1.5 1.5-2.4 1.6-.6.1-1.4.2-4.1-.9-3.5-1.4-5.7-5-5.9-5.3-.2-.3-1.4-1.9-1.4-3.6s.9-2.6 1.2-2.9c.3-.3.7-.5 1-.5h.7c.2 0 .5 0 .7.6l1 2.4c.1.2.2.4 0 .6l-.4.6c-.2.2-.4.4-.2.8.3.5 1.1 1.7 2.3 2.8 1.6 1.4 2.9 1.9 3.3 2.1.3.2.5.1.7-.1l1-1.1c.2-.3.5-.2.8-.1l2.1 1c.2.1.4.2.5.3.1.3.1 1-.2 1.7z"/></svg>';
            $waHeaderTitle = convertUtf8($bex->whatsapp_header_title ?: $bs->website_title);
            $waMessage = convertUtf8($bex->whatsapp_popup_message);
            // wa.me needs digits only — same cleanup Member::getWhatsappLinkAttribute() already uses.
            $waPhoneDigits = preg_replace('/\D/', '', (string) $bex->whatsapp_number);
        @endphp
        <div class="wa-widget" id="waWidget" data-state="closed" data-show-popup="{{ $bex->whatsapp_popup == 1 ? '1' : '0' }}" data-phone="{{ $waPhoneDigits }}">
            <div class="wa-teaser-wrap">
                <button class="wa-teaser-close" type="button" aria-label="{{ __('Dismiss') }}" id="waTeaserClose">✕</button>
                <div class="wa-teaser" id="waTeaserBody">
                    <span class="wa-teaser-avatar">{!! $waIcon !!}</span>
                    <div class="wa-teaser-body">
                        <div class="wa-teaser-name"><span class="dot"></span>{{ $waHeaderTitle }}</div>
                        <div class="wa-teaser-msg">{!! nl2br(e($waMessage)) !!}</div>
                    </div>
                </div>
            </div>

            <div class="wa-card" id="waCard">
                <div class="wa-card-header">
                    <button class="wa-card-close" type="button" aria-label="{{ __('Close') }}" id="waCardClose">✕</button>
                    <div class="wa-card-identity">
                        <span class="wa-card-avatar">{!! $waIcon !!}</span>
                        <div>
                            <div class="wa-card-name">{{ $waHeaderTitle }}</div>
                            <div class="wa-card-status"><span class="dot"></span>{{ __('Typically replies within minutes') }}</div>
                        </div>
                    </div>
                </div>
                <div class="wa-card-body">
                    <div class="wa-card-bubble">{!! nl2br(e($waMessage)) !!}</div>
                    <div class="wa-card-time">{{ __('Just now') }}</div>

                    <div class="wa-card-input-wrap">
                        <textarea class="wa-card-input" rows="2" id="waTypedMsg" placeholder="{{ __('Type your message…') }}">{{ $waMessage }}</textarea>
                    </div>

                    <button class="wa-card-cta" type="button" id="waStartChat">
                        {!! $waIcon !!}
                        <span>{{ __('Start Chat') }}</span>
                    </button>
                    <div class="wa-card-foot">{{ __('Opens in WhatsApp with your message pre-filled') }}</div>
                </div>
            </div>

            <button class="wa-fab" type="button" aria-label="{{ __('Open WhatsApp chat') }}" id="waFab">
                <span class="wa-fab-ring"></span>
                {!! $waIcon !!}
            </button>
        </div>
    @endif

    <!--====== PRELOADER PART START ======-->
    @if ($bex?->preloader_status == 1)
        <div id="preloader" class="@if ($be->theme_version == 'dark') dark-preloader @endif">
            <div class="loader revolve">
                @if ($be->theme_version == 'dark')
                    <span class="dark-preloader-ring"></span>
                    <span class="dark-preloader-glow"></span>
                    <span class="dark-preloader-mark">
                        <img src="{{ asset('assets/front/img/' . $bex?->preloader) }}" alt="">
                    </span>
                @else
                    <img src="{{ asset('assets/front/img/' . $bex?->preloader) }}" alt="">
                @endif
            </div>
        </div>
    @endif
    <!--====== PRELOADER PART ENDS ======-->

    <!-- back to top area start -->
    <div class="back-to-top">
        @if ($be->theme_version == 'dark')
            <svg class="dark-btt-ring" viewBox="0 0 46 46">
                <circle class="dark-btt-ring-track" cx="23" cy="23" r="20"></circle>
                <circle class="dark-btt-ring-progress" cx="23" cy="23" r="20"></circle>
            </svg>
        @endif
        <i class="fas fa-chevron-up"></i>
    </div>
    <!-- back to top area end -->


    {{-- Cookie alert dialog start --}}
    @if ($be->cookie_alert_status == 1)
        @include('cookie-consent::index')
    @endif
    {{-- Cookie alert dialog end --}}

    {{-- Popups start --}}
    @includeIf('front.partials.popups')
    {{-- Popups end --}}

    @php
        $mainbs = [];
        $mainbs = json_encode($mainbs);
    @endphp
    <script>
        var mainbs = {!! $mainbs !!};
        var mainurl = "{{ url('/') }}";
        var vap_pub_key = "{{ env('VAPID_PUBLIC_KEY') }}";
        var rtl = {{ $rtl }};
    </script>
    <!-- popper js -->
    <script src="{{ asset('assets/front/js/popper.min.js') }}"></script>
    <!-- bootstrap js -->
    <script src="{{ asset('assets/front/js/bootstrap.min.js') }}"></script>
    <!-- Plugin js -->
    <script src="{{ asset_v('assets/front/js/plugin.min.js') }}"></script>
    <!-- main js -->
    <script src="{{ asset_v('assets/front/js/main.js') }}"></script>
    <!-- team member profile popup (self-guards on .team-clickable) -->
    <script src="{{ asset_v('assets/front/js/team-modal.js') }}"></script>
    <!-- scroll-triggered stagger reveal, both themes (self-guards on .reveal-stagger) -->
    <script src="{{ asset_v('assets/front/js/dark-reveal.js') }}"></script>
    @if ($be->theme_version == 'dark')
        <!-- Per-section Owl Carousel autoplay speed (ms), set in
             admin/home/sections — read by each dark-*-fx.js init instead of
             a hardcoded value. Falls back to 4500 in each file if a key is
             ever missing here. -->
        <script>
            window.darkCarouselSpeeds = {
                tender: {{ (int) ($be->tender_carousel_speed ?? 4500) }},
                blog: {{ (int) ($be->blog_carousel_speed ?? 4500) }},
                portfolio: {{ (int) ($be->portfolio_carousel_speed ?? 4500) }},
                testimonial: {{ (int) ($be->testimonial_carousel_speed ?? 4500) }},
                team: {{ (int) ($be->team_carousel_speed ?? 4500) }},
                partner: {{ (int) ($be->partner_carousel_speed ?? 4500) }},
                approach: {{ (int) ($be->approach_carousel_speed ?? 4500) }}
            };
        </script>
        <!-- dark glass theme: mouse-reactive particle network (self-guards on [data-particle-network]) -->
        <script src="{{ asset_v('assets/front/js/particle-network.js') }}"></script>
        <!-- dark glass theme: hero mouse-tracking glow blob + flip-cycling titles -->
        <script src="{{ asset_v('assets/front/js/dark-hero-fx.js') }}"></script>
        <!-- dark glass theme: hero slider variant — keyboard/drag nav, autoplay progress (self-guards on #darkHeroSlider) -->
        <script src="{{ asset_v('assets/front/js/dark-hero-slider-fx.js') }}"></script>
        <!-- dark glass theme: intro-section video lightbox (self-guards on #darkIntroPlayBtn) -->
        <script src="{{ asset_v('assets/front/js/dark-intro-fx.js') }}"></script>
        <!-- dark glass theme: services card-grid Load More (self-guards on #darkSvcLoadMoreBtn) -->
        <script src="{{ asset_v('assets/front/js/dark-services-fx.js') }}"></script>
        <!-- dark glass theme: approach step-list scroll-active highlight (self-guards on [data-approach-step]) -->
        <script src="{{ asset_v('assets/front/js/dark-approach-fx.js') }}"></script>
        <!-- dark glass theme: statistics counter animation (self-guards on [data-stat]) -->
        <script src="{{ asset_v('assets/front/js/dark-statistics-fx.js') }}"></script>
        <!-- dark glass theme: portfolio carousel custom nav wired to Owl's API (self-guards on [data-case-nav]) -->
        <script src="{{ asset_v('assets/front/js/dark-portfolio-fx.js') }}"></script>
        <!-- dark glass theme: tenders carousel + live deadline countdown (self-guards on [data-deadline] / .dark-tender-carousel) -->
        <script src="{{ asset_v('assets/front/js/dark-tenders-fx.js') }}"></script>
        <!-- dark glass theme: team carousel with capped items (self-guards on .dark-team-carousel) -->
        <script src="{{ asset_v('assets/front/js/dark-team-fx.js') }}"></script>
        <!-- dark glass theme: testimonials carousel with capped items (self-guards on .dark-testi-carousel) -->
        <script src="{{ asset_v('assets/front/js/dark-testimonials-fx.js') }}"></script>
        <!-- dark glass theme: partners carousel, own smoother/slower pacing (self-guards on .dark-partner-carousel) -->
        <script src="{{ asset_v('assets/front/js/dark-partners-fx.js') }}"></script>
        <!-- dark glass theme: blog carousel with capped items (self-guards on .dark-blog-carousel) -->
        <script src="{{ asset_v('assets/front/js/dark-blog-fx.js') }}"></script>
        <!-- dark glass theme: back-to-top scroll-progress ring (self-guards on .dark-btt-ring) -->
        <script src="{{ asset_v('assets/front/js/dark-scrolltop-fx.js') }}"></script>
        <!-- dark glass theme: custom announcement-popup engine, replaces magnific-popup (self-guards on .popup-wrapper.dark-popup) -->
        <script src="{{ asset_v('assets/front/js/dark-popup-fx.js') }}"></script>
        <!-- dark glass theme: custom mobile offcanvas menu, replaces slicknav (self-guards on #darkOcPanel) -->
        <script src="{{ asset_v('assets/front/js/dark-offcanvas-fx.js') }}"></script>
    @endif
    <!-- pagebuilder custom js -->
    <script src="{{ asset_v('assets/front/js/common-main.js') }}" defer></script>

    {{-- Footer "Useful Links" — long link names truncate to ~2 lines with a
         Show more / Show less toggle right after the visible text. Swapping
         short/full <span>s (instead of CSS ellipsis) keeps the toggle inline
         because the <a> is display:inline, so trailing siblings flow onto
         the last wrapped line same as normal text. --}}
    <script>
        $(document).on('click', '.footer-link-toggle', function(e) {
            e.preventDefault();
            var $toggle = $(this);
            var $li = $toggle.closest('li');
            var $short = $li.find('.footer-link-short');
            var $full = $li.find('.footer-link-full');
            var expanded = $full.is(':visible');

            $short.toggle(expanded);
            $full.toggle(!expanded);
            $toggle.text(expanded ? @json(__('Show more')) : @json(__('Show less')));
        });

        {{-- Footer about-text — same short/full swap + Show more/Show less style. --}}
        $(document).on('click', '.footer-text-toggle', function(e) {
            e.preventDefault();
            var $toggle = $(this);
            var $p = $toggle.closest('p');
            var $short = $p.find('.footer-text-short');
            var $full = $p.find('.footer-text-full');
            var expanded = $full.is(':visible');

            $short.toggle(expanded);
            $full.toggle(!expanded);
            $toggle.text(expanded ? @json(__('Show more')) : @json(__('Show less')));
        });

        {{-- Testimonial card comment — same short/full swap + Show more/Show less
             style as the footer toggles, instead of a hard CSS ellipsis clamp. --}}
        $(document).on('click', '.testi-comment-toggle', function(e) {
            e.preventDefault();
            var $toggle = $(this);
            var $p = $toggle.closest('.comment');
            var $short = $p.find('.testi-comment-short');
            var $full = $p.find('.testi-comment-full');
            var expanded = $full.is(':visible');

            $short.toggle(expanded);
            $full.toggle(!expanded);
            $toggle.text(expanded ? @json(__('Show more')) : @json(__('Show less')));
        });
    </script>

    {{-- whatsapp widget behavior — state toggling + the Start Chat link.
         Replaces the old floatingWhatsApp plugin init (see widget markup
         above, near the footer). --}}
    @if ($bex?->is_whatsapp == 1)
        <script type="text/javascript">
            (function () {
                document.addEventListener('DOMContentLoaded', function () {
                    var widget = document.getElementById('waWidget');
                    if (!widget) return;

                    var phone = widget.getAttribute('data-phone');
                    var showPopup = widget.getAttribute('data-show-popup') === '1';
                    var teaserBody = document.getElementById('waTeaserBody');
                    var teaserClose = document.getElementById('waTeaserClose');
                    var card = document.getElementById('waCard');
                    var cardClose = document.getElementById('waCardClose');
                    var fab = document.getElementById('waFab');
                    var typedMsg = document.getElementById('waTypedMsg');
                    var startChat = document.getElementById('waStartChat');

                    function setState(state) {
                        widget.setAttribute('data-state', state);
                    }

                    if (showPopup) {
                        // Same "appear after a short delay, not instantly"
                        // convention as the site's other popups (see
                        // front.partials.popups / data-popup_delay).
                        setTimeout(function () { setState('teaser'); }, 1800);
                    }

                    fab.addEventListener('click', function () {
                        setState(widget.getAttribute('data-state') === 'card' ? 'closed' : 'card');
                    });
                    teaserBody.addEventListener('click', function () {
                        setState('card');
                    });
                    teaserClose.addEventListener('click', function (e) {
                        e.stopPropagation();
                        setState('closed');
                    });
                    cardClose.addEventListener('click', function () {
                        setState('closed');
                    });

                    startChat.addEventListener('click', function () {
                        var text = typedMsg ? typedMsg.value.trim() : '';
                        var url = 'https://wa.me/' + phone + (text ? '?text=' + encodeURIComponent(text) : '');
                        window.open(url, '_blank', 'noopener');
                    });
                });
            })();
        </script>
    @endif
    @yield('scripts')
    @stack('event-js')

    {{-- request()->hasSession() guard: cacheable anonymous pages (homepage,
         listings) skip the session middleware entirely so Cloudflare never
         sees a per-visitor Set-Cookie header — session()->has() would throw
         on those pages without this check. --}}
    @if (request()->hasSession() && session()->has('success'))
        <script>
            toastr["success"]("{{ __(session('success')) }}");
        </script>
    @endif

    @if (request()->hasSession() && session()->has('error'))
        <script>
            toastr["error"]("{{ __(session('error')) }}");
        </script>
    @endif

    <!--Start of subscribe functionality-->
    <script>
        $(document).ready(function() {
            // Delegated (not a direct .on() bind) + FormData built from `this`
            // (not a getElementById(id) re-lookup) — the Blog page's mobile
            // Categories offcanvas clones this form with its id stripped (to
            // avoid a duplicate id), so both the id-based selector match and
            // the getElementById lookup would silently miss/misfire on that
            // clone otherwise.
            $(document).on('submit', '#subscribeForm, #footerSubscribeForm', function(e) {
                e.preventDefault();

                let fd = new FormData(this);
                let $this = $(this);

                $.ajax({
                    url: $(this).attr('action'),
                    type: $(this).attr('method'),
                    data: fd,
                    contentType: false,
                    processData: false,
                    success: function(data) {
                        // console.log(data);
                        if ((data.errors)) {
                            $this.find(".err-email").html(data.errors.email[0]);
                        } else {
                            toastr["success"]("You are subscribed successfully!");
                            $this.trigger('reset');
                            $this.find(".err-email").html('');
                        }
                    }
                });
            });


        });
    </script>
    <!--End of subscribe functionality-->

    @stack('dark-offcanvas')
</body>

</html>
