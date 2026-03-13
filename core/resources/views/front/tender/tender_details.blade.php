@extends("front.$version.layout")

@section('pagename')
    - {{ __('Tender') }} - {{ convertUtf8($tender->title) }}
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/front/css/magnific-popup.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/front/css/nice-select.css') }}">
    <style>
        /* ── Expert section ── */
        .expert-wrap {
            display: flex;
            gap: 24px;
            align-items: flex-start;
        }

        .expert-wrap .thumb {
            flex-shrink: 0;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            overflow: hidden;
            border: 4px solid #f0f0f0;
        }

        .expert-wrap .thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .expert-wrap .content h4 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .expert-wrap .content .position {
            color: var(--main-color, #3498db);
            font-weight: 600;
            margin-bottom: 12px;
            display: block;
        }

        .expert-contact-btns {
            display: flex;
            gap: 12px;
            margin-top: 16px;
            flex-wrap: wrap;
        }

        .expert-contact-btns a {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            transition: opacity .2s;
        }

        .expert-contact-btns a:hover {
            opacity: .85;
        }

        .btn-whatsapp {
            background: #25d366;
            color: #fff !important;
        }

        .btn-phone {
            background: #2c3e50;
            color: #fff !important;
        }

        /* ── Left column: image box ── */
        .tender-thumb-box {
            border-radius: 10px;
            overflow: hidden;
            position: relative;
            background: #000;
        }

        .tender-thumb-box img {
            width: 100%;
            max-height: 320px;
            object-fit: cover;
            display: block;
            position: relative;
            z-index: 1;
        }

        .tender-thumb-box .video-overlay {
            position: absolute;
            inset: 0;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none; /* overlay itself doesn't block clicks */
        }

        .tender-thumb-box .video-overlay a.video-popup {
            pointer-events: all;
            width: 70px;
            height: 70px;
            background: rgba(255, 255, 255, 0.92);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--main-color, #3498db);
            font-size: 26px;
            text-decoration: none;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.35);
            transition: transform .2s, background .2s;
            animation: pulse-ring 2s ease-out infinite;
        }

        .tender-thumb-box .video-overlay a.video-popup:hover {
            transform: scale(1.1);
            background: #fff;
        }

        .tender-thumb-box .video-overlay a.video-popup i {
            margin-left: 4px; /* optical centre for play icon */
        }

        @keyframes pulse-ring {
            0%   { box-shadow: 0 0 0 0 rgba(52, 152, 219, 0.5), 0 4px 20px rgba(0,0,0,.35); }
            70%  { box-shadow: 0 0 0 14px rgba(52, 152, 219, 0), 0 4px 20px rgba(0,0,0,.35); }
            100% { box-shadow: 0 0 0 0 rgba(52, 152, 219, 0), 0 4px 20px rgba(0,0,0,.35); }
        }

        .tender-thumb-box .video-overlay a:hover {
            transform: scale(1.1);
        }

        /* ── Date + flag row ── */
        /* ── Deadline card ── */
        .deadline-card {
            display: flex;
            align-items: stretch;
            margin-top: 14px;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #e0e0e0;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
        }
        .deadline-card .dl-icon-col {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 14px 18px;
            background: var(--main-color, #3498db);
            color: #fff;
            font-size: 22px;
            flex-shrink: 0;
        }
        .deadline-card .dl-icon-col.expired { background: #e74c3c; }
        .deadline-card .dl-icon-col.urgent  { background: #e74c3c; }
        .deadline-card .dl-icon-col.soon    { background: #f39c12; }
        .deadline-card .dl-icon-col.ok      { background: #27ae60; }
        .deadline-card .dl-body {
            flex: 1;
            padding: 10px 16px;
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .deadline-card .dl-body .dl-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: #999;
            margin-bottom: 3px;
        }
        .deadline-card .dl-body .dl-date {
            font-size: 15px;
            font-weight: 700;
            color: #222;
        }
        .deadline-card .dl-badge-col {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px 14px;
            background: #f8f9fa;
            flex-shrink: 0;
        }
        .deadline-card .dl-days-badge {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 52px;
            height: 52px;
            border-radius: 50%;
            font-weight: 800;
            font-size: 13px;
            line-height: 1.2;
            text-align: center;
        }
        .deadline-card .dl-days-badge .days-num  { font-size: 18px; line-height: 1; }
        .deadline-card .dl-days-badge.badge-ok       { background: #e8f8f0; color: #27ae60; border: 2px solid #27ae60; }
        .deadline-card .dl-days-badge.badge-soon     { background: #fff3e0; color: #f39c12; border: 2px solid #f39c12; }
        .deadline-card .dl-days-badge.badge-urgent   { background: #fdecea; color: #e74c3c; border: 2px solid #e74c3c; }
        .deadline-card .dl-days-badge.badge-expired  { background: #f5f5f5; color: #aaa;    border: 2px solid #ddd; }
        .deadline-card .dl-country {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 0 14px 0 0;
            font-size: 13px;
            font-weight: 600;
            color: #555;
            flex-shrink: 0;
            border-left: 1px solid #eee;
            padding-left: 14px;
        }
        .deadline-card .dl-country i { color: var(--main-color, #3498db); }


        /* ── Right column: info ── */
        .tender-info-wrap {
            position: relative;
        }

        /* Stars rating */
        .tender-rating {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 10px;
        }

        .tender-rating .stars i {
            color: #ddd;
            font-size: 15px;
        }

        .tender-rating .count {
            font-size: 13px;
            color: #777;
        }

        /* Category top-right badge */
        .tender-cat-badge {
            position: absolute;
            top: 0;
            right: 0;
            background: #f39c12;
            color: #fff;
            padding: 5px 14px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
        }

        .tender-cat-badge:hover {
            color: #fff;
            opacity: .85;
        }

        /* Tender ID blue bar */
        .tender-id-bar {
            display: inline-block;
            background: var(--main-color, #3498db);
            color: #fff;
            padding: 6px 16px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .5px;
            margin-bottom: 12px;
        }

        /* Price */
        .tender-price-wrap {
            display: flex;
            align-items: baseline;
            gap: 12px;
            margin: 14px 0;
        }

        .tender-price-wrap .price-current {
            font-size: 26px;
            font-weight: 800;
            color: var(--main-color, #3498db);
        }

        .tender-price-wrap .price-current.free-price {
            color: #27ae60;
        }

        .tender-price-wrap .price-prev {
            font-size: 16px;
            color: #aaa;
            text-decoration: line-through;
        }

        /* Pay section */
        .pay-section {
            margin-top: 16px;
        }

        .pay-section .pay-label {
            font-size: 13px;
            font-weight: 600;
            color: #777;
            margin-bottom: 6px;
        }

        .pay-row {
            display: flex;
            align-items: stretch;
            gap: 10px;
            flex-wrap: wrap;
        }

        .pay-row .nice-select {
            flex: 1;
            min-width: 150px;
            height: 48px;
            line-height: 46px;
        }

        .pay-row .main-btn {
            flex-shrink: 0;
            min-width: 120px;
        }

        /* Social share */
        .tender-share {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 16px;
        }

        .tender-share a {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 14px;
            text-decoration: none;
            transition: opacity .2s;
        }

        .tender-share a:hover {
            opacity: .8;
        }

        .share-fb {
            background: #3b5998;
        }

        .share-tw {
            background: #1da1f2;
        }

        .share-pt {
            background: #bd081c;
        }

        .share-li {
            background: #0077b5;
        }

        /* ── Check plans section ── */
        .downloads-link {
            display: inline-block;
            color: var(--main-color, #3498db);
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 8px;
            text-decoration: none;
        }

        .downloads-link i {
            margin-right: 6px;
        }

        .downloads-link:hover {
            text-decoration: underline;
            color: var(--main-color, #3498db);
        }

        .check-plans-bar {
            background: var(--main-color, #3498db);
            color: #fff;
            text-align: center;
            padding: 14px 24px;
            border-radius: 6px 6px 0 0;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: .4px;
        }

        .module-badges-row {
            background: #eaf4fb;
            border: 1px solid #c6dff0;
            border-top: none;
            border-radius: 0 0 6px 6px;
            padding: 16px 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .module-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all .2s;
            border: 2px solid transparent;
            user-select: none;
            text-decoration: none;
        }

        .module-badge.free-badge {
            background: #e8f8f0;
            border-color: #27ae60;
            color: #27ae60;
        }

        .module-badge.free-badge:hover {
            background: #27ae60;
            color: #fff;
        }

        .module-badge.paid-badge {
            background: #eaf4fb;
            border-color: #3498db;
            color: #3498db;
        }

        .module-badge.paid-badge:hover {
            background: #d4ecf7;
        }

        .module-badge.paid-badge.selected {
            background: #3498db;
            color: #fff;
            border-color: #2980b9;
        }

        /* ── Module accordion (Tender Fees tab) ── */
        .module-item {
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 10px;
            overflow: hidden;
        }

        .module-item .module-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 20px;
            background: #1a237e;
            cursor: pointer;
            font-weight: 600;
            font-size: 15px;
            color: #fff;
        }

        .module-item .module-header .module-cost {
            font-size: 14px;
            font-weight: 700;
            flex-shrink: 0;
            margin-left: 12px;
        }

        .module-item .module-header .module-cost.free {
            color: #69f0ae;
        }

        .module-item .module-header .module-cost.paid {
            color: #90caf9;
        }

        .module-item .module-header .toggle-icon {
            font-size: 12px;
            color: rgba(255, 255, 255, .7);
            transition: transform .3s;
            margin-left: 8px;
        }

        .module-item .module-header.open .toggle-icon {
            transform: rotate(180deg);
        }

        .module-item .module-body {
            padding: 16px 20px;
            border-top: 1px solid #e8e8e8;
            background: #fff;
        }

        .module-item .module-body p {
            margin: 0;
            color: #555;
            font-size: 14px;
            line-height: 1.7;
        }

        /* ── Deadline color classes ── */
        .deadline-urgent {
            color: #e74c3c;
            font-weight: 700;
        }

        .deadline-soon {
            color: #f39c12;
            font-weight: 600;
        }

        .deadline-ok {
            color: #27ae60;
            font-weight: 600;
        }
    </style>
@endsection

@section('breadcrumb-title', $bex->course_details_title ?? __('Tender Details'))
@section('breadcrumb-subtitle', $tender->title)
@section('breadcrumb-link', $tender->title)

@section('content')
    <section class="course-details-section pt-120 pb-120">
        <div class="container">

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>{{ session('success') }}</strong>
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif

            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    <div class="alert alert-danger alert-block">
                        <strong>{{ $error }}</strong>
                        <button type="button" class="close" data-dismiss="alert">×</button>
                    </div>
                @endforeach
            @endif

            {{-- ═══════════════════════════════════════════
         TOP ROW: Thumbnail (left) + Info (right)
    ═══════════════════════════════════════════ --}}
            <div class="row">

                {{-- LEFT: Image / Video + Deadline + Flag --}}
                <div class="col-lg-6 mb-4">
                    <div class="tender-thumb-box">
                        @if (!empty($tender->tender_image))
                            <img data-src="{{ asset('assets/front/img/tenders/' . $tender->tender_image) }}" class="lazy"
                                alt="{{ $tender->title }}">
                        @else
                            <img src="{{ asset('assets/admin/img/noimage.jpg') }}" alt="">
                        @endif
                        @if (!empty($tender->video_link))
                            <div class="video-overlay">
                                <a href="{{ $tender->video_link }}" class="video-popup">
                                    <i class="fas fa-play"></i>
                                </a>
                            </div>
                        @endif
                    </div>

                    {{-- Deadline card --}}
                    @if ($tender->submission_deadline)
                        @php
                            $deadline  = \Carbon\Carbon::parse($tender->submission_deadline);
                            $daysLeft  = (int) now()->diffInDays($deadline, false);
                            $isExpired = $daysLeft < 0;
                            $iconState = $isExpired ? 'expired' : ($daysLeft <= 3 ? 'urgent' : ($daysLeft <= 7 ? 'soon' : 'ok'));
                            $badgeClass = 'badge-' . $iconState;
                        @endphp
                        <div class="deadline-card">
                            <div class="dl-icon-col {{ $iconState }}">
                                <i class="fas fa-hourglass-{{ $isExpired ? 'end' : ($daysLeft <= 7 ? 'half' : 'start') }}"></i>
                            </div>
                            <div class="dl-body">
                                <div class="dl-label">{{ __('Submission Deadline') }}</div>
                                <div class="dl-date">{{ $deadline->format('d-m-Y, H:i') }} <small class="text-muted" style="font-weight:500;">TU</small></div>
                            </div>
                            <div class="dl-badge-col">
                                <div class="dl-days-badge {{ $badgeClass }}">
                                    @if ($isExpired)
                                        <span style="font-size:11px;font-weight:700;">{{ __('EXPIRED') }}</span>
                                    @elseif ($daysLeft === 0)
                                        <span style="font-size:11px;font-weight:700;">{{ __('TODAY') }}</span>
                                    @else
                                        <span class="days-num">{{ $daysLeft }}</span>
                                        <span style="font-size:10px;">{{ __('days') }}</span>
                                    @endif
                                </div>
                            </div>
                            @if ($tender->country)
                            <div class="dl-country">
                                <i class="fas fa-map-marker-alt"></i>
                                {{ $tender->country }}
                            </div>
                            @endif
                        </div>
                    @else
                        <div class="deadline-card">
                            <div class="dl-icon-col" style="background:#aaa;">
                                <i class="fas fa-hourglass-start"></i>
                            </div>
                            <div class="dl-body">
                                <div class="dl-label">{{ __('Submission Deadline') }}</div>
                                <div class="dl-date text-muted">{{ __('Not set') }}</div>
                            </div>
                            @if ($tender->country)
                            <div class="dl-country">
                                <i class="fas fa-map-marker-alt"></i>
                                {{ $tender->country }}
                            </div>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- RIGHT: Tender Info & Purchase --}}
                <div class="col-lg-6">
                    <div class="tender-info-wrap">

                        {{-- Category badge (top-right corner) --}}
                        @if ($tender->tenderCategory)
                            <a href="{{ route('tenders', ['category_id' => $tender->tender_category_id]) }}"
                                class="tender-cat-badge">{{ $tender->tenderCategory->name }}</a>
                        @endif

                        {{-- Stars (placeholder – no reviews system yet) --}}
                        <div class="tender-rating">
                            <span class="stars">
                                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                <i class="fas fa-star"></i><i class="fas fa-star"></i>
                            </span>
                            <span class="count">0 (0)</span>
                        </div>

                        {{-- Tender ID badge --}}
                        @if ($tender->tender_code)
                            <div class="tender-id-bar">{{ $tender->tender_code }}</div>
                        @endif

                        {{-- Title --}}
                        <h3 class="mb-3" style="font-size:22px; line-height:1.4;">{{ convertUtf8($tender->title) }}</h3>

                        {{-- Price --}}
                        <div class="tender-price-wrap">
                            @if (is_null($tender->current_price))
                                <span class="price-current free-price" id="displayedPrice">{{ __('Free') }}</span>
                            @else
                                <span class="price-current" id="displayedPrice">
                                    {{ $bse->base_currency_symbol_position == 'left' ? $bse->base_currency_symbol : '' }}<span
                                        id="priceAmount">{{ number_format($tender->current_price, 0) }}</span>{{ $bse->base_currency_symbol_position == 'right' ? ' ' . $bse->base_currency_symbol : '' }}
                                </span>
                                @if (!is_null($tender->previous_price))
                                    <span class="price-prev">
                                        {{ $bse->base_currency_symbol_position == 'left' ? $bse->base_currency_symbol : '' }}{{ number_format($tender->previous_price, 0) }}{{ $bse->base_currency_symbol_position == 'right' ? ' ' . $bse->base_currency_symbol : '' }}
                                    </span>
                                @endif
                            @endif
                        </div>

                        {{-- Summary --}}
                        <p class="mb-2">{{ $tender->summary }}</p>

                        {{-- Category text line --}}
                        @if ($tender->tenderCategory)
                            <p class="mb-3">
                                <strong>{{ __('Category') }}:</strong>
                                <a href="{{ route('tenders', ['category_id' => $tender->tender_category_id]) }}">
                                    {{ $tender->tenderCategory->name }}
                                </a>
                            </p>
                        @endif

                        {{-- ── Purchase form (paid tender) ── --}}
                        @if (!is_null($tender->current_price))
                            <form method="POST" id="paymentGatewayForm" action="{{ route('tender.purchase.submit') }}"
                                enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="tender_id" value="{{ $tender->id }}">
                                <input type="hidden" name="selected_amount" id="selectedAmount"
                                    value="{{ $tender->current_price }}">

                                <div class="pay-section">
                                    <p class="pay-label">{{ __('Payer via') }}</p>
                                    <div class="pay-row">
                                        <select name="gateway" id="paymentType" class="select-payment">
                                            <option selected disabled>{{ __('Select') }}</option>
                                            @foreach ($paymentGateways as $gw)
                                                <option value="{{ $gw->keyword }}">{{ $gw->name }}</option>
                                            @endforeach
                                            @foreach ($offlineGateways as $ogw)
                                                <option value="{{ $ogw->id }}" data-type="offline">
                                                    {{ $ogw->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" id="purchaseBtn"
                                            class="main-btn">{{ __('PAY NOW') }}</button>
                                    </div>
                                </div>

                                <p class="text-danger payment-warning mt-2" style="display:none;">
                                    * {{ __('Please select a payment method') }}
                                </p>

                                {{-- Offline gateway details --}}
                                @foreach ($offlineGateways as $ogw)
                                    <div class="gateway-details row mt-3" id="tab-{{ $ogw->id }}"
                                        style="display:none;">
                                        <div class="col-12">
                                            <p class="gateway-desc">{{ $ogw->short_description }}</p>
                                        </div>
                                        <div class="col-12">
                                            <div class="gateway-instruction">{!! replaceBaseUrl($ogw->instructions) !!}</div>
                                        </div>
                                        @if ($ogw->is_receipt == 1)
                                            <div class="col-12 mb-3">
                                                <label class="d-block">{{ __('Receipt') }} **</label>
                                                <input type="file" name="receipt">
                                                <p class="mb-0 text-warning">**
                                                    {{ __('Receipt image must be .jpg / .jpeg / .png') }}</p>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach

                                {{-- Purchaser info (revealed after gateway selected) --}}
                                <div id="purchaserInfo" style="display:none;" class="mt-3">
                                    <h6 class="mb-3 font-weight-bold">{{ __('Your Information') }}</h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <input type="text" name="first_name" class="form-control"
                                                placeholder="{{ __('First Name') }} *"
                                                value="{{ Auth::check() ? Auth::user()->fname : '' }}" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <input type="text" name="last_name" class="form-control"
                                                placeholder="{{ __('Last Name') }} *"
                                                value="{{ Auth::check() ? Auth::user()->lname : '' }}" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <input type="email" name="email" class="form-control"
                                                placeholder="{{ __('Email Address') }} *"
                                                value="{{ Auth::check() ? Auth::user()->email : '' }}" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <input type="text" name="phone_number" class="form-control"
                                                placeholder="{{ __('Phone Number') }} *"
                                                value="{{ Auth::check() ? Auth::user()->phone : '' }}" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <input type="text" name="country" class="form-control"
                                                placeholder="{{ __('Country') }} *" value="{{ $tender->country }}"
                                                required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <input type="text" name="city" class="form-control"
                                                placeholder="{{ __('City') }}">
                                        </div>
                                    </div>
                                    <button type="submit" class="main-btn">{{ __('Confirm Purchase') }}</button>
                                </div>
                            </form>
                        @else
                            {{-- Free tender: contact expert buttons --}}
                            <div class="expert-contact-btns">
                                @if (!empty($tender->expert_whatsapp))
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $tender->expert_whatsapp) }}"
                                        class="btn-whatsapp" target="_blank">
                                        <i class="fab fa-whatsapp"></i> {{ __('WhatsApp Expert') }}
                                    </a>
                                @endif
                                @if (!empty($tender->expert_phone))
                                    <a href="tel:{{ $tender->expert_phone }}" class="btn-phone">
                                        <i class="fas fa-phone"></i> {{ $tender->expert_phone }}
                                    </a>
                                @endif
                            </div>
                        @endif

                        {{-- Social share --}}
                        <div class="tender-share">
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}"
                                class="share-fb" target="_blank" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                            <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($tender->title) }}"
                                class="share-tw" target="_blank" title="Twitter"><i class="fab fa-twitter"></i></a>
                            <a href="https://pinterest.com/pin/create/button/?url={{ urlencode(request()->url()) }}"
                                class="share-pt" target="_blank" title="Pinterest"><i
                                    class="fab fa-pinterest-p"></i></a>
                            <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(request()->url()) }}"
                                class="share-li" target="_blank" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        </div>

                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════
         CHECK THE PLANS TO PURCHASE
    ═══════════════════════════════════════════ --}}
            @if ($modules->count() > 0)
                <div class="row mt-5">
                    <div class="col-12">
                        <a href="#" class="downloads-link" id="downloadsLink">
                            <i class="fas fa-download"></i> {{ __('Click here to find your downloads') }}
                        </a>
                        <div class="check-plans-bar">{{ __('Check the plans to purchase') }}</div>
                        <div class="module-badges-row">
                            @foreach ($modules as $module)
                                @if (is_null($module->cost))
                                    {{-- Free module → click to download --}}
                                    <a @if (!empty($module->tender_file)) href="{{ asset('assets/front/files/tender_modules/' . $module->tender_file) }}" download
                 @else
                   href="#" @endif
                                        class="module-badge free-badge" title="{{ __('Free – click to download') }}">
                                        <i class="fas fa-download"></i>
                                        <span>{{ convertUtf8($module->name) }}
                                            <small style="font-weight:400;">({{ __('Free of charge') }})</small>
                                        </span>
                                    </a>
                                @else
                                    {{-- Paid module → toggle selection, adds cost to total --}}
                                    <div class="module-badge paid-badge" data-cost="{{ $module->cost }}"
                                        data-module-id="{{ $module->id }}" onclick="toggleModule(this)"
                                        title="{{ __('Click to select / deselect') }}">
                                        <i class="fas fa-lock"></i>
                                        <span>{{ convertUtf8($module->name) }}
                                            <small
                                                style="font-weight:400;">({{ $bse->base_currency_symbol_position == 'left' ? $bse->base_currency_symbol : '' }}{{ number_format($module->cost, 0) }}{{ $bse->base_currency_symbol_position == 'right' ? ' ' . $bse->base_currency_symbol : '' }})</small>
                                        </span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- ═══════════════════════════════════════════
         TABS: Overview / Tender Fees / Expert / Reviews
    ═══════════════════════════════════════════ --}}
            <div class="row mt-5">
                <div class="col-lg-12">
                    <div class="discription-area">
                        <div class="discription-tabs">
                            <ul class="nav nav-tabs">
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab"
                                        href="#overview">{{ __('Tender Overview') }}</a>
                                </li>
                                @if ($modules->count() > 0)
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#modules"
                                            id="modules-tab-link">{{ __('Tender Fees') }}</a>
                                    </li>
                                @endif
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab"
                                        href="#expert">{{ __('Tendering Expert') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#reviews">{{ __('Reviews') }}</a>
                                </li>
                            </ul>
                        </div>

                        <div class="tab-content">

                            {{-- Tender Overview --}}
                            <div id="overview" class="tab-pane active">
                                <div class="content-box">
                                    <h4>{{ __('Tender Overview') }}</h4>
                                    <div>{!! $tender->overview !!}</div>
                                </div>
                            </div>

                            {{-- Tender Fees – modules accordion (NO sections list) --}}
                            @if ($modules->count() > 0)
                                <div id="modules" class="tab-pane fade">
                                    <div class="content-box">
                                        @foreach ($modules as $module)
                                            <div class="module-item">
                                                <div class="module-header" data-toggle="collapse"
                                                    data-target="#module-body-{{ $module->id }}">
                                                    <span>{{ convertUtf8($module->name) }}</span>
                                                    <div class="d-flex align-items-center">
                                                        <span
                                                            class="module-cost {{ is_null($module->cost) ? 'free' : 'paid' }}">
                                                            @if (is_null($module->cost))
                                                                ({{ __('Free of charge') }})
                                                            @else
                                                                ({{ $bse->base_currency_symbol_position == 'left' ? $bse->base_currency_symbol : '' }}{{ number_format($module->cost, 0) }}{{ $bse->base_currency_symbol_position == 'right' ? ' ' . $bse->base_currency_symbol : '' }})
                                                            @endif
                                                        </span>
                                                        <i class="fas fa-chevron-down toggle-icon"></i>
                                                    </div>
                                                </div>
                                                <div id="module-body-{{ $module->id }}" class="collapse module-body">
                                                    <p>{{ !empty($module->summary) ? $module->summary : __('No description available.') }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Tendering Expert --}}
                            <div id="expert" class="tab-pane fade">
                                <div class="content-box">
                                    @if (!empty($tender->expert_name))
                                        <div class="expert-wrap">
                                            @if (!empty($tender->expert_image))
                                                <div class="thumb">
                                                    <img data-src="{{ asset('assets/front/img/tender_experts/' . $tender->expert_image) }}"
                                                        class="lazy img-fluid" alt="{{ $tender->expert_name }}">
                                                </div>
                                            @endif
                                            <div class="content">
                                                <h4>{{ $tender->expert_name }}</h4>
                                                @if ($tender->expert_position)
                                                    <span class="position">{{ $tender->expert_position }}</span>
                                                @endif
                                                @if ($tender->expert_details)
                                                    <div class="text-box">
                                                        <p>{{ $tender->expert_details }}</p>
                                                    </div>
                                                @endif
                                                <div class="expert-contact-btns">
                                                    @if (!empty($tender->expert_whatsapp))
                                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $tender->expert_whatsapp) }}"
                                                            class="btn-whatsapp" target="_blank">
                                                            <i class="fab fa-whatsapp"></i> {{ __('WhatsApp') }}:
                                                            {{ $tender->expert_whatsapp }}
                                                        </a>
                                                    @endif
                                                    @if (!empty($tender->expert_phone))
                                                        <a href="tel:{{ $tender->expert_phone }}" class="btn-phone">
                                                            <i class="fas fa-phone"></i> {{ $tender->expert_phone }}
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <p class="text-muted">{{ __('No expert information available.') }}</p>
                                    @endif
                                </div>
                            </div>

                            {{-- Reviews --}}
                            <div id="reviews" class="tab-pane fade">
                                <div class="content-box">
                                    <h4>{{ __('Reviews') }}</h4>
                                    <p class="text-muted">{{ __('No reviews yet.') }}</p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
@endsection

@section('scripts')
    <script src="{{ asset('assets/front/js/jquery.magnific-popup.min.js') }}"></script>
    <script src="{{ asset('assets/front/js/jquery.nice-select.min.js') }}"></script>
    <script>
        $(document).ready(function() {

            // Video popup – explicit YouTube pattern so watch?v= URLs embed correctly
            $('.video-popup').magnificPopup({
                type: 'iframe',
                removalDelay: 300,
                mainClass: 'mfp-fade',
                iframe: {
                    patterns: {
                        youtube: {
                            index: 'youtube.com/',
                            id: function(url) {
                                var m = url.match(/[?&]v=([^&#]+)/);
                                if (!m) m = url.match(/youtu\.be\/([^?&]+)/);
                                return m ? m[1] : null;
                            },
                            src: 'https://www.youtube.com/embed/%id%?autoplay=1'
                        },
                        youtube_short: {
                            index: 'youtu.be/',
                            id: function(url) {
                                var m = url.match(/youtu\.be\/([^?&]+)/);
                                return m ? m[1] : null;
                            },
                            src: 'https://www.youtube.com/embed/%id%?autoplay=1'
                        }
                    }
                }
            });

            // Nice select
            $('select').niceSelect();

            // Module accordion header – toggle arrow direction
            $(document).on('click', '.module-header', function() {
                $(this).toggleClass('open');
            });

            // Payment gateway select → show offline details / purchaser info
            $(document).on('change', '#paymentType', function() {
                var val = $(this).val();
                var type = $(this).find('option:checked').data('type');
                $('.gateway-details').hide();
                if (type === 'offline') {
                    $('#tab-' + val).show();
                    $('#purchaserInfo').slideDown();
                } else if (val) {
                    $('#purchaserInfo').slideDown();
                } else {
                    $('#purchaserInfo').slideUp();
                }
            });

            // PAY NOW button
            $(document).on('click', '#purchaseBtn', function() {
                var gw = $('#paymentType').val();
                if (!gw) {
                    $('.payment-warning').fadeIn().delay(2000).fadeOut();
                    return;
                }
                $('#purchaserInfo').slideDown();
                $('html, body').animate({
                    scrollTop: $('#purchaserInfo').offset().top - 100
                }, 400);
            });

            // Form submit guard
            $(document).on('submit', '#paymentGatewayForm', function(e) {
                var gw = $('#paymentType').val();
                if (!gw) {
                    e.preventDefault();
                    $('.payment-warning').fadeIn().delay(2000).fadeOut();
                }
            });

            // "Click here to find your downloads" → switch to Tender Fees tab
            $(document).on('click', '#downloadsLink', function(e) {
                e.preventDefault();
                $('#modules-tab-link').tab('show');
                $('html, body').animate({
                    scrollTop: $('.discription-area').offset().top - 80
                }, 500);
            });

        });

        // ── Dynamic module-selection price calculator ──
        var selectedModules = {};
        var basePrice = {{ is_null($tender->current_price) ? 0 : (float) $tender->current_price }};
        var currencySymbol = '{{ $bse->base_currency_symbol }}';
        var symbolPosition = '{{ $bse->base_currency_symbol_position }}';

        function formatPrice(amount) {
            var num = amount.toLocaleString('fr-FR');
            if (symbolPosition === 'left') return currencySymbol + ' ' + num;
            return num + ' ' + currencySymbol;
        }

        function toggleModule(el) {
            var cost = parseFloat($(el).data('cost')) || 0;
            var id = String($(el).data('module-id'));

            if ($(el).hasClass('selected')) {
                $(el).removeClass('selected').find('i').removeClass('fas fa-unlock').addClass('fas fa-lock');
                delete selectedModules[id];
            } else {
                $(el).addClass('selected').find('i').removeClass('fas fa-lock').addClass('fas fa-unlock');
                selectedModules[id] = cost;
            }

            // Sum selected module costs
            var total = 0;
            $.each(selectedModules, function(k, v) {
                total += v;
            });

            // Update displayed price and hidden form field
            $('#priceAmount').text(total.toLocaleString('fr-FR'));
            $('#selectedAmount').val(total);
        }
    </script>
@endsection
