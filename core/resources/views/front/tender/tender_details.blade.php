@extends("front.$version.layout")

@section('pagename')
    - {{ __('Tender') }} - {{ convertUtf8($tender->title) }}
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/front/css/magnific-popup.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/front/css/nice-select.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/front/css/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/front/css/owl.theme.default.min.css') }}">
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
            pointer-events: none;
            /* overlay itself doesn't block clicks */
        }

        .tender-thumb-box .video-overlay a.video-popup {
            pointer-events: all;
        }

        /* ── Left thumb card ── */
        .tender-thumb-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, .08);
            overflow: hidden;
            margin-bottom: 24px;
        }

        .tender-thumb-card .tender-thumb-box {
            position: relative;
            border-radius: 0;
            box-shadow: none;
        }

        .days-overlay-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            z-index: 3;
            width: 58px;
            height: 58px;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 11px;
            line-height: 1.1;
            text-align: center;
            border: 3px solid #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .2);
        }

        .days-overlay-badge .days-num {
            font-size: 20px;
            line-height: 1;
        }

        .days-overlay-badge.badge-ok {
            background: #e8f8f0;
            color: #27ae60;
            border-color: #27ae60;
        }

        .days-overlay-badge.badge-soon {
            background: #f39c12;
            color: #fff;
        }

        .days-overlay-badge.badge-urgent {
            background: #e74c3c;
            color: #fff;
        }

        .days-overlay-badge.badge-expired {
            background: #aaa;
            color: #fff;
        }

        .td-card-body {
            padding: 16px 20px 20px;
        }

        .td-cat-pill {
            position: absolute;
            bottom: 12px;
            left: 20px;
            z-index: 3;
            display: inline-block;
            background: #4aa4f8;
            color: #fff !important;
            font-size: 12px;
            font-weight: 700;
            padding: 5px 14px;
            border-radius: 20px;
            text-decoration: none;
        }

        .td-cat-pill:hover {
            color: #fff;
            opacity: .85;
        }

        .td-info-row {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }

        .td-deadline-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #27ae60;
            color: #fff;
            font-size: 13px;
            font-weight: 600;
            padding: 5px 14px;
            border-radius: 20px;
        }

        .td-id-pill {
            display: inline-block;
            border: 1.5px solid #bbb;
            color: #555;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 14px;
            border-radius: 20px;
            letter-spacing: .3px;
        }

        .td-title {
            font-size: 15px;
            font-weight: 600;
            color: #222;
            line-height: 1.5;
            margin-bottom: 14px;
        }

        .td-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 12px;
            border-top: 1px solid #f0f0f0;
            font-size: 13px;
            color: #666;
        }

        .td-footer .td-country i {
            color: var(--main-color, #3498db);
            margin-right: 4px;
        }

        .td-footer .td-price {
            font-weight: 800;
            font-size: 16px;
            color: var(--main-color, #3498db);
        }

        .td-footer .td-price.free {
            color: #27ae60;
        }

        /* ── Deadline card ── */
        .deadline-card {
            display: flex;
            align-items: stretch;
            margin-top: 14px;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #e0e0e0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .06);
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

        .deadline-card .dl-icon-col.expired {
            background: #e74c3c;
        }

        .deadline-card .dl-icon-col.urgent {
            background: #e74c3c;
        }

        .deadline-card .dl-icon-col.soon {
            background: #f39c12;
        }

        .deadline-card .dl-icon-col.ok {
            background: #27ae60;
        }

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

        .deadline-card .dl-days-badge .days-num {
            font-size: 18px;
            line-height: 1;
        }

        .deadline-card .dl-days-badge.badge-ok {
            background: #e8f8f0;
            color: #27ae60;
            border: 2px solid #27ae60;
        }

        .deadline-card .dl-days-badge.badge-soon {
            background: #fff3e0;
            color: #f39c12;
            border: 2px solid #f39c12;
        }

        .deadline-card .dl-days-badge.badge-urgent {
            background: #fdecea;
            color: #e74c3c;
            border: 2px solid #e74c3c;
        }

        .deadline-card .dl-days-badge.badge-expired {
            background: #f5f5f5;
            color: #aaa;
            border: 2px solid #ddd;
        }

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

        .deadline-card .dl-country i {
            color: var(--main-color, #3498db);
        }


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

        /* Step badges (1 · 2 · 3) in the checkout flow */
        .step-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: var(--main-color, #3498db);
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            margin-right: 6px;
        }

        /* ── Terms & conditions agreement ── */
        .terms-agree-label {
            display: flex;
            align-items: flex-start;
            gap: 9px;
            font-size: 13.5px;
            color: #475569;
            cursor: pointer;
            margin: 0;
            line-height: 1.5;
        }

        .terms-agree-label input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin-top: 1px;
            flex-shrink: 0;
            cursor: pointer;
            accent-color: var(--main-color, #3498db);
        }

        .terms-agree-label a {
            color: var(--main-color, #3498db);
            font-weight: 600;
            text-decoration: underline;
        }

        /* ── Payment method cards ── */
        .pay-methods {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 12px;
        }

        .pay-method-card {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
            padding: 16px 16px 14px;
            border: 1.5px solid #e3e8ef;
            border-radius: 12px;
            background: #fff;
            cursor: pointer;
            transition: border-color .18s, box-shadow .18s, transform .12s;
            outline: none;
        }

        .pay-method-card:hover {
            border-color: var(--main-color, #3498db);
            box-shadow: 0 6px 18px rgba(52, 152, 219, .12);
            transform: translateY(-2px);
        }

        .pay-method-card:focus-visible {
            border-color: var(--main-color, #3498db);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, .25);
        }

        .pay-method-card.active {
            border-color: var(--main-color, #3498db);
            box-shadow: 0 0 0 2px var(--main-color, #3498db) inset;
            background: #f5fbff;
        }

        .pay-method-card .pm-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            font-size: 20px;
            color: #fff;
            background: linear-gradient(135deg, #4aa4f8, #2c7be5);
        }

        .pay-method-card .pm-icon.pm-stripe {
            background: linear-gradient(135deg, #635bff, #4b45c6);
        }

        .pay-method-card .pm-icon.pm-razorpay {
            background: linear-gradient(135deg, #2d88ff, #0b63ce);
        }

        .pay-method-card .pm-icon.pm-offline {
            background: linear-gradient(135deg, #2c3e50, #1a2533);
        }

        /* Moneroo wordmark logo — no gradient square, render the SVG legibly */
        .pay-method-card .pm-icon.pm-moneroo {
            width: auto;
            min-width: 40px;
            height: 40px;
            padding: 0 10px;
            background: #fff;
            border: 1px solid #e9edf3;
        }

        .pay-method-card .pm-icon.pm-moneroo .pm-logo {
            height: 18px;
            width: auto;
            display: block;
        }

        .pay-method-card .pm-name {
            font-size: 14px;
            font-weight: 700;
            color: #1f2a37;
            line-height: 1.2;
        }

        .pay-method-card .pm-badge {
            font-size: 11px;
            font-weight: 600;
            color: #8794a6;
            text-transform: uppercase;
            letter-spacing: .3px;
        }

        .pay-method-card .pm-check {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: var(--main-color, #3498db);
            color: #fff;
            font-size: 11px;
            display: none;
            align-items: center;
            justify-content: center;
        }

        .pay-method-card.active .pm-check {
            display: inline-flex;
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

        .share-wa {
            background: #25d366;
        }

        .share-li {
            background: #0077b5;
        }

        /* ── Check plans section ── */
        /* "Already purchased" entry point — a full-width, animated CTA banner so
               buyers returning for their files can't miss it (the old plain text link
               did). Gradient body, a continuous shine sweep, a pulsing download icon
               and a looping arrow nudge; hover deepens the shadow and lifts it. */
        .downloads-cta {
            position: relative;
            display: flex;
            align-items: center;
            gap: 18px;
            width: 100%;
            padding: 18px 24px;
            margin-bottom: 20px;
            border-radius: 12px;
            overflow: hidden;
            text-decoration: none;
            color: #fff;
            background: var(--main-color, #3498db);
            background-image:
                linear-gradient(120deg, rgba(255, 255, 255, .16) 0%, rgba(255, 255, 255, 0) 42%),
                linear-gradient(135deg, rgba(255, 255, 255, .10) 0%, rgba(0, 0, 0, .22) 100%);
            box-shadow: 0 8px 22px rgba(0, 0, 0, .16);
            transition: transform .18s ease, box-shadow .18s ease;
        }

        .downloads-cta:hover,
        .downloads-cta:focus {
            transform: translateY(-3px);
            box-shadow: 0 14px 34px rgba(0, 0, 0, .26);
            text-decoration: none;
            color: #fff;
        }

        /* Diagonal light sweep travelling across the banner, forever. */
        .downloads-cta::before {
            content: "";
            position: absolute;
            top: 0;
            left: -60%;
            width: 45%;
            height: 100%;
            background: linear-gradient(100deg, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, .35) 50%, rgba(255, 255, 255, 0) 100%);
            transform: skewX(-18deg);
            animation: dl-shine 3.6s ease-in-out infinite;
        }

        @keyframes dl-shine {
            0% {
                left: -60%;
            }

            55%,
            100% {
                left: 130%;
            }
        }

        .downloads-cta-icon {
            position: relative;
            z-index: 1;
            flex: 0 0 auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .18);
            color: #fff;
            font-size: 22px;
            animation: dl-pulse 2.2s ease-out infinite;
        }

        /* Expanding ring around the icon. */
        @keyframes dl-pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(255, 255, 255, .55);
            }

            70% {
                box-shadow: 0 0 0 14px rgba(255, 255, 255, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(255, 255, 255, 0);
            }
        }

        .downloads-cta-icon i {
            animation: dl-bob 2.2s ease-in-out infinite;
        }

        @keyframes dl-bob {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(3px);
            }
        }

        .downloads-cta-text {
            position: relative;
            z-index: 1;
            flex: 1 1 auto;
            line-height: 1.4;
        }

        .downloads-cta-text strong {
            display: block;
            color: #fff;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: .2px;
        }

        .downloads-cta-text small {
            display: block;
            color: rgba(255, 255, 255, .9);
            font-size: 13px;
        }

        .downloads-cta-arrow {
            position: relative;
            z-index: 1;
            flex: 0 0 auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .18);
            color: #fff;
            font-size: 15px;
            animation: dl-nudge 1.6s ease-in-out infinite;
        }

        @keyframes dl-nudge {

            0%,
            100% {
                transform: translateX(0);
            }

            50% {
                transform: translateX(5px);
            }
        }

        .downloads-cta:hover .downloads-cta-arrow {
            background: rgba(255, 255, 255, .3);
        }

        @media (max-width: 480px) {
            .downloads-cta-arrow {
                display: none;
            }
        }

        /* Respect users who ask for less motion. */
        @media (prefers-reduced-motion: reduce) {

            .downloads-cta::before,
            .downloads-cta-icon,
            .downloads-cta-icon i,
            .downloads-cta-arrow {
                animation: none;
            }
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

        .module-badge.paid-owned {
            background: #ecfdf5 !important;
            border-color: #86efac !important;
            color: #16a34a !important;
            cursor: not-allowed;
            opacity: .85;
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

        /* Admin-authored WYSIWYG content (tender overview, module description,
           etc. — anything rendered inside .content-box) — the sitewide
           "ol, ul { list-style: none; }" reset strips bullets/numbers from
           any <ul>/<ol> a moderator types, with nothing restoring them here.
           .content-box is the shared ancestor for the Overview, Modules, and
           Expert tabs, so this covers all three instead of just modules. */
        .content-box ul,
        .content-box ol {
            padding-left: 20px;
            margin: 0 0 12px;
        }

        .content-box ul {
            list-style: disc;
        }

        .content-box ol {
            list-style: decimal;
        }

        .content-box li {
            color: #555;
            font-size: 14px;
            line-height: 1.7;
            margin-bottom: 4px;
        }

        /* .module-sections-list is its own icon-based checklist (see below) —
           exclude it from the WYSIWYG bullet restoration above; ".content-box
           ul" alone would otherwise outrank ".module-sections-list"'s plain
           class selector and put bullets back next to the check icons. */
        .content-box ul.module-sections-list {
            list-style: none;
            padding: 0;
        }

        .module-sections-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .module-sections-list li {
            font-size: 14px;
            color: #444;
            padding: 6px 0;
            border-bottom: 1px solid #f2f2f2;
            display: flex;
            align-items: center;
        }

        .module-sections-list li:last-child {
            border-bottom: none;
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

        /* ── Mobile tab scroll ── */
        .tab-scroll-wrap {
            position: relative;
        }

        .tab-scroll-wrap .tab-arrow {
            display: none;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 2;
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .12);
            padding: 0;
            line-height: 1;
            font-size: 12px;
            color: #555;
            flex-shrink: 0;
        }

        .tab-scroll-wrap .tab-arrow.arrow-prev {
            left: 0;
        }

        .tab-scroll-wrap .tab-arrow.arrow-next {
            right: 0;
        }

        .tab-scroll-wrap .tab-arrow:hover {
            background: #f5f5f5;
        }

        .tab-nav-scroll {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
        }

        .tab-nav-scroll::-webkit-scrollbar {
            display: none;
        }

        .tab-nav-scroll .nav-tabs {
            flex-wrap: nowrap;
            white-space: nowrap;
            border-bottom: none;
        }

        .tab-nav-scroll .nav-tabs .nav-item {
            flex-shrink: 0;
        }

        @media (max-width: 767px) {
            .tab-scroll-wrap .tab-arrow {
                display: flex;
            }

            .tab-scroll-wrap .tab-nav-scroll {
                /* padding: 0 34px; */
            }

            .course-details-section .discription-area .discription-tabs .nav-tabs .nav-link {
                padding: 10px 12px !important;
                font-size: 12px !important;
                line-height: 1.4 !important;
            }

            .discription-tabs {
                margin-bottom: 20px;
            }

            .course-details-section .discription-area .discription-tabs .nav-tabs {
                margin-bottom: 0px;
                padding: 10px 0;
            }
        }
    </style>
@endsection

@php
    // Normalize stored WhatsApp number to E.164 digits only.
    // Handles: "01630968359", "+8801630968359", "8801630968359", or doubled code "8808801630968359"
    $waDigits = preg_replace('/[^0-9]/', '', $tender->expert_whatsapp ?? '');
    if ($waDigits) {
        // If > 15 digits (E.164 max), a country code was likely prepended twice — strip it
        if (strlen($waDigits) > 15) {
            foreach (
                [
                    '880',
                    '966',
                    '974',
                    '971',
                    '973',
                    '972',
                    '977',
                    '95',
                    '94',
                    '92',
                    '91',
                    '90',
                    '86',
                    '84',
                    '82',
                    '81',
                    '66',
                    '65',
                    '64',
                    '63',
                    '62',
                    '61',
                    '60',
                    '55',
                    '54',
                    '52',
                    '49',
                    '48',
                    '47',
                    '46',
                    '45',
                    '44',
                    '43',
                    '41',
                    '40',
                    '39',
                    '34',
                    '33',
                    '32',
                    '31',
                    '30',
                    '27',
                    '20',
                    '7',
                    '1',
                ]
                as $c
            ) {
                if (str_starts_with($waDigits, $c . $c)) {
                    $waDigits = substr($waDigits, strlen($c));
                    break;
                }
            }
        }
        // Local format: leading 0 without country code — strip 0 (no-op if we can't determine code)
        // Numbers starting with 0 AND no clear country code are left for the admin to fix
    }
@endphp

@section('breadcrumb-title', $bex?->tender_details_title ?? __('Tender Details'))
@section('breadcrumb-subtitle', Str::limit($tender->title, 60))
@section('breadcrumb-link', Str::limit($tender->title, 60))

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
    <section
        class="course-details-section pt-120 pb-120 @if ($be->theme_version == 'dark') dark-tender-details @endif">
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

                {{-- LEFT: Thumb card --}}
                <div class="col-lg-6 mb-4">
                    @php
                        $deadline = $tender->submission_deadline
                            ? \Carbon\Carbon::parse($tender->submission_deadline)
                            : null;
                        $daysLeft = $deadline ? (int) now()->diffInDays($deadline, false) : null;
                        $isExpired = $daysLeft !== null && $daysLeft < 0;
                        $iconState =
                            $daysLeft === null
                                ? null
                                : ($isExpired
                                    ? 'expired'
                                    : ($daysLeft <= 3
                                        ? 'urgent'
                                        : ($daysLeft <= 7
                                            ? 'soon'
                                            : 'ok')));
                    @endphp
                    <div class="tender-thumb-card">
                        {{-- Thumbnail --}}
                        <div class="tender-thumb-box">
                            @if (!empty($tender->tender_image))
                                <img data-src="{{ asset('assets/front/img/tenders/' . $tender->tender_image) }}"
                                    class="lazy" alt="{{ $tender->title }}">
                            @else
                                <img src="{{ asset('assets/admin/img/noimage.jpg') }}" alt="">
                            @endif
                            @if (!empty($tender->video_link))
                                <div class="video-overlay">
                                    <a href="{{ $tender->video_link }}" class="video-popup video-play-button">
                                        <span></span>
                                    </a>
                                </div>
                            @endif
                            {{-- Days badge overlay --}}
                            @if ($daysLeft !== null)
                                <div class="days-overlay-badge badge-{{ $iconState }}">
                                    @if ($isExpired)
                                        <span style="font-size:9px;font-weight:800;">{{ __('EXPIRED') }}</span>
                                    @elseif ($daysLeft === 0)
                                        <span style="font-size:9px;font-weight:800;">{{ __('TODAY') }}</span>
                                    @else
                                        <span class="days-num">{{ $daysLeft }}</span>
                                        <span>{{ __('days') }}</span>
                                    @endif
                                </div>
                            @endif
                            {{-- Category pill: bottom-left overlay --}}
                            @if ($tender->tenderCategory)
                                <a href="{{ route('tenders', ['category_id' => $tender->tender_category_id]) }}"
                                    class="td-cat-pill">
                                    {{ $tender->tenderCategory->name }}
                                </a>
                            @endif
                        </div>

                        <div class="td-card-body">

                            {{-- Date + Tender ID row --}}
                            <div class="td-info-row">
                                @if ($deadline)
                                    <div class="td-deadline-pill">
                                        <i class="far fa-clock"></i> {{ $deadline->format('d M Y, H:i') }}
                                    </div>
                                @endif
                                @if ($tender->tender_code)
                                    <div class="td-id-pill">{{ $tender->tender_code }}</div>
                                @endif
                            </div>

                            {{-- Title --}}
                            <p class="td-title">{{ convertUtf8($tender->title) }}</p>

                            {{-- Footer: country + price --}}
                            <div class="td-footer">
                                <span class="td-country">
                                    <i class="fas fa-map-marker-alt"></i> {{ $tender->country }}
                                </span>
                                <span class="td-price {{ !$tender->current_price ? 'free' : '' }}">
                                    @if (!$tender->current_price)
                                        {{ __('Free') }}
                                    @else
                                        {{ $bse->base_currency_symbol_position == 'left' ? $bse->base_currency_symbol : '' }}{{ number_format($tender->current_price, 0) }}{{ $bse->base_currency_symbol_position == 'right' ? ' ' . $bse->base_currency_symbol : '' }}
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT: Tender Info & Purchase --}}
                <div class="col-lg-6">
                    <div class="tender-info-wrap">
                        {{-- Price — the amount payable *now*. It starts at 0 and only
                             rises as modules are selected; the tender's own total is
                             shown on the card above and is not touched by this. --}}
                        <div class="tender-price-wrap">
                            @if (!$tender->current_price)
                                <span class="price-current free-price" id="displayedPrice">{{ __('Free') }}</span>
                            @else
                                {{-- No struck-through previous_price here: this figure is a
                                     running total of selected modules (starts at 0), so a
                                     "was" price beside it would imply a discount off nothing. --}}
                                <span class="price-current" id="displayedPrice">
                                    {{ $bse->base_currency_symbol_position == 'left' ? $bse->base_currency_symbol : '' }}<span
                                        id="priceAmount">0</span>{{ $bse->base_currency_symbol_position == 'right' ? ' ' . $bse->base_currency_symbol : '' }}
                                </span>
                            @endif
                        </div>

                        {{-- ── Purchase form (paid tender) ── --}}
                        @if ($tender->current_price && !$isExpired)
                            <form method="POST" id="paymentGatewayForm" action="{{ route('tender.purchase.submit') }}"
                                enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="tender_id" value="{{ $tender->id }}">
                                {{-- Payable amount: 0 until modules are picked (see toggleModule) --}}
                                <input type="hidden" name="selected_amount" id="selectedAmount" value="0">
                                <div id="selectedModuleInputs"></div>

                                {{-- STEP 1 · Modules to pay — the buyer picks what to buy first,
                                     because nothing is payable until at least one is selected. --}}
                                @if ($modules->count() > 0)
                                    <div id="modulesToPay" class="mt-2 mb-4">
                                        <h6 class="mb-3 font-weight-bold">
                                            <span class="step-badge">1</span> {{ __('Modules to Pay') }}
                                        </h6>
                                        <div class="check-plans-bar">
                                            {{ __('Select the modules you want to purchase') }}
                                        </div>
                                        <div class="module-badges-row">
                                            @include('front.tender.partials.module_badges')
                                        </div>
                                        <p class="text-danger modules-warning mt-2" style="display:none;">
                                            * {{ __('Please select at least one module to continue.') }}
                                        </p>
                                    </div>
                                @endif

                                {{-- STEP 2 · Your information — always visible so owned modules lock
                                     the moment the buyer is identified (email entered / logged in) --}}
                                <div id="purchaserInfo" class="mt-2">
                                    <h6 class="mb-3 font-weight-bold">
                                        <span class="step-badge">2</span> {{ __('Your Information') }}
                                    </h6>
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
                                                value="{{ Auth::check() ? Auth::user()->email : '' }}"
                                                {{ Auth::check() ? 'readonly' : '' }} required>
                                        </div>
                                        @php
                                            // Pre-select the tender's country when it is on the canonical list.
$preCountry = collect($countries)->firstWhere('name', $tender->country);
$preName = $preCountry['name'] ?? '';
$preDial = $preCountry['dial'] ?? '';
$preFlag = $preCountry['flag'] ?? '';
                                        @endphp

                                        {{-- Phone: dialling code + number as one grouped control --}}
                                        <div class="col-md-6 mb-3">
                                            <div class="phone-group">
                                                <div class="ss ss-dial" data-target="#phoneCodeInput">
                                                    <button type="button" class="ss-toggle">
                                                        <span class="ss-flag">{{ $preFlag }}</span>
                                                        <span class="ss-label">{{ $preDial ?: __('Code') }}</span>
                                                        <i class="ss-caret"></i>
                                                    </button>
                                                    <div class="ss-panel">
                                                        <input type="text" class="ss-search"
                                                            placeholder="{{ __('Search…') }}" autocomplete="off">
                                                        <ul class="ss-list">
                                                            @foreach ($countries as $c)
                                                                <li class="ss-opt" data-value="{{ $c['dial'] }}"
                                                                    data-label="{{ $c['dial'] }}"
                                                                    data-flag="{{ $c['flag'] }}"
                                                                    data-search="{{ $c['name'] }} {{ $c['dial'] }}">
                                                                    <span class="ss-flag">{{ $c['flag'] }}</span>
                                                                    <span class="ss-cname">{{ $c['name'] }}</span>
                                                                    <span class="ss-dialcode">{{ $c['dial'] }}</span>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                        <div class="ss-empty">{{ __('No match') }}</div>
                                                    </div>
                                                </div>
                                                <input type="text" name="phone_number" id="phoneNumber"
                                                    class="phone-input" inputmode="numeric"
                                                    placeholder="{{ __('Phone Number') }} *" maxlength="15" required
                                                    value="{{ Auth::check() ? Auth::user()->phone : '' }}">
                                            </div>
                                            <input type="hidden" name="phone_code" id="phoneCodeInput"
                                                value="{{ $preDial }}">
                                        </div>

                                        {{-- Country: searchable picker (no free typing) --}}
                                        <div class="col-md-6 mb-3">
                                            <div class="ss ss-country" data-target="#countryInput"
                                                data-sync-dial="#phoneCodeInput">
                                                <button type="button" class="ss-toggle">
                                                    <span class="ss-flag">{{ $preFlag }}</span>
                                                    <span class="ss-label {{ $preName ? '' : 'ss-placeholder' }}">
                                                        {{ $preName ?: __('Select Country') . ' *' }}
                                                    </span>
                                                    <i class="ss-caret"></i>
                                                </button>
                                                <div class="ss-panel">
                                                    <input type="text" class="ss-search"
                                                        placeholder="{{ __('Search country…') }}" autocomplete="off">
                                                    <ul class="ss-list">
                                                        @foreach ($countries as $c)
                                                            <li class="ss-opt" data-value="{{ $c['name'] }}"
                                                                data-label="{{ $c['name'] }}"
                                                                data-flag="{{ $c['flag'] }}"
                                                                data-dial="{{ $c['dial'] }}"
                                                                data-search="{{ $c['name'] }}">
                                                                <span class="ss-flag">{{ $c['flag'] }}</span>
                                                                <span class="ss-cname">{{ $c['name'] }}</span>
                                                                <span class="ss-dialcode">{{ $c['dial'] }}</span>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                    <div class="ss-empty">{{ __('No match') }}</div>
                                                </div>
                                            </div>
                                            <input type="hidden" name="country" id="countryInput"
                                                value="{{ $preName }}">
                                            <p class="text-danger country-warning mt-1 mb-0"
                                                style="display:none;font-size:13px;">
                                                * {{ __('Please select a country from the list.') }}
                                            </p>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <input type="text" name="city" class="form-control"
                                                placeholder="{{ __('City') }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <input type="text" name="company_name" class="form-control"
                                                placeholder="{{ __('Company Name') }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <input type="text" name="company_address" class="form-control"
                                                placeholder="{{ __('Company Address') }}">
                                        </div>
                                        <style>
                                            /* ── Searchable select (country / dialling code) ── */
                                            .ss {
                                                position: relative;
                                            }

                                            .ss-toggle {
                                                display: flex;
                                                align-items: center;
                                                justify-content: space-between;
                                                gap: 8px;
                                                width: 100%;
                                                background: #fff;
                                                cursor: pointer;
                                                border: 1px solid #ced4da;
                                                border-radius: .25rem;
                                                padding: .375rem .75rem;
                                                min-height: 45px;
                                                font-size: 1rem;
                                                color: #495057;
                                                text-align: left;
                                            }

                                            .ss-toggle:focus {
                                                outline: none;
                                                border-color: #86b7fe;
                                            }

                                            /* Flag + label sit together on the left; the caret is pushed right. */
                                            .ss-toggle .ss-label {
                                                flex: 1 1 auto;
                                                overflow: hidden;
                                                text-overflow: ellipsis;
                                                white-space: nowrap;
                                            }

                                            .ss-label.ss-placeholder {
                                                color: #8a94a0;
                                            }

                                            .ss-caret {
                                                flex: 0 0 auto;
                                                width: 0;
                                                height: 0;
                                                border-left: 5px solid transparent;
                                                border-right: 5px solid transparent;
                                                border-top: 5px solid #6c757d;
                                            }

                                            .ss-panel {
                                                display: none;
                                                position: absolute;
                                                z-index: 60;
                                                top: calc(100% + 4px);
                                                left: 0;
                                                right: 0;
                                                min-width: 260px;
                                                background: #fff;
                                                border: 1px solid #e2e8f0;
                                                border-radius: 8px;
                                                box-shadow: 0 10px 28px rgba(0, 0, 0, .12);
                                                overflow: hidden;
                                            }

                                            .ss.open .ss-panel {
                                                display: block;
                                            }

                                            .ss-search {
                                                width: 100%;
                                                border: 0;
                                                border-bottom: 1px solid #edf2f7;
                                                padding: 10px 12px;
                                                font-size: 14px;
                                                outline: none;
                                            }

                                            .ss-list {
                                                max-height: 240px;
                                                overflow-y: auto;
                                                margin: 0;
                                                padding: 4px 0;
                                                list-style: none;
                                            }

                                            .ss-opt {
                                                display: flex;
                                                align-items: center;
                                                justify-content: space-between;
                                                gap: 12px;
                                                padding: 8px 12px;
                                                font-size: 14px;
                                                cursor: pointer;
                                            }

                                            .ss-opt:hover,
                                            .ss-opt.active {
                                                background: #f1f5f9;
                                            }

                                            .ss-opt.selected {
                                                background: #e2e8f0;
                                                font-weight: 600;
                                            }

                                            .ss-flag {
                                                flex: 0 0 auto;
                                                font-size: 18px;
                                                line-height: 1;
                                                /* Emoji-capable fonts first; Windows falls back to the ISO letters. */
                                                font-family: "Apple Color Emoji", "Segoe UI Emoji", "Noto Color Emoji",
                                                    "Twemoji Mozilla", sans-serif;
                                            }

                                            .ss-opt .ss-cname {
                                                flex: 1 1 auto;
                                            }

                                            .ss-cname {
                                                overflow: hidden;
                                                text-overflow: ellipsis;
                                                white-space: nowrap;
                                            }

                                            .ss-dialcode {
                                                color: #64748b;
                                                font-size: 13px;
                                                flex: 0 0 auto;
                                            }

                                            .ss-empty {
                                                display: none;
                                                padding: 10px 12px;
                                                color: #94a3b8;
                                                font-size: 14px;
                                                list-style: none;
                                            }

                                            .ss.no-match .ss-empty {
                                                display: block;
                                            }

                                            /* ── Phone: dialling code + number as one control ── */
                                            .phone-group {
                                                display: flex;
                                                align-items: stretch;
                                                border: 1px solid #ced4da;
                                                border-radius: .25rem;
                                                background: #fff;
                                                overflow: visible;
                                            }

                                            .phone-group:focus-within {
                                                border-color: #86b7fe;
                                            }

                                            .phone-group .ss-dial {
                                                flex: 0 0 auto;
                                            }

                                            .phone-group .ss-dial .ss-toggle {
                                                border: 0;
                                                border-right: 1px solid #e2e8f0;
                                                border-radius: .25rem 0 0 .25rem;
                                                min-width: 92px;
                                                background: #f8fafc;
                                            }

                                            .phone-group .phone-input {
                                                flex: 1 1 auto;
                                                min-width: 0;
                                                border: 0;
                                                outline: none;
                                                padding: .375rem .75rem;
                                                font-size: 1rem;
                                                color: #495057;
                                                border-radius: 0 .25rem .25rem 0;
                                                background: transparent;
                                            }

                                            .phone-group .ss-panel {
                                                left: 0;
                                                right: auto;
                                            }

                                            .regno-help {
                                                position: absolute;
                                                top: 50%;
                                                right: 10px;
                                                transform: translateY(-50%);
                                                width: 20px;
                                                height: 20px;
                                                line-height: 20px;
                                                text-align: center;
                                                border-radius: 50%;
                                                background: #e2e8f0;
                                                color: #475569;
                                                font-size: 13px;
                                                font-weight: 700;
                                                cursor: help;
                                                user-select: none;
                                            }

                                            .regno-help .regno-tip {
                                                visibility: hidden;
                                                opacity: 0;
                                                transition: opacity .15s ease;
                                                position: absolute;
                                                bottom: calc(100% + 10px);
                                                right: -6px;
                                                z-index: 20;
                                                width: 260px;
                                                max-width: 78vw;
                                                padding: 10px 12px;
                                                background: #1e293b;
                                                color: #f1f5f9;
                                                font-size: 12.5px;
                                                font-weight: 400;
                                                line-height: 1.5;
                                                text-align: left;
                                                border-radius: 8px;
                                                box-shadow: 0 6px 20px rgba(0, 0, 0, .25);
                                                white-space: normal;
                                            }

                                            .regno-help .regno-tip::after {
                                                content: "";
                                                position: absolute;
                                                top: 100%;
                                                right: 12px;
                                                border: 6px solid transparent;
                                                border-top-color: #1e293b;
                                            }

                                            .regno-help:hover .regno-tip,
                                            .regno-help:focus .regno-tip {
                                                visibility: visible;
                                                opacity: 1;
                                            }
                                        </style>
                                        <div class="col-md-12 mb-3">
                                            <div style="position:relative;">
                                                <input type="text" name="company_registration_no" id="companyRegNo"
                                                    class="form-control"
                                                    placeholder="{{ __('Company Registration No.') }} *" maxlength="100"
                                                    required pattern="[A-Z0-9]+"
                                                    style="text-transform:uppercase;padding-right:38px;">
                                                <span class="regno-help" tabindex="0" role="button"
                                                    aria-label="{{ __('What is this?') }}">?
                                                    <span
                                                        class="regno-tip">{{ __("Your company's official registration or incorporation number (e.g. trade licence / business registration ID). Uppercase letters and numbers only — used to stop the same company buying this tender twice.") }}</span>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-12 mb-3" id="paymentReferenceField" style="display:none;">
                                            <input type="text" name="payment_reference" class="form-control"
                                                placeholder="{{ __('Payment / Transaction Reference (optional)') }}"
                                                maxlength="100" style="text-transform:uppercase;">
                                            <small class="text-muted">
                                                {{ __('Enter your bank transfer reference, gateway transaction ID, or any payment confirmation number. This helps recover your files later.') }}
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                {{-- STEP 3 · Payment method (selectable cards) --}}
                                <div class="pay-section" id="paySection">
                                    <p class="pay-label">
                                        <span class="step-badge">3</span> {{ __('Payment method') }}
                                    </p>
                                    <input type="hidden" name="gateway" id="paymentGateway" value="">
                                    <div class="pay-methods">
                                        @foreach ($paymentGateways as $gw)
                                            <div class="pay-method-card" data-gw="{{ $gw->keyword }}"
                                                data-type="online" role="button" tabindex="0"
                                                aria-label="{{ $gw->name }}">
                                                <span class="pm-icon pm-{{ $gw->keyword }}">
                                                    @if ($gw->keyword === 'stripe')
                                                        <i class="fab fa-cc-stripe"></i>
                                                    @elseif ($gw->keyword === 'razorpay')
                                                        <i class="fas fa-bolt"></i>
                                                    @elseif ($gw->keyword === 'moneroo')
                                                        <img src="{{ asset('assets/front/img/payment/moneroo.svg') }}"
                                                            alt="Moneroo" class="pm-logo">
                                                    @else
                                                        <i class="far fa-credit-card"></i>
                                                    @endif
                                                </span>
                                                <span class="pm-name">{{ $gw->name }}</span>
                                                <span class="pm-badge">{{ __('Card / Online') }}</span>
                                                <span class="pm-check"><i class="fas fa-check"></i></span>
                                            </div>
                                        @endforeach
                                        @foreach ($offlineGateways as $ogw)
                                            <div class="pay-method-card" data-gw="{{ $ogw->id }}"
                                                data-type="offline" role="button" tabindex="0"
                                                aria-label="{{ $ogw->name }}">
                                                <span class="pm-icon pm-offline">
                                                    <i class="fas fa-university"></i>
                                                </span>
                                                <span class="pm-name">{{ $ogw->name }}</span>
                                                <span class="pm-badge">{{ __('Bank / Manual') }}</span>
                                                <span class="pm-check"><i class="fas fa-check"></i></span>
                                            </div>
                                        @endforeach
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
                                            <div
                                            class="gateway-instruction @if ($be->theme_version == 'dark') dark-service-details @endif">
                                            {!! replaceBaseUrl($ogw->instructions) !!}
                                        </div>
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

                                {{-- Stripe card fields (shown only when Stripe selected) --}}
                                <div id="stripeTab" class="d-none mt-3">
                                    <div class="row">
                                        <div class="col-12 mb-2">
                                            <input type="text" name="cardNumber" class="form-control"
                                                placeholder="{{ __('Card Number') }}" disabled>
                                        </div>
                                        <div class="col-4 mb-2">
                                            <input type="text" name="cvcNumber" class="form-control"
                                                placeholder="{{ __('CVC') }}" disabled>
                                        </div>
                                        <div class="col-4 mb-2">
                                            <input type="text" name="month" class="form-control"
                                                placeholder="{{ __('MM') }}" disabled>
                                        </div>
                                        <div class="col-4 mb-2">
                                            <input type="text" name="year" class="form-control"
                                                placeholder="{{ __('YYYY') }}" disabled>
                                        </div>
                                    </div>
                                </div>

                                {{-- Terms & Conditions — mandatory --}}
                                <div class="terms-agree mt-3">
                                    <label class="terms-agree-label">
                                        <input type="checkbox" name="agree_terms" id="agreeTerms" value="1"
                                            required>
                                        <span>
                                            @if ($termsUrl ?? null)
                                                {{ __('Click on this link to read the general') }}
                                                <a href="{{ $termsUrl }}" target="_blank"
                                                    rel="noopener">{{ __('terms and conditions') }}</a>
                                                {{ __('related to the purchase of tender documents.') }}
                                            @else
                                                {{ __('Click on this link to read the general terms and conditions related to the purchase of tender documents.') }}
                                            @endif
                                        </span>
                                    </label>
                                    <p class="text-danger terms-warning mt-1 mb-0" style="display:none;font-size:13px;">
                                        * {{ __('You must accept the terms and conditions to proceed.') }}
                                    </p>
                                </div>

                                {{-- STEP 3 · Confirm. Nothing selected = buy the whole tender (intentional). --}}
                                <button type="submit" id="confirmPurchaseBtn" class="main-btn mt-3">
                                    {{ __('Confirm Purchase') }}
                                </button>
                            </form>
                        @else
                            {{-- Free tender: contact expert buttons --}}
                            {{-- <div class="expert-contact-btns">
                                @if (!empty($tender->expert_whatsapp))
                                    <a href="https://wa.me/{{ $waDigits }}"
                                        class="btn-whatsapp" target="_blank">
                                        <i class="fab fa-whatsapp"></i> {{ __('WhatsApp Expert') }}
                                    </a>
                                @endif
                            </div> --}}
                        @endif

                        @if ($isExpired)
                            <div class="alert alert-danger mt-3" style="border-radius:8px; font-size:14px;">
                                <i class="fas fa-lock mr-1"></i>
                                {{ __('Submission deadline has passed. This tender is no longer available for purchase.') }}
                            </div>
                        @endif

                        {{-- Social share --}}
                        <div class="tender-share">
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}"
                                class="share-fb" target="_blank" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                            <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($tender->title) }}"
                                class="share-tw" target="_blank" title="Twitter"><i class="fab fa-twitter"></i></a>
                            <a href="https://wa.me/?text={{ urlencode($tender->title . ' ' . request()->url()) }}"
                                class="share-wa" target="_blank" rel="noopener" title="WhatsApp"><i
                                    class="fab fa-whatsapp"></i></a>
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
                        <a href="{{ route('find_my_files') }}?tender={{ $tender->slug }}" class="downloads-cta">
                            <span class="downloads-cta-icon"><i class="fas fa-cloud-download-alt"></i></span>
                            <span class="downloads-cta-text">
                                <strong>{{ __('Already purchased this tender?') }}</strong>
                                <small>{{ __('Access and download your files here') }}</small>
                            </span>
                            <span class="downloads-cta-arrow"><i class="fas fa-arrow-right"></i></span>
                        </a>
                        {{-- The badges now live in the checkout form (step 1). They are only
                             repeated here when there is no form to hold them — an expired or
                             free tender — so free modules stay downloadable. --}}
                        @unless ($tender->current_price && !$isExpired)
                            <div class="check-plans-bar">{{ __('Check the plans to purchase') }}</div>
                            <div class="module-badges-row">
                                @include('front.tender.partials.module_badges')
                            </div>
                        @endunless
                    </div>
                </div>
            @endif

            {{-- ═══════════════════════════════════════════
         TABS: Overview / Tender Fees / Expert
    ═══════════════════════════════════════════ --}}
            <div class="row mt-5">
                <div class="col-lg-12">
                    <div class="discription-area">
                        <div class="discription-tabs">
                            <div class="tab-scroll-wrap">
                                <button class="tab-arrow arrow-prev" id="tabArrowPrev"
                                    aria-label="Scroll left">&#8249;</button>
                                <div class="tab-nav-scroll" id="tabNavScroll">
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
                                    </ul>
                                </div>
                                <button class="tab-arrow arrow-next" id="tabArrowNext"
                                    aria-label="Scroll right">&#8250;</button>
                            </div>
                        </div>

                        <div class="tab-content">

                            {{-- Tender Overview --}}
                            <div id="overview" class="tab-pane active">
                                <div class="content-box">
                                    <h4>{{ __('Tender Overview') }}</h4>
                                    <div
                                        class="@if ($be->theme_version == 'dark') dark-service-details @endif">
                                        {!! $tender->overview !!}
                                    </div>
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
                                                    @if (!empty($module->summary))
                                                        <div
                                                            class="mb-3 @if ($be->theme_version == 'dark') dark-service-details @endif">
                                                            {!! $module->summary !!}
                                                        </div>
                                                    @endif
                                                    @if ($module->sections->count() > 0)
                                                        <ul class="module-sections-list">
                                                            @foreach ($module->sections as $section)
                                                                <li>
                                                                    <i class="fas fa-check-circle text-success mr-2"></i>
                                                                    {{ $section->name }}
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
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
                                            @php
                                                $expertHasImage =
                                                    $be->theme_version == 'dark' &&
                                                    !empty($tender->expert_image) &&
                                                    file_exists(
                                                        base_path(
                                                            '../assets/front/img/tender_experts/' .
                                                                $tender->expert_image,
                                                        ),
                                                    );
                                            @endphp
                                            @if ($be->theme_version == 'dark' && !$expertHasImage)
                                                <div class="dark-td-avatar-fallback">
                                                    {{ strtoupper(mb_substr($tender->expert_name, 0, 1)) }}
                                                </div>
                                            @elseif (!empty($tender->expert_image))
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
                                                    <div class="text-box @if ($be->theme_version == 'dark') dark-service-details @endif">
                                                        {!! $tender->expert_details !!}
                                                    </div>
                                                @endif
                                                <div class="expert-contact-btns">
                                                    @if (!empty($tender->expert_whatsapp))
                                                        <a href="https://wa.me/{{ $waDigits }}" class="btn-whatsapp"
                                                            target="_blank">
                                                            <i class="fab fa-whatsapp"></i> {{ __('WhatsApp') }}:
                                                            {{ $tender->expert_whatsapp }}
                                                        </a>
                                                    @endif
                                                    @if (!empty($tender->expert_email))
                                                        <a href="mailto:{{ $tender->expert_email }}" class="btn-phone">
                                                            <i class="fas fa-envelope"></i> {{ $tender->expert_email }}
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

                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════
                 RELATED TENDERS
            ═══════════════════════════════════════════ --}}
            @if (!empty($relatedTenders) && $relatedTenders->count() > 0)
                @if ($be->theme_version == 'dark')
                    <div class="dark-td-related">
                        <div class="dark-td-related-row">
                            <span class="dark-td-related-label">{{ __('Related Tenders') }}</span>
                            <div class="dark-td-related-arrows">
                                <button type="button" id="relTendersPrev" aria-label="Previous">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.2">
                                        <path d="M15 6l-6 6 6 6" />
                                    </svg>
                                </button>
                                <button type="button" id="relTendersNext" aria-label="Next">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.2">
                                        <path d="M9 6l6 6-6 6" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="dark-td-related-track" id="relTendersTrack">
                            @foreach ($relatedTenders as $rt)
                                @php
                                    $rtHasImage =
                                        !empty($rt->tender_image) &&
                                        file_exists(base_path('../assets/front/img/tenders/' . $rt->tender_image));
                                @endphp
                                <a href="{{ route('tender_details', ['slug' => $rt->slug]) }}" class="dark-tender-card">
                                    <div class="dark-tender-thumb @if (!$rtHasImage) no-image @endif"
                                        @if ($rtHasImage) style="background-image: url('{{ asset('assets/front/img/tenders/' . $rt->tender_image) }}');" @endif>
                                        @if (!$rtHasImage)
                                            <svg viewBox="0 0 24 24">
                                                <path
                                                    d="M7 3h8l4 4v14a1 1 0 01-1 1H7a1 1 0 01-1-1V4a1 1 0 011-1z" />
                                                <path d="M15 3v4h4" />
                                                <path d="M9 12h6M9 16h6M9 8h2" />
                                            </svg>
                                        @endif
                                        @if (!empty($rt->tenderCategory))
                                            <span class="dark-tender-cat">{{ convertUtf8($rt->tenderCategory->name) }}</span>
                                        @endif
                                        @if ($rt->submission_deadline)
                                            <div class="dark-tender-countdown"
                                                data-deadline="{{ \Carbon\Carbon::parse($rt->submission_deadline)->toIso8601String() }}">
                                                <span class="unit"><b data-d>00</b><span>{{ __('d') }}</span></span>
                                                <span class="unit"><b data-h>00</b><span>{{ __('h') }}</span></span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="dark-tender-body">
                                        <h3>{{ Str::limit(convertUtf8($rt->title), 60) }}</h3>
                                        <div class="dark-tender-meta">
                                            <span><svg viewBox="0 0 24 24">
                                                    <path
                                                        d="M12 21s7-6.5 7-12a7 7 0 10-14 0c0 5.5 7 12 7 12z" />
                                                    <circle cx="12" cy="9" r="2.5" />
                                                </svg>{{ $rt->country }}</span>
                                        </div>
                                        <div class="dark-tender-foot">
                                            <div class="dark-tender-price">
                                                @if (is_null($rt->current_price))
                                                    <span class="now">{{ __('Free') }}</span>
                                                @else
                                                    <span class="now">
                                                        {{ optional($bse)->base_currency_symbol_position == 'left' ? optional($bse)->base_currency_symbol . ' ' : '' }}{{ number_format($rt->current_price, 0) }}
                                                        <small>{{ optional($bse)->base_currency_symbol_position == 'right' ? optional($bse)->base_currency_symbol : '' }}</small>
                                                    </span>
                                                @endif
                                            </div>
                                            <span class="dark-tender-cta"><svg viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2.2">
                                                    <path d="M5 12h14M13 6l6 6-6 6" />
                                                </svg></span>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @else
                    <style>
                        .related-tenders-title {
                            font-size: 22px;
                            font-weight: 700;
                            color: #1f2a37;
                            margin-bottom: 22px;
                        }

                        .rt-card {
                            background: #fff;
                            border: 1px solid #eef1f5;
                            border-radius: 14px;
                            overflow: hidden;
                            box-shadow: 0 4px 18px rgba(0, 0, 0, .05);
                            transition: transform .18s, box-shadow .18s;
                            height: 100%;
                            display: flex;
                            flex-direction: column;
                        }

                        .rt-card:hover {
                            transform: translateY(-4px);
                            box-shadow: 0 10px 26px rgba(0, 0, 0, .1);
                        }

                        .rt-thumb {
                            position: relative;
                            display: block;
                            height: 170px;
                            overflow: hidden;
                            background: #f2f4f7;
                        }

                        .rt-thumb img {
                            width: 100%;
                            height: 100%;
                            object-fit: cover;
                            transition: transform .3s;
                        }

                        .rt-card:hover .rt-thumb img {
                            transform: scale(1.05);
                        }

                        .rt-cat {
                            position: absolute;
                            top: 10px;
                            left: 10px;
                            background: #4aa4f8;
                            color: #fff;
                            font-size: 11px;
                            font-weight: 700;
                            padding: 4px 12px;
                            border-radius: 20px;
                        }

                        .rt-body {
                            padding: 14px 16px 16px;
                            display: flex;
                            flex-direction: column;
                            flex: 1;
                        }

                        .rt-deadline {
                            font-size: 12px;
                            font-weight: 600;
                            color: #e74c3c;
                            margin-bottom: 8px;
                        }

                        .rt-name {
                            font-size: 15px;
                            font-weight: 600;
                            line-height: 1.45;
                            margin: 0 0 12px;
                            flex: 1;
                        }

                        .rt-name a {
                            color: #222;
                            text-decoration: none;
                        }

                        .rt-name a:hover {
                            color: var(--main-color, #3498db);
                        }

                        .rt-meta {
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            border-top: 1px solid #f0f2f5;
                            padding-top: 10px;
                            font-size: 13px;
                            color: #666;
                        }

                        .rt-meta .rt-country i {
                            color: var(--main-color, #3498db);
                            margin-right: 4px;
                        }

                        .rt-meta .rt-price {
                            font-weight: 800;
                            color: var(--main-color, #3498db);
                        }

                        .rt-meta .rt-price.free {
                            color: #27ae60;
                        }
                    </style>
                    <div class="row mt-5 pt-4" style="border-top:1px solid #eef1f5;">
                        <div class="col-12">
                            <h3 class="related-tenders-title">{{ __('Related Tenders') }}</h3>
                            <div class="related-tenders-carousel owl-carousel owl-theme">
                                @foreach ($relatedTenders as $rt)
                                    <div class="rt-item">
                                        <div class="rt-card">
                                            <a href="{{ route('tender_details', ['slug' => $rt->slug]) }}"
                                                class="rt-thumb">
                                                @if (!empty($rt->tender_image))
                                                    <img src="{{ asset('assets/front/img/tenders/' . $rt->tender_image) }}"
                                                        alt="{{ $rt->title }}">
                                                @else
                                                    <img src="{{ asset('assets/admin/img/noimage.jpg') }}" alt="">
                                                @endif
                                                @if ($rt->tenderCategory)
                                                    <span class="rt-cat">{{ $rt->tenderCategory->name }}</span>
                                                @endif
                                            </a>
                                            <div class="rt-body">
                                                @if ($rt->submission_deadline)
                                                    <div class="rt-deadline">
                                                        <i class="far fa-clock"></i>
                                                        {{ \Carbon\Carbon::parse($rt->submission_deadline)->format('d M Y') }}
                                                    </div>
                                                @endif
                                                <h4 class="rt-name">
                                                    <a href="{{ route('tender_details', ['slug' => $rt->slug]) }}">
                                                        {{ Str::limit(convertUtf8($rt->title), 60) }}
                                                    </a>
                                                </h4>
                                                <div class="rt-meta">
                                                    <span class="rt-country">
                                                        <i class="fas fa-map-marker-alt"></i> {{ $rt->country }}
                                                    </span>
                                                    <span
                                                        class="rt-price {{ is_null($rt->current_price) ? 'free' : '' }}">
                                                        @if (is_null($rt->current_price))
                                                            {{ __('Free') }}
                                                        @else
                                                            {{ $bse->base_currency_symbol_position == 'left' ? $bse->base_currency_symbol : '' }}{{ number_format($rt->current_price, 0) }}{{ $bse->base_currency_symbol_position == 'right' ? ' ' . $bse->base_currency_symbol : '' }}
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            @endif

        </div>
    </section>
@endsection

@section('scripts')
    <script src="{{ asset('assets/front/js/jquery.magnific-popup.min.js') }}"></script>
    <script src="{{ asset('assets/front/js/jquery.nice-select.min.js') }}"></script>
    <script src="{{ asset('assets/front/js/owl.carousel.min.js') }}"></script>
    <script>
        // ── Related tenders carousel (auto-scroll) ──
        $(function() {
            var $rel = $('.related-tenders-carousel');
            if ($rel.length && $.fn.owlCarousel) {
                var count = $rel.children().length;
                $rel.owlCarousel({
                    margin: 24,
                    loop: count > 3,
                    autoplay: true,
                    autoplayTimeout: 3000,
                    autoplaySpeed: 800,
                    smartSpeed: 800,
                    autoplayHoverPause: true,
                    dots: true,
                    nav: false,
                    responsive: {
                        0: {
                            items: 1
                        },
                        576: {
                            items: 2
                        },
                        992: {
                            items: 3
                        }
                    }
                });
            }
        });

        // ── Mobile tab scroll arrows ──
        (function() {
            var $scroll = $('#tabNavScroll');
            var $prev = $('#tabArrowPrev');
            var $next = $('#tabArrowNext');
            var step = 120;

            function updateArrows() {
                var el = $scroll[0];
                $prev.css('opacity', el.scrollLeft > 0 ? 1 : 0.3);
                $next.css('opacity', el.scrollLeft + el.clientWidth < el.scrollWidth - 1 ? 1 : 0.3);
            }

            $prev.on('click', function() {
                $scroll.animate({
                    scrollLeft: $scroll.scrollLeft() - step
                }, 200, updateArrows);
            });

            $next.on('click', function() {
                $scroll.animate({
                    scrollLeft: $scroll.scrollLeft() + step
                }, 200, updateArrows);
            });

            $scroll.on('scroll', updateArrows);
            updateArrows();
        })();

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

            var onlineRoutes = {
                'stripe': '{{ route('tender.payment.stripe') }}',
                'razorpay': '{{ route('tender.payment.razorpay') }}',
                'moneroo': '{{ route('tender.payment.moneroo') }}',
            };
            var offlineAction = '{{ route('tender.purchase.submit') }}';

            function updateFormAction(gw, type) {
                if (type === 'offline') {
                    $('#paymentGatewayForm').attr('action', offlineAction);
                } else if (onlineRoutes[gw]) {
                    $('#paymentGatewayForm').attr('action', onlineRoutes[gw]);
                } else {
                    $('#paymentGatewayForm').attr('action', offlineAction);
                }

                // Stripe card fields
                if (gw === 'stripe') {
                    $('#stripeTab').removeClass('d-none');
                    $('#stripeTab input').removeAttr('disabled');
                } else {
                    $('#stripeTab').addClass('d-none');
                    $('#stripeTab input').attr('disabled', true);
                }

                // Payment reference: only for offline
                if (type === 'offline') {
                    $('#paymentReferenceField').show();
                } else {
                    $('#paymentReferenceField').hide();
                    $('#paymentReferenceField input').val('');
                }
            }

            // Payment method cards → select gateway, reveal offline details
            $(document).on('click', '.pay-method-card', function() {
                var gw = String($(this).data('gw'));
                var type = $(this).data('type');
                $('.pay-method-card').removeClass('active');
                $(this).addClass('active');
                $('#paymentGateway').val(gw);
                $('.payment-warning').stop(true, true).hide();
                $('.gateway-details').hide();
                updateFormAction(gw, type);
                if (type === 'offline') {
                    $('#tab-' + gw).show();
                }
            });

            // Keyboard accessibility for the cards
            $(document).on('keydown', '.pay-method-card', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    $(this).trigger('click');
                }
            });

            // Form submit guard: must pick a gateway; block when buyer already owns everything
            $(document).on('submit', '#paymentGatewayForm', function(e) {
                // Step 1 first: there is nothing to pay for until a module is picked.
                if ($('.module-badge.paid-badge').length && !$('#selectedModuleInputs input').length) {
                    e.preventDefault();
                    $('.modules-warning').stop(true, true).fadeIn().delay(2500).fadeOut();
                    $('html, body').animate({
                        scrollTop: $('#modulesToPay').offset().top - 120
                    }, 350);
                    return;
                }

                var gw = $('#paymentGateway').val();
                if (!gw) {
                    e.preventDefault();
                    $('.payment-warning').fadeIn().delay(2000).fadeOut();
                    $('html, body').animate({
                        scrollTop: $('#paySection').offset().top - 120
                    }, 350);
                    return;
                }
                // Country/phone code live in hidden inputs (custom pickers), so the
                // browser's `required` does not cover them — check here.
                if (!$('#countryInput').val() || !$('#phoneCodeInput').val()) {
                    e.preventDefault();
                    $('.country-warning').stop(true, true).fadeIn().delay(2500).fadeOut();
                    $('html, body').animate({
                        scrollTop: $('#countryInput').offset().top - 160
                    }, 350);
                    return;
                }
                if (!$('#agreeTerms').is(':checked')) {
                    e.preventDefault();
                    $('.terms-warning').stop(true, true).fadeIn().delay(2500).fadeOut();
                    $('html, body').animate({
                        scrollTop: $('.terms-agree').offset().top - 140
                    }, 350);
                    return;
                }
                if ($('#confirmPurchaseBtn').prop('disabled')) {
                    e.preventDefault();
                }
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

        // Payable amount = sum of the selected modules only. Nothing selected means
        // nothing to pay, so the total reads 0 and the submit guard blocks checkout.
        function selectedTotal() {
            var total = 0;
            $.each(selectedModules, function(k, v) {
                total += v;
            });
            return total;
        }

        function toggleModule(el) {
            // Already-paid modules cannot be re-selected (duplicate-payment guard)
            if ($(el).hasClass('paid-owned')) {
                return;
            }

            var cost = parseFloat($(el).data('cost')) || 0;
            var id = String($(el).data('module-id'));

            if ($(el).hasClass('selected')) {
                $(el).removeClass('selected').find('i').removeClass('fas fa-unlock').addClass('fas fa-lock');
                delete selectedModules[id];
            } else {
                $(el).addClass('selected').find('i').removeClass('fas fa-lock').addClass('fas fa-unlock');
                selectedModules[id] = cost;
            }

            // Update displayed price and hidden form field
            var displayTotal = selectedTotal();
            $('#priceAmount').text(displayTotal.toLocaleString('fr-FR'));
            $('#selectedAmount').val(displayTotal);

            // Sync selected module IDs as hidden inputs
            var container = $('#selectedModuleInputs');
            container.empty();
            $.each(selectedModules, function(moduleId) {
                container.append('<input type="hidden" name="selected_module_ids[]" value="' + moduleId + '">');
            });

            if (Object.keys(selectedModules).length > 0) {
                $('.modules-warning').stop(true, true).hide();
            }
        }

        // ── Duplicate-payment guard ───────────────────────────────────────────
        (function() {
            var tenderId = {{ (int) $tender->id }};
            var $regNo = $('#companyRegNo');
            var lastReg = '';

            // ── Searchable select (country + dialling code) ──────────────────────
            // Native <select> is not used: the theme runs $('select').niceSelect(),
            // which wraps every select and offers no search.

            // Scoped to the toggle: `.ss-flag` also exists on every option row.
            function ssPaintToggle($ss, flag, label) {
                var $toggle = $ss.children('.ss-toggle');
                $toggle.children('.ss-flag').text(flag || '');
                $toggle.children('.ss-label').text(label).removeClass('ss-placeholder');
            }

            function ssSelect($ss, $opt) {
                $($ss.data('target')).val($opt.data('value'));
                ssPaintToggle($ss, $opt.data('flag'), $opt.data('label'));
                $ss.find('.ss-opt').removeClass('selected');
                $opt.addClass('selected');

                // Picking a country switches the phone dialling code (and flag) to match.
                var syncSel = $ss.data('sync-dial');
                var dial = $opt.data('dial');
                if (syncSel && dial) {
                    var $dial = $('.ss-dial');
                    $(syncSel).val(dial);
                    ssPaintToggle($dial, $opt.data('flag'), dial);
                    $dial.find('.ss-opt').removeClass('selected')
                        .filter('[data-value="' + dial + '"]').first().addClass('selected');
                }
            }

            function ssClose($ss) {
                $ss.removeClass('open no-match');
                $ss.find('.ss-search').val('');
                $ss.find('.ss-opt').show();
            }

            $(document).on('click', '.ss-toggle', function(e) {
                e.preventDefault();
                var $ss = $(this).closest('.ss');
                var wasOpen = $ss.hasClass('open');
                $('.ss').each(function() {
                    ssClose($(this));
                });
                if (!wasOpen) {
                    $ss.addClass('open');
                    $ss.find('.ss-search').focus();
                }
            });

            $(document).on('input', '.ss-search', function() {
                var $ss = $(this).closest('.ss');
                var q = ($(this).val() || '').toLowerCase().trim();
                var hits = 0;
                $ss.find('.ss-opt').each(function() {
                    var match = !q || String($(this).data('search')).toLowerCase().indexOf(q) > -1;
                    $(this).toggle(match);
                    if (match) hits++;
                });
                $ss.toggleClass('no-match', hits === 0);
            });

            $(document).on('click', '.ss-opt', function() {
                var $ss = $(this).closest('.ss');
                ssSelect($ss, $(this));
                ssClose($ss);
                $('.country-warning').hide();
            });

            // Click outside closes any open picker.
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.ss').length) {
                    $('.ss').each(function() {
                        ssClose($(this));
                    });
                }
            });

            $(document).on('keydown', '.ss', function(e) {
                if (e.key === 'Escape') {
                    ssClose($(this));
                }
            });

            // National number only — digits, no dialling code (that is the picker).
            $(document).on('input', '#phoneNumber', function() {
                var el = this;
                var start = el.selectionStart;
                var before = el.value || '';
                var cleaned = before.replace(/\D/g, '');
                if (cleaned !== before) {
                    var removed = before.length - cleaned.length;
                    el.value = cleaned;
                    var pos = Math.max(0, (start || 0) - removed);
                    el.setSelectionRange(pos, pos);
                }
            });

            // Force the registration number to uppercase alphanumeric as the buyer
            // types, matching how it is stored and matched server-side.
            $(document).on('input', '#companyRegNo', function() {
                var el = this;
                var start = el.selectionStart;
                var before = el.value || '';
                var cleaned = before.toUpperCase().replace(/[^A-Z0-9]/g, '');
                if (cleaned !== before) {
                    // Move the caret back only by the count of characters actually
                    // removed; pure uppercasing (no removal) leaves it in place.
                    var removed = before.length - cleaned.length;
                    el.value = cleaned;
                    var pos = Math.max(0, (start || 0) - removed);
                    el.setSelectionRange(pos, pos);
                }
            });

            function recomputeTotal() {
                var displayTotal = selectedTotal();
                $('#priceAmount').text(displayTotal.toLocaleString('fr-FR'));
                $('#selectedAmount').val(displayTotal);
                var c = $('#selectedModuleInputs');
                c.empty();
                $.each(selectedModules, function(moduleId) {
                    c.append('<input type="hidden" name="selected_module_ids[]" value="' + moduleId + '">');
                });
            }

            function applyPaid(paidIds, allPaid) {
                // Reset previous paid markings
                $('.module-badge.paid-owned').removeClass('paid-owned').find('.paid-owned-tag').remove();

                $.each(paidIds, function(i, pid) {
                    var $b = $('.module-badge[data-module-id="' + pid + '"]');
                    if (!$b.length) return;
                    // Deselect if currently selected
                    if ($b.hasClass('selected')) {
                        $b.removeClass('selected').find('i').removeClass('fa-unlock').addClass('fa-lock');
                        delete selectedModules[String(pid)];
                    }
                    $b.addClass('paid-owned');
                    if (!$b.find('.paid-owned-tag').length) {
                        $b.find('span').first().append(
                            ' <small class="paid-owned-tag" style="color:#16a34a;font-weight:700;">✓ {{ __('Paid') }}</small>'
                        );
                    }
                });

                recomputeTotal();

                if (allPaid) {
                    // Terminal state: nothing left to buy → hide gateway + block submit
                    showAllPaidNotice();
                    $('#paySection').hide();
                    $('#confirmPurchaseBtn').prop('disabled', true)
                        .css({
                            opacity: 0.5,
                            cursor: 'not-allowed'
                        });
                } else {
                    $('#dupPaidNotice').remove();
                    $('#paySection').show();
                    $('#confirmPurchaseBtn').prop('disabled', false)
                        .css({
                            opacity: '',
                            cursor: ''
                        });
                }
            }

            function showAllPaidNotice() {
                if ($('#dupPaidNotice').length) return;
                var html =
                    '<div id="dupPaidNotice" class="alert mt-3" style="background:#ecfdf5;border:1px solid #86efac;color:#065f46;border-radius:8px;padding:14px 16px;">' +
                    '<strong>{{ __('You have already paid for all modules of this tender.') }}</strong><br>' +
                    '<span style="font-size:13px;">{{ __('No further payment is required. Get a fresh secure download link emailed to you.') }}</span>' +
                    '<div class="mt-2"><button type="button" id="emailNewLinkBtn" class="btn btn-success btn-sm">{{ __('Email me a new download link') }}</button> ' +
                    '<span id="emailNewLinkMsg" style="font-size:13px;margin-left:8px;"></span></div></div>';
                $('#purchaserInfo').append(html);
            }

            function checkPaidModules() {
                var reg = ($regNo.val() || '').toUpperCase().replace(/[^A-Z0-9]/g, '');
                if (!reg || reg === lastReg || reg.length < 2) return;
                lastReg = reg;

                $.ajax({
                    url: '{{ route('tender.paid_modules') }}',
                    method: 'POST',
                    data: {
                        _token: $('#paymentGatewayForm input[name="_token"]').val(),
                        tender_id: tenderId,
                        company_registration_no: reg
                    },
                    success: function(res) {
                        applyPaid(res.paid_module_ids || [], !!res.all_paid);
                    }
                });
            }

            $(document).on('blur', '#companyRegNo', checkPaidModules);

            // Re-check while typing (debounced) so owned modules lock without needing blur
            var typeTimer = null;
            $(document).on('input', '#companyRegNo', function() {
                clearTimeout(typeTimer);
                typeTimer = setTimeout(checkPaidModules, 600);
            });

            // Re-check when a gateway is picked, covering edits made before selecting.
            $(document).on('click', '.pay-method-card', function() {
                setTimeout(checkPaidModules, 60);
            });

            // Run once on load in case the field is pre-filled.
            checkPaidModules();

            // Regenerate link for the all-paid case (reuses Find My Files regenerate)
            $(document).on('click', '#emailNewLinkBtn', function() {
                var $btn = $(this);
                var email = ($('#paymentGatewayForm input[name="email"]').val() || '').trim();

                // Need the buyer's email to send the link.
                if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    $('#emailNewLinkMsg').text('{{ __('Please enter your email address above first.') }}')
                        .css('color', '#dc2626');
                    return;
                }

                var errMsg = {
                    'validation':     '{{ __('Please enter a valid email address.') }}',
                    'email_mismatch': '{{ __('This email does not match the order for this registration number. Enter the email used for the purchase.') }}',
                    'rate_limited':   '{{ __('Too many requests. Please wait a few minutes and try again.') }}',
                    'regen_limit':    '{{ __('You have reached the limit of new links for today. Please try again tomorrow.') }}',
                    'email_failed':   '{{ __('The email could not be sent right now. Please try again shortly or contact support.') }}',
                    'suspended':      '{{ __('This order is under verification. Please contact ICA.') }}'
                };

                $btn.prop('disabled', true);
                $('#emailNewLinkMsg').text('{{ __('Sending...') }}').css('color', '#065f46');
                $.ajax({
                    url: '{{ route('find_my_files.regenerate') }}',
                    method: 'POST',
                    data: {
                        _token: $('#paymentGatewayForm input[name="_token"]').val(),
                        email: email,
                        tender_id: tenderId,
                        company_registration_no: (($('#companyRegNo').val() || '').toUpperCase().replace(/[^A-Z0-9]/g, ''))
                    },
                    success: function(res) {
                        if (res.status === 'success') {
                            $('#emailNewLinkMsg').text('{{ __('Link sent! Check your email.') }}')
                                .css('color', '#16a34a');
                        } else {
                            $btn.prop('disabled', false);
                            $('#emailNewLinkMsg')
                                .text(errMsg[res.type] || '{{ __('Could not send. Try again shortly.') }}')
                                .css('color', '#dc2626');
                        }
                    },
                    error: function() {
                        $btn.prop('disabled', false);
                        $('#emailNewLinkMsg').text(
                            '{{ __('Could not send. Try again shortly.') }}').css('color',
                            '#dc2626');
                    }
                });
            });
        })();
    </script>
    @if ($be->theme_version == 'dark')
        <script>
            (function() {
                var track = document.getElementById('relTendersTrack');
                if (!track) return;
                var prev = document.getElementById('relTendersPrev');
                var next = document.getElementById('relTendersNext');
                var card = track.querySelector('.dark-tender-card');
                function step() {
                    return card ? card.getBoundingClientRect().width + 22 : 320;
                }
                function updateArrows() {
                    prev.disabled = track.scrollLeft <= 2;
                    next.disabled = track.scrollLeft + track.clientWidth >= track.scrollWidth - 2;
                }
                prev.addEventListener('click', function() {
                    track.scrollBy({
                        left: -step(),
                        behavior: 'smooth'
                    });
                });
                next.addEventListener('click', function() {
                    track.scrollBy({
                        left: step(),
                        behavior: 'smooth'
                    });
                });
                track.addEventListener('scroll', updateArrows);
                updateArrows();
            })();
        </script>
    @endif
@endsection
