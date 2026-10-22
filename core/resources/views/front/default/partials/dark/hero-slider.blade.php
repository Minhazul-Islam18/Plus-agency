@php
    $dhsSlides = $sliders ?? collect();

    // Admin-controlled scrim — same one-color/one-opacity-for-the-whole-
    // section convention as hero_overlay_color/opacity (basicinfo.blade.php,
    // the light-theme hero variants) and every breadcrumb overlay pair
    // elsewhere in admin. Scales the existing 2-layer gradient's alpha
    // stops by the admin's opacity value rather than flattening it to a
    // single flat tint — preserves the left-to-right/bottom legibility
    // shape the gradient was designed for; only the strength changes.
    $dhsHex = ltrim($be->hero_slider_overlay_color ?? '090d16', '#');
    $dhsOpacity = is_numeric($be->hero_slider_overlay_opacity ?? null) ? (float) $be->hero_slider_overlay_opacity : 1.0;
    [$dhsR, $dhsG, $dhsB] = sscanf($dhsHex, '%02x%02x%02x') ?: [9, 13, 22];
    $dhsRgb = "{$dhsR}, {$dhsG}, {$dhsB}";
@endphp

@if ($dhsSlides->count() > 0)
    <style>
        .dark-hslider-scrim {
            background: linear-gradient(
                    90deg,
                    rgba({{ $dhsRgb }}, {{ 0.92 * $dhsOpacity }}) 0%,
                    rgba({{ $dhsRgb }}, {{ 0.6 * $dhsOpacity }}) 45%,
                    rgba({{ $dhsRgb }}, {{ 0.25 * $dhsOpacity }}) 75%,
                    rgba({{ $dhsRgb }}, {{ 0.55 * $dhsOpacity }}) 100%
                ),
                linear-gradient(0deg, rgba({{ $dhsRgb }}, {{ 0.9 * $dhsOpacity }}) 0%, transparent 30%);
        }
        @media (max-width: 860px) {
            .dark-hslider-scrim {
                background: linear-gradient(
                    0deg,
                    rgba({{ $dhsRgb }}, {{ 0.96 * $dhsOpacity }}) 0%,
                    rgba({{ $dhsRgb }}, {{ 0.8 * $dhsOpacity }}) 30%,
                    rgba({{ $dhsRgb }}, {{ 0.35 * $dhsOpacity }}) 58%,
                    rgba({{ $dhsRgb }}, {{ 0.08 * $dhsOpacity }}) 78%,
                    transparent 92%
                );
            }
        }
    </style>
    <div class="dark-hslider">
        <div class="dark-hslider-viewport" id="darkHeroSlider">
            @foreach ($dhsSlides as $key => $slide)
                <div class="dark-hslider-slide {{ $key == 0 ? 'is-active' : '' }}" data-slide="{{ $key }}">
                    <img class="dark-hslider-img lazy" data-src="{{ asset('assets/front/img/sliders/' . $slide->image) }}" alt="">
                    <span class="dark-hslider-scrim"></span>
                    <div class="dark-hslider-content">
                        <div>
                            <span class="dark-hslider-counter"><span class="line"></span>{{ convertUtf8($slide->title) }}</span>
                            <h1 class="dark-hslider-title">{{ convertUtf8($slide->text) }}</h1>
                            @if (!empty($slide->button_url) && !empty($slide->button_text))
                                <a href="{{ $slide->button_url }}" class="dark-hslider-cta">
                                    {{ convertUtf8($slide->button_text) }}
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach

            @if ($dhsSlides->count() > 1)
                <nav class="dark-hslider-rail" id="darkHeroSliderRail">
                    @foreach ($dhsSlides as $key => $slide)
                        <button type="button" class="dark-hslider-rail-item {{ $key == 0 ? 'is-active' : '' }}" data-goto="{{ $key }}">
                            <span class="dark-hslider-rail-num">{{ str_pad($key + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="dark-hslider-rail-bar"><span class="dark-hslider-rail-bar-fill"></span></span>
                        </button>
                    @endforeach
                </nav>

                <div class="dark-hslider-control-cluster">
                    <div class="dark-hslider-controls">
                        <button type="button" class="dark-hslider-arrow" id="darkHeroSliderPrev" aria-label="{{ __('Previous') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M15 6l-6 6 6 6" /></svg></button>
                        <button type="button" class="dark-hslider-arrow" id="darkHeroSliderNext" aria-label="{{ __('Next') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M9 6l6 6-6 6" /></svg></button>
                        <span class="dark-hslider-total"><strong id="darkHeroSliderCurrent">01</strong> / {{ str_pad($dhsSlides->count(), 2, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <div class="dark-hslider-progress"><span class="dark-hslider-progress-fill" id="darkHeroSliderProgressFill"></span></div>
                </div>
            @endif
        </div>

        @if ($bs->partner_section == 1 && !empty($partners) && count($partners) > 0)
            <div class="dark-hero-marquee dark-hslider-marquee">
                <div class="dark-hero-marquee-label">{{ !empty($be->partner_marquee_label) ? convertUtf8($be->partner_marquee_label) : __('Trusted across sectors') }}</div>
                <div class="dark-hero-marquee-track">
                    @for ($i = 0; $i < 2; $i++)
                        @foreach ($partners as $partner)
                            @if (!empty($partner->url))
                                <a href="{{ $partner->url }}" target="_blank" rel="noopener" class="dark-hero-marquee-item">
                                    <img src="{{ asset('assets/front/img/partners/' . $partner->image) }}" alt="" loading="lazy" decode="async">
                                </a>
                            @else
                                <span class="dark-hero-marquee-item">
                                    <img src="{{ asset('assets/front/img/partners/' . $partner->image) }}" alt="" loading="lazy" decode="async">
                                </span>
                            @endif
                        @endforeach
                    @endfor
                </div>
            </div>
        @endif
    </div>
@else
    @includeif('front.default.partials.dark.hero')
@endif
