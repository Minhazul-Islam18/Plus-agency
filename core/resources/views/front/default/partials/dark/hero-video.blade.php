@php
    // Dedicated dark-theme video hero settings (admin: Content Management ->
    // Home Page Sections -> Hero Section -> Video Version). Falls back to
    // the dark Static hero's own text fields if this page was never filled
    // in, matching dark/hero.blade.php's own fallback-to-light-theme-fields
    // convention one level up.
    $dhvEyebrow = $darkHeroVideo?->eyebrow ?: ($darkHero?->eyebrow ?: __('Welcome'));
    $dhvText = $darkHeroVideo?->text ?: ($darkHero?->text ?: $bs->hero_section_text);
    $dhvButtonText = $darkHeroVideo?->button_text ?: $darkHero?->button_text;
    $dhvButtonUrl = $darkHeroVideo?->button_url ?: $darkHero?->button_url;

    // titles: a repeater of {"text": "...", "accent"?: "..."} rows (admin:
    // Content Management -> Home Page Sections -> Hero Section -> Video
    // Version -> Titles). `text` renders plain white, `accent` (optional)
    // is appended after it in the gradient — two parts of one line, not a
    // substring to find inside `text` (that read as "find this exact
    // phrase verbatim" to admins, who naturally typed the white part and
    // the colored part as two separate pieces instead). Falls back to the
    // Static dark hero's own titles, then the shared light-theme fields
    // converted to the same row shape — neither carries an accent, so
    // those rows just render as plain white text.
    $dhvTitleRows = $darkHeroVideo?->titles ?: ($darkHero?->titles ?: null);
    if (empty($dhvTitleRows)) {
        $dhvTitleRows = collect([$bs->hero_section_title])
            ->merge(preg_split('/\r\n|\r|\n/', (string) $bs->hero_rotating_titles))
            ->map(fn($t) => trim((string) $t))
            ->filter()
            ->map(fn($t) => ['text' => $t])
            ->values()
            ->all();
    }
    $dhvTitlePairs = collect($dhvTitleRows)
        ->map(fn($row) => [trim((string) ($row['text'] ?? '')), trim((string) ($row['accent'] ?? '')), trim((string) ($row['accent_color'] ?? ''))])
        ->filter(fn($pair) => $pair[0] !== '')
        ->values();

    $dhvHighlightTitle = function ($title, $accent, $accentColor = '') {
        $title = convertUtf8($title);
        if ($accent !== '') {
            $accent = convertUtf8($accent);
            // A custom color (admin-picked, "Custom color" checkbox) renders
            // as a flat inline color instead of the default brand gradient
            // the .grad class applies.
            $style = $accentColor !== '' ? ' style="background:none;-webkit-text-fill-color:' . e($accentColor) . ';color:' . e($accentColor) . ';"' : '';
            return e($title) . ' <span class="grad"' . $style . '>' . e($accent) . '</span>';
        }
        return e($title);
    };

    // 'youtube' never touches video_file/the <video> element at all — the
    // player is a plain iframe built client-side, deferred the same way the
    // local <video> source is (see the script below): nothing from
    // youtube.com is requested until strictly after window 'load', and only
    // on a connection/device that can afford it. Autoplays muted + looped,
    // same ambient-background treatment as the local source — no click
    // needed, no play-button icon, since this is the hero's background, not
    // a video someone opts into watching.
    $dhvIsYoutube = ($darkHeroVideo?->video_source ?? 'local') === 'youtube';
    $dhvYoutubeId = $dhvIsYoutube ? extract_youtube_id($darkHeroVideo?->youtube_url) : null;
    $dhvHasYoutube = !empty($dhvYoutubeId);
    $dhvHasVideo = !$dhvIsYoutube && !empty($darkHeroVideo?->video_file);
    $dhvHasPoster = !empty($darkHeroVideo?->poster_image);
    // No front-end "missing image" placeholder asset exists in this app
    // (every other front-end image is admin-required, same as this one) —
    // a 1x1 transparent pixel is the safe last resort if 'Video' is
    // selected before the Poster Image is actually filled in: the
    // .dark-hero's own radial-gradient background still shows through,
    // not a broken-image icon.
    $dhvPosterUrl = $dhvHasPoster
        ? asset('assets/front/img/hero-video/' . $darkHeroVideo->poster_image)
        : ($dhvHasYoutube
            ? "https://i.ytimg.com/vi/{$dhvYoutubeId}/maxresdefault.jpg"
            : 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==');
    $dhvVideoUrl = $dhvHasVideo ? asset('assets/front/img/hero-video/' . $darkHeroVideo->video_file) : '';

    // Admin-controlled scrim — same one-color/one-opacity convention as the
    // hero slider's overlay (hero-slider.blade.php). Bottom-anchored: solid
    // near the bottom, fading out only in the top ~10-15% so the headline
    // area stays legible against any footage while most of the frame reads
    // as tinted, not clear video showing through.
    $dhvOverlayHex = ltrim($darkHeroVideo?->overlay_color ?? '090d16', '#');
    $dhvOverlayOpacity = is_numeric($darkHeroVideo?->overlay_opacity ?? null) ? (float) $darkHeroVideo->overlay_opacity : 0.55;
    [$dhvR, $dhvG, $dhvB] = sscanf($dhvOverlayHex, '%02x%02x%02x') ?: [9, 13, 22];
    $dhvRgb = "{$dhvR}, {$dhvG}, {$dhvB}";

    $dhvDisableOnMobile = $darkHeroVideo?->disable_on_mobile ?? true;
    $dhvScrollText = convertUtf8($darkHeroVideo?->scroll_text ?: __('SCROLL TO EXPLORE'));
@endphp

<style>
    .dark-hero-video-overlay {
        background: linear-gradient(
            to top,
            rgba({{ $dhvRgb }}, {{ 0.9 * $dhvOverlayOpacity }}) 0%,
            rgba({{ $dhvRgb }}, {{ 0.7 * $dhvOverlayOpacity }}) 40%,
            rgba({{ $dhvRgb }}, {{ 0.4 * $dhvOverlayOpacity }}) 70%,
            transparent 88%
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
                 video bytes are requested until the activation script below
                 runs, right after DOMContentLoaded. fetchpriority="low" is
                 what actually keeps it from competing with CSS/fonts/JS —
                 Chrome schedules it behind them automatically — so it can
                 start downloading immediately instead of sitting idle until
                 window 'load', which just adds dead time before a large
                 file even begins fetching. --}}
            <video class="dark-hero-video-el" muted loop playsinline preload="none" fetchpriority="low"
                poster="{{ $dhvPosterUrl }}" data-video-src="{{ $dhvVideoUrl }}"></video>
        @endif

        @if ($dhvHasYoutube)
            {{-- Nothing from youtube.com is ever requested until the
                 activation script below runs — the iframe itself doesn't
                 exist in the markup yet. --}}
            <div class="dark-hero-video-yt-mount" data-youtube-mount data-youtube-id="{{ $dhvYoutubeId }}"></div>
        @endif

        <div class="dark-hero-video-overlay"></div>
    </div>

    {{-- Partners — a ghosted, drifting strip pinned to the frame's very
         bottom edge. Independent of the headline below; doesn't reflow if
         the title wraps to two lines. --}}
    @if ($bs->partner_section == 1 && !empty($partners) && count($partners) > 0)
        <div class="dark-hero-video-partners">
            <div class="container">
                <span class="dark-hero-video-partners-tag">{{ !empty($be->partner_marquee_label) ? convertUtf8($be->partner_marquee_label) : __('Trusted across sectors') }}</span>
                <div class="dark-hero-video-partners-track-wrap">
                    <div class="dark-hero-video-partners-track">
                        @for ($i = 0; $i < 2; $i++)
                            @foreach ($partners as $partner)
                                @if (!empty($partner->url))
                                    <a href="{{ $partner->url }}" target="_blank" rel="noopener" class="dark-hero-video-partners-item">
                                        <img src="{{ asset('assets/front/img/partners/' . $partner->image) }}" alt="" loading="lazy" decode="async">
                                    </a>
                                @else
                                    <span class="dark-hero-video-partners-item">
                                        <img src="{{ asset('assets/front/img/partners/' . $partner->image) }}" alt="" loading="lazy" decode="async">
                                    </span>
                                @endif
                            @endforeach
                        @endfor
                    </div>
                </div>
            </div>
        </div>
    @endif

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
        <svg class="orbit-ring" viewBox="0 0 52 52">
            <defs>
                <path id="darkHeroOrbitPath" d="M 26,26 m -20,0 a 20,20 0 1,1 40,0 a 20,20 0 1,1 -40,0" />
            </defs>
            <text>
                <textPath href="#darkHeroOrbitPath" startOffset="0%">{{ e($dhvScrollText) }} &#8226; {{ e($dhvScrollText) }} &#8226;</textPath>
            </text>
        </svg>
        <span class="orbit-arrow">
            <span class="orbit-arrow-chip">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><polyline points="18 13 12 19 6 13"></polyline></svg>
            </span>
        </span>
    </div>

    <div class="dark-hero-inner">
        <div class="container">
            {{-- No frosted-glass panel here on purpose — text sits straight on
                 the footage, bottom-left, lower-third style. --}}
            <div class="dark-hero-video-content">
                <span class="dark-hero-eyebrow">{{ convertUtf8($dhvEyebrow) }}</span>

                <h1 class="dark-hero-flip" data-hero-flip>
                    @foreach ($dhvTitlePairs as $key => $pair)
                        <span class="dark-hero-flip-line {{ $key == 0 ? 'is-active' : '' }}">{!! $dhvHighlightTitle($pair[0], $pair[1], $pair[2]) !!}</span>
                    @endforeach
                </h1>

                <p class="dark-hero-text">{{ convertUtf8($dhvText) }}</p>

                {{-- No meta row here on purpose — the approved design ends
                     at the CTA, nothing below it. (meta_left/meta_right
                     exist on this model for parity with the other hero
                     variants, but this variant doesn't render them.) --}}
                @if (!empty($dhvButtonUrl) && !empty($dhvButtonText))
                    <a href="{{ $dhvButtonUrl }}" class="dark-hero-cta" target="_blank">
                        <span>{{ convertUtf8($dhvButtonText) }}</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        document.addEventListener('DOMContentLoaded', function () {
            var root = document.querySelector('[data-hero-video]');
            if (!root) return;

            var video = root.querySelector('.dark-hero-video-el');
            var ytMount = root.querySelector('[data-youtube-mount]');
            var muteBtn = root.querySelector('[data-video-mute-toggle]');
            var playBtn = root.querySelector('[data-video-play-toggle]');

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

            function activateYoutube() {
                if (ytMount.dataset.activated) return;
                ytMount.dataset.activated = '1';

                var id = ytMount.getAttribute('data-youtube-id');
                var iframe = document.createElement('iframe');
                // mute=1 is what makes autoplay reliably allowed by every
                // browser's autoplay policy — same reason the local <video>
                // element is muted. loop=1 needs playlist=<same id> to
                // actually loop a single video (a YouTube embed quirk).
                iframe.src = 'https://www.youtube.com/embed/' + encodeURIComponent(id) +
                    '?autoplay=1&mute=1&loop=1&playlist=' + encodeURIComponent(id) +
                    '&controls=0&modestbranding=1&rel=0&playsinline=1&iv_load_policy=3&disablekb=1';
                iframe.allow = 'autoplay; encrypted-media';
                iframe.title = '';
                ytMount.appendChild(iframe);
            }

            // Starts right away, not deferred until window 'load' — the local
            // <video> has fetchpriority="low" (Chrome schedules it behind
            // CSS/fonts/JS automatically), so starting the fetch immediately
            // doesn't compete with critical resources, it just means a large
            // file isn't sitting idle for several extra seconds before it even
            // begins downloading. The YouTube iframe has no file to wait on
            // the same way, so it starts immediately too.
            if (!shouldSkip()) {
                if (video) activate();
                else if (ytMount) activateYoutube();
            }

            if (!video) return; // no local video file — poster/YouTube only, nothing else to control

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
