@extends("front.$version.layout")

@php
    // The hero gallery + sidebar right below already carry the project's
// title/context — the layout's own breadcrumb banner on top of that
    // read as a redundant duplicate header, per feedback. Portfolio detail
    // only; every other page keeps its breadcrumb as-is.
    $hideBreadcrumb = true;
@endphp

@section('pagename')
    - {{ convertUtf8($portfolio->title) }}
@endsection

@section('meta-keywords', "$portfolio->meta_keywords")
@section('meta-description', "$portfolio->meta_description")

@section('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icons@7.2.3/css/flag-icons.min.css"
        integrity="sha384-aQuvIWWIbpu/mSqULLDiUveyYiPoJzPKAWjUmGJ+Elm+N/LJhzfZqsutsfw870JS" crossorigin="anonymous">
    <style>
        .project-infos {
            font-size: 15px;
        }

        .info-row {
            display: grid;
            grid-template-columns: 125px 12px 1fr;
            column-gap: 6px;
            margin-bottom: 8px;
            align-items: start;
        }

        .info-row .label {
            font-weight: 600;
            white-space: nowrap;
        }

        .info-row .colon {
            font-weight: 600;
            text-align: center;
        }

        .info-row .value {
            word-break: break-word;
        }

        @media (max-width: 576px) {
            .info-row {
                grid-template-columns: 1fr;
                row-gap: 2px;
            }

            .info-row .colon {
                display: none;
            }
        }
    </style>
@endsection
@section('content')

    @if ($be->theme_version == 'dark')
        @php
            $pdCostFormatted = null;
            if (!empty($portfolio->cost_of_service)) {
                $decimalSeparator = app()->getLocale() == 'fr' ? ',' : '.';
                $thousandSeparator = app()->getLocale() == 'fr' ? ' ' : ',';
                $pdCostFormatted =
                    floor($portfolio->cost_of_service) == $portfolio->cost_of_service
                        ? number_format($portfolio->cost_of_service, 0, $decimalSeparator, $thousandSeparator)
                        : number_format($portfolio->cost_of_service, 2, $decimalSeparator, $thousandSeparator);
            }
        @endphp
        <!--    dark portfolio details section start   -->
        {{-- Hiding the breadcrumb banner (above) removed the page's only real
         <h1> — this keeps one for SEO/screen readers without bringing
         back a visual duplicate of the title already in the hero/sidebar. --}}
        <h1 class="sr-only">{{ convertUtf8($portfolio->title) }}</h1>
        <div class="dark-svcp-section dark-pd-page">
            <div class="dark-svcp-inner">
                <div class="dark-pd-main-col">
                    @if ($portfolio->portfolio_images->count() > 0)
                        @php
                            $pdImageUrls = $portfolio->portfolio_images
                                ->map(fn($pi) => asset('assets/front/img/portfolios/sliders/' . $pi->image))
                                ->values();
                        @endphp
                        <div class="dark-pd-gallery">
                            <div class="dark-pd-gallery-main" data-images="{{ $pdImageUrls->toJson() }}">
                                @if ($portfolio->portfolio_images->count() > 1)
                                    <div class="dark-pd-gallery-progress">
                                        <div class="dark-pd-gallery-progress-fill" id="pdGalleryProgressFill"></div>
                                    </div>
                                @endif
                                <img id="pdMainImg" class="lazy is-kenburns" draggable="false"
                                    data-src="{{ $pdImageUrls->first() }}" alt="">
                                @include('front.default.partials.dark.portfolio-hero-overlay', [
                                    'portfolio' => $portfolio,
                                ])
                                <button type="button" class="dark-pd-gallery-nav dark-pd-gallery-zoom" id="pdGalleryZoom"
                                    aria-label="{{ __('Zoom') }}"><svg viewBox="0 0 24 24">
                                        <circle cx="11" cy="11" r="7" />
                                        <path d="M21 21l-4.35-4.35M11 8v6M8 11h6" />
                                    </svg></button>
                                @if ($portfolio->portfolio_images->count() > 1)
                                    <button type="button" class="dark-pd-gallery-nav dark-pd-gallery-nav-prev"
                                        id="pdGalleryPrev" aria-label="{{ __('Previous') }}"><svg viewBox="0 0 24 24">
                                            <path d="M15 6l-6 6 6 6" />
                                        </svg></button>
                                    <button type="button" class="dark-pd-gallery-nav dark-pd-gallery-nav-next"
                                        id="pdGalleryNext" aria-label="{{ __('Next') }}"><svg viewBox="0 0 24 24">
                                            <path d="M9 6l6 6-6 6" />
                                        </svg></button>
                                    <button type="button" class="dark-pd-gallery-nav dark-pd-gallery-play"
                                        id="pdGalleryPlay" aria-label="{{ __('Pause autoplay') }}">
                                        <svg class="icon-pause" viewBox="0 0 24 24" fill="currentColor" stroke="none">
                                            <rect x="6" y="5" width="4" height="14" rx="1" />
                                            <rect x="14" y="5" width="4" height="14" rx="1" />
                                        </svg>
                                        <svg class="icon-play" viewBox="0 0 24 24" fill="currentColor" stroke="none">
                                            <path d="M7 5l12 7-12 7z" />
                                        </svg>
                                    </button>
                                    <span class="dark-pd-gallery-counter" id="pdGalleryCounter">1 /
                                        {{ $portfolio->portfolio_images->count() }}</span>
                                @endif
                            </div>
                        </div>
                    @elseif (!empty($portfolio->featured_image))
                        <div class="dark-pd-gallery">
                            <div class="dark-pd-gallery-main">
                                <img id="pdMainImg" class="lazy is-kenburns" draggable="false"
                                    data-src="{{ asset('assets/front/img/portfolios/featured/' . $portfolio->featured_image) }}"
                                    alt="">
                                @include('front.default.partials.dark.portfolio-hero-overlay', [
                                    'portfolio' => $portfolio,
                                ])
                                <button type="button" class="dark-pd-gallery-nav dark-pd-gallery-zoom" id="pdGalleryZoom"
                                    aria-label="{{ __('Zoom') }}"><svg viewBox="0 0 24 24">
                                        <circle cx="11" cy="11" r="7" />
                                        <path d="M21 21l-4.35-4.35M11 8v6M8 11h6" />
                                    </svg></button>
                            </div>
                        </div>
                    @endif


                    {{-- Structured reference blocks (Problem -> Mission -> Expertise ->
             Solution -> Result -> Impact) — same shared partial used by the
             admin Aperçu preview and read-only eye modal (see the doc: "all
             ICA references will have exactly the same architecture").
             showIdentityRow is off here — Client/Sector/Country/Service/
             dates/cost/status already show in the sidebar above; the
             partial's own identity strip would just repeat it. --}}
                    <div class="dark-pd-identity-section">
                        @include('front.default.partials.dark.portfolio-identity-card', [
                            'portfolio' => $portfolio,
                            'showIdentityRow' => false,
                            'documentList' => [],
                        ])
                    </div>

                </div>

                <div>
                    <aside class="dark-pd-side-card">
                        @if (!empty($portfolio->client_name))
                            <div class="dark-pd-side-client-row">
                                <div>
                                    <span class="dark-pd-side-label">{{ __('Client Name') }}</span>
                                    <span
                                        class="dark-pd-side-client-name">{{ convertUtf8($portfolio->client_name) }}</span>
                                </div>
                                @if (!empty($portfolio->client_logo))
                                    <img class="dark-pd-side-logo"
                                        src="{{ asset('assets/front/img/portfolios/logos/' . $portfolio->client_logo) }}"
                                        alt="{{ convertUtf8($portfolio->client_name) }}">
                                @endif
                            </div>
                        @endif

                        <div class="dark-pd-side-grid">
                            @if (!empty($portfolio->sector))
                                <div>
                                    <span class="dark-pd-side-label">{{ __('Sector') }}</span>
                                    <span class="dark-pd-side-value">{{ convertUtf8($portfolio->sector->name) }}</span>
                                </div>
                            @endif

                            @if (!empty($portfolio->country))
                                <div>
                                    <span class="dark-pd-side-label">{{ __('Country') }}</span>
                                    <span class="dark-pd-side-value dark-pd-flag-chip"><span
                                            class="fi fi-{{ strtolower($portfolio->country) }}"></span>
                                        {{ $pdCountryName ?? $portfolio->country }}</span>
                                </div>
                            @endif

                            @if (!empty($portfolio->service->title))
                                <div class="dark-pd-side-full">
                                    <span class="dark-pd-side-label">{{ __('Service') }}</span>
                                    <span
                                        class="dark-pd-side-value dark-pd-side-value--dim">{{ convertUtf8($portfolio->service->title) }}</span>
                                </div>
                            @endif

                            @if ($portfolio->start_date)
                                @php $startDate = Carbon\Carbon::parse($portfolio->start_date); @endphp
                                <div>
                                    <span class="dark-pd-side-label"><i
                                            class="fas fa-calendar-alt dark-pd-side-icon"></i>{{ __('Start Date') }}</span>
                                    <span
                                        class="dark-pd-side-value">{{ date_format($startDate, app()->getLocale() == 'fr' ? 'd-m-Y' : 'M d, Y') }}</span>
                                </div>
                            @endif

                            @if ($portfolio->submission_date)
                                @php $submissionDate = Carbon\Carbon::parse($portfolio->submission_date); @endphp
                                <div>
                                    <span class="dark-pd-side-label"><i
                                            class="fas fa-calendar-check dark-pd-side-icon"></i>{{ __('End Date') }}</span>
                                    <span
                                        class="dark-pd-side-value">{{ date_format($submissionDate, app()->getLocale() == 'fr' ? 'd-m-Y' : 'M d, Y') }}</span>
                                </div>
                            @endif

                            @if ($pdCostFormatted)
                                <div class="dark-pd-side-full">
                                    <span class="dark-pd-side-label"><i
                                            class="fas fa-coins dark-pd-side-icon"></i>{{ __('Cost of Service') }}</span>
                                    <span class="dark-pd-side-value">
                                        @if ($bex?->base_currency_symbol_position == 'left')
                                            {{ $bex?->base_currency_symbol }} {{ $pdCostFormatted }}
                                        @else
                                            {{ $pdCostFormatted }} {{ $bex?->base_currency_symbol }}
                                        @endif
                                    </span>
                                </div>
                            @endif

                            @if (!empty($portfolio->statusInfo))
                                <div class="dark-pd-side-full">
                                    <span class="dark-pd-side-label">{{ __('Status') }}</span>
                                    <span class="dark-pd-status-pill"><span class="dot"></span>
                                        {{ convertUtf8($portfolio->statusInfo->name) }}</span>
                                </div>
                            @endif
                        </div>

                        @if ($portfolio->website_link)
                            <a href="{{ $portfolio->website_link }}" class="dark-pd-demo-btn" target="_blank">
                                {{ __('Live Demo') }}
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                                    <path d="M7 17L17 7M7 7h10v10" />
                                </svg>
                            </a>
                        @endif

                        <a href="{{ route('front.contact') }}" class="dark-pd-demo-btn">
                            {{ __('Contact us about this project') }}
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                                <path d="M4 4h16v16H4z" opacity="0" />
                                <path d="M3 7l9 6 9-6M4 5h16a1 1 0 011 1v12a1 1 0 01-1 1H4a1 1 0 01-1-1V6a1 1 0 011-1z" />
                            </svg>
                        </a>
                    </aside>
                </div>
            </div>

            @php
                $pdHasGallery = $portfolio->portfolio_images->count() > 0;
                $pdHasDocs = !empty($documentList);
                $pdColCount = 1 + ($pdHasGallery ? 1 : 0) + ($pdHasDocs ? 1 : 0);
            @endphp
            <div class="dark-pd-desc-docs-row dark-pd-desc-docs-row--cols-{{ $pdColCount }}">
                <div class="reveal-left">
                    <div class="dark-pd-section-heading">
                        <span class="dark-pd-section-icon"><i class="fas fa-file-alt"></i></span>
                        {{ __('Project Description') }}
                    </div>
                    <div class="dark-pd-prose dark-service-details" id="pdProse">
                        {!! replaceBaseUrl(convertUtf8($portfolio->content)) !!}
                    </div>
                    <button type="button" class="dark-pd-read-more" id="pdReadMore" hidden>
                        <span>{{ __('Read more') }}</span>
                        <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor"
                            stroke-width="2.5">
                            <path d="M9 6l6 6-6 6" />
                        </svg>
                    </button>
                </div>

                @if ($pdHasGallery)
                    <div class="dark-pd-gallery-col">
                        <div class="dark-pd-gallery-panel">
                            <div class="dark-pd-section-heading">
                                <span class="dark-pd-section-icon"><i class="fas fa-images"></i></span>
                                {{ __('Gallery') }}
                            </div>
                            <div class="dark-pd-gallery-grid">
                                @foreach ($portfolio->portfolio_images as $key => $pi)
                                    <button type="button" class="dark-pd-gallery-grid-item"
                                        data-lightbox-index="{{ $key }}"
                                        aria-label="{{ __('Open image') }} {{ $key + 1 }}">
                                        <img class="lazy"
                                            data-src="{{ asset('assets/front/img/portfolios/sliders/' . $pi->image) }}"
                                            alt="">
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                @if ($pdHasDocs)
                    <div class="dark-pd-docs-col">
                        @include('front.default.partials.dark.portfolio-documents-list', [
                            'documentList' => $documentList,
                        ])
                    </div>
                @endif
            </div>

            @if (!empty($similarProjects) && $similarProjects->count() > 0)
                <div class="dark-pd-similar-section">
                    <h3 class="dark-pd-similar-title">{{ __('Similar projects') }} <span
                            class="dark-pd-similar-rule"></span></h3>
                    <div class="owl-carousel dark-pd-similar-carousel" id="pdSimilarCarousel">
                        @foreach ($similarProjects as $sp)
                            <a href="{{ route('front.portfoliodetails', $sp->slug) }}" class="dark-pd-similar-card">
                                <div class="dark-pd-similar-img">
                                    <img class="lazy"
                                        data-src="{{ asset('assets/front/img/portfolios/featured/' . $sp->featured_image) }}"
                                        alt="">
                                    @if (!empty($sp->sector))
                                        <span class="dark-pd-similar-badge">{{ convertUtf8($sp->sector->name) }}</span>
                                    @endif
                                </div>
                                <div class="dark-pd-similar-body">
                                    <h5>{{ convertUtf8($sp->title) }}</h5>
                                    <p>{{ convertUtf8($sp->client_name) }}{{ !empty($sp->country) ? ' — ' . ($countryNames[$sp->country] ?? $sp->country) : '' }}
                                    </p>
                                </div>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                                    <path d="M9 6l6 6-6 6" />
                                </svg>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Image lightbox — zoom (wheel/pinch/double-click/double-tap) + pan.
         Works for both the multi-image gallery above and the single
         featured-image case; see the script section for how the album is
         built either way. --}}
        <div class="dark-img-lightbox" id="pdImgLightbox">
            <button type="button" class="dark-img-lightbox-close" aria-label="{{ __('Close') }}">
                <svg viewBox="0 0 24 24">
                    <path d="M6 6l12 12M18 6L6 18" />
                </svg>
            </button>
            <button type="button" class="dark-img-lightbox-nav dark-img-lightbox-prev"
                aria-label="{{ __('Previous') }}">
                <svg viewBox="0 0 24 24">
                    <path d="M15 6l-6 6 6 6" />
                </svg>
            </button>
            <button type="button" class="dark-img-lightbox-nav dark-img-lightbox-next"
                aria-label="{{ __('Next') }}">
                <svg viewBox="0 0 24 24">
                    <path d="M9 6l6 6-6 6" />
                </svg>
            </button>
            <div class="dark-img-lightbox-stage">
                <img class="dark-img-lightbox-img" id="pdLightboxImg" src="" alt="">
            </div>
            <div class="dark-img-lightbox-zoombar">
                <button type="button" class="dark-img-lightbox-zbtn" data-zoom-out
                    aria-label="{{ __('Zoom out') }}">−</button>
                <button type="button" class="dark-img-lightbox-zbtn dark-img-lightbox-zreset" data-zoom-reset
                    aria-label="{{ __('Reset zoom') }}">1:1</button>
                <button type="button" class="dark-img-lightbox-zbtn" data-zoom-in
                    aria-label="{{ __('Zoom in') }}">+</button>
            </div>
            <p class="dark-img-lightbox-hint">{{ __('Scroll or pinch to zoom · drag to pan') }}</p>
        </div>
        <!--    dark portfolio details section end   -->
    @else
        <!--    case details section start   -->
        <div class="case-details-section">
            <div class="container">
                <div class="row">
                    <div class="col-lg-7 col-xl-7">
                        <div class="project-ss-carousel owl-carousel owl-theme common-carousel">
                            @foreach ($portfolio->portfolio_images as $key => $pi)
                                <a href="#" class="single-ss" data-id="{{ $pi->id }}">
                                    <img class="lazy"
                                        data-src="{{ asset('assets/front/img/portfolios/sliders/' . $pi->image) }}"
                                        alt="">
                                </a>
                            @endforeach
                        </div>
                        @foreach ($portfolio->portfolio_images as $key => $pi)
                            <a id="singleMagnificSs{{ $pi->id }}" class="single-magnific-ss d-none"
                                href="{{ asset('assets/front/img/portfolios/sliders/' . $pi->image) }}"></a>
                        @endforeach
                        <div class="case-details reveal-left">
                            {!! replaceBaseUrl(convertUtf8($portfolio->content)) !!}
                        </div>
                    </div>
                    <!--    appoint section start   -->
                    <div class="col-lg-5 offset-xl-1 col-xl-4">
                        <div class="right-side">
                            <div class="row">
                                <div class="col-xl-12 col-lg-12 col-md-12">
                                    <div class="project-infos">
                                        <h3>{{ convertUtf8($portfolio->title) }}</h3>

                                        <div class="info-row">
                                            <div class="label">{{ __('Client Name') }}</div>
                                            <div class="colon">:</div>
                                            <div class="value">{{ convertUtf8($portfolio->client_name) }}</div>
                                        </div>

                                        @if (!empty($portfolio->service->title))
                                            <div class="info-row">
                                                <div class="label">{{ __('Service') }}</div>
                                                <div class="colon">:</div>
                                                <div class="value">{{ convertUtf8($portfolio->service->title) }}</div>
                                            </div>
                                        @endif

                                        @if ($portfolio->start_date)
                                            @php $startDate = Carbon\Carbon::parse($portfolio->start_date); @endphp
                                            <div class="info-row">
                                                <div class="label">{{ __('Start Date') }}</div>
                                                <div class="colon">:</div>
                                                <div class="value">
                                                    {{ date_format($startDate, app()->getLocale() == 'fr' ? 'd-m-Y' : 'M d, Y') }}
                                                </div>
                                            </div>
                                        @endif

                                        @if ($portfolio->submission_date)
                                            @php $submissionDate = Carbon\Carbon::parse($portfolio->submission_date); @endphp
                                            <div class="info-row">
                                                <div class="label">{{ __('End Date') }}</div>
                                                <div class="colon">:</div>
                                                <div class="value">
                                                    {{ date_format($submissionDate, app()->getLocale() == 'fr' ? 'd-m-Y' : 'M d, Y') }}
                                                </div>
                                            </div>
                                        @endif

                                        @if (!empty($portfolio->cost_of_service))
                                            <div class="info-row">
                                                <div class="label">{{ __('Cost of Service') }}</div>
                                                <div class="colon">:</div>
                                                <div class="value">
                                                    @php
                                                        $decimalSeparator = app()->getLocale() == 'fr' ? ',' : '.';
                                                        $thousandSeparator = app()->getLocale() == 'fr' ? ' ' : ',';

                                                        // Determine if the amount has decimals
                                                        if (
                                                            floor($portfolio->cost_of_service) ==
                                                            $portfolio->cost_of_service
                                                        ) {
                                                            // Whole number, no decimals
                                                            $formattedAmount = number_format(
                                                                $portfolio->cost_of_service,
                                                                0,
                                                                $decimalSeparator,
                                                                $thousandSeparator,
                                                            );
                                                        } else {
                                                            // Has decimals
                                                            $formattedAmount = number_format(
                                                                $portfolio->cost_of_service,
                                                                2,
                                                                $decimalSeparator,
                                                                $thousandSeparator,
                                                            );
                                                        }
                                                    @endphp

                                                    @if ($bex?->base_currency_symbol_position == 'left')
                                                        {{ $bex?->base_currency_symbol }} {{ $formattedAmount }}
                                                    @else
                                                        {{ $formattedAmount }} {{ $bex?->base_currency_symbol }}
                                                    @endif
                                                </div>
                                            </div>
                                        @endif


                                        @if (!empty($portfolio->statusInfo))
                                            <div class="info-row">
                                                <div class="label">{{ __('Status') }}</div>
                                                <div class="colon">:</div>
                                                <div class="value">{{ convertUtf8($portfolio->statusInfo->name) }}</div>
                                            </div>
                                        @endif

                                        @if ($portfolio->website_link)
                                            <div class="mt-2">
                                                <a href="{{ $portfolio->website_link }}"
                                                    class="btn base-bg text-white btn-sm" target="_blank">
                                                    {{ __('Live Demo') }}
                                                </a>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="subscribe-section">
                                        <span>{{ __('SUBSCRIBE') }}</span>
                                        <h3>{{ __('SUBSCRIBE FOR NEWSLETTER') }}</h3>
                                        <form id="subscribeForm" class="subscribe-form"
                                            action="{{ route('front.subscribe') }}" method="POST">
                                            @csrf
                                            <div class="form-element"><input name="email" type="email"
                                                    placeholder="{{ __('Email') }}"></div>
                                            <p id="erremail" class="text-danger mb-3 err-email"></p>
                                            <div class="form-element"><input type="submit"
                                                    value="{{ __('Subscribe') }}">
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--    appoint section end   -->
                </div>
            </div>
        </div>
        <!--    case details section end   -->
    @endif

@endsection

@section('scripts')
    @if ($be->theme_version == 'dark')
        <script>
            {{-- No thumbnail strip — the main image is the whole gallery here,
                 advanced by the prev/next buttons and autoplay; the separate
                 "Galerie d'images" panel further down the page (real thumbnail
                 grid) is what a visitor uses to jump straight to a given
                 photo. This replaces the old Owl-carousel-driven thumb strip
                 controller with a plain index over the image URL list. --}}
                (function() {
                    var mainImg = document.getElementById('pdMainImg');
                    var mainWrap = document.querySelector('.dark-pd-gallery-main');
                    if (!mainImg || !mainWrap) return;

                    var images = [];
                    try {
                        images = JSON.parse(mainWrap.getAttribute('data-images') || '[]');
                    } catch (e) {}
                    var n = images.length;
                    var index = 0;

                    // Set in admin/portfolio/settings (global, all languages).
                    var GALLERY_AUTOPLAY_MS = {{ (int) ($be->portfolio_details_gallery_speed ?? 7000) }};

                    var counter = document.getElementById('pdGalleryCounter');
                    var progressFill = document.getElementById('pdGalleryProgressFill');
                    var playBtn = document.getElementById('pdGalleryPlay');
                    var prevBtn = document.getElementById('pdGalleryPrev');
                    var nextBtn = document.getElementById('pdGalleryNext');

                    var hovering = false;
                    var manuallyPaused = false;
                    var timer = null;

                    if (progressFill) {
                        progressFill.style.setProperty('--dur', GALLERY_AUTOPLAY_MS + 'ms');
                    }

                    function updateProgressRunning() {
                        if (progressFill) progressFill.classList.toggle('is-running', !hovering && !manuallyPaused && n >
                            1);
                    }

                    // Only called on a genuine slide change — resets the bar to
                    // 0% for a fresh full window. Hover/pause never call this:
                    // they freeze/resume the running animation in place instead
                    // (updateProgressRunning), so mousing off and back on never
                    // resets progress mid-way.
                    function restartProgressBar() {
                        if (!progressFill) return;
                        progressFill.style.animation = 'none';
                        progressFill.classList.remove('is-running');
                        void progressFill.offsetWidth; // reflow so it restarts at 0%
                        progressFill.style.animation = '';
                        updateProgressRunning();
                    }

                    function stopTimer() {
                        if (timer) {
                            clearTimeout(timer);
                            timer = null;
                        }
                    }

                    function queueNext() {
                        stopTimer();
                        if (hovering || manuallyPaused || n < 2) return;
                        timer = setTimeout(function() {
                            goTo(index + 1);
                            queueNext();
                        }, GALLERY_AUTOPLAY_MS);
                    }

                    function showImage(newIndex) {
                        index = ((newIndex % n) + n) % n;
                        if (counter) counter.textContent = (index + 1) + ' / ' + n;

                        var full = images[index];
                        // Crossfade instead of a hard swap — preload off-screen so
                        // the fade-in only starts once the new image is actually
                        // ready, avoiding a flash of the old frame at full opacity.
                        mainImg.style.opacity = '0';
                        var preload = new Image();
                        preload.onload = function() {
                            mainImg.classList.remove('lazy');
                            mainImg.removeAttribute('data-src');
                            mainImg.src = full;
                            mainImg.style.opacity = '1';

                            // Restart the Ken Burns drift for the new image.
                            mainImg.classList.remove('is-kenburns');
                            void mainImg.offsetWidth;
                            mainImg.classList.add('is-kenburns');
                        };
                        preload.src = full;

                        restartProgressBar();
                    }

                    function goTo(newIndex) {
                        if (n < 2) return;
                        showImage(newIndex);
                    }

                    if (prevBtn) prevBtn.addEventListener('click', function() {
                        goTo(index - 1);
                        queueNext();
                    });
                    if (nextBtn) nextBtn.addEventListener('click', function() {
                        goTo(index + 1);
                        queueNext();
                    });

                    if (playBtn) {
                        playBtn.addEventListener('click', function() {
                            manuallyPaused = !manuallyPaused;
                            playBtn.classList.toggle('is-paused', manuallyPaused);
                            playBtn.setAttribute('aria-label', manuallyPaused ? '{{ __('Play autoplay') }}' :
                                '{{ __('Pause autoplay') }}');
                            if (manuallyPaused) {
                                stopTimer();
                            } else {
                                // The button sits inside mainWrap, so the pointer
                                // is still over it right after this click —
                                // without clearing `hovering` here, the bar would
                                // stay frozen until the mouse physically left and
                                // re-entered.
                                hovering = false;
                                queueNext();
                            }
                            updateProgressRunning();
                        });
                    }

                    mainWrap.addEventListener('mouseenter', function() {
                        hovering = true;
                        stopTimer();
                        updateProgressRunning();
                    });
                    mainWrap.addEventListener('mouseleave', function() {
                        hovering = false;
                        queueNext();
                        updateProgressRunning();
                    });

                    // Drag / swipe directly on the main image.
                    var dragging = false,
                        dragStartX = 0,
                        dx = 0,
                        dragMoved = false;

                    function pointerDown(e) {
                        dragging = true;
                        dx = 0;
                        dragMoved = false;
                        dragStartX = (e.touches ? e.touches[0].clientX : e.clientX);
                        mainWrap.classList.add('is-dragging');
                    }

                    function pointerMove(e) {
                        if (!dragging) return;
                        var x = (e.touches ? e.touches[0].clientX : e.clientX);
                        dx = x - dragStartX;
                        if (Math.abs(dx) > 8) dragMoved = true;
                    }

                    function pointerUp() {
                        if (!dragging) return;
                        dragging = false;
                        mainWrap.classList.remove('is-dragging');
                        if (Math.abs(dx) > 60) {
                            goTo(dx < 0 ? index + 1 : index - 1);
                            queueNext();
                        }
                    }
                    mainWrap.addEventListener('mousedown', pointerDown);
                    window.addEventListener('mousemove', pointerMove);
                    window.addEventListener('mouseup', pointerUp);
                    mainWrap.addEventListener('touchstart', pointerDown, {
                        passive: true
                    });
                    mainWrap.addEventListener('touchmove', pointerMove, {
                        passive: true
                    });
                    mainWrap.addEventListener('touchend', pointerUp);

                    // mainImg also has its own click-to-zoom listener (bound in
                    // the separate lightbox script below, on the same element —
                    // listeners on one element/event run in attachment order, so
                    // registering this one first lets it veto that one). Without
                    // it, ending a drag lands a normal 'click' on the image too,
                    // popping the lightbox open right after every swipe.
                    mainImg.addEventListener('click', function(e) {
                        if (dragMoved) {
                            e.stopImmediatePropagation();
                            e.preventDefault();
                        }
                    });

                    restartProgressBar();
                    queueNext();

                    // Read by the lightbox script below and by the separate
                    // "Galerie d'images" thumbnail-grid panel further down the
                    // page (clicking a grid thumb jumps the main image + opens
                    // the lightbox at that index).
                    window.__pdGallery = {
                        images: images,
                        goTo: goTo,
                        getIndex: function() {
                            return index;
                        }
                    };
                })();
        </script>

        {{-- Deliberately a separate, self-contained IIFE from the carousel
             one above — it works whether the gallery has multiple images or
             just one (featured image only), and shares state with the
             carousel script only through the small window.__pdGallery
             handle it exposes (image list + a goTo(index) to keep the main
             image in sync with whatever the lightbox last showed on
             close). Also opened directly, at a given index, by the
             thumbnail-grid "Galerie d'images" panel further down the page
             (window.__pdOpenLightbox). --}}
        <script>
            (function() {
                "use strict";
                var mainImg = document.getElementById('pdMainImg');
                var lb = document.getElementById('pdImgLightbox');
                if (!mainImg || !lb) return;

                var zoomBtn = document.getElementById('pdGalleryZoom');
                var gallery = window.__pdGallery;
                var album = (gallery && gallery.images && gallery.images.length) ?
                    gallery.images : [mainImg.getAttribute('data-src') || mainImg.src];

                var lbImg = document.getElementById('pdLightboxImg');
                var lbClose = lb.querySelector('.dark-img-lightbox-close');
                var lbPrevBtn = lb.querySelector('.dark-img-lightbox-prev');
                var lbNextBtn = lb.querySelector('.dark-img-lightbox-next');
                var lbStage = lb.querySelector('.dark-img-lightbox-stage');
                var zoomInBtn = lb.querySelector('[data-zoom-in]');
                var zoomOutBtn = lb.querySelector('[data-zoom-out]');
                var zoomResetBtn = lb.querySelector('[data-zoom-reset]');

                var MIN_SCALE = 1;
                var MAX_SCALE = 4;
                var lbIndex = 0;
                var scale = 1;
                var panX = 0;
                var panY = 0;

                function applyTransform() {
                    lbImg.style.transform = 'translate(' + panX + 'px,' + panY + 'px) scale(' + scale + ')';
                    lbImg.classList.toggle('is-zoomed', scale > 1);
                }

                function clampPan() {
                    var maxX = Math.max(0, (lbImg.offsetWidth * scale - lbImg.offsetWidth) / 2);
                    var maxY = Math.max(0, (lbImg.offsetHeight * scale - lbImg.offsetHeight) / 2);
                    panX = Math.max(-maxX, Math.min(maxX, panX));
                    panY = Math.max(-maxY, Math.min(maxY, panY));
                }

                function resetZoom() {
                    scale = 1;
                    panX = 0;
                    panY = 0;
                    applyTransform();
                }

                // Zooms toward a screen point (cursor, or a pinch midpoint)
                // instead of always toward image center — keeps whatever the
                // user is actually looking at stationary while the scale
                // changes, which is what "real" zoom controls do.
                function zoomToward(clientX, clientY, newScale) {
                    newScale = Math.max(MIN_SCALE, Math.min(MAX_SCALE, newScale));
                    if (newScale === scale) return;
                    var rect = lbImg.getBoundingClientRect();
                    var offsetX = clientX - (rect.left + rect.width / 2);
                    var offsetY = clientY - (rect.top + rect.height / 2);
                    var ratio = newScale / scale;
                    panX = offsetX - (offsetX - panX) * ratio;
                    panY = offsetY - (offsetY - panY) * ratio;
                    scale = newScale;
                    if (scale === MIN_SCALE) {
                        panX = 0;
                        panY = 0;
                    }
                    clampPan();
                    applyTransform();
                }

                function showLb(index) {
                    lbIndex = (index + album.length) % album.length;
                    resetZoom();
                    lbImg.src = album[lbIndex];
                    var multi = album.length > 1;
                    if (lbPrevBtn) lbPrevBtn.style.display = multi ? '' : 'none';
                    if (lbNextBtn) lbNextBtn.style.display = multi ? '' : 'none';
                }

                function currentAlbumIndex() {
                    var src = mainImg.getAttribute('src') || mainImg.getAttribute('data-src') || '';
                    var idx = album.indexOf(src);
                    return idx >= 0 ? idx : 0;
                }

                function openLb(index) {
                    showLb(typeof index === 'number' ? index : currentAlbumIndex());
                    lb.classList.add('is-open');
                    document.body.style.overflow = 'hidden';
                }

                function closeLb() {
                    lb.classList.remove('is-open');
                    document.body.style.overflow = '';
                    // Leave the underlying main image showing whatever was
                    // last viewed here — reuses the carousel script's own
                    // crossfade/progress-bar-restart logic rather than
                    // duplicating it.
                    if (gallery && gallery.goTo) gallery.goTo(lbIndex);
                }

                if (zoomBtn) zoomBtn.addEventListener('click', function() {
                    openLb();
                });
                mainImg.style.cursor = 'zoom-in';
                mainImg.addEventListener('click', function() {
                    openLb();
                });

                // Exposed for the "Galerie d'images" thumbnail-grid panel
                // further down the page — clicking a grid thumb opens
                // straight to that photo.
                window.__pdOpenLightbox = openLb;

                var galleryGridBtns = document.querySelectorAll('.dark-pd-gallery-grid-item[data-lightbox-index]');
                for (var gi = 0; gi < galleryGridBtns.length; gi++) {
                    (function(btn) {
                        btn.addEventListener('click', function() {
                            openLb(parseInt(btn.getAttribute('data-lightbox-index'), 10) || 0);
                        });
                    })(galleryGridBtns[gi]);
                }

                if (lbClose) lbClose.addEventListener('click', closeLb);
                lb.addEventListener('click', function(e) {
                    if (e.target === lb) closeLb();
                });
                if (lbNextBtn) lbNextBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    showLb(lbIndex + 1);
                });
                if (lbPrevBtn) lbPrevBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    showLb(lbIndex - 1);
                });

                document.addEventListener('keydown', function(e) {
                    if (!lb.classList.contains('is-open')) return;
                    if (e.key === 'Escape') closeLb();
                    else if (e.key === 'ArrowRight') showLb(lbIndex + 1);
                    else if (e.key === 'ArrowLeft') showLb(lbIndex - 1);
                });

                // ---- Explicit +/−/reset buttons (discoverable on touch too,
                // where there's no hover state to reveal the wheel-zoom hint) ----
                function centerZoom(delta) {
                    var rect = lbStage.getBoundingClientRect();
                    zoomToward(rect.left + rect.width / 2, rect.top + rect.height / 2, scale + delta);
                }
                if (zoomInBtn) zoomInBtn.addEventListener('click', function() {
                    centerZoom(0.6);
                });
                if (zoomOutBtn) zoomOutBtn.addEventListener('click', function() {
                    centerZoom(-0.6);
                });
                if (zoomResetBtn) zoomResetBtn.addEventListener('click', resetZoom);

                // ---- Desktop: wheel zoom (cursor-anchored), double-click
                // toggle, drag-to-pan once zoomed ----
                lbImg.addEventListener('wheel', function(e) {
                    e.preventDefault();
                    zoomToward(e.clientX, e.clientY, scale + (e.deltaY < 0 ? 0.3 : -0.3));
                }, {
                    passive: false
                });

                lbImg.addEventListener('dblclick', function(e) {
                    zoomToward(e.clientX, e.clientY, scale > 1 ? 1 : 2.5);
                });

                var dragging = false;
                var dragStartX = 0;
                var dragStartY = 0;
                var panStartX = 0;
                var panStartY = 0;

                lbImg.addEventListener('mousedown', function(e) {
                    if (scale <= 1) return;
                    dragging = true;
                    dragStartX = e.clientX;
                    dragStartY = e.clientY;
                    panStartX = panX;
                    panStartY = panY;
                    lbImg.classList.add('is-dragging');
                });
                window.addEventListener('mousemove', function(e) {
                    if (!dragging) return;
                    panX = panStartX + (e.clientX - dragStartX);
                    panY = panStartY + (e.clientY - dragStartY);
                    clampPan();
                    applyTransform();
                });
                window.addEventListener('mouseup', function() {
                    if (!dragging) return;
                    dragging = false;
                    lbImg.classList.remove('is-dragging');
                });

                // ---- Touch: pinch-to-zoom (anchored on the pinch midpoint),
                // double-tap toggle, single-finger pan once zoomed ----
                var pinch = null;
                var pan1 = null;
                var lastTapTime = 0;

                function touchDist(t) {
                    var dx = t[0].clientX - t[1].clientX;
                    var dy = t[0].clientY - t[1].clientY;
                    return Math.sqrt(dx * dx + dy * dy);
                }

                lbImg.addEventListener('touchstart', function(e) {
                    if (e.touches.length === 2) {
                        pan1 = null;
                        pinch = {
                            startDist: touchDist(e.touches),
                            startScale: scale,
                            startPanX: panX,
                            startPanY: panY,
                            midX: (e.touches[0].clientX + e.touches[1].clientX) / 2,
                            midY: (e.touches[0].clientY + e.touches[1].clientY) / 2
                        };
                    } else if (e.touches.length === 1) {
                        var now = Date.now();
                        if (now - lastTapTime < 300) {
                            zoomToward(e.touches[0].clientX, e.touches[0].clientY, scale > 1 ? 1 : 2.5);
                            pan1 = null;
                        } else if (scale > 1) {
                            pan1 = {
                                startX: e.touches[0].clientX,
                                startY: e.touches[0].clientY,
                                startPanX: panX,
                                startPanY: panY
                            };
                        }
                        lastTapTime = now;
                    }
                }, {
                    passive: true
                });

                lbImg.addEventListener('touchmove', function(e) {
                    if (pinch && e.touches.length === 2) {
                        e.preventDefault();
                        var factor = touchDist(e.touches) / pinch.startDist;
                        var newScale = Math.max(MIN_SCALE, Math.min(MAX_SCALE, pinch.startScale * factor));
                        var ratio = newScale / pinch.startScale;
                        panX = pinch.midX - (pinch.midX - pinch.startPanX) * ratio;
                        panY = pinch.midY - (pinch.midY - pinch.startPanY) * ratio;
                        scale = newScale;
                        if (scale === MIN_SCALE) {
                            panX = 0;
                            panY = 0;
                        }
                        clampPan();
                        applyTransform();
                    } else if (pan1 && e.touches.length === 1) {
                        e.preventDefault();
                        panX = pan1.startPanX + (e.touches[0].clientX - pan1.startX);
                        panY = pan1.startPanY + (e.touches[0].clientY - pan1.startY);
                        clampPan();
                        applyTransform();
                    }
                }, {
                    passive: false
                });

                lbImg.addEventListener('touchend', function(e) {
                    if (e.touches.length === 0) {
                        pinch = null;
                        pan1 = null;
                    } else if (e.touches.length === 1) {
                        pinch = null;
                    }
                });
            })();
        </script>

        <script>
            (function() {
                var $ = window.jQuery;
                var $track = $('#pdSimilarCarousel');
                if (!$ || !$track.length) return;

                $track.owlCarousel({
                    loop: true,
                    margin: 20,
                    nav: true,
                    dots: false,
                    autoplay: true,
                    autoplayTimeout: 4500,
                    autoplayHoverPause: true,
                    smartSpeed: 600,
                    responsive: {
                        0: {
                            items: 1
                        },
                        576: {
                            items: 2
                        },
                        992: {
                            items: 3
                        },
                        1200: {
                            items: 4
                        }
                    }
                });
            })();
        </script>

        <script>
            (function() {
                var prose = document.getElementById('pdProse');
                var btn = document.getElementById('pdReadMore');
                if (!prose || !btn) return;

                var readMoreLabel = btn.querySelector('span');
                var readMoreText = readMoreLabel.textContent;
                var showLessText = '{{ __('Show less') }}';

                // Only clamp (and only show the button) when the rich-text
                // content actually overflows the collapsed height — a short
                // description should never show a "Read more" that does nothing.
                if (prose.scrollHeight > prose.clientHeight + 4) {
                    prose.classList.add('is-clamped');
                    btn.hidden = false;

                    btn.addEventListener('click', function() {
                        var expanded = prose.classList.toggle('is-expanded');
                        prose.classList.toggle('is-clamped', !expanded);
                        btn.classList.toggle('is-open', expanded);
                        readMoreLabel.textContent = expanded ? showLessText : readMoreText;
                    });
                }
            })();
        </script>
    @endif
@endsection
