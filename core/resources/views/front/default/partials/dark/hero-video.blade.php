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

    // Admin-controlled scrim — same one-color/one-opacity convention as the
    // hero slider's overlay (hero-slider.blade.php). Deliberately a
    // BOTTOM-ANCHORED gradient, not a flat full-frame tint: the point of a
    // background-video hero is that the footage stays visible, so only the
    // lower band behind the text needs darkening. Approved design:
    // https://claude.ai/artifact/APRGW9UXsfuHGUPQvaJtsa
    $dhvOverlayHex = ltrim($darkHeroVideo?->overlay_color ?? '090d16', '#');
    $dhvOverlayOpacity = is_numeric($darkHeroVideo?->overlay_opacity ?? null) ? (float) $darkHeroVideo->overlay_opacity : 0.55;
    [$dhvR, $dhvG, $dhvB] = sscanf($dhvOverlayHex, '%02x%02x%02x') ?: [9, 13, 22];
    $dhvRgb = "{$dhvR}, {$dhvG}, {$dhvB}";

    $dhvDisableOnMobile = $darkHeroVideo?->disable_on_mobile ?? true;
@endphp

<style>
    .dark-hero-video-overlay {
        background: linear-gradient(
            to top,
            rgba({{ $dhvRgb }}, {{ 0.88 * $dhvOverlayOpacity }}) 0%,
            rgba({{ $dhvRgb }}, {{ 0.55 * $dhvOverlayOpacity }}) 24%,
            rgba({{ $dhvRgb }}, {{ 0.14 * $dhvOverlayOpacity }}) 48%,
            transparent 64%
        );
    }
</style>

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

        <div class="dark-hero-video-overlay"></div>
    </div>

    <div class="dark-hero-inner">
        {{-- Partner trust-logos marquee — stacked directly above the text
             block, both bottom-packed by normal flow. Kept inside the
             hero frame (not a standalone band below it) per feedback. --}}
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

        {{-- No frosted-glass panel here on purpose — text sits straight on
             the footage, bottom-left, lower-third style. --}}
        <div class="dark-hero-video-content">
            <span class="dark-hero-eyebrow">{{ convertUtf8($dhvEyebrow) }}</span>

            <h1 class="dark-hero-flip" data-hero-flip>
                @foreach ($dhvTitles as $key => $title)
                    <span class="dark-hero-flip-line {{ $key == 0 ? 'is-active' : '' }}">{{ convertUtf8($title) }}</span>
                @endforeach
            </h1>

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

        @if ($dhvHasVideo)
            <div class="dark-hero-video-controls">
                <button type="button" class="dark-hero-video-ctrl" data-video-mute-toggle aria-label="Unmute video">
                    <svg class="icon-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><line x1="23" y1="9" x2="17" y2="15"></line><line x1="17" y1="9" x2="23" y2="15"></line></svg>
                    <svg class="icon-unmuted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><path d="M15.54 8.46a5 5 0 0 1 0 7.07"></path><path d="M19.07 4.93a10 10 0 0 1 0 14.14"></path></svg>
                </button>
                <button type="button" class="dark-hero-video-ctrl" data-video-play-toggle aria-label="Pause video">
                    <svg class="icon-pause" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="4" width="4" height="16"></rect><rect x="14" y="4" width="4" height="16"></rect></svg>
                    <svg class="icon-play" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                </button>
            </div>
        @endif

        <div class="dark-hero-video-scrollcue" aria-hidden="true">
            <svg class="orbit-ring" viewBox="0 0 84 84">
                <defs>
                    <path id="darkHeroOrbitPath" d="M 42,42 m -32,0 a 32,32 0 1,1 64,0 a 32,32 0 1,1 -64,0" />
                </defs>
                <text>
                    <textPath href="#darkHeroOrbitPath" startOffset="0%">SCROLL TO EXPLORE &#8226; SCROLL TO EXPLORE &#8226;</textPath>
                </text>
            </svg>
            <span class="orbit-arrow">
                <span class="orbit-arrow-chip">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><polyline points="18 13 12 19 6 13"></polyline></svg>
                </span>
            </span>
        </div>
    </div>
</div>

<script>
    (function () {
        document.addEventListener('DOMContentLoaded', function () {
            var root = document.querySelector('[data-hero-video]');
            if (!root) return;

            var video = root.querySelector('.dark-hero-video-el');
            var muteBtn = root.querySelector('[data-video-mute-toggle]');
            var playBtn = root.querySelector('[data-video-play-toggle]');

            if (!video) return; // no video file set — poster only, nothing to control

            function setMuteIcon() {
                if (!muteBtn) return;
                muteBtn.querySelector('.icon-muted').style.display = video.muted ? '' : 'none';
                muteBtn.querySelector('.icon-unmuted').style.display = video.muted ? 'none' : '';
                muteBtn.setAttribute('aria-label', video.muted ? 'Unmute video' : 'Mute video');
            }

            function setPlayIcon() {
                if (!playBtn) return;
                var paused = video.paused;
                playBtn.querySelector('.icon-pause').style.display = paused ? 'none' : '';
                playBtn.querySelector('.icon-play').style.display = paused ? '' : 'none';
                playBtn.setAttribute('aria-label', paused ? 'Play video' : 'Pause video');
            }

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

            // Strictly after everything else has finished loading, and only
            // during an idle moment — the video request can never compete
            // with or delay any other resource on the page.
            function deferredActivate() {
                if (shouldSkip()) return;
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

            video.addEventListener('play', setPlayIcon);
            video.addEventListener('pause', setPlayIcon);
            video.addEventListener('volumechange', setMuteIcon);
            setMuteIcon();
            setPlayIcon();

            if (muteBtn) {
                muteBtn.addEventListener('click', function () {
                    video.muted = !video.muted;
                    setMuteIcon();
                });
            }

            if (playBtn) {
                playBtn.addEventListener('click', function () {
                    // Explicit user gesture — always honor it, even if the
                    // skip heuristics above would otherwise never have
                    // started the video (data saver, reduced motion,
                    // mobile-disabled, or simply not idle yet).
                    activate();
                    if (video.paused) {
                        video.play().catch(function () {});
                    } else {
                        video.pause();
                    }
                });
            }
        });
    })();
</script>
