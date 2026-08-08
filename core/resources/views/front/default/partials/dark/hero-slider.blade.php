@php
    $dhsSlides = $sliders ?? collect();
@endphp

@if ($dhsSlides->count() > 0)
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
                <div class="dark-hero-marquee-label">{{ __('Trusted across sectors') }}</div>
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
@else
    @includeif('front.default.partials.dark.hero')
@endif
