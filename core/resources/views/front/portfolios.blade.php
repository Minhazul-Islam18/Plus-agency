@extends("front.$version.layout")

@section('pagename')
    -
    @if (empty($category))
        {{ __('All') }}
    @else
        {{ convertUtf8($category->name) }}
    @endif
    {{ __('Portfolios') }}
@endsection

@section('meta-keywords', "$be->portfolios_meta_keywords")
@section('meta-description', "$be->portfolios_meta_description")

@section('breadcrumb-title', convertUtf8($bs->portfolio_title))
@section('breadcrumb-subtitle', convertUtf8($bs->portfolio_subtitle))
@section('breadcrumb-link', __('Portfolios'))
@if (!empty($bs->portfolio_breadcrumb_bg))
    @section('breadcrumb-bg', asset('assets/front/img/' . $bs->portfolio_breadcrumb_bg))
@endif

@if (!empty($bs->portfolio_breadcrumb_overlay_color))
    @section('breadcrumb-overlay-color', $bs->portfolio_breadcrumb_overlay_color)
@endif

@if (!empty($bs->portfolio_breadcrumb_overlay_opacity))
    @section('breadcrumb-overlay-opacity', $bs->portfolio_breadcrumb_overlay_opacity)
@endif

@if ($be->theme_version == 'dark')
    @section('breadcrumb-ledger')
        <div class="dark-bc-ledger-item">
            <span class="dark-bc-ledger-num">{{ str_pad($portfolios->total(), 2, '0', STR_PAD_LEFT) }}</span>
            <span class="dark-bc-ledger-label">{{ __('Completed Projects') }}</span>
        </div>
        @if (serviceCategory())
            <div class="dark-bc-ledger-item">
                <span class="dark-bc-ledger-num">{{ str_pad(count($scats), 2, '0', STR_PAD_LEFT) }}</span>
                <span class="dark-bc-ledger-label">{{ __('Categories Covered') }}</span>
            </div>
        @endif
    @endsection
@endif

@section('content')

