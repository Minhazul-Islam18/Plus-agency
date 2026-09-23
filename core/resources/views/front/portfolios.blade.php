@extends("front.$version.layout")

@section('pagename')
    -
    @if (empty($sector))
        {{ __('All') }}
    @else
        {{ convertUtf8($sector->name) }}
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

@section('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icons@7.2.3/css/flag-icons.min.css" integrity="sha384-aQuvIWWIbpu/mSqULLDiUveyYiPoJzPKAWjUmGJ+Elm+N/LJhzfZqsutsfw870JS" crossorigin="anonymous">
    @if ($be->theme_version == 'dark')
        <style>
            /* .dark-bc-inner (the breadcrumb hero, rendered in the shared
               layout above @yield('content')) is still at its site-wide
               1320px, while this page's own container was widened to
               1520px — mismatched. This <style> block only ever renders
               on THIS page, so it's a safe way to scope the override
               without a body/page class to hook a selector onto. */
            .dark-bc-inner {
                /* !important needed — this <style> block renders in <head>
                   BEFORE dark-glass.css's own <link>, so without it the
                   later external stylesheet's same-specificity rule wins
                   on source order alone. */
                max-width: 1520px !important;
                padding-bottom: 20px !important;
            }
            .dark-breadcrumb-hero {
                padding-top: 70px !important;
            }
            .dark-bc-ledger {
                gap: 6px !important;
            }
            .dark-bc-ledger-item {
                padding-top: 6px !important;
            }
        </style>
    @endif
@endsection

@if ($be->theme_version == 'dark')
    @section('breadcrumb-ledger')
        <div class="dark-bc-ledger-item">
            <span class="dark-bc-ledger-num">{{ str_pad($statusCounts->get('completed', 0), 2, '0', STR_PAD_LEFT) }}</span>
            <span class="dark-bc-ledger-label">{{ __('Completed Projects') }}</span>
        </div>
        <div class="dark-bc-ledger-item">
            <span class="dark-bc-ledger-num">{{ str_pad($statusCounts->get('in_progress', 0), 2, '0', STR_PAD_LEFT) }}</span>
            <span class="dark-bc-ledger-label">{{ __('Projects in Progress') }}</span>
        </div>
        <div class="dark-bc-ledger-item">
            <span class="dark-bc-ledger-num">{{ str_pad($statusCounts->get('pending', 0), 2, '0', STR_PAD_LEFT) }}</span>
            <span class="dark-bc-ledger-label">{{ __('Pending Projects') }}</span>
        </div>
        @if (count($sectors) > 0)
            <div class="dark-bc-ledger-item">
                <span class="dark-bc-ledger-num">{{ str_pad(count($sectors), 2, '0', STR_PAD_LEFT) }}</span>
                <span class="dark-bc-ledger-label">{{ __('Strategic Sectors') }}</span>
            </div>
        @endif
    @endsection
@endif

@section('content')

@if ($be->theme_version == 'dark')
    <!--    dark portfolios page start: same right-sidebar pattern as Blog/Services/FAQ   -->
    <div class="dark-svcp-section dark-pf-section">
        @if (count($sectors) > 0)
            <button type="button" class="dark-pf-oc-trigger" id="pfOcToggle" aria-expanded="false">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M7 12h10M10 18h4" stroke-linecap="round" /></svg>
                {{ __('Sectors') }}
            </button>
        @endif
        <div class="dark-svcp-inner">
            <div>
                <form method="get" class="dark-pf-toolbar">
                    <input type="hidden" name="sector" value="{{ request()->input('sector') }}">
                    <div class="dark-svcp-search">
                        <input type="text" name="search" placeholder="{{ __('Search projects') }}" value="{{ $search }}">
                        <button type="submit"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><circle cx="11" cy="11" r="7" /><path d="M21 21l-4.3-4.3" /></svg></button>
                    </div>
                    @if (count($years) > 0)
                        <div class="dark-pf-year-select">
                            <select name="year" onchange="this.form.submit()">
                                <option value="">{{ __('All years') }}</option>
                                @foreach ($years as $y)
                                    <option value="{{ $y }}" {{ (string) $year === (string) $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </form>

                @if (count($portfolios) == 0)
                    <div class="dark-svcp-empty">
                        <h3>{{ __('NO PORTFOLIO FOUND') }}</h3>
                    </div>
                @else
                    <div class="dark-pf-grid">
                        @foreach ($portfolios as $key => $portfolio)
                            @php
                                // Global position across pagination, not just this page's
                                // own $key — matches how the details page's own "Latest
                                // projects" N°01/02/... numbering reads as a running count.
                                $globalIndex = $portfolios instanceof \Illuminate\Pagination\LengthAwarePaginator
                                    ? ($portfolios->currentPage() - 1) * $portfolios->perPage() + $key + 1
                                    : $key + 1;
                            @endphp
                            <a href="{{ route('front.portfoliodetails', [$portfolio->slug]) }}" class="dark-pd-similar-card reveal-card" style="--d:{{ ($key % 3) * 0.1 }}s">
                                <div class="dark-pd-similar-img">
                                    <img class="lazy" data-src="{{ asset('assets/front/img/portfolios/featured/' . $portfolio->featured_image) }}" alt="">
                                    <span class="dark-pd-similar-index">N°{{ str_pad($globalIndex, 2, '0', STR_PAD_LEFT) }}</span>
                                    @if (!empty($portfolio->year))
                                        <span class="dark-pd-similar-year">{{ $portfolio->year }}</span>
                                    @endif
                                    @if (!empty($portfolio->sector))
                                        <span class="dark-pd-similar-badge">
                                            <i class="{{ $portfolio->sector->icon ?: 'fas fa-building' }}"></i>
                                            <span class="dark-pd-similar-badge-text">{{ convertUtf8($portfolio->sector->name) }}</span>
                                        </span>
                                    @elseif (!empty($portfolio->service->scategory))
                                        <span class="dark-pd-similar-badge">
                                            <i class="fas fa-tag"></i>
                                            <span class="dark-pd-similar-badge-text">{{ convertUtf8($portfolio->service->scategory->name) }}</span>
                                        </span>
                                    @endif
                                </div>
                                <div class="dark-pd-similar-body">
                                    @if (!empty($portfolio->country))
                                        <div class="dark-pd-similar-meta">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span>{{ $countryNames[$portfolio->country] ?? $portfolio->country }}</span>
                                        </div>
                                    @endif
                                    <h5 class="dark-pd-similar-card-title">{{ convertUtf8($portfolio->title) }}</h5>
                                    <button type="button" class="dark-pd-similar-toggle" hidden>{{ __('Show more') }}</button>
                                    <p>{{ convertUtf8($portfolio->client_name) }}</p>
                                </div>
                                <span class="dark-pd-similar-cta">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6">
                                        <path d="M9 6l6 6-6 6" />
                                    </svg>
                                </span>
                            </a>
                        @endforeach
                    </div>
                @endif

                @if ($portfolios instanceof \Illuminate\Pagination\LengthAwarePaginator && $portfolios->hasPages())
                    <nav class="dark-pf-pagination">
                        {{ $portfolios->appends(request()->query())->links('vendor.pagination.dark-glass') }}
                    </nav>
                @endif
            </div>

            <div>
                @if (count($sectors) > 0)
                    <div id="pfCatSource">
                        <div class="dark-svcp-widget">
                            <h4>{{ __('Sectors') }}</h4>
                            <ul class="dark-svcp-cat-list dark-pf-cat-list">
                                <li class="{{ empty($sector) ? 'is-active' : '' }}">
                                    <a href="{{ route('front.portfolios') }}">
                                        <span class="dark-pf-cat-icon"><i class="fas fa-th-large"></i></span>
                                        <span class="dark-pf-cat-name">{{ __('All sectors') }}</span>
                                        <span class="dark-pf-cat-count">{{ $totalPortfoliosCount }}</span>
                                        <span class="dark-pf-cat-chevron"><i class="fas fa-chevron-right"></i></span>
                                    </a>
                                </li>
                                @foreach ($sectors as $key => $s)
                                    <li class="{{ !empty($sector) && $sector->id == $s->id ? 'is-active' : '' }}">
                                        <a href="{{ route('front.portfolios', ['sector' => $s->id]) }}">
                                            <span class="dark-pf-cat-icon"><i class="{{ $s->icon ?: 'fas fa-building' }}"></i></span>
                                            <span class="dark-pf-cat-name">{{ convertUtf8($s->name) }}</span>
                                            <span class="dark-pf-cat-count">{{ $sectorCounts->get($s->id, 0) }}</span>
                                            <span class="dark-pf-cat-chevron"><i class="fas fa-chevron-right"></i></span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                {{-- Same widget/markup as the Service details page sidebar —
                     kept OUTSIDE #pfCatSource so it doesn't get duplicated
                     into the mobile sectors offcanvas below, which only
                     ever clones that one filter widget. --}}
                <div class="dark-svcp-widget dark-svcp-newsletter">
                    <span class="dark-bc-eyebrow">{{ __('SUBSCRIBE') }}</span>
                    <h4 class="dark-svcp-newsletter-title">{{ __('SUBSCRIBE FOR NEWSLETTER') }}</h4>
                    <form id="subscribeForm" class="dark-svcp-newsletter-form" action="{{ route('front.subscribe') }}"
                        method="POST">
                        @csrf
                        <input name="email" type="email" placeholder="{{ __('Email') }}">
                        <button type="submit">{{ __('Subscribe') }}</button>
                    </form>
                    <p id="erremail" class="text-danger mb-0 err-email"></p>
                </div>
            </div>
        </div>

        @if (count($sectors) > 0)
            {{-- Mobile sectors filter offcanvas: JS clones #pfCatSource into #pfOcBody --}}
            <div class="dark-oc-backdrop" id="pfOcBackdrop"></div>
            <nav class="dark-oc-panel pf-oc-single" id="pfOcPanel">
                <div class="dark-oc-head">
                    <span class="dark-oc-head-logo">{{ __('Sectors') }}</span>
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
            @if (count($sectors) > 0)
                <button type="button" class="pf-oc-trigger" id="pfOcToggle" aria-expanded="false">
                    <i class="fas fa-filter"></i> {{ __('Sectors') }}
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
                                    <div class="single-case lazy reveal-card" style="--d:{{ ($key % 2) * 0.1 }}s"
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
                                {{ $portfolios->appends(request()->query())->links() }}
                            </div>
                        </div>
                    @endif
                </div>

                @if (count($sectors) > 0)
                    <div class="col-lg-4" id="pfCatSource">
                        <div class="sidebar">
                            <div class="blog-sidebar-widgets category-widget">
                                <div class="category-lists job">
                                    <h4>{{ __('Sectors') }}</h4>
                                    <ul>
                                        <li class="single-category {{ empty($sector) ? 'active' : '' }}">
                                            <a href="{{ route('front.portfolios') }}">{{ __('All sectors') }}</a>
                                        </li>
                                        @foreach ($sectors as $key => $s)
                                            <li class="single-category {{ !empty($sector) && $sector->id == $s->id ? 'active' : '' }}">
                                                <a href="{{ route('front.portfolios', ['sector' => $s->id]) }}">{{ convertUtf8($s->name) }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            @if (count($sectors) > 0)
                {{-- Mobile sectors filter offcanvas: JS clones #pfCatSource into #pfOcBody --}}
                <div class="pf-oc-backdrop" id="pfOcBackdrop"></div>
                <nav class="pf-oc-panel pf-oc-single" id="pfOcPanel">
                    <div class="pf-oc-head">
                        <span>{{ __('Sectors') }}</span>
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
    @if ($be->theme_version == 'dark')
        <script>
            (function () {
                // Same "Show more" truncation toggle as the details page's own
                // "Latest projects" cards (.dark-pd-similar-*, reused here as-is
                // for exact visual parity) — but this grid is static (no owl
                // carousel to re-measure on), so it just runs once on load and
                // again on resize, since a 2-col/1-col breakpoint change is what
                // decides whether a title actually wraps to 2 lines here.
                var $ = window.jQuery;
                if (!$) return;

                function measureCardTitles() {
                    document.querySelectorAll('.dark-pd-similar-card-title').forEach(function (title) {
                        var btn = title.nextElementSibling;
                        if (!btn || !btn.classList.contains('dark-pd-similar-toggle')) return;
                        btn.hidden = title.scrollHeight <= title.clientHeight + 1;
                    });
                }

                measureCardTitles();

                var resizeTimer;
                $(window).on('resize', function () {
                    clearTimeout(resizeTimer);
                    resizeTimer = setTimeout(measureCardTitles, 200);
                });

                $(document).on('click', '.dark-pd-similar-toggle', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var $title = $(this).siblings('.dark-pd-similar-card-title');
                    var expanded = $title.toggleClass('is-expanded').hasClass('is-expanded');
                    $(this).text(expanded ? @json(__('Show less')) : @json(__('Show more')));
                });
            })();
        </script>
    @endif
@endsection

