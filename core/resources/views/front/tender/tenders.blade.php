@extends("front.$version.layout")

@section('pagename')
    - {{ __('All Tenders') }}
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/front/css/jquery-ui.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/front/css/nice-select.css') }}">
    <style>
        .nice-select {
            width: 100%;
        }

        .tender-card {
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 15px rgba(0, 0, 0, .08);
            margin-bottom: 30px;
            transition: transform .3s, box-shadow .3s;
        }

        .tender-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, .12);
        }

        .tender-card .card-img {
            position: relative;
            overflow: hidden;
            height: 200px;
        }

        .tender-card .card-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform .4s;
        }

        .tender-card:hover .card-img img {
            transform: scale(1.06);
        }

        .tender-card .card-img .overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, transparent 50%, rgba(0, 0, 0, .6));
            display: flex;
            align-items: flex-end;
            padding: 12px;
        }

        .tender-card .card-img .cat-badge {
            background: var(--main-color, #3498db);
            color: #fff;
            font-size: 11px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 20px;
            text-decoration: none;
        }

        .tender-card .card-img .deadline-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: rgba(231, 76, 60, .9);
            color: #fff;
            font-size: 11px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 20px;
        }

        .tender-card .card-body {
            padding: 18px 20px;
        }

        .tender-card .card-body h4 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 8px;
            line-height: 1.4;
        }

        .tender-card .card-body h4 a {
            color: inherit;
            text-decoration: none;
        }

        .tender-card .card-body h4 a:hover {
            color: var(--main-color, #3498db);
        }

        .tender-card .meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            color: #777;
            border-top: 1px solid #f0f0f0;
            padding-top: 12px;
            margin-top: 10px;
        }

        .tender-card .meta .country {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .tender-card .meta .price {
            font-weight: 700;
            font-size: 16px;
            color: var(--main-color, #3498db);
        }

        .tender-card .meta .price.free {
            color: #27ae60;
        }

        .tender-card .modules-count {
            font-size: 12px;
            color: #999;
            margin-top: 6px;
        }

        .tender-card .modules-count i {
            margin-right: 4px;
        }

        /* Featured slider */
        .tender-slide .slick-prev,
        .tender-slide .slick-next {
            z-index: 10;
        }

        /* Sidebar */
        .active-search {
            font-weight: 600;
        }

        .active-search a {
            color: var(--main-color, #3498db) !important;
        }
    </style>
@endsection

@section('breadcrumb-title', $bex->tender_title ?? __('Tenders'))
@section('breadcrumb-subtitle', $bex->tender_subtitle ?? __('Browse Public Tenders'))
@section('breadcrumb-link', __('Tenders'))

@if (!empty($bse->tender_breadcrumb_bg))
    @section('breadcrumb-bg', asset('assets/front/img/' . $bse->tender_breadcrumb_bg))
@endif
@if (!empty($bse->tender_breadcrumb_overlay_color))
    @section('breadcrumb-overlay-color', $bse->tender_breadcrumb_overlay_color)
@endif
@if (!empty($bse->tender_breadcrumb_overlay_opacity))
    @section('breadcrumb-overlay-opacity', $bse->tender_breadcrumb_overlay_opacity)
@endif

@section('content')

@if ($be->theme_version == 'dark')
    @php
        $darkTenderCatId = request()->input('category_id');
    @endphp
    @section('breadcrumb-ledger')
        <div class="dark-bc-ledger-item">
            <span class="dark-bc-ledger-num">{{ str_pad($tenderCount, 2, '0', STR_PAD_LEFT) }}</span>
            <span class="dark-bc-ledger-label">{{ __('Active Tenders') }}</span>
        </div>
        <div class="dark-bc-ledger-item">
            <span class="dark-bc-ledger-num">{{ str_pad(count($tender_categories), 2, '0', STR_PAD_LEFT) }}</span>
            <span class="dark-bc-ledger-label">{{ __('Categories') }}</span>
        </div>
    @endsection

    <div class="dark-tp-page">
        @if ($featured_tenders->count() > 0)
            <div class="dark-tp-spotlight">
                <div class="dark-tp-spotlight-head reveal-text">
                    <span class="dark-tp-spotlight-label">{{ __('Featured') }}</span>
                    @if ($featured_tenders->count() > 1)
                        <div class="dark-tp-spotlight-nav">
                            <button type="button" class="dark-tp-spotlight-arrow" id="darkTpSpotPrev" aria-label="{{ __('Previous') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M15 6l-6 6 6 6" /></svg></button>
                            <button type="button" class="dark-tp-spotlight-arrow" id="darkTpSpotNext" aria-label="{{ __('Next') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M9 6l6 6-6 6" /></svg></button>
                        </div>
                    @endif
                </div>
                <div class="dark-tp-spotlight-track-wrap">
                    <div class="dark-tp-spotlight-track" id="darkTpSpotTrack">
                        @foreach ($featured_tenders as $key => $ft)
                            @php
                                // A non-empty tender_image doesn't guarantee the file is actually
                                // on disk (real data has rows pointing at deleted/missing files) —
                                // check existence too, otherwise the thumb silently renders blank
                                // instead of falling back to the "no image" dossier-icon state.
                                $ftHasImage = !empty($ft->tender_image) && file_exists(base_path('../assets/front/img/tenders/' . $ft->tender_image));
                            @endphp
                            <a href="{{ route('tender_details', ['slug' => $ft->slug]) }}" class="dark-tender-card reveal-card" style="--d:{{ ($key % 3) * 0.1 }}s">
                                <div class="dark-tender-thumb @if (!$ftHasImage) no-image @endif"
                                    @if ($ftHasImage) style="background-image: url('{{ asset('assets/front/img/tenders/' . $ft->tender_image) }}');" @endif>
                                    @if (!$ftHasImage)
                                        <svg viewBox="0 0 24 24"><path d="M7 3h8l4 4v14a1 1 0 01-1 1H7a1 1 0 01-1-1V4a1 1 0 011-1z" /><path d="M15 3v4h4" /><path d="M9 12h6M9 16h6M9 8h2" /></svg>
                                    @endif
                                    @if (!empty($ft->tenderCategory))
                                        <span class="dark-tender-cat">{{ convertUtf8($ft->tenderCategory->name) }}</span>
                                    @endif
                                    @if ($ft->submission_deadline)
                                        @php $tenderCd = tenderCountdown($ft->submission_deadline); @endphp
                                        <div class="dark-tender-countdown" data-deadline="{{ \Carbon\Carbon::parse($ft->submission_deadline)->toIso8601String() }}">
                                            <span class="unit"><b data-d>{{ $tenderCd['d'] }}</b><span>{{ __('d') }}</span></span>
                                            <span class="unit"><b data-h>{{ $tenderCd['h'] }}</b><span>{{ __('h') }}</span></span>
                                        </div>
                                    @endif
                                </div>
                                <div class="dark-tender-body">
                                    <h3>{{ convertUtf8($ft->title) }}</h3>
                                    <div class="dark-tender-meta">
                                        <span><svg viewBox="0 0 24 24"><path d="M12 21s7-6.5 7-12a7 7 0 10-14 0c0 5.5 7 12 7 12z" /><circle cx="12" cy="9" r="2.5" /></svg>{{ $ft->country }}</span>
                                        <span>{{ $ft->tender_code }}</span>
                                    </div>
                                    <div class="dark-tender-foot">
                                        <div class="dark-tender-price">
                                            @if (is_null($ft->current_price))
                                                <span class="now">{{ __('Free') }}</span>
                                            @else
                                                @if (!empty($ft->previous_price))
                                                    <span class="prev">{{ number_format($ft->previous_price, 0) }} {{ optional($bse)->base_currency_symbol }}</span>
                                                @endif
                                                <span class="now">
                                                    {{ optional($bse)->base_currency_symbol_position == 'left' ? optional($bse)->base_currency_symbol . ' ' : '' }}{{ number_format($ft->current_price, 0) }}
                                                    <small>{{ optional($bse)->base_currency_symbol_position == 'right' ? optional($bse)->base_currency_symbol : '' }}</small>
                                                </span>
                                            @endif
                                        </div>
                                        <span class="dark-tender-cta"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M13 6l6 6-6 6" /></svg></span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <div class="dark-tp-layout">
            <div class="dark-tp-main">
                <div class="dark-tp-toolbar">
                    <div class="dark-svcp-search dark-tp-search">
                        <input type="search" id="searchInput" placeholder="{{ __('Search By Tender Title or ID') }}" value="{{ request()->input('search') ?? '' }}">
                        <button type="button" id="search-input-btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><circle cx="11" cy="11" r="7" /><path d="M21 21l-4.3-4.3" /></svg></button>
                    </div>
                    <div class="dark-tp-sort">
                        <select id="filterType">
                            <option selected disabled>{{ __('Filter') }}</option>
                            <option value="new" {{ request()->input('filterValue') == 'new' ? 'selected' : '' }}>{{ __('Newest First') }}</option>
                            <option value="old" {{ request()->input('filterValue') == 'old' ? 'selected' : '' }}>{{ __('Oldest First') }}</option>
                            <option value="deadline_asc" {{ request()->input('filterValue') == 'deadline_asc' ? 'selected' : '' }}>{{ __('Deadline (Soonest)') }}</option>
                            <option value="expired" {{ request()->input('filterValue') == 'expired' ? 'selected' : '' }}>{{ __('Expired Tenders') }}</option>
                        <option value="high-to-low" {{ request()->input('filterValue') == 'high-to-low' ? 'selected' : '' }}>{{ __('High To Low Price') }}</option>
                            <option value="low-to-high" {{ request()->input('filterValue') == 'low-to-high' ? 'selected' : '' }}>{{ __('Low To High Price') }}</option>
                        </select>
                    </div>
                </div>

                <div class="dark-tp-results-wrap" id="darkTpResultsWrap">
                    <div class="dark-tp-loader" id="darkTpLoader">
                        <span class="dark-tp-spinner"></span>
                    </div>
                    <div id="darkTpResults">
                        @include('front.tender.partials.dark-results', ['tenders' => $tenders, 'bse' => $bse])
                    </div>
                </div>
            </div>

            <div class="dark-tp-side">
                <span class="dark-tp-rail-label">{{ __('Filter') }}</span>

                <div class="dark-svcp-widget">
                    <h4>{{ __('Categories') }}</h4>
                    <ul class="dark-svcp-cat-list dark-tp-cat-list">
                        <li class="{{ empty($darkTenderCatId) ? 'is-active' : '' }}">
                            <a href="#" data-href="" class="categoryId">{{ __('All') }}<span class="count">({{ str_pad($tenderCount, 2, '0', STR_PAD_LEFT) }})</span></a>
                        </li>
                        @foreach ($tender_categories as $cat)
                            <li class="{{ $darkTenderCatId == $cat->id ? 'is-active' : '' }}">
                                <a href="#" data-href="{{ $cat->id }}" class="categoryId">{{ convertUtf8($cat->name) }}<span class="count">({{ str_pad($cat->tenders_count, 2, '0', STR_PAD_LEFT) }})</span></a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                @if ($countries->count() > 0)
                    <div class="dark-svcp-widget">
                        <h4>{{ __('Country') }}</h4>
                        <ul class="dark-tp-country-list">
                            <li class="{{ empty(request()->input('country')) ? 'is-active' : '' }}">
                                <a href="#" data-href="" class="countryFilter"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s7-6.5 7-12a7 7 0 10-14 0c0 5.5 7 12 7 12z" /><circle cx="12" cy="9" r="2.5" /></svg>{{ __('All Countries') }}</a>
                            </li>
                            @foreach ($countries as $c)
                                <li class="{{ request()->input('country') == $c ? 'is-active' : '' }}">
                                    <a href="#" data-href="{{ $c }}" class="countryFilter"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s7-6.5 7-12a7 7 0 10-14 0c0 5.5 7 12 7 12z" /><circle cx="12" cy="9" r="2.5" /></svg>{{ $c }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="dark-svcp-widget">
                    <h4>{{ __('Filter By Price') }}</h4>
                    <div class="dark-tp-range">
                        <div id="slider-range"></div>
                        <input type="text" id="amount" class="dark-tp-range-value" readonly>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Hidden search form — same real filter contract as the light theme,
         just restyled controls above trigger it via the JS below. --}}
    <form id="searchForm" class="d-none" action="{{ route('tenders') }}" method="GET">
        <input type="hidden" id="searchKey" name="search" value="{{ request()->input('search') ?? '' }}">
        <input type="hidden" id="categoryKey" name="category_id" value="{{ request()->input('category_id') ?? '' }}">
        <input type="hidden" id="countryKey" name="country" value="{{ request()->input('country') ?? '' }}">
        <input type="hidden" id="checkedKey" name="checked_value" value="{{ request()->input('checked_value') ?? '' }}">
        <input type="hidden" id="minPriceId" name="minValue" value="{{ request()->input('minValue') ?? '' }}">
        <input type="hidden" id="maxPriceId" name="maxValue" value="{{ request()->input('maxValue') ?? '' }}">
        <input type="hidden" id="typeId" name="filterValue" value="{{ request()->input('filterValue') ?? '' }}">
        <button type="submit" id="submitBtn"></button>
    </form>
@else
    {{-- Featured Tenders --}}
    @if (count($featured_tenders) > 0)
        <section class="course-area-v1 pt-80 pb-120 bg_cover reveal-stagger"
            style="background-image: url({{ asset('assets/front/img/counter-bg-1.png') }});">
            <div class="container">
                <div class="row">
                    <div class="col">
                        <div class="py-5 text-center">
                            <h2 class="text-light">{{ __('FEATURED TENDERS') }}</h2>
                        </div>
                    </div>
                </div>
                <div class="row tender-slide">
                    @foreach ($featured_tenders as $ft)
                        <div class="col-lg-4">
                            <div class="tender-card">
                                <div class="card-img">
                                    @if (!empty($ft->tender_image))
                                        <img data-src="{{ asset('assets/front/img/tenders/' . $ft->tender_image) }}"
                                            class="lazy" alt="">
                                    @else
                                        <img src="{{ asset('assets/admin/img/noimage.jpg') }}" alt="">
                                    @endif
                                    <div class="overlay">
                                        @if ($ft->tenderCategory)
                                            <a href="{{ route('tenders', ['category_id' => $ft->tender_category_id]) }}"
                                                class="cat-badge">{{ $ft->tenderCategory->name }}</a>
                                        @endif
                                    </div>
                                    @if ($ft->submission_deadline)
                                        <span class="deadline-badge">
                                            <i class="far fa-clock"></i>
                                            {{ \Carbon\Carbon::parse($ft->submission_deadline)->format('d M Y') }}
                                        </span>
                                    @endif
                                </div>
                                <div class="card-body">
                                    <h4>
                                        <a href="{{ route('tender_details', ['slug' => $ft->slug]) }}">
                                            {{ strlen($ft->title) > 55 ? mb_substr($ft->title, 0, 55, 'utf-8') . '...' : $ft->title }}
                                        </a>
                                    </h4>
                                    <div class="modules-count">
                                        <i class="fas fa-layer-group"></i>
                                        {{ $ft->tenderModules->count() }} {{ __('Modules') }}
                                        &nbsp;&bull;&nbsp;
                                        <i class="fas fa-globe"></i> {{ $ft->country }}
                                    </div>
                                    <div class="meta">
                                        <span class="country">
                                            <i class="fas fa-map-marker-alt"></i> {{ $ft->country }}
                                        </span>
                                        <span class="price {{ is_null($ft->current_price) ? 'free' : '' }}">
                                            @if (is_null($ft->current_price))
                                                {{ __('Free') }}
                                            @else
                                                {{ $bse->base_currency_symbol_position == 'left' ? $bse->base_currency_symbol : '' }}
                                                {{ number_format($ft->current_price, 0) }}
                                                {{ $bse->base_currency_symbol_position == 'right' ? ' ' . $bse->base_currency_symbol : '' }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- All Tenders --}}
    <section class="courses-grid-style pt-120 pb-80">
        <div class="container">
            <div class="row">

                {{-- Tender Grid --}}
                <div class="col-lg-8">
                    <div class="course-filter mb-50">
                        <div class="row">
                            <div class="col-lg-4 col-md-5 col-sm-12">
                                <div class="form_group">
                                    <select id="filterType">
                                        <option selected disabled>{{ __('Filter') }}</option>
                                        <option value="new"
                                            {{ request()->input('filterValue') == 'new' ? 'selected' : '' }}>
                                            {{ __('Newest First') }}</option>
                                        <option value="old"
                                            {{ request()->input('filterValue') == 'old' ? 'selected' : '' }}>
                                            {{ __('Oldest First') }}</option>
                                        <option value="deadline_asc"
                                            {{ request()->input('filterValue') == 'deadline_asc' ? 'selected' : '' }}>
                                            {{ __('Deadline (Soonest)') }}</option>
                                        <option value="expired"
                                            {{ request()->input('filterValue') == 'expired' ? 'selected' : '' }}>
                                            {{ __('Expired Tenders') }}</option>
                                        <option value="high-to-low"
                                            {{ request()->input('filterValue') == 'high-to-low' ? 'selected' : '' }}>
                                            {{ __('High To Low Price') }}</option>
                                        <option value="low-to-high"
                                            {{ request()->input('filterValue') == 'low-to-high' ? 'selected' : '' }}>
                                            {{ __('Low To High Price') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-8 col-md-7 col-sm-12">
                                <div class="form_group search_group {{ isset($rtl) && $rtl == 1 ? 'float-left' : '' }}">
                                    <i class="fas fa-search" id="search-input-btn"></i>
                                    <input type="search" class="form_control" id="searchInput"
                                        placeholder="{{ __('Search By Tender Title or ID') }}"
                                        value="{{ request()->input('search') ?? '' }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        @if ($tenders->count() == 0)
                            <div class="col-md-12">
                                <div class="py-5 text-center">
                                    <h3>{{ __('No Tender Found!') }}</h3>
                                </div>
                            </div>
                        @else
                            @foreach ($tenders as $key => $tender)
                                <div class="col-md-6 col-sm-12">
                                    <div class="tender-card reveal-card" style="--d:{{ ($key % 2) * 0.1 }}s">
                                        <div class="card-img">
                                            @if (!empty($tender->tender_image))
                                                <img data-src="{{ asset('assets/front/img/tenders/' . $tender->tender_image) }}"
                                                    class="lazy" alt="">
                                            @else
                                                <img src="{{ asset('assets/admin/img/noimage.jpg') }}" alt="">
                                            @endif
                                            <div class="overlay">
                                                @if ($tender->tenderCategory)
                                                    <a href="{{ route('tenders', ['category_id' => $tender->tender_category_id]) }}"
                                                        class="cat-badge">{{ $tender->tenderCategory->name }}</a>
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
                                                <a href="{{ route('tender_details', ['slug' => $tender->slug]) }}">
                                                    {{ strlen($tender->title) > 55 ? mb_substr($tender->title, 0, 55, 'utf-8') . '...' : $tender->title }}
                                                </a>
                                            </h4>
                                            <div class="modules-count">
                                                <i class="fas fa-layer-group"></i>
                                                {{ $tender->tenderModules->count() }} {{ __('Modules') }}
                                                &nbsp;&bull;&nbsp;
                                                <i class="fas fa-hashtag"></i> {{ $tender->tender_code }}
                                            </div>
                                            <div class="meta">
                                                <span class="country">
                                                    <i class="fas fa-map-marker-alt"></i> {{ $tender->country }}
                                                </span>
                                                <span class="price {{ is_null($tender->current_price) ? 'free' : '' }}">
                                                    @if (is_null($tender->current_price))
                                                        {{ __('Free') }}
                                                    @else
                                                        {{ $bse->base_currency_symbol_position == 'left' ? $bse->base_currency_symbol : '' }}
                                                        {{ number_format($tender->current_price, 0) }}
                                                        {{ $bse->base_currency_symbol_position == 'right' ? ' ' . $bse->base_currency_symbol : '' }}
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <nav class="pagination-nav mb-3">
                                {{ $tenders->appends([
                                        'search' => request()->input('search'),
                                        'category_id' => request()->input('category_id'),
                                        'country' => request()->input('country'),
                                        'checked_value' => request()->input('checked_value'),
                                        'minValue' => request()->input('minValue'),
                                        'maxValue' => request()->input('maxValue'),
                                        'filterValue' => request()->input('filterValue'),
                                    ])->links() }}
                            </nav>
                        </div>
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="col-lg-4">
                    <div class="courses-sidebar">
                        {{-- Categories --}}
                        <div class="widget-box categories-widget">
                            <h4>{{ __('Categories') }}</h4>
                            <ul>
                                <li class="{{ empty(request()->input('category_id')) ? 'active-search' : '' }}">
                                    <a data-href="" class="categoryId">
                                        {{ __('All') }} <span>({{ $tenderCount }})</span>
                                    </a>
                                </li>
                                @foreach ($tender_categories as $cat)
                                    <li class="{{ request()->input('category_id') == $cat->id ? 'active-search' : '' }}">
                                        <a data-href="{{ $cat->id }}" class="categoryId">
                                            {{ convertUtf8($cat->name) }} <span>({{ $cat->tenders_count }})</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        {{-- Countries --}}
                        @if ($countries->count() > 0)
                            <div class="widget-box categories-widget">
                                <h4>{{ __('Country') }}</h4>
                                <ul>
                                    <li class="{{ empty(request()->input('country')) ? 'active-search' : '' }}">
                                        <a data-href="" class="countryFilter">{{ __('All Countries') }}</a>
                                    </li>
                                    @foreach ($countries as $c)
                                        <li class="{{ request()->input('country') == $c ? 'active-search' : '' }}">
                                            <a data-href="{{ $c }}" class="countryFilter">
                                                <i class="fas fa-map-marker-alt mr-1 text-muted"
                                                    style="font-size:11px;"></i>{{ $c }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Price Range --}}
                        <div class="widget-box price-range-widget">
                            <h4>{{ __('Filter By Price') }}</h4>
                            <div id="slider-range"></div>
                            <label for="amount">{{ __('Price') . ':' }}</label>
                            <input type="text" id="amount">
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- Hidden search form --}}
    <form id="searchForm" class="d-none" action="{{ route('tenders') }}" method="GET">
        <input type="hidden" id="searchKey" name="search" value="{{ request()->input('search') ?? '' }}">
        <input type="hidden" id="categoryKey" name="category_id" value="{{ request()->input('category_id') ?? '' }}">
        <input type="hidden" id="countryKey" name="country" value="{{ request()->input('country') ?? '' }}">
        <input type="hidden" id="checkedKey" name="checked_value"
            value="{{ request()->input('checked_value') ?? '' }}">
        <input type="hidden" id="minPriceId" name="minValue" value="{{ request()->input('minValue') ?? '' }}">
        <input type="hidden" id="maxPriceId" name="maxValue" value="{{ request()->input('maxValue') ?? '' }}">
        <input type="hidden" id="typeId" name="filterValue" value="{{ request()->input('filterValue') ?? '' }}">
        <button type="submit" id="submitBtn"></button>
    </form>
