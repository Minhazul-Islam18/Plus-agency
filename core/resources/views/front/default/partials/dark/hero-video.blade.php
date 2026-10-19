@php
    // Dedicated dark-theme video hero settings (admin: Content Management ->
    // Home Page Sections -> Hero Section -> Video Version). Falls back to
    // the dark Static hero's own text fields if this page was never filled
    // in, matching dark/hero.blade.php's own fallback-to-light-theme-fields
    // convention one level up.
    $dhvEyebrow = $darkHeroVideo?->eyebrow ?: ($darkHero?->eyebrow ?: __('Welcome'));
    $dhvTitle = $darkHeroVideo?->title ?: ($darkHero?->title ?: $bs->hero_section_title);
    $dhvRotating = $darkHeroVideo?->rotating_titles ?: $darkHero?->rotating_titles;
    $dhvText = $darkHeroVideo?->text ?: ($darkHero?->text ?: $bs->hero_section_text);
    $dhvButtonText = $darkHeroVideo?->button_text ?: $darkHero?->button_text;
    $dhvButtonUrl = $darkHeroVideo?->button_url ?: $darkHero?->button_url;
    $dhvMetaLeft = $darkHeroVideo?->meta_left ?: $darkHero?->meta_left;
    $dhvMetaRight = $darkHeroVideo?->meta_right ?: $darkHero?->meta_right;

    $dhvTitles = collect([$dhvTitle])
        ->merge(preg_split('/\r\n|\r|\n/', (string) $dhvRotating))
        ->map(fn($t) => trim((string) $t))
        ->filter()
        ->values();

    $dhvHasVideo = !empty($darkHeroVideo?->video_file);
    $dhvHasPoster = !empty($darkHeroVideo?->poster_image);
    // No front-end "missing image" placeholder asset exists in this app
    // (every other front-end image is admin-required, same as this one) —
    // a 1x1 transparent pixel is the safe last resort if 'Video' is
    // selected before the Poster Image is actually filled in: the
    // .dark-hero's own radial-gradient background still shows through,
    // not a broken-image icon.
    $dhvPosterUrl = $dhvHasPoster
        ? asset('assets/front/img/hero-video/' . $darkHeroVideo->poster_image)
        : 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==';
    $dhvVideoUrl = $dhvHasVideo ? asset('assets/front/img/hero-video/' . $darkHeroVideo->video_file) : '';
    $dhvOverlayHex = ltrim($darkHeroVideo?->overlay_color ?? '090d16', '#');
    $dhvOverlayOpacity = is_numeric($darkHeroVideo?->overlay_opacity ?? null) ? (float) $darkHeroVideo->overlay_opacity : 0.55;
    $dhvDisableOnMobile = $darkHeroVideo?->disable_on_mobile ?? true;
