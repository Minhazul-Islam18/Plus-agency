<div class="service-categories dark-service-categories dark-section-seam"
    style="@if (!empty($be->service_section_bg)) background-image: url('{{ asset('assets/front/img/' . $be->service_section_bg) }}');
        background-size: cover;
        background-position: center;
        position: relative;
        overflow: hidden; @endif">
    @if (!empty($be->service_section_bg))
        <div class="service-overlay"
            style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: #{{ $be->service_overlay_color ?? '000000' }}; opacity: {{ $be->service_overlay_opacity ?? '0.6' }}; z-index: 0; pointer-events: none;">
        </div>
    @endif
    <div class="container" style="position: relative; z-index: 2;">
        <div class="row text-center">
            <div class="col-lg-6 offset-lg-3 reveal-text">
                <span class="section-eyebrow">{{ convertUtf8($bs->service_section_title) }}</span>
                <h2 class="gradient-shine-heading">{{ convertUtf8($bs->service_section_subtitle) }}</h2>
            </div>
        </div>
    </div>
    @php
        $darkSvcPerPage = 6;
        $darkSvcTotal = $scategories->count();
        $darkSvcFirstPage = $scategories->take($darkSvcPerPage);
    @endphp
    <div class="container">
        <div class="dark-svc-grid" id="darkSvcGrid">
            @foreach ($darkSvcFirstPage as $key => $scategory)
                <div class="dark-svc-card reveal-card" style="--d:{{ ($key % 3) * 0.1 }}s">
                    <span class="dark-svc-card-num">{{ sprintf('%02d', $key + 1) }}</span>
                    @if (!empty($scategory->image))
                        <span class="dark-svc-card-icon">
                            <img class="lazy" data-src="{{ asset('assets/front/img/service_category_icons/' . $scategory->image) }}" alt="">
                        </span>
                    @endif
                    <h3>{{ convertUtf8($scategory->name) }}</h3>
                    <p>{{ convertUtf8($scategory->short_text) }}</p>
                    <a href="{{ route('front.services', ['category' => $scategory->id]) }}" class="dark-svc-card-link">{{ __('View Services') }} &#8594;</a>
                </div>
            @endforeach
        </div>

        @if ($darkSvcTotal > $darkSvcPerPage)
            <div class="dark-svc-load-more-wrap" id="darkSvcLoadMoreWrap">
                <button class="dark-svc-load-more-btn" id="darkSvcLoadMoreBtn" type="button"
                    data-lang="{{ $currentLang->id ?? '' }}"
                    data-offset="{{ $darkSvcPerPage }}"
                    data-total="{{ $darkSvcTotal }}"
                    data-url="{{ route('front.serviceCategories.loadMore') }}"
                    data-text-loading="{{ __('Loading…') }}"
                    data-text-load-more="{{ __('Load more') }}"
                    data-text-show-less="{{ __('Show less') }}"
                    data-text-more-suffix="{{ __('more') }}">
                    <span class="dark-svc-load-more-label">{{ __('Load more') }}</span>
                    <span class="dark-svc-load-more-count">({{ $darkSvcTotal - $darkSvcPerPage }} {{ __('more') }})</span>
                    <span class="dark-svc-load-more-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                        <span class="dark-svc-load-more-spinner"></span>
                    </span>
                </button>
            </div>
        @endif
    </div>
</div>