@endif

@endsection

@section('scripts')
    <script src="{{ asset('assets/front/js/jquery.ui.js') }}"></script>
    @if ($be->theme_version != 'dark')
        <script src="{{ asset('assets/front/js/jquery.nice-select.min.js') }}"></script>
    @endif
    <script>
        $(document).ready(function() {
            var featuredCount = {{ count($featured_tenders) }};
            var slidesToShow = Math.min(3, featuredCount);

            $('.tender-slide').slick({
                dots: false,
                arrows: true,
                infinite: featuredCount > slidesToShow,
                autoplay: false,
                autoplaySpeed: 2500,
                prevArrow: '<div class="prev"><i class="fas fa-angle-left"></i></div>',
                nextArrow: '<div class="next"><i class="fas fa-angle-right"></i></div>',
                slidesToShow: slidesToShow,
                slidesToScroll: 1,
                rtl: {{ isset($rtl) && $rtl == 1 ? 'true' : 'false' }},
                responsive: [{
                        breakpoint: 992,
                        settings: {
                            slidesToShow: Math.min(2, featuredCount)
                        }
                    },
                    {
                        breakpoint: 576,
                        settings: {
                            slidesToShow: 1
                        }
                    }
                ]
            });

            @if ($be->theme_version != 'dark')
                $('select').niceSelect();
            @endif

            var isDark = @json($be->theme_version == 'dark');
            var $darkTpResults = $('#darkTpResults');
            var $darkTpResultsWrap = $('#darkTpResultsWrap');
            var darkTpLoadingTimer = null;

            // Dark theme: every filter/sort/pagination control fetches the
            // results fragment via AJAX and swaps it in place instead of a
            // full page reload. Falls back to a real navigation if the
            // request fails for any reason (offline, server error, etc.).
            function loadTendersUrl(url, scrollToResults) {
                if (!isDark) return;
                // Small delay before showing the spinner — fast responses
                // (same-server, cached) shouldn't flash a loader for 80ms.
                clearTimeout(darkTpLoadingTimer);
                darkTpLoadingTimer = setTimeout(function() {
                    $darkTpResultsWrap.addClass('is-loading');
                }, 150);

                $.get(url)
                    .done(function(res) {
                        $darkTpResults.html(res.html);
                        if (window.observeRevealStagger) {
                            window.observeRevealStagger($darkTpResults[0]);
                        }
                        history.pushState({
                            darkTpAjax: true
                        }, '', url);
                        if (scrollToResults) {
                            $('html, body').animate({
                                scrollTop: $darkTpResultsWrap.offset().top - 120
                            }, 300);
                        }
                    })
                    .fail(function() {
                        window.location.href = url;
                    })
                    .always(function() {
                        clearTimeout(darkTpLoadingTimer);
                        $darkTpResultsWrap.removeClass('is-loading');
                    });
            }

            function triggerFilter() {
                if (isDark) {
                    loadTendersUrl('{{ route('tenders') }}?' + $('#searchForm').serialize(), false);
                } else {
                    $('#submitBtn').click();
                }
            }

            // Pagination links get replaced with every AJAX swap, so this is
            // delegated on document rather than bound once.
            $(document).on('click', '.dark-tp-pagination a', function(e) {
                if (!isDark) return;
                e.preventDefault();
                var url = $(this).attr('href');
                if (url) loadTendersUrl(url, true);
            });

            window.addEventListener('popstate', function() {
                if (isDark) loadTendersUrl(location.href, false);
            });

            var position = '{{ $bse->base_currency_symbol_position }}';
            var symbol = '{{ $bse->base_currency_symbol }}';

            // Price slider
            $('#slider-range').slider({
                range: true,
                min: 0,
                max: {{ $maxPrice }},
                values: [
                    {{ !empty(request()->input('minValue')) ? request()->input('minValue') : 0 }},
                    {{ !empty(request()->input('maxValue')) ? request()->input('maxValue') : $maxPrice }}
                ],
                slide: function(event, ui) {
                    $('#amount').val(
                        (position == 'left' ? symbol : '') + ui.values[0] + (position == 'right' ?
                            ' ' + symbol : '') +
                        ' - ' +
                        (position == 'left' ? symbol : '') + ui.values[1] + (position == 'right' ?
                            ' ' + symbol : '')
                    );
                }
            });

            $('#amount').val(
                (position == 'left' ? symbol : '') + ' ' + $('#slider-range').slider('values', 0) + (position ==
                    'right' ? ' ' + symbol : '') +
                ' - ' +
                (position == 'left' ? symbol : '') + ' ' + $('#slider-range').slider('values', 1) + (position ==
                    'right' ? ' ' + symbol : '')
            );

            // Search on Enter
            $(document).on('keyup', '#searchInput', function(e) {
                if (e.keyCode === 13) {
                    $('#searchKey').val($(this).val());
                    triggerFilter();
                }
            });

            // Category filter
            $(document).on('click', '.categoryId', function(e) {
                e.preventDefault();
                var id = $(this).attr('data-href');
                $('#categoryKey').val(id != '0' ? id : '');
                if (isDark) {
                    $('.categoryId').closest('li').removeClass('is-active');
                    $(this).closest('li').addClass('is-active');
                }
                triggerFilter();
            });

            // Country filter
            $(document).on('click', '.countryFilter', function(e) {
                e.preventDefault();
                var c = $(this).attr('data-href');
                $('#countryKey').val(c);
                if (isDark) {
                    $('.countryFilter').closest('li').removeClass('is-active');
                    $(this).closest('li').addClass('is-active');
                }
                triggerFilter();
            });

            // Search icon button (dark theme's is a real <button>, unlike the
            // light theme's decorative <i> — Enter-to-search below still works too)
            $(document).on('click', '#search-input-btn', function(e) {
                e.preventDefault();
                $('#searchKey').val($('#searchInput').val());
                triggerFilter();
            });

            // Tender type radio
            $(document).on('click', '.tender-type', function() {
                $('#checkedKey').val($('.tender-type:checked').val());
                triggerFilter();
            });

            // Price slider stop
            $(document).on('slidestop', '#slider-range', function() {
                var parts = $('#amount').val().split('-');
                $('#minPriceId').val(parseInt(parts[0].replace(/[^0-9]/g, '')));
                $('#maxPriceId').val(parseInt(parts[1].replace(/[^0-9]/g, '')));
                triggerFilter();
            });

            // Sort filter
            $(document).on('change', '#filterType', function() {
                $('#typeId').val($(this).val());
                triggerFilter();
            });

            @if ($be->theme_version == 'dark')
                // Featured carousel — arrow-driven horizontal scroll-snap.
                // The live countdown on each card is handled sitewide by
                // dark-tenders-fx.js (matches any [data-deadline] element).
                (function() {
                    var track = document.getElementById('darkTpSpotTrack');
                    if (!track) return;
                    var prevBtn = document.getElementById('darkTpSpotPrev');
                    var nextBtn = document.getElementById('darkTpSpotNext');
                    if (!prevBtn || !nextBtn) return;

                    function cardStep() {
                        var card = track.querySelector('.dark-tender-card');
                        return card ? card.getBoundingClientRect().width + 22 : 360;
                    }
                    function updateArrows() {
                        prevBtn.disabled = track.scrollLeft <= 4;
                        nextBtn.disabled = track.scrollLeft >= track.scrollWidth - track.clientWidth - 4;
                    }
                    prevBtn.addEventListener('click', function() {
                        track.scrollBy({
                            left: -cardStep(),
                            behavior: 'smooth'
                        });
                    });
                    nextBtn.addEventListener('click', function() {
                        track.scrollBy({
                            left: cardStep(),
                            behavior: 'smooth'
                        });
                    });
                    track.addEventListener('scroll', updateArrows, {
                        passive: true
                    });
                    updateArrows();
                })();
            @endif
        });
    </script>
@endsection
