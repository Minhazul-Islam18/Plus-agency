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

    {{-- Featured Tenders --}}
    @if (count($featured_tenders) > 0)
        <section class="course-area-v1 pt-80 pb-120 bg_cover"
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
                                            {{ convertUtf8($cat->name) }} <span>({{ $cat->tenders()->count() }})</span>
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
                            @foreach ($tenders as $tender)
                                <div class="col-md-6 col-sm-12">
                                    <div class="tender-card">
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

@endsection

@section('scripts')
    <script src="{{ asset('assets/front/js/jquery.ui.js') }}"></script>
    <script src="{{ asset('assets/front/js/jquery.nice-select.min.js') }}"></script>
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

            $('select').niceSelect();

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
                    $('#submitBtn').click();
                }
            });

            // Category filter
            $(document).on('click', '.categoryId', function() {
                var id = $(this).attr('data-href');
                $('#categoryKey').val(id != '0' ? id : '');
                $('#submitBtn').click();
            });

            // Country filter
            $(document).on('click', '.countryFilter', function() {
                var c = $(this).attr('data-href');
                $('#countryKey').val(c);
                $('#submitBtn').click();
            });

            // Tender type radio
            $(document).on('click', '.tender-type', function() {
                $('#checkedKey').val($('.tender-type:checked').val());
                $('#submitBtn').click();
            });

            // Price slider stop
            $(document).on('slidestop', '#slider-range', function() {
                var parts = $('#amount').val().split('-');
                $('#minPriceId').val(parseInt(parts[0].replace(/[^0-9]/g, '')));
                $('#maxPriceId').val(parseInt(parts[1].replace(/[^0-9]/g, '')));
                $('#submitBtn').click();
            });

            // Sort filter
            $(document).on('change', '#filterType', function() {
                $('#typeId').val($(this).val());
                $('#submitBtn').click();
            });
        });
    </script>
@endsection