@endphp
<div class="dark-hero dark-hero--video" data-particle-network data-hero-video data-disable-mobile="{{ $dhvDisableOnMobile ? '1' : '0' }}">
    <div class="dark-hero-video-bg">
        {{-- Poster paints immediately — this is the only thing most mobile
             visitors (disable_on_mobile default) or reduced-motion users
             ever see. Real LCP element, so it gets eager/high priority,
             unlike the video itself. --}}
        <img class="dark-hero-video-poster" src="{{ $dhvPosterUrl }}" alt="" fetchpriority="high">

        @if ($dhvHasVideo)
            {{-- No <source> at all yet, preload="none" — guarantees zero
                 video bytes are requested until the activation script
                 below explicitly decides to, strictly after window 'load'
                 and only on a connection/device that can afford it. --}}
            <video class="dark-hero-video-el" muted loop playsinline preload="none"
                poster="{{ $dhvPosterUrl }}" data-video-src="{{ $dhvVideoUrl }}"></video>
        @endif

        <div class="dark-hero-video-overlay"
            style="background-color: #{{ $dhvOverlayHex }}; opacity: {{ $dhvOverlayOpacity }};"></div>
    </div>

    <div class="dark-hero-inner">
        <div class="dark-hero-panel-wrap" data-hero-blob-zone>
            <span class="dark-hero-blob" data-hero-blob></span>

            <div class="dark-hero-panel">
                <div class="dark-hero-grid">
                    <div class="dark-hero-col-left">
                        <span class="dark-hero-eyebrow">{{ convertUtf8($dhvEyebrow) }}</span>

                        <h1 class="dark-hero-flip" data-hero-flip>
                            @foreach ($dhvTitles as $key => $title)
                                <span class="dark-hero-flip-line {{ $key == 0 ? 'is-active' : '' }}">{{ convertUtf8($title) }}</span>
                            @endforeach
                        </h1>
                    </div>

                    <div class="dark-hero-col-right">
                        <p class="dark-hero-text">{{ convertUtf8($dhvText) }}</p>

                        @if (!empty($dhvButtonUrl) && !empty($dhvButtonText))
                            <a href="{{ $dhvButtonUrl }}" class="dark-hero-cta" target="_blank">
                                <span>{{ convertUtf8($dhvButtonText) }}</span>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        @endif

                        @if (!empty($dhvMetaLeft) || !empty($dhvMetaRight))
                            <div class="dark-hero-meta">
                                @if (!empty($dhvMetaLeft))<span>{{ convertUtf8($dhvMetaLeft) }}</span>@endif
                                @if (!empty($dhvMetaRight))<span>{{ convertUtf8($dhvMetaRight) }}</span>@endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if ($bs->partner_section == 1 && !empty($partners) && count($partners) > 0)
            <div class="dark-hero-marquee">
                <div class="dark-hero-marquee-label">{{ !empty($be->partner_marquee_label) ? convertUtf8($be->partner_marquee_label) : __('Trusted across sectors') }}</div>
                <div class="dark-hero-marquee-track">
                    @for ($i = 0; $i < 2; $i++)
                        @foreach ($partners as $partner)
                            @if (!empty($partner->url))
                                <a href="{{ $partner->url }}" target="_blank" rel="noopener" class="dark-hero-marquee-item">
                                    <img src="{{ asset('assets/front/img/partners/' . $partner->image) }}" alt="">
                                </a>
                            @else
                                <span class="dark-hero-marquee-item">
                                    <img src="{{ asset('assets/front/img/partners/' . $partner->image) }}" alt="">
                                </span>
                            @endif
                        @endforeach
                    @endfor
                </div>
            </div>
        @endif
    </div>
</div>

<script>
    (function () {
        document.addEventListener('DOMContentLoaded', function () {
            var root = document.querySelector('[data-hero-video]');
            if (!root) return;
            var video = root.querySelector('.dark-hero-video-el');
            if (!video) return; // no video file set — poster only, nothing to activate

            function activate() {
                if (video.dataset.activated) return;
                video.dataset.activated = '1';

                var src = video.getAttribute('data-video-src');
                if (!src) return;

                var source = document.createElement('source');
                source.src = src;
                source.type = 'video/mp4';
                video.setAttribute('preload', 'auto');
                video.appendChild(source);
                video.load();
                // Autoplay can be rejected by the browser (no user gesture,
                // battery saver, etc.) — the poster just keeps showing, no
                // error surfaced, no retry loop.
                video.play().catch(function () {});
            }

            function shouldSkip() {
                if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    return true;
                }
                if (navigator.connection && navigator.connection.saveData) {
                    return true;
                }
                var disableMobile = root.getAttribute('data-disable-mobile') === '1';
                if (disableMobile && window.matchMedia && window.matchMedia('(max-width: 860px)').matches) {
                    return true;
                }
                return false;
            }

            if (shouldSkip()) return;

            // Strictly after everything else has finished loading, and only
            // during an idle moment — the video request can never compete
            // with or delay any other resource on the page.
            function deferredActivate() {
                if (window.requestIdleCallback) {
                    requestIdleCallback(activate, { timeout: 3000 });
                } else {
                    setTimeout(activate, 500);
                }
            }

            if (document.readyState === 'complete') {
                deferredActivate();
            } else {
                window.addEventListener('load', deferredActivate);
            }
        });
    })();
</script>
