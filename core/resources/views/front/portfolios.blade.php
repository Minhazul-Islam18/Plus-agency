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
    <!--    dark portfolios page start: same right-sidebar pattern as Blog/Services/FAQ   -->
    <div class="dark-svcp-section dark-pf-section">
        @if (serviceCategory())
            <button type="button" class="dark-pf-oc-trigger" id="pfOcToggle" aria-expanded="false">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M7 12h10M10 18h4" stroke-linecap="round" /></svg>
                {{ __('Categories') }}
            </button>
        @endif
        <div class="dark-svcp-inner">
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

            @if (serviceCategory())
                <div id="pfCatSource">
                    <div class="dark-svcp-widget">
                        <h4>{{ __('Categories') }}</h4>
                        <ul class="dark-svcp-cat-list">
                            <li class="{{ empty($category) ? 'is-active' : '' }}">
                                <a href="{{ route('front.portfolios') }}">{{ __('All Projects') }}</a>
                            </li>
                            @foreach ($scats as $key => $scat)
                                <li class="{{ !empty($category) && $category->id == $scat->id ? 'is-active' : '' }}">
                                    <a href="{{ route('front.portfolios', ['category' => $scat->id]) }}">{{ convertUtf8($scat->name) }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </div>

        @if (serviceCategory())
            {{-- Mobile categories filter offcanvas: JS clones #pfCatSource into #pfOcBody --}}
            <div class="dark-oc-backdrop" id="pfOcBackdrop"></div>
            <nav class="dark-oc-panel pf-oc-single" id="pfOcPanel">
                <div class="dark-oc-head">
                    <span class="dark-oc-head-logo">{{ __('Categories') }}</span>
                    <button type="button" class="dark-oc-close" id="pfOcClose" aria-label="Close">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18" stroke-linecap="round" /></svg>
                    </button>
                </div>
                <div class="dark-oc-nav" id="pfOcBody"></div>
            </nav>
        @endif
    </div>
    <!--    dark portfolios page end   -->
@else
    <!--    case lists start: same right-sidebar pattern as Blog/Services/FAQ.
         Category filtering moved from client-side isotope (which only
         reshuffled the current page's 9 results and never actually changed
         ?category=, so pagination + filter together were broken) to the same
         server-filtered link pattern already used for the dark theme and for
         Blog/Services — plain Bootstrap grid, no isotope needed any more.   -->
    <div class="case-lists section-padding case-page pt-120 pb-110">
        <div class="container">
            @if (serviceCategory())
                <button type="button" class="pf-oc-trigger" id="pfOcToggle" aria-expanded="false">
                    <i class="fas fa-filter"></i> {{ __('Categories') }}
                </button>
            @endif
            <div class="row">
                <div class="col-lg-8">
                    <div class="row">
                        @if (count($portfolios) == 0)
                            <div class="col-md-12 py-5 bg-light text-center mb-4">
                                <h3>{{ __('NO PORTFOLIO FOUND') }}</h3>
                            </div>
                        @else
                            @foreach ($portfolios as $key => $portfolio)
                                <div class="col-md-6">
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
                    @if ($portfolios instanceof \Illuminate\Pagination\LengthAwarePaginator && $portfolios->hasPages())
                        <div class="row">
                            <div class="col-md-12">
                                {{ $portfolios->appends(['category' => request()->input('category')])->links() }}
                            </div>
                        </div>
                    @endif
                </div>

                @if (serviceCategory())
                    <div class="col-lg-4" id="pfCatSource">
                        <div class="sidebar">
                            <div class="blog-sidebar-widgets category-widget">
                                <div class="category-lists job">
                                    <h4>{{ __('Categories') }}</h4>
                                    <ul>
                                        <li class="single-category {{ empty($category) ? 'active' : '' }}">
                                            <a href="{{ route('front.portfolios') }}">{{ __('All Projects') }}</a>
                                        </li>
                                        @foreach ($scats as $key => $scat)
                                            <li class="single-category {{ !empty($category) && $category->id == $scat->id ? 'active' : '' }}">
                                                <a href="{{ route('front.portfolios', ['category' => $scat->id]) }}">{{ convertUtf8($scat->name) }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            @if (serviceCategory())
                {{-- Mobile categories filter offcanvas: JS clones #pfCatSource into #pfOcBody --}}
                <div class="pf-oc-backdrop" id="pfOcBackdrop"></div>
                <nav class="pf-oc-panel pf-oc-single" id="pfOcPanel">
                    <div class="pf-oc-head">
                        <span>{{ __('Categories') }}</span>
                        <button type="button" class="pf-oc-close" id="pfOcClose" aria-label="Close">&times;</button>
                    </div>
                    <div class="pf-oc-nav" id="pfOcBody"></div>
                </nav>
            @endif
        </div>
    </div>
    <!--    case lists end   -->
@endif
@endsection

@section('scripts')
    <script src="{{ asset_v('assets/front/js/category-offcanvas.js') }}"></script>
@endsection

