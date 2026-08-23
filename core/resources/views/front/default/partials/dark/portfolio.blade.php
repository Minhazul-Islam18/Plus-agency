<div class="case-section dark-portfolio-section dark-section-seam"
    style="@if (!empty($be->portfolio_section_bg)) background-image: url('{{ asset('assets/front/img/' . $be->portfolio_section_bg) }}');
        background-size: cover;
        background-position: center;
        position: relative;
        overflow: hidden; @endif">
    @if (!empty($be->portfolio_section_bg))
        <div class="portfolio-overlay"
            style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: #{{ $be->portfolio_overlay_color ?? '000000' }}; opacity: {{ $be->portfolio_overlay_opacity ?? '0.6' }}; z-index: 0; pointer-events: none;">
        </div>
    @endif
    <div class="container" style="position: relative; z-index: 2;">
        <div class="dark-case-head reveal-text" style="--d:.05s">
            <div class="dark-case-head-text">
                <span class="section-eyebrow">{{ convertUtf8($bs->portfolio_section_title) }}</span>
                <h2 class="gradient-shine-heading">{{ convertUtf8($bs->portfolio_section_text) }}</h2>
            </div>
            <div class="dark-case-actions">
                @if (Route::has('front.portfolios'))
                    <a href="{{ route('front.portfolios') }}" class="dark-intro-cta">
                        <span>{{ __('Show all') }}</span><i>&#8594;</i>
                    </a>
                @endif
                @if ($portfolios->count() > 1)
                    <div class="dark-case-nav" data-case-nav>
                        <button type="button" class="dark-case-nav-prev" aria-label="{{ __('Previous') }}"><svg viewBox="0 0 24 24"><path d="M15 6l-6 6 6 6"/></svg></button>
                        <button type="button" class="dark-case-nav-next" aria-label="{{ __('Next') }}"><svg viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></svg></button>
                    </div>
                @endif
            </div>
        </div>

        <div class="dark-case-carousel dark-glass-carousel owl-carousel owl-theme">
            @foreach ($portfolios as $key => $portfolio)
                <a href="{{ route('front.portfoliodetails', $portfolio->slug) }}" class="dark-case-card reveal-card" style="--d:{{ ($key % 3) * 0.1 }}s">
                    <img class="dark-case-img" src="{{ asset('assets/front/img/portfolios/featured/' . $portfolio->featured_image) }}" alt="">
                    <span class="dark-case-scrim"></span>
                    <span class="dark-case-num">{{ sprintf('%02d', $key + 1) }}</span>
                    @if (!empty($portfolio->service))
                        <span class="dark-case-tag">{{ convertUtf8($portfolio->service->title) }}</span>
                    @endif
                    <div class="dark-case-body">
                        <h3>{{ convertUtf8($portfolio->title) }}</h3>
                        <span class="dark-case-link">{{ __('View Project') }} <span class="arrow"><svg viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span></span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</div>
