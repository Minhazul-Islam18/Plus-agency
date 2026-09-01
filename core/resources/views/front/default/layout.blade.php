<!DOCTYPE html>
<html lang="{{ $currentLang->code ?? 'en' }}">

<head>
    <!--Start of Google Analytics script-->
    @if ($bs->is_analytics == 1)
        {!! $bs->google_analytics_script !!}
    @endif
    <!--End of Google Analytics script-->

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description" content="@yield('meta-description')">
    <meta name="keywords" content="@yield('meta-keywords')">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $bs->website_title }} @yield('pagename')</title>

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

    @if ($bs->is_tawkto == 1 || $bex?->is_whatsapp == 1)
        <style>
            .back-to-top.show {
                right: auto;
                left: 20px;
            }
        </style>
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

    @if ($bs->is_appzi == 1)
        <!-- Start of Appzi Feedback Script -->
        <script async src="https://app.appzi.io/bootstrap/bundle.js?token={{ $bs->appzi_token }}"></script>
        <!-- End of Appzi Feedback Script -->
    @endif

    <!-- Start of Facebook Pixel Code -->
    @if ($be->is_facebook_pexel == 1)
        {!! $be->facebook_pexel_script !!}
    @endif
    <!-- End of Facebook Pixel Code -->

    <!--Start of Appzi script-->
    @if ($bs->is_appzi == 1)
        {!! $bs->appzi_script !!}
    @endif
    <!--End of Appzi script-->
</head>



<body @if ($rtl == 1) dir="rtl" @endif>

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
            <div class="dark-breadcrumb-hero">
                <span class="dark-bc-watermark">@yield('breadcrumb-link')</span>
                <div class="dark-bc-inner">
                    <div class="dark-bc-text">
                        <span class="dark-bc-eyebrow">@yield('breadcrumb-title')</span>
                        <h1>@yield('breadcrumb-subtitle')</h1>
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


    {{-- WhatsApp Chat Button --}}
    <div id="WAButton"></div>

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

    {{-- whatsapp init code --}}
    @if ($bex?->is_whatsapp == 1)
        <script type="text/javascript">
            var whatsapp_popup = {{ $bex?->whatsapp_popup }};
            var whatsappImg = "{{ asset('assets/front/img/whatsapp.svg') }}";
            $(function() {
                $('#WAButton').floatingWhatsApp({
                    phone: "{{ $bex?->whatsapp_number }}", //WhatsApp Business phone number
                    headerTitle: "{{ $bex?->whatsapp_header_title }}", //Popup Title
                    popupMessage: `{!! nl2br($bex?->whatsapp_popup_message) !!}`, //Popup Message
                    showPopup: whatsapp_popup == 1 ? true : false, //Enables popup display
                    buttonImage: '<img src="' + whatsappImg + '" />', //Button Image
                    position: "right" //Position: left | right

                });
            });
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

    <!--Start of Tawk.to script-->
    @if ($bs->is_tawkto == 1)
        {!! $bs->tawk_to_script !!}
    @endif
    <!--End of Tawk.to script-->

    <!--Start of AddThis script-->
    @if ($bs->is_addthis == 1)
        {!! $bs->addthis_script !!}
    @endif
    <!--End of AddThis script-->
    @stack('dark-offcanvas')
</body>

</html>