@if ($be->theme_version == 'dark')
    <!--    dark portfolios page start   -->
    <div class="dark-pf-layout">
        @if (serviceCategory())
            <nav class="dark-pf-rail">
                <span class="dark-pf-rail-label">{{ __('Categories') }}</span>
                <ul class="dark-pf-rail-list">
                    <li class="dark-pf-rail-item {{ empty($category) ? 'is-active' : '' }}">
                        <a href="{{ route('front.portfolios') }}">
                            {{ __('All Projects') }}
                            <span class="dark-pf-rail-count">{{ str_pad(\App\Portfolio::where('language_id', $currentLang->id)->count(), 2, '0', STR_PAD_LEFT) }}</span>
                        </a>
                    </li>
                    @foreach ($scats as $key => $scat)
                        @php
                            $scatPortfolioCount = \App\Portfolio::where('language_id', $currentLang->id)
                                ->whereHas('service', function ($q) use ($scat) {
                                    $q->where('scategory_id', $scat->id);
                                })
                                ->count();
                        @endphp
                        <li class="dark-pf-rail-item {{ !empty($category) && $category->id == $scat->id ? 'is-active' : '' }}">
                            <a href="{{ route('front.portfolios', ['category' => $scat->id]) }}">
                                {{ convertUtf8($scat->name) }}
                                <span class="dark-pf-rail-count">{{ str_pad($scatPortfolioCount, 2, '0', STR_PAD_LEFT) }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>
        @endif

        <div>
            @if (count($portfolios) == 0)
                <div class="dark-svcp-empty">
                    <h3>{{ __('NO PORTFOLIO FOUND') }}</h3>
                </div>
            @else
                <div class="dark-pf-grid">
                    @foreach ($portfolios as $key => $portfolio)
                        <a class="dark-pf-card" href="{{ route('front.portfoliodetails', [$portfolio->slug]) }}">
                            <img class="lazy" data-src="{{ asset('assets/front/img/portfolios/featured/' . $portfolio->featured_image) }}" alt="">
                            <span class="dark-pf-scrim"></span>
                            @if (!empty($portfolio->service->scategory))
                                <span class="dark-pf-tag">{{ convertUtf8($portfolio->service->scategory->name) }}</span>
                            @endif
                            <div class="dark-pf-body">
                                @if (!empty($portfolio->service))
                                    <div class="service">{{ convertUtf8($portfolio->service->title) }}</div>
                                @endif
                                <h3>{{ strlen($portfolio->title) > 60 ? mb_substr($portfolio->title, 0, 60, 'utf-8') . '...' : $portfolio->title }}</h3>
                                <span class="dark-pf-link">
                                    {{ __('View Project') }}
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif

            @if ($portfolios instanceof \Illuminate\Pagination\LengthAwarePaginator && $portfolios->hasPages())
                <nav class="dark-pf-pagination">
                    {{ $portfolios->appends(['category' => request()->input('category')])->links('vendor.pagination.dark-glass') }}
                </nav>
            @endif
        </div>
    </div>
    <!--    dark portfolios page end   -->
@else
    <!--    case lists start   -->
    <div class="case-lists section-padding case-page pt-120 pb-110" id="masonry-portfolio">
        <div class="container">
            @if (serviceCategory())
                <div class="row">
                    <div class="col-xl-12">
                        <div class="filter-nav text-center mb-15">
                            <ul class="filter-btn">
                                <li data-filter="*" class="active">All</li>
                                @foreach ($scats as $key => $scat)
                                    @php
                                        $filterValue = '.' . strtolower($scat->name);

                                        if (str_contains($filterValue, ' ')) {
                                            $filterValue = str_replace(' ', '-', $filterValue);
                                        }
                                    @endphp

                                    <li data-filter="{{ $filterValue }}">{{ convertUtf8($scat->name) }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <div class="cases masonry-row">
                <div class="row">
                    @if (count($portfolios) == 0)
                        <div class="col-lg-12 py-5 bg-light text-center mb-4">
                            <h3>{{ __('NO PORTFOLIO FOUND') }}</h3>
                        </div>
                    @else
                        @foreach ($portfolios as $key => $portfolio)
                            @php
                                $categoryName = '';

                                if (!empty($portfolio->service->scategory)) {
                                    $portfolioCategory = $portfolio->service->scategory;

                                    $categoryName = strtolower($portfolioCategory->name);

                                    if (str_contains($categoryName, ' ')) {
                                        $categoryName = str_replace(' ', '-', $categoryName);
                                    }
                                }
                            @endphp

                            <div class="col-lg-4 col-md-6 portfolio-column {{ $categoryName }}">
                                <div class="single-case lazy"
                                    data-bg="{{ asset('assets/front/img/portfolios/featured/' . $portfolio->featured_image) }}">
                                    <div class="outer-container">
                                        <div class="inner-container">
                                            <h4>{{ strlen($portfolio->title) > 25 ? mb_substr($portfolio->title, 0, 25, 'utf-8') . '...' : $portfolio->title }}
                                            </h4>
                                            @if (!empty($portfolio->service))
                                                <p>{{ $portfolio->service->title }}</p>
                                            @endif

                                            <a href="{{ route('front.portfoliodetails', [$portfolio->slug]) }}"
                                                class="readmore-btn"><span>{{ __('Read More') }}</span></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
                @if ($portfolios instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="col-lg-12">
                        {{ $portfolios->appends(['category' => request()->input('category')])->links() }}
                    </div>
                @endif
            </div>


        </div>
    </div>
    <!--    case lists end   -->
@endif
@endsection

@section('scripts')
    @if ($be->theme_version != 'dark')
        <script>
            $('#masonry-portfolio').imagesLoaded(function() {
                // items on button click
                $('.filter-btn').on('click', 'li', function() {
                    var filterValue = $(this).attr('data-filter');
                    $grid.isotope({
                        filter: filterValue
                    });
                });
                // menu active class
                $('.filter-btn li').on('click', function(e) {
                    $(this).siblings('.active').removeClass('active');
                    $(this).addClass('active');
                    e.preventDefault();
                });
                var $grid = $('.masonry-row').isotope({
                    itemSelector: '.portfolio-column',
                    percentPosition: true,
                    masonry: {
                        columnWidth: 0
                    }
                });
            });
        </script>
    @endif
@endsection
