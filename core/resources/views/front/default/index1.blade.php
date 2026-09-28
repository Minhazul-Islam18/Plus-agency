@extends('front.default.layout')

@section('meta-keywords', "$be->home_meta_keywords")
@section('meta-description', "$be->home_meta_description")


@section('content')
    <!--   hero area start   -->
    @if ($be->theme_version == 'dark')
        @if ($bs->home_version == 'slider')
            @includeif('front.default.partials.dark.hero-slider')
        @else
            @includeif('front.default.partials.dark.hero')
        @endif
    @elseif ($bs->home_version == 'static')
        @includeif('front.default.partials.static')
    @elseif ($bs->home_version == 'slider')
        @includeif('front.default.partials.slider')
    @elseif ($bs->home_version == 'video')
        @includeif('front.default.partials.video')
    @elseif ($bs->home_version == 'particles')
        @includeif('front.default.partials.particles')
    @elseif ($bs->home_version == 'water')
        @includeif('front.default.partials.water')
    @elseif ($bs->home_version == 'parallax')
        @includeif('front.default.partials.parallax')
    @endif
    <!--   hero area end    -->

    <!--    introduction area start   -->
    {{--
        .intro-section-backdrop carries the bg-image/overlay/fallback color and
        is offset by +213px whenever .has-features shifts the section itself
        by -213px, so the backdrop's own paint area always starts exactly at
        the hero's real bottom edge — it can never bleed upward over the hero,
        regardless of whether a bg image/overlay is set. Only the feature
        cards (own opaque background-color) are meant to render in that
        213px overlap band. See style.css .intro-section rules.
    --}}
    @if ($be->theme_version == 'dark')
        @includeif('front.default.partials.dark.intro')
    @else
    <div class="intro-section reveal-stagger {{ $bs->feature_section == 1 ? 'has-features' : '' }}">
        <div class="intro-section-backdrop"
            @if (!empty($be->intro_section_bg)) style="background-image: url('{{ asset('assets/front/img/' . $be->intro_section_bg) }}');" @endif>
            @if (!empty($be->intro_section_bg))
                <div class="intro-section-overlay"
                    style="background-color: #{{ $be->intro_overlay_color ?? '000000' }}; opacity: {{ $be->intro_overlay_opacity ?? '0.6' }};">
                </div>
            @endif
        </div>
        <div class="container" style="position: relative; z-index: 2;">
            @if ($bs->feature_section == 1)
                <div class="hero-features">
                    <div class="row">
                        @foreach ($features as $key => $feature)
                            <style>
                                .sf{{ $feature->id }}::after {
                                    background-color: #{{ $feature->color }};
                                }
                            </style>
                            <div class="col-md-3 col-sm-6 single-hero-feature sf{{ $feature->id }}"
                                style="background-color: #{{ $feature->color }};">
                                <div class="outer-container">
                                    <div class="inner-container">
                                        <div class="icon-wrapper">
                                            <i class="{{ $feature->icon }}"></i>
                                        </div>
                                        <h3>{{ convertUtf8($feature->title) }}</h3>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($bs->intro_section == 1)
                <div class="row">
                    <div class="col-lg-6 {{ $rtl == 1 ? 'pl-lg-0' : 'pr-lg-0' }}">
                        <div class="intro-txt">
                            <span class="section-title">{{ convertUtf8($bs->intro_section_title) }}</span>
                            <h2 class="section-summary">{{ convertUtf8($bs->intro_section_text) }} </h2>
                            @if (!empty($bs->intro_section_button_url) && !empty($bs->intro_section_button_text))
                                <a href="{{ $bs->intro_section_button_url }}" class="intro-btn"
                                    target="_blank"><span>{{ convertUtf8($bs->intro_section_button_text) }}</span></a>
                            @endif
                        </div>
                    </div>
                    <div class="col-lg-6 {{ $rtl == 1 ? 'pr-lg-0' : 'pl-lg-0' }} px-md-3 px-0">
                        <div class="intro-bg"
                            style="@if (!empty($bs->intro_bg)) background-image: url('{{ asset('assets/front/img/' . $bs->intro_bg) }}'); @endif background-size: cover; position: relative;">
                            @if (!empty($bs->intro_section_video_link))
                                <a id="play-video" class="video-play-button" href="{{ $bs->intro_section_video_link }}"
                                    style="position: relative; z-index: 2;">
                                    <span></span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
    @endif
    <!--    introduction area end   -->


    {{-- From here down, order is deliberately different per theme (dark theme's
         order was chosen for a credibility narrative: capability → method →
         proof → people → validation → live opportunities → conversion; light
         theme's order was specified directly) — so the two themes get fully
         separate top-to-bottom sequences instead of one shared linear order.
         Dark theme's Partners lives inside Hero's marquee (not a section here);
         light theme's Partners is its own section, positioned after Team. --}}
    @if ($be->theme_version == 'dark')

        {{-- Tenders is the primary revenue line, not a secondary mention —
             placed right after the capability ledger so it's visible near
             the top, not buried below Approach/Statistics/Portfolio/Team. --}}
        @if ($bs->tender_section == 1)
            @includeif('front.default.partials.dark.tenders')
        @endif

        @if ($bs->service_section == 1)
            @if (!serviceCategory())
                @includeif('front.default.partials.dark.services')
            @else
                @includeif('front.default.partials.dark.service-categories')
            @endif
        @endif

        @if ($bs->approach_section == 1)
            @includeif('front.default.partials.dark.approach')
        @endif

        @if ($bs->statistics_section == 1)
            @includeif('front.default.partials.dark.statistics')
        @endif

        @if ($bs->portfolio_section == 1)
            @includeif('front.default.partials.dark.portfolio')
        @endif

        @if ($bs->team_section == 1)
            @includeif('front.default.partials.dark.team')
        @endif

        @if ($bs->testimonial_section == 1)
            @includeif('front.default.partials.dark.testimonials')
        @endif

        @if ($bs->news_section == 1)
            @includeif('front.default.partials.dark.blog')
        @endif

        @if ($bs->call_to_action_section == 1)
            @includeif('front.default.partials.dark.cta')
        @endif

    @else

        @if ($bs->service_section == 1)
            @if (!serviceCategory())
                <!--   service section start   -->
                <section class="services-area pb-130"
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
                                <span class="section-title">{{ convertUtf8($bs->service_section_title) }}</span>
                                <h2 class="section-summary">{{ convertUtf8($bs->service_section_subtitle) }}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="container" style="position: relative; z-index: 2;">
                        <div class="row justify-content-center">
                            @foreach ($services as $key => $service)
                                <div class="col-lg-4 col-md-6 col-sm-8">
                                    <div class="services-item mt-30 reveal-card" style="--d:{{ ($key % 3) * 0.1 }}s">
                                        <div class="services-thumb">
                                            <img class="lazy"
                                                data-src="{{ asset('assets/front/img/services/' . $service->main_image) }}"
                                                alt="service" />
                                        </div>
                                        <div class="services-content">
                                            <a class="title"
                                                @if ($service->details_page_status == 1) href="{{ route('front.servicedetails', $service->slug) }}" @endif>
                                                <h4>{{ $service->title }}</h4>
                                            </a>

                                            <p>
                                                @if (strlen($service->summary) > 120)
                                                    {{ mb_substr($service->summary, 0, 120, 'utf-8') }}<span
                                                        style="display: none;">{{ mb_substr($service->summary, 120, null, 'utf-8') }}</span>
                                                    <a href="#" class="see-more">{{ __('see more') }}...</a>
                                                @else
                                                    {{ $service->summary }}
                                                @endif
                                            </p>

                                            @if ($service->details_page_status == 1)
                                                <a href="{{ route('front.servicedetails', $service->slug) }}">{{ __('Read More') }}
                                                    <i class="fas fa-plus"></i></a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
                <!--   service section end   -->
            @else
                <!--   service category section start   -->
                <div class="service-categories"
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
                                <span class="section-title">{{ convertUtf8($bs->service_section_title) }}</span>
                                <h2 class="section-summary">{{ convertUtf8($bs->service_section_subtitle) }}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="container">
                        <div class="row">
                            @foreach ($scategories as $key => $scategory)
                                <div class="col-xl-3 col-lg-4 col-sm-6">
                                    <div class="single-category reveal-card" style="--d:{{ ($key % 4) * 0.1 }}s">
                                        @if (!empty($scategory->image))
                                            <div class="img-wrapper">
                                                <img class="lazy"
                                                    data-src="{{ asset('assets/front/img/service_category_icons/' . $scategory->image) }}"
                                                    alt="">
                                            </div>
                                        @endif
                                        <div class="text">
                                            <h4>{{ convertUtf8($scategory->name) }}</h4>
                                            <p>
                                                @if (strlen($scategory->short_text) > 112)
                                                    {{ mb_substr($scategory->short_text, 0, 112, 'utf-8') }}<span
                                                        style="display: none;">{{ mb_substr($scategory->short_text, 112, null, 'utf-8') }}</span>
                                                    <a href="#" class="see-more">{{ __('see more') }}...</a>
                                                @else
                                                    {{ $scategory->short_text }}
                                                @endif
                                            </p>
                                            <a href="{{ route('front.services', ['category' => $scategory->id]) }}"
                                                class="readmore">{{ __('View Services') }}</a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <!--   service category section end   -->
            @endif
        @endif


        @if ($bs->approach_section == 1)
            <!--   how we do section start   -->
            <div class="approach-section"
                style="@if (!empty($be->approach_section_bg)) background-image: url('{{ asset('assets/front/img/' . $be->approach_section_bg) }}');
                    background-size: cover;
                    background-position: center;
                    position: relative;
                    overflow: hidden; @endif">
                @if (!empty($be->approach_section_bg))
                    <div class="approach-overlay"
                        style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: #{{ $be->approach_overlay_color ?? '000000' }}; opacity: {{ $be->approach_overlay_opacity ?? '0.6' }}; z-index: 0; pointer-events: none;">
                    </div>
                @endif
                <div class="container" style="position: relative; z-index: 2;">
                    <div class="row">
                        <div class="col-lg-6 reveal-text">
                            <div class="approach-summary">
                                <span class="section-title">{{ convertUtf8($bs->approach_title) }}</span>
                                <h2 class="section-summary">{{ convertUtf8($bs->approach_subtitle) }}</h2>
                                @if (!empty($bs->approach_button_url) && !empty($bs->approach_button_text))
                                    <a href="{{ $bs->approach_button_url }}" class="boxed-btn"
                                        target="_blank"><span>{{ convertUtf8($bs->approach_button_text) }}</span></a>
                                @endif
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <ul class="approach-lists reveal-timeline">
                                @foreach ($points as $key => $point)
                                    <li class="single-approach reveal-timeline-item" style="--d:{{ $key * 0.15 }}s">
                                        <div class="approach-icon-wrapper"><i class="{{ $point->icon }}"></i></div>
                                        <div class="approach-text">
                                            <h4>{{ convertUtf8($point->title) }}</h4>
                                            <p>
                                                @if (strlen($point->short_text) > 150)
                                                    {{ mb_substr($point->short_text, 0, 150, 'utf-8') }}<span
                                                        style="display: none;">{{ mb_substr($point->short_text, 150, null, 'utf-8') }}</span>
                                                    <a href="#" class="see-more">{{ __('see more') }}...</a>
                                                @else
                                                    {{ $point->short_text }}
                                                @endif
                                            </p>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!--   how we do section end   -->
        @endif


        @if ($bs->tender_section == 1)
            <!--    tender section start   -->
            <div class="tender-showcase-section"
                style="@if (!empty($be->tender_section_bg)) background-image: url('{{ asset('assets/front/img/' . $be->tender_section_bg) }}');
                    background-size: cover;
                    background-position: center;
                    position: relative;
                    overflow: hidden; @endif">
                @if (!empty($be->tender_section_bg))
                    <div class="portfolio-overlay"
                        style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: #{{ $be->tender_overlay_color ?? '000000' }}; opacity: {{ $be->tender_overlay_opacity ?? '0.6' }}; z-index: 0; pointer-events: none;">
                    </div>
                @endif
                <div class="container" style="position: relative; z-index: 2;">
                    <div class="row text-center">
                        <div class="col-lg-6 offset-lg-3 reveal-text">
                            <span class="section-title">{{ convertUtf8($bs->tender_section_title) }}</span>
                            <h2 class="section-summary">{{ convertUtf8($bs->tender_section_text) }}</h2>
                        </div>
                    </div>
                </div>
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="tender-carousel owl-carousel owl-theme common-carousel">
                                @foreach ($tenders as $key => $tender)
                                    <div class="tender-card reveal-card" style="--d:{{ ($key % 3) * 0.1 }}s">
                                        <div class="card-img">
                                            @if (!empty($tender->tender_image))
                                                <img data-src="{{ asset('assets/front/img/tenders/' . $tender->tender_image) }}"
                                                    class="lazy" alt="">
                                            @else
                                                <img src="{{ asset('assets/admin/img/noimage.jpg') }}" alt="">
                                            @endif
                                            <div class="overlay">
                                                @if (!empty($tender->tenderCategory))
                                                    <span class="cat-badge">{{ convertUtf8($tender->tenderCategory->name) }}</span>
                                                @endif
                                            </div>
                                            @if ($tender->submission_deadline)
                                                <span class="deadline-badge">
                                                    <i class="far fa-clock"></i>
                                                    {{ \Carbon\Carbon::parse($tender->submission_deadline)->format('d M Y') }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="card-body">
                                            <h4>
                                                <a href="{{ route('tender_details', [$tender->slug]) }}">
                                                    {{ strlen($tender->title) > 55 ? mb_substr($tender->title, 0, 55, 'utf-8') . '...' : $tender->title }}
                                                </a>
                                            </h4>
                                            <div class="meta">
                                                <span class="country">
                                                    <i class="fas fa-map-marker-alt"></i> {{ $tender->country }}
                                                </span>
                                                <span class="price {{ is_null($tender->current_price) ? 'free' : '' }}">
                                                    @if (is_null($tender->current_price))
                                                        {{ __('Free') }}
                                                    @else
                                                        {{ optional($bse)->base_currency_symbol_position == 'left' ? optional($bse)->base_currency_symbol : '' }}
                                                        {{ number_format($tender->current_price, 0) }}
                                                        {{ optional($bse)->base_currency_symbol_position == 'right' ? ' ' . optional($bse)->base_currency_symbol : '' }}
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @if (Route::has('tenders'))
                        <div class="row">
                            <div class="col-12 text-center mt-4">
                                <a href="{{ route('tenders') }}" class="boxed-btn">
                                    <span>{{ __('View All Tenders') }}</span>
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            <!--    tender section end   -->
        @endif


        @if ($bs->statistics_section == 1)
            <!--    statistics section start    -->
            <div class="statistics-section"
                @if ($bs->home_version != 'parallax') style="background-image: url('{{ asset('assets/front/img/' . $be->statistics_bg) }}'); background-size:cover; position: relative; overflow: hidden;" @else style="position: relative; overflow: hidden;" @endif
                id="statisticsSection"
                @if ($bs->home_version == 'parallax') data-parallax="scroll" data-speed="0.2" data-image-src="{{ asset('assets/front/img/' . $be->statistics_bg) }}" @endif>
                <div class="statistics-overlay"
                    style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: #{{ $be->statistics_overlay_color ?? '000000' }}; opacity: {{ $be->statistics_overlay_opacity ?? '0.6' }}; z-index: 0; pointer-events: none;">
                </div>
                <div class="statistics-container" style="position: relative; z-index: 2;">
                    <div class="container">
                        <div class="row no-gutters">
                            @foreach ($statistics as $key => $statistic)
                                <div class="col-lg-3 col-md-6 reveal-card" style="--d:{{ ($key % 4) * 0.1 }}s">
                                    <div class="round" data-value="1" data-number="{{ convertUtf8($statistic->quantity) }}"
                                        data-size="200" data-thickness="6"
                                        data-fill="{
                         &quot;color&quot;: &quot;#{{ $bs->base_color }}&quot;
                         }">
                                        <strong></strong>
                                        <h5><i class="{{ $statistic->icon }}"></i> {{ convertUtf8($statistic->title) }}</h5>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <!--    statistics section end    -->
        @endif


        @if ($bs->portfolio_section == 1)
            <!--    case section start   -->
            <div class="case-section"
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
                    <div class="row text-center">
                        <div class="col-lg-6 offset-lg-3 reveal-text">
                            <span class="section-title">{{ convertUtf8($bs->portfolio_section_title) }}</span>
                            <h2 class="section-summary">{{ convertUtf8($bs->portfolio_section_text) }}</h2>
                        </div>
                    </div>
                </div>
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="case-carousel owl-carousel owl-theme">
                                @foreach ($portfolios as $key => $portfolio)
                                    <div class="single-case single-case-bg-1 reveal-card"
                                        style="--d:{{ ($key % 3) * 0.1 }}s; background-image: url('{{ asset('assets/front/img/portfolios/featured/' . $portfolio->featured_image) }}');">
                                        <div class="outer-container">
                                            <div class="inner-container">
                                                <h4>{{ strlen($portfolio->title) > 36 ? mb_substr($portfolio->title, 0, 36, 'utf-8') . '...' : $portfolio->title }}
                                                </h4>
                                                @if (!empty($portfolio->service))
                                                    <p>{{ $portfolio->service->title }}</p>
                                                @endif

                                                <a href="{{ route('front.portfoliodetails', $portfolio->slug) }}"
                                                    class="readmore-btn"><span>{{ __('Read More') }}</span></a>

                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--    case section end   -->
        @endif


        @if ($bs->team_section == 1)
            <!--    team section start   -->
            <div class="team-section section-padding"
                @if ($bs->home_version != 'parallax') style="background-image: url('{{ asset('assets/front/img/' . $bs->team_bg) }}'); background-size:cover; position: relative; overflow: hidden;" @endif
                @if ($bs->home_version == 'parallax') data-parallax="scroll" data-speed="0.2" data-image-src="{{ asset('assets/front/img/' . $bs->team_bg) }}" style="position: relative; overflow: hidden;" @endif>
                @if (!empty($bs->team_bg))
                    <div class="team-overlay"
                        style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: #{{ $be->team_overlay_color ?? '000000' }}; opacity: {{ $be->team_overlay_opacity ?? '0.6' }}; z-index: 0; pointer-events: none;">
                    </div>
                @endif
                <div class="team-content" style="position: relative; z-index: 2;">
                    <div class="container">
                        <div class="row text-center">
                            <div class="col-lg-6 offset-lg-3 reveal-text">
                                <span class="section-title">{{ convertUtf8($bs->team_section_title) }}</span>
                                <h2 class="section-summary">{{ convertUtf8($bs->team_section_subtitle) }}</h2>
                            </div>
                        </div>
                        <div class="row">
                            <div class="team-carousel common-carousel owl-carousel owl-theme">
                                @foreach ($members as $key => $member)
                                    <div class="single-team-member team-clickable reveal-card" style="--d:{{ ($key % 4) * 0.1 }}s" data-member-id="{{ $member->id }}"
                                        role="button" tabindex="0">
                                        <div class="team-img-wrapper">
                                            <img class="lazy"
                                                data-src="{{ asset('assets/front/img/members/' . $member->image) }}"
                                                alt="">
                                            <span class="team-view-hint" aria-label="{{ __('View Profile') }}"><i class="fas fa-plus"></i></span>
                                            <div class="social-accounts">
                                                <ul class="social-account-lists">
                                                    @if (!empty($member->facebook))
                                                        <li class="single-social-account"><a
                                                                href="{{ $member->facebook }}"><i
                                                                        class="fab fa-facebook-f"></i></a></li>
                                                    @endif
                                                    @if (!empty($member->twitter))
                                                        <li class="single-social-account"><a href="{{ $member->twitter }}"><i
                                                                    class="fab fa-twitter"></i></a></li>
                                                    @endif
                                                    @if (!empty($member->linkedin))
                                                        <li class="single-social-account"><a
                                                                href="{{ $member->linkedin }}"><i
                                                                        class="fab fa-linkedin-in"></i></a></li>
                                                    @endif
                                                    @if (!empty($member->whatsapp_link))
                                                        <li class="single-social-account"><a
                                                                href="{{ $member->whatsapp_link }}" target="_blank"><i
                                                                        class="fab fa-whatsapp"></i></a></li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="member-info">
                                            <h5 class="member-name">{{ convertUtf8($member->name) }}</h5>
                                            <small>{{ convertUtf8($member->rank) }}</small>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--    team section end   -->
            @include('front.partials.team-member-modal')
        @endif


        @if ($bs->partner_section == 1)
            <!--   partner section start    -->
            <div class="partner-section responsive-section-padding"
                @if (!empty($be->partner_bg)) style="background-image: url('{{ asset('assets/front/img/' . $be->partner_bg) }}'); background-size:cover; background-position: center; position: relative; overflow: hidden;" @endif>
                @if (!empty($be->partner_bg))
                    <div class="partner-overlay"
                        style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: #{{ $be->partner_overlay_color ?? '000000' }}; opacity: {{ $be->partner_overlay_opacity ?? '0.6' }}; z-index: 0; pointer-events: none;">
                    </div>
                @endif
                <div class="container" style="position: relative; z-index: 2;">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="partner-carousel owl-carousel owl-theme common-carousel">
                                @foreach ($partners as $key => $partner)
                                    <a class="single-partner-item d-block reveal-card" style="--d:{{ ($key % 5) * 0.06 }}s" href="{{ $partner->url }}" target="_blank">
                                        <div class="outer-container">
                                            <div class="inner-container">
                                                <img class="lazy"
                                                    data-src="{{ asset('assets/front/img/partners/' . $partner->image) }}"
                                                    alt="">
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--   partner section end    -->
        @endif


        @if ($bs->testimonial_section == 1)
            <!--   Testimonial section start    -->
            <div class="testimonial-section pb-115"
                style="@if (!empty($be->testimonial_section_bg)) background-image: url('{{ asset('assets/front/img/' . $be->testimonial_section_bg) }}');
                    background-size: cover;
                    background-position: center;
                    position: relative;
                    overflow: hidden; @endif">
                @if (!empty($be->testimonial_section_bg))
                    <div class="testimonial-overlay"
                        style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: #{{ $be->testimonial_overlay_color ?? '000000' }}; opacity: {{ $be->testimonial_overlay_opacity ?? '0.6' }}; z-index: 0; pointer-events: none;">
                    </div>
                @endif
                <div class="container" style="position: relative; z-index: 2;">
                    <div class="row text-center">
                        <div class="col-lg-6 offset-lg-3 reveal-text">
                            <span class="section-title">{{ convertUtf8($bs->testimonial_title) }}</span>
                            <h2 class="section-summary">{{ convertUtf8($bs->testimonial_subtitle) }}</h2>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="testimonial-carousel owl-carousel owl-theme">
                                @foreach ($testimonials as $key => $testimonial)
                                    <div class="single-testimonial reveal-card" style="--d:{{ ($key % 3) * 0.1 }}s">
                                        <div class="img-wrapper"><img class="lazy"
                                                data-src="{{ asset('assets/front/img/testimonials/' . $testimonial->image) }}"
                                                alt=""></div>
                                        <div class="client-desc">
                                            <p class="comment">{{ convertUtf8($testimonial->comment) }}</p>
                                            <h6 class="name">{{ convertUtf8($testimonial->name) }}</h6>
                                            <p class="rank">{{ convertUtf8($testimonial->rank) }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--   Testimonial section end    -->
        @endif


        @if ($bs->news_section == 1)
            <!--    blog section start   -->
            <div class="blog-section section-padding"
                @if (!empty($be->blog_bg)) style="background-image: url('{{ asset('assets/front/img/' . $be->blog_bg) }}'); background-size:cover; position: relative; overflow: hidden;" @endif>
                @if (!empty($be->blog_bg))
                    <div class="blog-overlay"
                        style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: #{{ $be->blog_overlay_color ?? '000000' }}; opacity: {{ $be->blog_overlay_opacity ?? '0.6' }}; z-index: 0; pointer-events: none;">
                    </div>
                @endif
                <div class="container" style="position: relative; z-index: 2;">
                    <div class="row text-center">
                        <div class="col-lg-6 offset-lg-3 reveal-text">
                            <span class="section-title">{{ convertUtf8($bs->blog_section_title) }}</span>
                            <h2 class="section-summary">{{ convertUtf8($bs->blog_section_subtitle) }}</h2>
                        </div>
                    </div>
                    <div class="blog-carousel owl-carousel owl-theme common-carousel">
                        @foreach ($blogs as $key => $blog)
                            <div class="single-blog reveal-card" style="--d:{{ ($key % 3) * 0.1 }}s">
                                <div class="blog-img-wrapper">
                                    <img class="lazy"
                                        data-src="{{ asset('assets/front/img/blogs/' . $blog->main_image) }}"
                                        alt="">
                                </div>
                                <div class="blog-txt">
                                    @php
                                        $blogDate = \Carbon\Carbon::parse($blog->created_at)->locale("$currentLang->code");
                                        $blogDate = $blogDate->translatedFormat('jS F, Y');
                                    @endphp

                                    <p class="date"><small>{{ __('By') }} <span
                                                class="username">{{ convertUtf8($blog->author_name) }}</span></small> |
                                        <small>{{ $blogDate }}</small>
                                    </p>

                                    <h4 class="blog-title"><a
                                            href="{{ route('front.blogdetails', $blog->slug) }}">{{ strlen($blog->title) > 40 ? mb_substr($blog->title, 0, 40, 'utf-8') . '...' : $blog->title }}</a>
                                    </h4>


                                    <p class="blog-summary">{!! strlen(strip_tags($blog->content)) > 100
                                        ? mb_substr(strip_tags($blog->content), 0, 100, 'utf-8') . '...'
                                        : strip_tags($blog->content) !!}</p>


                                    <a href="{{ route('front.blogdetails', $blog->slug) }}"
                                        class="readmore-btn"><span>{{ __('Read More') }}</span></a>

                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <!--    blog section end   -->
        @endif


        @if ($bs->call_to_action_section == 1)
            <!--    call to action section start    -->
            <div class="cta-section reveal-cta"
                style="background-image: url('{{ asset('assets/front/img/' . $bs->cta_bg) }}'); background-size: cover; position: relative; overflow: hidden;">
                @if (!empty($bs->cta_bg))
                    <div class="cta-overlay"
                        style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: #{{ $be->cta_overlay_color ?? '000000' }}; opacity: {{ $be->cta_overlay_opacity ?? '0.6' }}; z-index: 0; pointer-events: none;">
                    </div>
                @endif
                <div class="container" style="position: relative; z-index: 2;">
                    <div class="cta-content">
                        <div class="row">
                            <div class="col-md-9 col-lg-7">
                                <h3>{{ convertUtf8($bs->cta_section_text) }}</h3>
                            </div>
                            <div class="col-md-3 col-lg-5 contact-btn-wrapper">
                                <a href="{{ $bs->cta_section_button_url }}"
                                    class="boxed-btn contact-btn"><span>{{ convertUtf8($bs->cta_section_button_text) }}</span></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--    call to action section end    -->
        @endif

    @endif

@endsection

@section('scripts')
<script>
    // The hero can be a JS-sized carousel and the card row's text wraps to a
    // variable number of lines, so neither the hero's nor the card row's
    // height is a fixed number — measure the card row and feed its real
    // height to CSS (--intro-overlap) so the cards' bottom edge always lands
    // exactly on the hero/intro boundary, whatever that height turns out to be.
    (function () {
        function syncIntroOverlap() {
            var section = document.querySelector('.intro-section.has-features');
            var features = section && section.querySelector('.hero-features');
            if (!section || !features) return;
            section.style.setProperty('--intro-overlap', features.offsetHeight + 'px');
        }
        document.addEventListener('DOMContentLoaded', syncIntroOverlap);
        window.addEventListener('load', syncIntroOverlap);
        window.addEventListener('resize', syncIntroOverlap);
    })();
</script>
@endsection
