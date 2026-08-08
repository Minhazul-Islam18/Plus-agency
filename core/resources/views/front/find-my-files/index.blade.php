@extends("front.$version.layout")

@section('pagename')
    - {{ __('Secure File Recovery') }}
@endsection

@section('breadcrumb-title', __('Secure File Recovery'))
@section('breadcrumb-subtitle', __('Recover your purchased tender documents securely'))
@section('breadcrumb-link', __('Find My Files'))

@section('styles')
    <style>
        /* ── Page wrapper ── */
        .fmf-section {
            padding: 60px 0 80px;
            background: #f4f6f9;
            min-height: 70vh;
        }

        /* ── Card shell ── */
        .fmf-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, .09);
            overflow: hidden;
        }

        /* ── Left sidebar — method selector ── */
        .fmf-methods {
            background: #f8f9fb;
            border-right: 1px solid #e8ecf0;
            padding: 28px 0;
            min-height: 380px;
        }

        .fmf-methods h6 {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #8a94a6;
            padding: 0 24px 12px;
            margin: 0;
            border-bottom: 1px solid #e8ecf0;
        }

        .fmf-method-btn {
            display: block;
            width: 100%;
            text-align: left;
            padding: 13px 24px;
            background: none;
            border: none;
            border-left: 3px solid transparent;
            font-size: 14px;
            color: #4a5568;
            cursor: pointer;
            transition: all .2s;
            line-height: 1.4;
        }

        .fmf-method-btn:hover {
            background: #eef1f6;
            color: #2d3748;
        }

        .fmf-method-btn.active {
            background: #e8f0fe;
            border-left-color: #3b6cf8;
            color: #3b6cf8;
            font-weight: 600;
        }

        .fmf-method-btn.disabled-method {
            color: #b0b8c9;
            cursor: default;
        }

        .fmf-method-btn.disabled-method:hover {
            background: none;
        }

        /* ── Right panel — form ── */
        .fmf-form-panel {
            padding: 32px 36px 28px;
        }

        .fmf-form-panel h6 {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #8a94a6;
            margin-bottom: 20px;
        }

        .fmf-form-panel .form-label {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 5px;
        }

        .fmf-form-panel .form-control {
            height: 44px;
            border-radius: 6px;
            border: 1px solid #d1d9e0;
            font-size: 14px;
            color: #2d3748;
            transition: border-color .2s, box-shadow .2s;
        }

        .fmf-form-panel .form-control:focus {
            border-color: #3b6cf8;
            box-shadow: 0 0 0 3px rgba(59, 108, 248, .12);
        }

        .fmf-form-panel .form-control::placeholder {
            color: #b0b8c9;
        }

        .fmf-method-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 220px;
            color: #b0b8c9;
            text-align: center;
        }

        .fmf-method-placeholder i {
            font-size: 40px;
            margin-bottom: 12px;
            color: #d1d9e0;
        }

        /* ── Submit button ── */
        .btn-fmf-submit {
            width: 100%;
            padding: 13px;
            background: #3b6cf8;
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background .2s, opacity .2s;
            margin-top: 6px;
        }

        .btn-fmf-submit:hover:not(:disabled) {
            background: #2a58e0;
        }

        .btn-fmf-submit:disabled {
            opacity: .5;
            cursor: not-allowed;
        }

        /* ── reCAPTCHA wrapper ── */
        .fmf-captcha-wrap {
            margin: 16px 0 12px;
        }

        /* ── Security best practices box ── */
        .fmf-security-box {
            background: #f0f4ff;
            border: 1px solid #d0dbf7;
            border-radius: 8px;
            padding: 18px 22px;
            margin-top: 28px;
            font-size: 13px;
            color: #4a5568;
        }

        .fmf-security-box h6 {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: #3b6cf8;
            margin-bottom: 10px;
        }

        .fmf-security-box ul {
            margin: 0;
            padding-left: 18px;
        }

        .fmf-security-box ul li {
            margin-bottom: 4px;
            line-height: 1.5;
        }

        /* ── Error alerts ── */
        .fmf-alert {
            display: none;
            align-items: flex-start;
            gap: 12px;
            border-radius: 7px;
            padding: 13px 16px;
            margin-bottom: 14px;
            font-size: 14px;
            line-height: 1.5;
        }

        .fmf-alert.fmf-alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #7f1d1d;
        }

        .fmf-alert.fmf-alert-warning {
            background: #fffbeb;
            border: 1px solid #fcd34d;
            color: #78350f;
        }

        /* The red circle ! icon */
        .fmf-alert-icon {
            flex-shrink: 0;
            width: 22px;
            height: 22px;
            background: #ef4444;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 1px;
        }

        .fmf-alert-icon span {
            color: #fff;
            font-weight: 700;
            font-size: 13px;
            line-height: 1;
        }

        .fmf-alert-body strong {
            display: block;
            font-weight: 700;
            color: #991b1b;
            margin-bottom: 2px;
        }

        .fmf-alert-body p {
            margin: 0;
            color: #7f1d1d;
            font-size: 14px;
        }

        .fmf-alert-body a {
            color: #991b1b;
            font-weight: 600;
            text-decoration: underline;
        }

        /* ── Spinner overlay ── */
        .fmf-overlay {
            display: none;
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, .88);
            z-index: 100;
            border-radius: 10px;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 18px;
        }

        .fmf-overlay.active {
            display: flex;
        }

        .fmf-spinner {
            width: 52px;
            height: 52px;
            border: 5px solid #e2e8f0;
            border-top-color: #3b6cf8;
            border-radius: 50%;
            animation: fmfSpin .75s linear infinite;
        }

        @keyframes fmfSpin {
            to {
                transform: rotate(360deg);
            }
        }

        .fmf-overlay p {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: #374151;
        }

        /* ── Processing heading ── */
        .fmf-processing-title {
            display: none;
        }

        /* ── Mobile overlay (full screen) ── */
        @media (max-width: 767px) {
            .fmf-overlay {
                position: fixed;
                border-radius: 0;
            }
        }

        /* ── Mobile adjustments ── */
        @media (max-width: 767px) {
            .fmf-section {
                padding: 30px 0 50px;
            }

            .fmf-methods {
                border-right: none;
                border-bottom: 1px solid #e8ecf0;
                min-height: unset;
                padding: 16px 0;
            }

            .fmf-form-panel {
                padding: 24px 20px 20px;
            }

            .fmf-method-btn {
                padding: 11px 20px;
                font-size: 14px;
            }
        }

        /* ── OTP digit boxes ── */
        .otp-digits {
            display: flex;
            gap: 10px;
            margin: 18px 0 6px;
            justify-content: center;
        }

        .otp-digit {
            width: 46px;
            height: 54px;
            text-align: center;
            font-size: 22px;
            font-weight: 700;
            border: 2px solid #d1d9e0;
            border-radius: 8px;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
            color: #2d3748;
            background: #fff;
        }

        .otp-digit:focus {
            border-color: #3b6cf8;
            box-shadow: 0 0 0 3px rgba(59, 108, 248, .14);
        }

        .otp-digit.filled {
            border-color: #3b6cf8;
        }

        /* ── Countdown timer ── */
        .otp-timer {
            font-size: 13px;
            color: #6b7280;
            text-align: center;
            margin: 8px 0 14px;
        }

        .otp-timer span {
            font-weight: 700;
            color: #374151;
        }

        .otp-timer.urgent span {
            color: #dc2626;
        }

        /* ── Resend link ── */
        .otp-resend-wrap {
            text-align: center;
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 14px;
        }

        .otp-resend-btn {
            background: none;
            border: none;
            color: #3b6cf8;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            padding: 0;
            text-decoration: underline;
        }

        .otp-resend-btn:disabled {
            color: #b0b8c9;
            text-decoration: none;
            cursor: default;
        }

        .otp-resend-btn.loading {
            text-decoration: none;
            color: #3b6cf8;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .otp-resend-spin {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 2px solid rgba(59, 108, 248, 0.35);
            border-top-color: #3b6cf8;
            border-radius: 50%;
            animation: otpResendSpin 0.6s linear infinite;
        }

        @keyframes otpResendSpin {
            to { transform: rotate(360deg); }
        }

        /* ── Phone code + national grouped input ── */
        .otp-phone-group {
            display: flex;
            gap: 8px;
        }

        .otp-phone-group .otp-phone-national {
            flex: 1 1 auto;
            min-width: 0;
        }

        /* ── Searchable country-code picker ── */
        .otp-cc {
            position: relative;
            flex: 0 0 42%;
            max-width: 210px;
            min-width: 108px;
        }

        .otp-cc-toggle {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 10px 10px;
            background: #fff;
            border: 1px solid #d7dce5;
            border-radius: 8px;
            font-size: 14px;
            color: #334155;
            cursor: pointer;
        }

        .otp-cc-toggle:focus {
            outline: none;
            border-color: #93b4f5;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        .otp-cc-flag { flex: 0 0 auto; }
        .otp-cc-dial { flex: 1 1 auto; text-align: left; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .otp-cc-caret { flex: 0 0 auto; font-style: normal; color: #94a3b8; transition: transform 0.15s ease; }
        .otp-cc.open .otp-cc-caret { transform: rotate(180deg); }

        .otp-cc-panel {
            display: none;
            position: absolute;
            z-index: 40;
            top: calc(100% + 4px);
            left: 0;
            width: 300px;
            max-width: 78vw;
            background: #fff;
            border: 1px solid #d7dce5;
            border-radius: 8px;
            box-shadow: 0 8px 28px rgba(15, 23, 42, 0.16);
            overflow: hidden;
        }

        .otp-cc.open .otp-cc-panel { display: block; }

        .otp-cc-search {
            width: 100%;
            border: none;
            border-bottom: 1px solid #e5e9f0;
            padding: 10px 14px;
            font-size: 14px;
        }

        .otp-cc-search:focus { outline: none; box-shadow: none; }

        .otp-cc-list {
            max-height: 220px;
            overflow-y: auto;
        }

        .otp-cc-opt {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 14px;
            font-size: 14px;
            color: #334155;
            cursor: pointer;
            border-bottom: 1px solid #f1f4f9;
        }

        .otp-cc-opt:hover { background: #f6f9ff; }
        .otp-cc-name { flex: 1 1 auto; min-width: 0; }
        .otp-cc-code { flex: 0 0 auto; color: #64748b; font-variant-numeric: tabular-nums; }

        .otp-cc-empty {
            padding: 14px;
            font-size: 13px;
            color: #94a3b8;
            text-align: center;
        }

        /* ── Tender picker: custom multi-select dropdown ── */
        .otp-tender-picker {
            position: relative;
        }

        .otp-tender-toggle {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            background: #fff;
            border: 1px solid #d7dce5;
            border-radius: 8px;
            font-size: 14px;
            color: #334155;
            text-align: left;
            cursor: pointer;
        }

        .otp-tender-toggle:focus {
            outline: none;
            border-color: #93b4f5;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        .otp-tender-toggle-label {
            flex: 1 1 auto;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .otp-tender-toggle-label.is-placeholder {
            color: #94a3b8;
        }

        .otp-tender-caret {
            flex: 0 0 auto;
            font-style: normal;
            color: #94a3b8;
            transition: transform 0.15s ease;
        }

        .otp-tender-picker.open .otp-tender-caret {
            transform: rotate(180deg);
        }

        .otp-tender-panel {
            display: none;
            position: absolute;
            z-index: 30;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            background: #fff;
            border: 1px solid #d7dce5;
            border-radius: 8px;
            box-shadow: 0 8px 28px rgba(15, 23, 42, 0.14);
            overflow: hidden;
        }

        .otp-tender-picker.open .otp-tender-panel {
            display: block;
        }

        .otp-tender-search {
            width: 100%;
            border: none;
            border-bottom: 1px solid #e5e9f0;
            border-radius: 0;
            padding: 10px 14px;
            font-size: 14px;
        }

        .otp-tender-search:focus {
            outline: none;
            box-shadow: none;
        }

        .otp-tender-list {
            max-height: 200px;
            overflow-y: auto;
        }

        .otp-tender-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 10px 14px;
            margin: 0;
            cursor: pointer;
            font-size: 14px;
            color: #334155;
            border-bottom: 1px solid #f1f4f9;
        }

        .otp-tender-all-row {
            font-weight: 600;
            color: #1d4ed8;
            background: #f8fafc;
        }

        .otp-tender-list .otp-tender-item:last-of-type {
            border-bottom: none;
        }

        .otp-tender-item:hover {
            background: #f6f9ff;
        }

        .otp-tender-item input {
            margin-top: 3px;
            flex: 0 0 auto;
        }

        .otp-tender-text {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
        }

        .otp-tender-code {
            font-size: 11px;
            color: #94a3b8;
            letter-spacing: 0.02em;
        }

        .otp-tender-empty {
            padding: 14px;
            font-size: 13px;
            color: #94a3b8;
            text-align: center;
        }

        /* ── Downloads-ready step ── */
        .otp-dl-head {
            text-align: center;
            margin-bottom: 16px;
        }

        .otp-dl-check {
            width: 52px;
            height: 52px;
            line-height: 52px;
            margin: 0 auto 10px;
            border-radius: 50%;
            background: #16a34a;
            color: #fff;
            font-size: 26px;
        }

        .otp-dl-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .otp-dl-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #fff;
        }

        .otp-dl-text {
            flex: 1 1 auto;
            min-width: 0;
        }

        .otp-dl-title {
            font-size: 14px;
            font-weight: 600;
            color: #0f172a;
            word-break: break-word;
        }

        .otp-dl-sub {
            margin-top: 3px;
            font-size: 12px;
            color: #64748b;
            word-break: break-word;
        }

        .otp-dl-btn {
            flex: 0 0 auto;
            display: inline-block;
            padding: 9px 18px;
            border-radius: 7px;
            background: #2563eb;
            color: #fff !important;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
        }

        .otp-dl-btn:hover {
            background: #1d4ed8;
        }

        /* ── Masked phone hint ── */
        .otp-hint {
            font-size: 13px;
            color: #6b7280;
            text-align: center;
            margin-bottom: 4px;
        }

        /* ── Success state ── */
        .fmf-alert-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #14532d;
        }

        .fmf-alert-success .fmf-alert-icon {
            background: #16a34a;
        }

        .fmf-alert-success .fmf-alert-body strong {
            color: #15803d;
        }

        .fmf-alert-success .fmf-alert-body p {
            color: #166534;
        }

        @media (max-width: 400px) {
            .otp-digit {
                width: 38px;
                height: 46px;
                font-size: 18px;
            }

            .otp-digits {
                gap: 6px;
            }
        }
    </style>
@endsection

@section('content')
    <section class="fmf-section @if ($be->theme_version == 'dark') dark-fmf @endif"
        @if ($be->theme_version == 'dark') data-particle-network @endif>
        <div class="container">

            {{-- Page intro --}}
            <div class="row justify-content-center mb-4">
                <div class="col-lg-9 text-center">
                    <p class="text-muted" style="font-size:15px; margin:0;">
                        {{ __('To protect your data, please select a verification method below.') }}<br>
                        {{ __('If your request is valid, a temporary secure download link will be emailed to you.') }}
                    </p>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-9">

                    {{-- Processing heading (shown while spinner is active) --}}
                    <h4 class="fmf-processing-title text-center mb-3" id="fmf-processing-heading"
                        style="display:none; color:#1a2a4a;">
                        {{ __('Processing your secure file recovery request...') }}
                    </h4>

                    <div class="fmf-card" style="position:relative;">

                        {{-- ── Spinner overlay ── --}}
                        <div class="fmf-overlay" id="fmf-overlay">
                            <div class="fmf-spinner"></div>
                            <p>{{ __('Processing your request') }}</p>
                        </div>

                        <div class="row no-gutters">

                            {{-- ── Left: Method Selector ── --}}
                            <div class="col-md-4 fmf-methods" id="fmf-methods-section">
                                <h6>{{ __('1) Select a Method') }}</h6>

                                <button type="button" class="fmf-method-btn active" data-method="order_number">
                                    {{ __('Email + Order Number') }}
                                </button>

                                <button type="button" class="fmf-method-btn" data-method="phone_otp">
                                    {{ __('Email + Phone (OTP)') }}
                                </button>

                                <button type="button" class="fmf-method-btn" data-method="payment_ref">
                                    {{ __('Email + Payment Reference') }}
                                </button>

                                <button type="button" class="fmf-method-btn" data-method="expired_link">
                                    {{ __('Expired Link / Regenerate') }}
                                </button>

                                <button type="button" class="fmf-method-btn" data-method="contact_support">
                                    {{ __('Contact Support') }}
                                </button>
                            </div>

                            {{-- ── Right: Form Panel ── --}}
                            <div class="col-md-8 fmf-form-panel p-3">

                                {{-- Method 1: Email + Order Number --}}
                                <div id="panel-order_number" class="">
                                    <h6>{{ __('2) Enter Your Information') }}</h6>

                                    <form id="fmf-form" action="{{ route('find_my_files.request_link') }}" method="POST"
                                        autocomplete="off">
                                        @csrf
                                        <input type="hidden" name="method" value="order_number">

                                        <div class="form-group mb-3">
                                            <label class="form-label">{{ __('Email Address') }}</label>
                                            <input type="email" name="email" id="fmf-email" class="form-control"
                                                placeholder="{{ __('e.g., name@domain.com') }}" autocomplete="off">
                                        </div>

                                        <div class="form-group mb-2">
                                            <label class="form-label">{{ __('Order Number') }}</label>
                                            <input type="text" name="order_number" id="fmf-order" class="form-control"
                                                placeholder="{{ __('e.g., ORD-2026-000123') }}" autocomplete="off">
                                        </div>

                                        {{-- Inline feedback alert (validation + lookup errors) --}}
                                        <div class="fmf-alert fmf-alert-error" id="fmf-error-validation">
                                            <div class="fmf-alert-icon"><span>!</span></div>
                                            <div class="fmf-alert-body">
                                                <strong
                                                    id="fmf-error-title">{{ __("We couldn't verify your information.") }}</strong>
                                                <p id="fmf-error-body">{{ __('Please check your details and try again.') }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="fmf-alert fmf-alert-error" id="fmf-error-emailfailed" style="display:none;">
                                            <div class="fmf-alert-icon"><span>!</span></div>
                                            <div class="fmf-alert-body">
                                                <strong>{{ __('Email delivery failed.') }}</strong>
                                                <p>{{ __('Your order was verified but we could not send the download link. Please contact support and quote your order number.') }}</p>
                                            </div>
                                        </div>

                                        {{-- Step 6: rate limit error --}}
                                        <div class="fmf-alert fmf-alert-error" id="fmf-error-ratelimit">
                                            <div class="fmf-alert-icon"><span>!</span></div>
                                            <div class="fmf-alert-body">
                                                <strong>{{ __('Too many attempts.') }}</strong>
                                                <p>{{ __('For security reasons, access has been temporarily restricted due to multiple failed verification attempts.') }}
                                                </p>
                                                <strong id="fmf-ratelimit-msg"
                                                    style="display:block; margin-top:5px;"></strong>
                                                <p style="margin-top:3px;">
                                                    {{ __('Repeated attempts may result in longer restrictions (1 hour / 24 hours).') }}
                                                </p>
                                                <p style="margin-top:6px;">
                                                    <a href="#fmf-methods-section"
                                                        id="fmf-change-method-link">{{ __('Change verification method') }}</a>
                                                    <span style="margin:0 5px; color:#fca5a5;">|</span>
                                                    <a href="{{ route('front.contact') }}">{{ __('Contact support') }}</a>
                                                </p>
                                            </div>
                                        </div>

                                        <div id="fmf-submit-area">
                                            @if ($bs->is_recaptcha == 1)
                                                <div class="fmf-captcha-wrap">
                                                    {!! NoCaptcha::renderJs() !!}
                                                    {!! NoCaptcha::display() !!}
                                                </div>
                                            @endif

                                            <button type="submit" class="btn-fmf-submit" id="fmf-submit-btn" disabled>
                                                {{ __('Resend Link by Email') }}
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                {{-- Method 2: Email + Phone (OTP) --}}
                                <div id="panel-phone_otp" style="display:none;">

                                    {{-- Step A: Email + Phone input --}}
                                    <div id="otp-step-input">
                                        <h6>{{ __('2) Enter Your Information') }}</h6>

                                        <form id="otp-request-form" autocomplete="off">
                                            @csrf

                                            {{-- Tender selector: searchable checkbox list (single or multiple) --}}
                                            <div class="form-group mb-3" id="otp-tender-wrap">
                                                <label class="form-label">{{ __('Which tender(s) did you purchase?') }}</label>
                                                <div class="otp-tender-picker" id="otp-tender-picker"
                                                    data-placeholder="{{ __('Select tender(s)…') }}"
                                                    data-count-suffix="{{ __('tenders selected') }}"
                                                    data-count-suffix-one="{{ __('tender selected') }}">
                                                    <button type="button" class="otp-tender-toggle" id="otp-tender-toggle"
                                                        aria-haspopup="listbox" aria-expanded="false">
                                                        <span class="otp-tender-toggle-label" id="otp-tender-toggle-label">{{ __('Select tender(s)…') }}</span>
                                                        <i class="otp-tender-caret">&#9662;</i>
                                                    </button>
                                                    <div class="otp-tender-panel" id="otp-tender-panel" role="listbox">
                                                        <input type="text" id="otp-tender-search" class="otp-tender-search"
                                                            placeholder="{{ __('Search tenders…') }}" autocomplete="off">
                                                        <label class="otp-tender-item otp-tender-all-row">
                                                            <input type="checkbox" id="otp-tender-all" class="otp-tender-allcb">
                                                            <span>{{ __('Select all') }}</span>
                                                        </label>
                                                        <div class="otp-tender-list" id="otp-tender-list">
                                                            @foreach($tenders as $t)
                                                                <label class="otp-tender-item"
                                                                    data-search="{{ \Illuminate\Support\Str::lower(trim($t->title . ' ' . $t->tender_code . ' ' . $t->id)) }}">
                                                                    <input type="checkbox" name="tender_slugs[]" value="{{ $t->slug }}"
                                                                        class="otp-tender-cb"
                                                                        {{ (isset($tender) && $tender && $tender->id === $t->id) ? 'checked' : '' }}>
                                                                    <span class="otp-tender-text">
                                                                        <span class="otp-tender-title">{{ $t->title }}</span>
                                                                        @if($t->tender_code)
                                                                            <small class="otp-tender-code">{{ $t->tender_code }}</small>
                                                                        @endif
                                                                    </span>
                                                                </label>
                                                            @endforeach
                                                            <div class="otp-tender-empty" id="otp-tender-empty" style="display:none;">{{ __('No tender matches your search.') }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <small class="text-muted d-block mt-1">{{ __('Tick every tender you paid for — one code unlocks them all.') }}</small>
                                            </div>

                                            <div class="form-group mb-3">
                                                <label class="form-label">{{ __('Email Address') }}</label>
                                                <input type="email" name="email" id="otp-email" class="form-control"
                                                    placeholder="{{ __('e.g., name@domain.com') }}" autocomplete="off">
                                            </div>

                                            <div class="form-group mb-2">
                                                <label class="form-label">{{ __('Phone Number') }}</label>
                                                @php
                                                    $otpPreDial = $tender ? \App\Http\Helpers\Countries::dialFor($tender->country) : null;
                                                    $preCC   = $otpPreDial ? collect($countries)->firstWhere('dial', $otpPreDial) : null;
                                                    $preFlag = $preCC['flag'] ?? '';
                                                    $preDial = $preCC['dial'] ?? '';
                                                @endphp
                                                <div class="otp-phone-group">
                                                    {{-- Searchable country-code picker --}}
                                                    <div class="otp-cc" id="otp-cc">
                                                        <button type="button" class="otp-cc-toggle" id="otp-cc-toggle"
                                                            aria-haspopup="listbox" aria-expanded="false">
                                                            <span class="otp-cc-flag" id="otp-cc-flag">{{ $preFlag }}</span>
                                                            <span class="otp-cc-dial" id="otp-cc-dial-label">{{ $preDial ?: __('Code') }}</span>
                                                            <i class="otp-cc-caret">&#9662;</i>
                                                        </button>
                                                        <div class="otp-cc-panel" id="otp-cc-panel" role="listbox">
                                                            <input type="text" class="otp-cc-search" id="otp-cc-search"
                                                                placeholder="{{ __('Search country or code…') }}" autocomplete="off">
                                                            <div class="otp-cc-list" id="otp-cc-list">
                                                                @foreach ($countries as $c)
                                                                    <div class="otp-cc-opt" role="option"
                                                                        data-dial="{{ $c['dial'] }}" data-flag="{{ $c['flag'] }}"
                                                                        data-search="{{ \Illuminate\Support\Str::lower($c['name'] . ' ' . $c['dial']) }}">
                                                                        <span class="otp-cc-flag">{{ $c['flag'] }}</span>
                                                                        <span class="otp-cc-name">{{ $c['name'] }}</span>
                                                                        <span class="otp-cc-code">{{ $c['dial'] }}</span>
                                                                    </div>
                                                                @endforeach
                                                                <div class="otp-cc-empty" id="otp-cc-empty" style="display:none;">{{ __('No country matches your search.') }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    {{-- Hidden dial value read by the phone-combining logic --}}
                                                    <input type="hidden" id="otp-phone-code" value="{{ $preDial }}">
                                                    <input type="tel" id="otp-phone" class="form-control otp-phone-national"
                                                        inputmode="numeric" placeholder="{{ __('Phone Number') }}"
                                                        autocomplete="off">
                                                </div>
                                                {{-- Combined E.164 value actually submitted to the server. --}}
                                                <input type="hidden" name="phone" id="otp-phone-full">
                                            </div>

                                            {{-- Inline error alerts --}}
                                            <div class="fmf-alert fmf-alert-error" id="otp-error-validation">
                                                <div class="fmf-alert-icon"><span>!</span></div>
                                                <div class="fmf-alert-body">
                                                    <strong>{{ __('Please fill in all fields correctly.') }}</strong>
                                                    <p>{{ __('Enter a valid email and phone number.') }}</p>
                                                </div>
                                            </div>

                                            <div class="fmf-alert fmf-alert-error" id="otp-error-sms">
                                                <div class="fmf-alert-icon"><span>!</span></div>
                                                <div class="fmf-alert-body">
                                                    <strong>{{ __('Could not send code.') }}</strong>
                                                    <p>{{ __('SMS delivery failed. Please try the Email + Order Number method or contact support.') }}
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="fmf-alert fmf-alert-error" id="otp-error-ratelimit">
                                                <div class="fmf-alert-icon"><span>!</span></div>
                                                <div class="fmf-alert-body">
                                                    <strong>{{ __('Too many attempts.') }}</strong>
                                                    <p id="otp-ratelimit-msg"></p>
                                                </div>
                                            </div>

                                            <div class="fmf-alert fmf-alert-error" id="otp-error-regenlimit" style="display:none;">
                                                <div class="fmf-alert-icon"><span>!</span></div>
                                                <div class="fmf-alert-body">
                                                    <strong>{{ __('Daily regeneration limit reached.') }}</strong>
                                                    <p>{{ __('You have requested the maximum number of new links for today. Please try again after 24 hours, or contact support.') }}</p>
                                                </div>
                                            </div>

                                            <div class="fmf-alert fmf-alert-warning" id="otp-error-payment-pending" style="display:none;">
                                                <div class="fmf-alert-icon"><span>!</span></div>
                                                <div class="fmf-alert-body">
                                                    <strong>{{ __('Order Payment Pending') }}</strong>
                                                    <p>{{ __('Your order has been received but payment has not been confirmed yet. Files will be available once your payment is approved. Please contact support if you believe this is an error.') }}</p>
                                                </div>
                                            </div>

                                            <div class="fmf-alert fmf-alert-warning" id="otp-error-suspended" style="display:none;">
                                                <div class="fmf-alert-icon"><span>!</span></div>
                                                <div class="fmf-alert-body">
                                                    <strong>{{ __('Order under verification') }}</strong>
                                                    <p>{{ __('This order is currently being verified. For any information, please contact ICA.') }}</p>
                                                </div>
                                            </div>

                                            <div class="fmf-alert fmf-alert-error" id="otp-error-nomatch" style="display:none;">
                                                <div class="fmf-alert-icon"><span>!</span></div>
                                                <div class="fmf-alert-body">
                                                    <strong>{{ __('No matching purchase found') }}</strong>
                                                    <p>{{ __('We could not find a purchase for that email and phone number on the selected tender(s). Please re-check your details, or contact support if you believe this is an error.') }}</p>
                                                </div>
                                            </div>

                                            @if ($bs->is_recaptcha == 1)
                                                <div class="fmf-captcha-wrap fmf-captcha-otp">
                                                    {!! NoCaptcha::display(['data-callback' => 'fmfOtpCaptchaVerified', 'data-expired-callback' => 'fmfOtpCaptchaExpired']) !!}
                                                </div>
                                            @endif

                                            <button type="submit" class="btn-fmf-submit" id="otp-send-btn" disabled>
                                                {{ __('Send Verification Code') }}
                                            </button>
                                        </form>
                                    </div>

                                    {{-- Step B: OTP entry (hidden until code is sent) --}}
                                    <div id="otp-step-verify" style="display:none;">
                                        <h6>{{ __('3) Enter Verification Code') }}</h6>

                                        <p class="otp-hint" id="otp-sent-hint"></p>

                                        <div class="otp-digits">
                                            <input class="otp-digit" type="tel" maxlength="1" inputmode="numeric"
                                                pattern="[0-9]" autocomplete="one-time-code">
                                            <input class="otp-digit" type="tel" maxlength="1" inputmode="numeric"
                                                pattern="[0-9]">
                                            <input class="otp-digit" type="tel" maxlength="1" inputmode="numeric"
                                                pattern="[0-9]">
                                            <input class="otp-digit" type="tel" maxlength="1" inputmode="numeric"
                                                pattern="[0-9]">
                                            <input class="otp-digit" type="tel" maxlength="1" inputmode="numeric"
                                                pattern="[0-9]">
                                            <input class="otp-digit" type="tel" maxlength="1" inputmode="numeric"
                                                pattern="[0-9]">
                                        </div>

                                        <div class="otp-timer" id="otp-timer">
                                            {{ __('Code expires in') }} <span id="otp-countdown">10:00</span>
                                        </div>

                                        {{-- OTP verify alerts --}}
                                        <div class="fmf-alert fmf-alert-error" id="otp-error-invalid">
                                            <div class="fmf-alert-icon"><span>!</span></div>
                                            <div class="fmf-alert-body">
                                                <strong>{{ __('Incorrect code.') }}</strong>
                                                <p id="otp-attempts-msg">{{ __('Please try again.') }}</p>
                                            </div>
                                        </div>

                                        <div class="fmf-alert fmf-alert-error" id="otp-error-exhausted">
                                            <div class="fmf-alert-icon"><span>!</span></div>
                                            <div class="fmf-alert-body">
                                                <strong>{{ __('Too many wrong attempts.') }}</strong>
                                                <p>{{ __('Please request a new code.') }}</p>
                                            </div>
                                        </div>

                                        <div class="fmf-alert fmf-alert-error" id="otp-error-expired">
                                            <div class="fmf-alert-icon"><span>!</span></div>
                                            <div class="fmf-alert-body">
                                                <strong>{{ __('Code expired.') }}</strong>
                                                <p>{{ __('Please request a new code using the Resend button below.') }}</p>
                                            </div>
                                        </div>

                                        <div class="fmf-alert fmf-alert-error" id="otp-error-session">
                                            <div class="fmf-alert-icon"><span>!</span></div>
                                            <div class="fmf-alert-body">
                                                <strong>{{ __('Session expired.') }}</strong>
                                                <p>{{ __('Please start over.') }}</p>
                                            </div>
                                        </div>

                                        {{-- Shown briefly when a resend succeeds --}}
                                        <div class="fmf-alert fmf-alert-success" id="otp-resend-success" style="display:none;">
                                            <div class="fmf-alert-icon"><span>&#10003;</span></div>
                                            <div class="fmf-alert-body">
                                                <strong>{{ __('New code sent.') }}</strong>
                                                <p>{{ __('A fresh 6-digit code was just sent to your phone.') }}</p>
                                            </div>
                                        </div>

                                        <div class="otp-resend-wrap">
                                            {{ __('Didn\'t receive it?') }}
                                            <button type="button" class="otp-resend-btn" id="otp-resend-btn" disabled>
                                                {{ __('Resend code') }}
                                            </button>
                                            <span id="otp-resend-timer" style="font-size:12px; color:#b0b8c9;"></span>
                                        </div>

                                        <button type="button" class="btn-fmf-submit" id="otp-verify-btn" disabled>
                                            {{ __('Verify Code') }}
                                        </button>

                                        <div style="text-align:center; margin-top:12px;">
                                            <button type="button" id="otp-back-btn"
                                                style="background:none;border:none;color:#6b7280;font-size:13px;cursor:pointer;text-decoration:underline;">
                                                {{ __('← Start over') }}
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Step C: Downloads ready (one row per selected tender) --}}
                                    <div id="otp-step-downloads" style="display:none;">
                                        <div class="otp-dl-head">
                                            <div class="otp-dl-check">&#10003;</div>
                                            <h6 class="mb-1">{{ __('Verified — your downloads are ready') }}</h6>
                                            <p class="otp-hint">{{ __('We also emailed these links to you. Each link can be opened a limited number of times.') }}</p>
                                        </div>
                                        <div class="otp-dl-list" id="otp-dl-list"></div>
                                    </div>

                                </div>

                                {{-- Contact Support placeholder --}}
                                {{-- Method 3: Email + Payment Reference --}}
                                <div id="panel-payment_ref" style="display:none;">
                                    <h6>{{ __('2) Enter Your Information') }}</h6>

                                    <form id="payref-form" autocomplete="off">
                                        @csrf
                                        <div class="form-group mb-3">
                                            <label class="form-label">{{ __('Email Address') }}</label>
                                            <input type="email" name="email" id="payref-email" class="form-control"
                                                placeholder="{{ __('e.g., name@domain.com') }}" autocomplete="off">
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="form-label">{{ __('Payment / Transaction Reference') }}</label>
                                            <input type="text" name="payment_reference" id="payref-ref"
                                                class="form-control"
                                                placeholder="{{ __('e.g., FLW-XXXX or TRF-2026-001') }}"
                                                autocomplete="off"
                                                maxlength="100"
                                                style="text-transform:uppercase; font-family:monospace; letter-spacing:.04em;">
                                            <small class="text-muted" style="font-size:12px;">
                                                {{ __('Enter the gateway transaction ID or bank transfer reference you received when paying.') }}
                                            </small>
                                        </div>

                                        {{-- Validation error --}}
                                        <div class="fmf-alert fmf-alert-error" id="payref-error-validation">
                                            <div class="fmf-alert-icon"><span>!</span></div>
                                            <div class="fmf-alert-body">
                                                <strong>{{ __('Please fill in all fields correctly.') }}</strong>
                                                <p>{{ __('Enter a valid email and payment reference (letters, numbers, hyphens, spaces allowed).') }}</p>
                                            </div>
                                        </div>

                                        <div class="fmf-alert fmf-alert-error" id="payref-error-emailfailed" style="display:none;">
                                            <div class="fmf-alert-icon"><span>!</span></div>
                                            <div class="fmf-alert-body">
                                                <strong>{{ __('Email delivery failed.') }}</strong>
                                                <p>{{ __('Your order was verified but we could not send the download link. Please contact support and quote your order number.') }}</p>
                                            </div>
                                        </div>

                                        <div class="fmf-alert fmf-alert-error" id="payref-error-nomatch" style="display:none;">
                                            <div class="fmf-alert-icon"><span>!</span></div>
                                            <div class="fmf-alert-body">
                                                <strong>{{ __('No matching order found.') }}</strong>
                                                <p>{{ __('The email address or payment reference does not match any completed order. Please check and try again.') }}</p>
                                            </div>
                                        </div>

                                        {{-- Suspended (under verification) --}}
                                        <div class="fmf-alert fmf-alert-warning" id="payref-error-suspended" style="display:none;">
                                            <div class="fmf-alert-icon"><span>!</span></div>
                                            <div class="fmf-alert-body">
                                                <strong>{{ __('Order under verification') }}</strong>
                                                <p>{{ __('This order is currently being verified. For any information, please contact ICA.') }}</p>
                                            </div>
                                        </div>

                                        {{-- Rate limit error --}}
                                        <div class="fmf-alert fmf-alert-error" id="payref-error-ratelimit">
                                            <div class="fmf-alert-icon"><span>!</span></div>
                                            <div class="fmf-alert-body">
                                                <strong>{{ __('Too many attempts.') }}</strong>
                                                <p id="payref-ratelimit-msg"></p>
                                            </div>
                                        </div>

                                        {{-- Recovery cap error --}}
                                        <div class="fmf-alert fmf-alert-error" id="payref-error-regenlimit" style="display:none;">
                                            <div class="fmf-alert-icon"><span>!</span></div>
                                            <div class="fmf-alert-body">
                                                <strong>{{ __('Daily regeneration limit reached.') }}</strong>
                                                <p>{{ __('You have requested the maximum number of new links for today. Please try again after 24 hours, or contact support.') }}</p>
                                            </div>
                                        </div>

                                        @if ($bs->is_recaptcha == 1)
                                            <div class="fmf-captcha-wrap fmf-captcha-payref">
                                                {!! NoCaptcha::display(['data-callback' => 'fmfPayrefCaptchaVerified', 'data-expired-callback' => 'fmfPayrefCaptchaExpired']) !!}
                                            </div>
                                        @endif

                                        <button type="submit" class="btn-fmf-submit" id="payref-submit-btn" disabled>
                                            {{ __('Send Download Link') }}
                                        </button>
                                    </form>
                                </div>

                                {{-- Method 4: Expired Link / Regenerate --}}
                                <div id="panel-expired_link" style="display:none;">
                                  <div id="regen-step-input">
                                    <h6>{{ __('2) Enter Your Email') }}</h6>

                                    <p style="font-size:13px; color:#6b7280; margin-bottom:18px; line-height:1.6;">
                                        {{ __('Enter the email address you used when purchasing and a purchase date range. We\'ll email you a verification code, then a download link for every completed order in that range.') }}
                                    </p>

                                    <form id="regen-form" autocomplete="off">
                                        @csrf
                                        <div class="form-group mb-3">
                                            <label class="form-label">{{ __('Email Address') }}</label>
                                            <input type="email" name="email" id="regen-email" class="form-control"
                                                placeholder="{{ __('e.g., name@domain.com') }}" autocomplete="off">
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="form-label">{{ __('Purchase Date Range') }}</label>
                                            <div class="row no-gutters" style="gap:10px 0;">
                                                <div class="col-6" style="padding-right:5px;">
                                                    <input type="date" name="date_from" id="regen-date-from" class="form-control"
                                                        aria-label="{{ __('From') }}" max="{{ now()->format('Y-m-d') }}" required>
                                                </div>
                                                <div class="col-6" style="padding-left:5px;">
                                                    <input type="date" name="date_to" id="regen-date-to" class="form-control"
                                                        aria-label="{{ __('To') }}" max="{{ now()->format('Y-m-d') }}" required>
                                                </div>
                                            </div>
                                            <small class="text-muted d-block mt-1">{{ __('Only purchases paid for within this range will be included.') }}</small>
                                        </div>

                                        {{-- Validation error --}}
                                        <div class="fmf-alert fmf-alert-error" id="regen-error-validation">
                                            <div class="fmf-alert-icon"><span>!</span></div>
                                            <div class="fmf-alert-body">
                                                <strong>{{ __('Please fill in all fields correctly.') }}</strong>
                                                <p>{{ __('Enter a valid email address and a valid purchase date range (not in the future).') }}</p>
                                            </div>
                                        </div>

                                        {{-- Rate limit error --}}
                                        <div class="fmf-alert fmf-alert-error" id="regen-error-ratelimit">
                                            <div class="fmf-alert-icon"><span>!</span></div>
                                            <div class="fmf-alert-body">
                                                <strong>{{ __('Too many attempts.') }}</strong>
                                                <p id="regen-ratelimit-msg"></p>
                                            </div>
                                        </div>

                                        <div class="fmf-alert fmf-alert-error" id="regen-error-emailfailed" style="display:none;">
                                            <div class="fmf-alert-icon"><span>!</span></div>
                                            <div class="fmf-alert-body">
                                                <strong>{{ __('Email delivery failed.') }}</strong>
                                                <p>{{ __('Your order was verified but we could not send the download link. Please contact support and quote your order number.') }}</p>
                                            </div>
                                        </div>

                                        <div class="fmf-alert fmf-alert-warning" id="regen-error-suspended" style="display:none;">
                                            <div class="fmf-alert-icon"><span>!</span></div>
                                            <div class="fmf-alert-body">
                                                <strong>{{ __('Order under verification') }}</strong>
                                                <p>{{ __('This order is currently being verified. For any information, please contact ICA.') }}</p>
                                            </div>
                                        </div>

                                        <div class="fmf-alert fmf-alert-error" id="regen-error-nomatch" style="display:none;">
                                            <div class="fmf-alert-icon"><span>!</span></div>
                                            <div class="fmf-alert-body">
                                                <strong>{{ __('No matching purchase found') }}</strong>
                                                <p>{{ __('We could not find a completed purchase for that email within the selected date range. Please check your details, or contact support if you believe this is an error.') }}</p>
                                            </div>
                                        </div>

                                        {{-- Regen limit error --}}
                                        <div class="fmf-alert fmf-alert-error" id="regen-error-limit">
                                            <div class="fmf-alert-icon"><span>!</span></div>
                                            <div class="fmf-alert-body">
                                                <strong>{{ __('Daily regeneration limit reached.') }}</strong>
                                                <p>{{ __('You have requested the maximum number of new links for today. Please try again after 24 hours, or contact support.') }}</p>
                                            </div>
                                        </div>

                                        @if ($bs->is_recaptcha == 1)
                                            <div class="fmf-captcha-wrap fmf-captcha-regen">
                                                {!! NoCaptcha::display(['data-callback' => 'fmfRegenCaptchaVerified', 'data-expired-callback' => 'fmfRegenCaptchaExpired']) !!}
                                            </div>
                                        @endif

                                        <button type="submit" class="btn-fmf-submit" id="regen-submit-btn" disabled>
                                            {{ __('Send Verification Code') }}
                                        </button>
                                    </form>

                                    <div style="margin-top:16px; padding:12px 16px; background:#fff8e1; border:1px solid #fde68a; border-radius:6px; font-size:12px; color:#92400e; line-height:1.5;">
                                        {{ __('This method emails a separate download link for every completed purchase on this address within the date range you select.') }}
                                    </div>
                                  </div>
                                  {{-- /regen-step-input --}}

                                  {{-- Step B: OTP entry (hidden until code is sent) --}}
                                  <div id="regen-step-verify" style="display:none;">
                                    <h6>{{ __('3) Enter Verification Code') }}</h6>

                                    <p class="otp-hint" id="regen-otp-sent-hint"></p>

                                    <div class="otp-digits">
                                        <input class="regen-otp-digit" type="tel" maxlength="1" inputmode="numeric"
                                            pattern="[0-9]" autocomplete="one-time-code">
                                        <input class="regen-otp-digit" type="tel" maxlength="1" inputmode="numeric"
                                            pattern="[0-9]">
                                        <input class="regen-otp-digit" type="tel" maxlength="1" inputmode="numeric"
                                            pattern="[0-9]">
                                        <input class="regen-otp-digit" type="tel" maxlength="1" inputmode="numeric"
                                            pattern="[0-9]">
                                        <input class="regen-otp-digit" type="tel" maxlength="1" inputmode="numeric"
                                            pattern="[0-9]">
                                        <input class="regen-otp-digit" type="tel" maxlength="1" inputmode="numeric"
                                            pattern="[0-9]">
                                    </div>

                                    <div class="otp-timer" id="regen-otp-timer">
                                        {{ __('Code expires in') }} <span id="regen-otp-countdown">10:00</span>
                                    </div>

                                    {{-- OTP verify alerts --}}
                                    <div class="fmf-alert fmf-alert-error" id="regen-otp-error-invalid">
                                        <div class="fmf-alert-icon"><span>!</span></div>
                                        <div class="fmf-alert-body">
                                            <strong>{{ __('Incorrect code.') }}</strong>
                                            <p id="regen-otp-attempts-msg">{{ __('Please try again.') }}</p>
                                        </div>
                                    </div>

                                    <div class="fmf-alert fmf-alert-error" id="regen-otp-error-exhausted">
                                        <div class="fmf-alert-icon"><span>!</span></div>
                                        <div class="fmf-alert-body">
                                            <strong>{{ __('Too many wrong attempts.') }}</strong>
                                            <p>{{ __('Please request a new code.') }}</p>
                                        </div>
                                    </div>

                                    <div class="fmf-alert fmf-alert-error" id="regen-otp-error-expired">
                                        <div class="fmf-alert-icon"><span>!</span></div>
                                        <div class="fmf-alert-body">
                                            <strong>{{ __('Code expired.') }}</strong>
                                            <p>{{ __('Please request a new code using the Resend button below.') }}</p>
                                        </div>
                                    </div>

                                    <div class="fmf-alert fmf-alert-error" id="regen-otp-error-session">
                                        <div class="fmf-alert-icon"><span>!</span></div>
                                        <div class="fmf-alert-body">
                                            <strong>{{ __('Session expired.') }}</strong>
                                            <p>{{ __('Please start over.') }}</p>
                                        </div>
                                    </div>

                                    <div class="fmf-alert fmf-alert-error" id="regen-otp-error-sendfailed" style="display:none;">
                                        <div class="fmf-alert-icon"><span>!</span></div>
                                        <div class="fmf-alert-body">
                                            <strong>{{ __('Email delivery failed.') }}</strong>
                                            <p>{{ __('Could not send the verification code. Please try again shortly or contact support.') }}</p>
                                        </div>
                                    </div>

                                    {{-- Shown briefly when a resend succeeds --}}
                                    <div class="fmf-alert fmf-alert-success" id="regen-otp-resend-success" style="display:none;">
                                        <div class="fmf-alert-icon"><span>&#10003;</span></div>
                                        <div class="fmf-alert-body">
                                            <strong>{{ __('New code sent.') }}</strong>
                                            <p>{{ __('A fresh 6-digit code was just sent to your email.') }}</p>
                                        </div>
                                    </div>

                                    <div class="otp-resend-wrap">
                                        {{ __('Didn\'t receive it?') }}
                                        <button type="button" class="otp-resend-btn" id="regen-otp-resend-btn" disabled>
                                            {{ __('Resend code') }}
                                        </button>
                                        <span id="regen-otp-resend-timer" style="font-size:12px; color:#b0b8c9;"></span>
                                    </div>

                                    <button type="button" class="btn-fmf-submit" id="regen-otp-verify-btn" disabled>
                                        {{ __('Verify Code') }}
                                    </button>

                                    <div style="text-align:center; margin-top:12px;">
                                        <button type="button" id="regen-otp-back-btn"
                                            style="background:none;border:none;color:#6b7280;font-size:13px;cursor:pointer;text-decoration:underline;">
                                            {{ __('← Start over') }}
                                        </button>
                                    </div>
                                  </div>
                                  {{-- /regen-step-verify --}}
                                </div>

                                <div id="panel-contact_support" style="display:none;">
                                    <h6>{{ __('Contact Support') }}</h6>
                                    <div class="fmf-method-placeholder">
                                        <i class="far fa-envelope"></i>
                                        <p style="font-size:14px; color:#6b7280; margin:0;">
                                            {{ __('Please use the') }}
                                            <a href="{{ route('front.contact') }}"
                                                style="color:#3b6cf8;">{{ __('contact page') }}</a>
                                            {{ __('to reach our support team.') }}
                                        </p>
                                    </div>
                                </div>

                            </div>
                            {{-- /col --}}

                        </div>
                        {{-- /row --}}
                    </div>
                    {{-- /fmf-card --}}

                    {{-- Security Best Practices --}}
                    <div class="fmf-security-box">
                        <h6>
                            {{ __('Security Best Practices') }}
                            <a href="{{ route('find_my_files.security_info') }}"
                                style="font-size:11px; font-weight:600; color:#3b6cf8; text-decoration:none; text-transform:none; letter-spacing:0; margin-left:8px;">
                                {{ __('Learn more →') }}
                            </a>
                        </h6>
                        <ul>
                            <li>{{ __('Neutral message: "If a purchase exists, an email will be sent."') }}</li>
                            <li>{{ __('Temporary links (e.g., 24 hours) + limited downloads (e.g., 3).') }}</li>
                            <li>{{ __('Attempt limits + progressive lockout.') }}</li>
                            <li>{{ __('Logs: IP address, device, timestamp, method used.') }}</li>
                        </ul>
                    </div>

                </div>
            </div>
            {{-- /row --}}

        </div>
    </section>
@endsection

@section('scripts')
    <script>
        // ── reCAPTCHA global callbacks for methods 2, 3, 4 ──────────────────────
        @if ($bs->is_recaptcha == 1)
        window.fmfOtpCaptchaVerified    = function() { window._fmfOtpCaptcha    = true;  var e = document.getElementById('otp-email');     if (e) e.dispatchEvent(new Event('input')); };
        window.fmfOtpCaptchaExpired     = function() { window._fmfOtpCaptcha    = false; var e = document.getElementById('otp-email');     if (e) e.dispatchEvent(new Event('input')); };
        window.fmfPayrefCaptchaVerified = function() { window._fmfPayrefCaptcha = true;  var e = document.getElementById('payref-email');  if (e) e.dispatchEvent(new Event('input')); };
        window.fmfPayrefCaptchaExpired  = function() { window._fmfPayrefCaptcha = false; var e = document.getElementById('payref-email');  if (e) e.dispatchEvent(new Event('input')); };
        window.fmfRegenCaptchaVerified  = function() { window._fmfRegenCaptcha  = true;  var e = document.getElementById('regen-email');   if (e) e.dispatchEvent(new Event('input')); };
        window.fmfRegenCaptchaExpired   = function() { window._fmfRegenCaptcha  = false; var e = document.getElementById('regen-email');   if (e) e.dispatchEvent(new Event('input')); };
        @endif

        // ── OTP tender picker: custom multi-select dropdown (Method 2 only) ──────
        (function() {
            var picker = document.getElementById('otp-tender-picker');
            var toggle = document.getElementById('otp-tender-toggle');
            var labelEl= document.getElementById('otp-tender-toggle-label');
            var panel  = document.getElementById('otp-tender-panel');
            var search = document.getElementById('otp-tender-search');
            var list   = document.getElementById('otp-tender-list');
            var empty  = document.getElementById('otp-tender-empty');
            var allCb  = document.getElementById('otp-tender-all');
            if (!picker || !toggle || !list) return;

            var items = Array.prototype.slice.call(list.querySelectorAll('.otp-tender-item'));
            var boxes = Array.prototype.slice.call(list.querySelectorAll('.otp-tender-cb'));

            var placeholder = picker.getAttribute('data-placeholder') || 'Select…';
            var suffix      = picker.getAttribute('data-count-suffix') || 'selected';
            var suffixOne   = picker.getAttribute('data-count-suffix-one') || suffix;

            function visibleBoxes() {
                return boxes.filter(function(cb) {
                    return cb.closest('.otp-tender-item').style.display !== 'none';
                });
            }

            function notifyReady() {
                var emailEl = document.getElementById('otp-email');
                if (emailEl) emailEl.dispatchEvent(new Event('input'));
            }

            // Toggle-button label reflects current selection.
            function refreshLabel() {
                var checked = boxes.filter(function(cb) { return cb.checked; });
                if (checked.length === 0) {
                    labelEl.textContent = placeholder;
                    labelEl.classList.add('is-placeholder');
                } else if (checked.length === 1) {
                    var titleEl = checked[0].closest('.otp-tender-item').querySelector('.otp-tender-title');
                    labelEl.textContent = titleEl ? titleEl.textContent.trim() : '';
                    labelEl.classList.remove('is-placeholder');
                } else {
                    labelEl.textContent = checked.length + ' ' + suffix;
                    labelEl.classList.remove('is-placeholder');
                }
            }

            // "Select all" tri-state, scoped to the currently visible (searched) rows.
            function refreshAll() {
                if (!allCb) return;
                var vis = visibleBoxes();
                var on  = vis.filter(function(cb) { return cb.checked; }).length;
                allCb.checked = vis.length > 0 && on === vis.length;
                allCb.indeterminate = on > 0 && on < vis.length;
            }

            function applySearch() {
                var q = (search ? search.value : '').trim().toLowerCase();
                var shown = 0;
                items.forEach(function(it) {
                    if (it === empty) return;
                    var match = !q || (it.getAttribute('data-search') || '').indexOf(q) !== -1;
                    it.style.display = match ? '' : 'none';
                    if (match) shown++;
                });
                if (empty) empty.style.display = shown ? 'none' : 'block';
                refreshAll();
            }

            function open()  { picker.classList.add('open'); toggle.setAttribute('aria-expanded', 'true'); if (search) { search.value = ''; applySearch(); search.focus(); } }
            function close() { picker.classList.remove('open'); toggle.setAttribute('aria-expanded', 'false'); }

            toggle.addEventListener('click', function() {
                picker.classList.contains('open') ? close() : open();
            });

            // Close when clicking outside.
            document.addEventListener('click', function(e) {
                if (!picker.contains(e.target)) close();
            });

            if (search) search.addEventListener('input', applySearch);

            if (allCb) {
                allCb.addEventListener('change', function() {
                    var target = this.checked;
                    visibleBoxes().forEach(function(cb) { cb.checked = target; });
                    refreshLabel();
                    refreshAll();
                    notifyReady();
                });
            }

            list.addEventListener('change', function(e) {
                if (!e.target.classList.contains('otp-tender-cb')) return;
                refreshLabel();
                refreshAll();
                notifyReady();
            });

            // Initial state (honours a pre-checked ?tender= slug).
            refreshLabel();
            refreshAll();
        })();

        // ── OTP phone country-code: searchable single-select dropdown ────────────
        (function() {
            var cc      = document.getElementById('otp-cc');
            var toggle  = document.getElementById('otp-cc-toggle');
            var panel   = document.getElementById('otp-cc-panel');
            var search  = document.getElementById('otp-cc-search');
            var list    = document.getElementById('otp-cc-list');
            var empty   = document.getElementById('otp-cc-empty');
            var flagEl  = document.getElementById('otp-cc-flag');
            var dialEl  = document.getElementById('otp-cc-dial-label');
            var hidden  = document.getElementById('otp-phone-code');
            if (!cc || !toggle || !list || !hidden) return;

            var opts = Array.prototype.slice.call(list.querySelectorAll('.otp-cc-opt'));

            function open()  { cc.classList.add('open'); toggle.setAttribute('aria-expanded', 'true'); if (search) { search.value = ''; applySearch(); search.focus(); } }
            function close() { cc.classList.remove('open'); toggle.setAttribute('aria-expanded', 'false'); }

            function applySearch() {
                var q = (search ? search.value : '').trim().toLowerCase();
                var shown = 0;
                opts.forEach(function(o) {
                    var match = !q || (o.getAttribute('data-search') || '').indexOf(q) !== -1;
                    o.style.display = match ? '' : 'none';
                    if (match) shown++;
                });
                if (empty) empty.style.display = shown ? 'none' : 'block';
            }

            function select(dial, flag) {
                hidden.value = dial;
                if (dialEl) dialEl.textContent = dial;
                if (flagEl) flagEl.textContent = flag;
                hidden.dispatchEvent(new Event('change')); // triggers sync + readiness
                close();
            }

            toggle.addEventListener('click', function() {
                cc.classList.contains('open') ? close() : open();
            });

            document.addEventListener('click', function(e) {
                if (!cc.contains(e.target)) close();
            });

            if (search) search.addEventListener('input', applySearch);

            list.addEventListener('click', function(e) {
                var opt = e.target.closest('.otp-cc-opt');
                if (!opt) return;
                select(opt.getAttribute('data-dial'), opt.getAttribute('data-flag'));
            });
        })();

        (function() {
            'use strict';

            var emailInput = document.getElementById('fmf-email');
            var orderInput = document.getElementById('fmf-order');
            var submitBtn = document.getElementById('fmf-submit-btn');
            var submitArea = document.getElementById('fmf-submit-area');
            var overlay = document.getElementById('fmf-overlay');
            var procHeading = document.getElementById('fmf-processing-heading');
            var errValidation = document.getElementById('fmf-error-validation');
            var errRatelimit = document.getElementById('fmf-error-ratelimit');
            var rateLimitMsg = document.getElementById('fmf-ratelimit-msg');
            var changeMethodLink = document.getElementById('fmf-change-method-link');
            var methodsSection = document.getElementById('fmf-methods-section');
            var captchaReady = {{ $bs->is_recaptcha == 1 ? 'false' : 'true' }};

            // ── reCAPTCHA callbacks ──
            window.fmfCaptchaVerified = function() {
                captchaReady = true;
                checkFormReady();
            };
            window.fmfCaptchaExpired = function() {
                captchaReady = false;
                checkFormReady();
            };

            @if ($bs->is_recaptcha == 1)
                document.addEventListener('DOMContentLoaded', function() {
                    var captchaEl = document.querySelector('.fmf-captcha-wrap .g-recaptcha');
                    if (captchaEl) {
                        captchaEl.setAttribute('data-callback', 'fmfCaptchaVerified');
                        captchaEl.setAttribute('data-expired-callback', 'fmfCaptchaExpired');
                    }
                });
            @endif

            // ── Form field validation ──
            function checkFormReady() {
                var emailOk = emailInput && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value.trim());
                var orderOk = orderInput && orderInput.value.trim().length >= 3;
                if (submitBtn) {
                    submitBtn.disabled = !(emailOk && orderOk && captchaReady);
                }
            }

            if (emailInput) emailInput.addEventListener('input', checkFormReady);
            if (orderInput) orderInput.addEventListener('input', checkFormReady);

            // ── Show/hide overlay ──
            function showOverlay() {
                if (overlay) overlay.classList.add('active');
                if (procHeading) procHeading.style.display = 'block';
            }

            function hideOverlay() {
                if (overlay) overlay.classList.remove('active');
                if (procHeading) procHeading.style.display = 'none';
            }

            // ── Hide all error alerts + restore submit area ──
            function hideErrors() {
                if (errValidation) {
                    errValidation.style.display = 'none';
                }
                if (errRatelimit) {
                    errRatelimit.style.display = 'none';
                }
                if (submitArea) {
                    submitArea.style.display = '';
                }
            }

            // ── Reset reCAPTCHA ──
            function resetCaptcha() {
                captchaReady = {{ $bs->is_recaptcha == 1 ? 'false' : 'true' }};
                if (typeof grecaptcha !== 'undefined') {
                    try {
                        grecaptcha.reset();
                    } catch (e) {}
                }
            }

            // ── AJAX form submission ──
            var form = document.getElementById('fmf-form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    hideErrors();
                    showOverlay();
                    submitBtn.disabled = true;

                    var formData = new FormData(form);

                    fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                            body: formData,
                        })
                        .then(function(res) {
                            if (!res.ok) {
                                // Non-2xx (500, 422, etc.) — throw to catch handler
                                throw new Error('HTTP ' + res.status);
                            }
                            return res.json();
                        })
                        .then(function(data) {
                            if (data.status === 'success' && data.redirect) {
                                window.location.href = data.redirect;
                            } else if (data.type === 'rate_limited') {
                                hideOverlay();
                                resetCaptcha();
                                if (rateLimitMsg) {
                                    rateLimitMsg.textContent = '{{ __('Please try again in') }} ' + (data
                                        .wait_text || (data.minutes + ' {{ __('minutes.') }}'));
                                }
                                if (submitArea) submitArea.style.display = 'none';
                                if (errRatelimit) errRatelimit.style.display = 'flex';
                            } else if (data.type === 'email_failed') {
                                hideOverlay();
                                resetCaptcha();
                                submitBtn.disabled = false;
                                var el = document.getElementById('fmf-error-emailfailed');
                                if (el) el.style.display = 'flex';
                            } else if (data.status === 'error') {
                                hideOverlay();
                                resetCaptcha();
                                submitBtn.disabled = false;

                                var titles = {
                                    'validation': '{{ __('Please fill in all required fields correctly.') }}',
                                    'order_not_found': '{{ __('Order not found.') }}',
                                    'email_mismatch': '{{ __('Email address does not match.') }}',
                                    'invalid_status': '{{ __('Order not eligible.') }}',
                                    'suspended': '{{ __('Order under verification') }}',
                                    'regen_limit': '{{ __('Daily regeneration limit reached.') }}',
                                };
                                var bodies = {
                                    'validation': '{{ __('Check your email and order number, then try again.') }}',
                                    'order_not_found': data.message ||
                                        '{{ __('No order was found with this order number. Please check and try again.') }}',
                                    'email_mismatch': data.message ||
                                        '{{ __('The email address does not match the one used for this order.') }}',
                                    'invalid_status': data.message ||
                                        '{{ __('This order has not been completed. Only paid orders are eligible for file recovery.') }}',
                                    'regen_limit': '{{ __('You have requested the maximum number of new links for today. Please try again after 24 hours, or contact support.') }}',
                                };

                                var errTitle = document.getElementById('fmf-error-title');
                                var errBody = document.getElementById('fmf-error-body');
                                if (errTitle) errTitle.textContent = titles[data.type] ||
                                    '{{ __('An error occurred.') }}';
                                if (errBody) errBody.textContent = bodies[data.type] || data.message ||
                                    '{{ __('Please try again.') }}';
                                if (errValidation) errValidation.style.display = 'flex';
                            } else {
                                // Unexpected response shape — always hide overlay
                                hideOverlay();
                                resetCaptcha();
                                submitBtn.disabled = false;
                            }
                        })
                        .catch(function() {
                            hideOverlay();
                            resetCaptcha();
                            var errTitle = document.getElementById('fmf-error-title');
                            var errBody = document.getElementById('fmf-error-body');
                            if (errTitle) errTitle.textContent = '{{ __('Connection error.') }}';
                            if (errBody) errBody.textContent =
                                '{{ __('Could not reach the server. Please check your connection and try again.') }}';
                            if (errValidation) errValidation.style.display = 'flex';
                            submitBtn.disabled = false;
                        });
                });
            }

            // ── "Change verification method" link — scroll to method selector ──
            if (changeMethodLink) {
                changeMethodLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (methodsSection) {
                        methodsSection.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                    // Restore submit area so user can try another method
                    hideErrors();
                    checkFormReady();
                });
            }

            // ── Method selector ──
            var methodBtns = document.querySelectorAll('.fmf-method-btn:not(.disabled-method)');
            methodBtns.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.fmf-method-btn').forEach(function(b) {
                        b.classList.remove('active');
                    });
                    this.classList.add('active');

                    document.querySelectorAll('[id^="panel-"]').forEach(function(p) {
                        p.style.display = 'none';
                    });
                    var target = document.getElementById('panel-' + this.getAttribute('data-method'));
                    if (target) target.style.display = 'block';

                    hideErrors();
                    checkFormReady();
                });
            });

            checkFormReady();
        })();

        // ══════════════════════════════════════════════════════════════════════
        // MODULE 2 — Email + Phone (OTP) JavaScript
        // ══════════════════════════════════════════════════════════════════════
        (function() {
            'use strict';

            var otpEmailInput = document.getElementById('otp-email');
            var otpPhoneInput = document.getElementById('otp-phone');
            var otpSendBtn = document.getElementById('otp-send-btn');
            var otpRequestForm = document.getElementById('otp-request-form');
            var otpStepInput = document.getElementById('otp-step-input');
            var otpStepVerify = document.getElementById('otp-step-verify');

            var otpVerifyBtn = document.getElementById('otp-verify-btn');
            var otpResendBtn = document.getElementById('otp-resend-btn');
            var otpResendTimer = document.getElementById('otp-resend-timer');
            var otpCountdown = document.getElementById('otp-countdown');
            var otpTimerEl = document.getElementById('otp-timer');
            var otpBackBtn = document.getElementById('otp-back-btn');
            var digits = document.querySelectorAll('.otp-digit');

            var sessionToken = null;
            var countdownInterval = null;
            var resendInterval = null;
            var expireAt = null; // JS Date

            var otpPhoneCode = document.getElementById('otp-phone-code');
            var otpPhoneFull = document.getElementById('otp-phone-full');

            // Combine dial code + national number into the hidden E.164 field that
            // is actually submitted (e.g. "+226" + "76642050" -> "+22676642050").
            function syncOtpPhone() {
                if (!otpPhoneFull) return;
                var code = otpPhoneCode ? otpPhoneCode.value : '';
                var national = otpPhoneInput ? otpPhoneInput.value.replace(/\D/g, '') : '';
                otpPhoneFull.value = national ? (code + national) : '';
            }

            // ── Form ready check ──────────────────────────────────────────────
            function checkOtpSendReady() {
                syncOtpPhone();
                var emailOk   = otpEmailInput && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(otpEmailInput.value.trim());
                var phoneOk   = otpPhoneInput && otpPhoneInput.value.replace(/\D/g, '').length >= 6;
                var tenderOk  = document.querySelectorAll('.otp-tender-cb:checked').length > 0;
                var captchaOk = {{ $bs->is_recaptcha == 1 ? '!!(window._fmfOtpCaptcha)' : 'true' }};
                if (otpSendBtn) otpSendBtn.disabled = !(emailOk && phoneOk && tenderOk && captchaOk);
            }

            if (otpEmailInput) otpEmailInput.addEventListener('input', checkOtpSendReady);
            if (otpPhoneInput) otpPhoneInput.addEventListener('input', checkOtpSendReady);
            if (otpPhoneCode)  otpPhoneCode.addEventListener('change', checkOtpSendReady);

            // ── Hide all OTP alerts ───────────────────────────────────────────
            function hideOtpAlerts() {
                ['otp-error-validation', 'otp-error-sms', 'otp-error-ratelimit', 'otp-error-payment-pending',
                    'otp-error-suspended', 'otp-error-nomatch', 'otp-error-regenlimit',
                    'otp-error-invalid', 'otp-error-exhausted', 'otp-error-expired', 'otp-error-session',
                    'otp-resend-success'
                ]
                .forEach(function(id) {
                    var el = document.getElementById(id);
                    if (el) el.style.display = 'none';
                });
            }

            // ── Countdown timer (OTP expiry) ──────────────────────────────────
            function startCountdown(ttlSeconds) {
                clearInterval(countdownInterval);
                expireAt = new Date(Date.now() + ttlSeconds * 1000);

                function tick() {
                    var remaining = Math.max(0, Math.round((expireAt - Date.now()) / 1000));
                    var m = Math.floor(remaining / 60);
                    var s = remaining % 60;
                    if (otpCountdown) otpCountdown.textContent = m + ':' + (s < 10 ? '0' : '') + s;
                    if (otpTimerEl) {
                        if (remaining <= 60) {
                            otpTimerEl.classList.add('urgent');
                        } else {
                            otpTimerEl.classList.remove('urgent');
                        }
                    }
                    if (remaining === 0) {
                        clearInterval(countdownInterval);
                        hideOtpAlerts();
                        var el = document.getElementById('otp-error-expired');
                        if (el) el.style.display = 'flex';
                        if (otpVerifyBtn) otpVerifyBtn.disabled = true;
                    }
                }

                tick();
                countdownInterval = setInterval(tick, 1000);
            }

            // ── Resend cooldown timer ─────────────────────────────────────────
            function startResendCooldown(seconds) {
                clearInterval(resendInterval);
                if (otpResendBtn) otpResendBtn.disabled = true;

                var end = Date.now() + seconds * 1000;

                function tick() {
                    var remaining = Math.max(0, Math.round((end - Date.now()) / 1000));
                    if (otpResendTimer) {
                        otpResendTimer.textContent = remaining > 0 ? '(' + remaining + 's)' : '';
                    }
                    if (remaining === 0) {
                        clearInterval(resendInterval);
                        if (otpResendBtn) otpResendBtn.disabled = false;
                    }
                }

                tick();
                resendInterval = setInterval(tick, 1000);
            }

            // ── OTP digit box behaviour ───────────────────────────────────────
            digits.forEach(function(input, index) {
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Backspace' && !input.value && index > 0) {
                        digits[index - 1].focus();
                    }
                });

                input.addEventListener('input', function() {
                    // Allow only digits
                    input.value = input.value.replace(/\D/g, '');
                    if (input.value) {
                        input.classList.add('filled');
                        if (index < digits.length - 1) {
                            digits[index + 1].focus();
                        }
                    } else {
                        input.classList.remove('filled');
                    }
                    checkOtpComplete();
                });

                // Handle paste on first box
                input.addEventListener('paste', function(e) {
                    e.preventDefault();
                    var pasted = (e.clipboardData || window.clipboardData).getData('text').replace(
                        /\D/g, '');
                    pasted.split('').slice(0, digits.length).forEach(function(ch, i) {
                        if (digits[i]) {
                            digits[i].value = ch;
                            digits[i].classList.toggle('filled', !!ch);
                        }
                    });
                    checkOtpComplete();
                    var lastFilled = Math.min(pasted.length, digits.length - 1);
                    if (digits[lastFilled]) digits[lastFilled].focus();
                });
            });

            function getOtpCode() {
                return Array.from(digits).map(function(d) {
                    return d.value;
                }).join('');
            }

            function clearDigits() {
                digits.forEach(function(d) {
                    d.value = '';
                    d.classList.remove('filled');
                });
                if (digits[0]) digits[0].focus();
            }

            function checkOtpComplete() {
                var code = getOtpCode();
                if (otpVerifyBtn) otpVerifyBtn.disabled = (code.length !== 6);
                if (code.length === 6) {
                    // Auto-submit
                    submitVerify();
                }
            }

            // ── Show OTP verify step ──────────────────────────────────────────
            function showVerifyStep(masked, resendAfter, otpSent) {
                if (otpStepInput) otpStepInput.style.display = 'none';
                if (otpStepVerify) otpStepVerify.style.display = 'block';

                var hint = document.getElementById('otp-sent-hint');
                if (hint) {
                    if (otpSent === false) {
                        hint.textContent = '{{ __("If your details match our records, a 6-digit code was sent to your phone.") }}';
                    } else {
                        hint.textContent = '{{ __("A 6-digit code was sent to") }} ' + masked;
                    }
                }
                hideOtpAlerts();
                clearDigits();
                startCountdown({{ \App\OtpVerification::OTP_TTL_MIN }} * 60);
                startResendCooldown(resendAfter || {{ \App\OtpVerification::RESEND_DELAY }});
            }

            // ── Show downloads-ready step (one row per selected tender) ────────
            function showDownloads(downloads) {
                clearInterval(countdownInterval);
                clearInterval(resendInterval);
                hideOtpAlerts();
                if (otpStepInput) otpStepInput.style.display = 'none';
                if (otpStepVerify) otpStepVerify.style.display = 'none';

                var list = document.getElementById('otp-dl-list');
                var step = document.getElementById('otp-step-downloads');
                if (!list || !step) return;

                list.innerHTML = '';
                (downloads || []).forEach(function(d) {
                    var row = document.createElement('div');
                    row.className = 'otp-dl-item';

                    var textWrap = document.createElement('div');
                    textWrap.className = 'otp-dl-text';

                    var title = document.createElement('div');
                    title.className = 'otp-dl-title';
                    title.textContent = d.title || '';
                    textWrap.appendChild(title);

                    // Sub-line: "Order XXXX · Name · Company" (only present parts).
                    var meta = [];
                    if (d.order)   meta.push('{{ __('Order') }} ' + d.order);
                    if (d.name)    meta.push(d.name);
                    if (d.company) meta.push(d.company);
                    if (meta.length) {
                        var sub = document.createElement('div');
                        sub.className = 'otp-dl-sub';
                        sub.textContent = meta.join(' · ');
                        textWrap.appendChild(sub);
                    }

                    var btn = document.createElement('a');
                    btn.className = 'otp-dl-btn';
                    btn.href = d.url;
                    btn.target = '_blank';
                    btn.rel = 'noopener';
                    btn.textContent = '{{ __('Download') }}';

                    row.appendChild(textWrap);
                    row.appendChild(btn);
                    list.appendChild(row);
                });

                step.style.display = 'block';
            }

            // ── Back button ───────────────────────────────────────────────────
            if (otpBackBtn) {
                otpBackBtn.addEventListener('click', function() {
                    sessionToken = null;
                    clearInterval(countdownInterval);
                    clearInterval(resendInterval);
                    hideOtpAlerts();
                    if (otpStepVerify) otpStepVerify.style.display = 'none';
                    if (otpStepInput) otpStepInput.style.display = 'block';
                });
            }

            // ── Step 1: Send OTP ──────────────────────────────────────────────
            if (otpRequestForm) {
                otpRequestForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    hideOtpAlerts();
                    syncOtpPhone();

                    var overlay = document.getElementById('fmf-overlay');
                    var procHeading = document.getElementById('fmf-processing-heading');
                    if (overlay) overlay.classList.add('active');
                    if (procHeading) procHeading.style.display = 'block';
                    if (otpSendBtn) otpSendBtn.disabled = true;

                    var formData = new FormData(otpRequestForm);
                    formData.append('_token', '{{ csrf_token() }}');

                    fetch('{{ route('find_my_files.otp_request') }}', {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },
                            body: formData,
                        })
                        .then(function(res) {
                            if (!res.ok) throw new Error('HTTP ' + res.status);
                            return res.json();
                        })
                        .then(function(data) {
                            if (overlay) overlay.classList.remove('active');
                            if (procHeading) procHeading.style.display = 'none';

                            if (data.status === 'success') {
                                sessionToken = data.session_token;
                                showVerifyStep(data.masked_phone, data.resend_after, data.otp_sent);
                            } else if (data.type === 'rate_limited') {
                                if (otpSendBtn) otpSendBtn.disabled = false;
                                @if ($bs->is_recaptcha == 1) window._fmfOtpCaptcha = false; if (typeof grecaptcha !== 'undefined') { try { grecaptcha.reset(); } catch(e){} } @endif
                                var msg = document.getElementById('otp-ratelimit-msg');
                                if (msg) msg.textContent = '{{ __('Please try again in') }} ' + (data
                                    .wait_text || (data.minutes + ' {{ __('minutes.') }}'));
                                var el = document.getElementById('otp-error-ratelimit');
                                if (el) el.style.display = 'flex';
                            } else if (data.type === 'payment_pending') {
                                if (otpSendBtn) otpSendBtn.disabled = false;
                                @if ($bs->is_recaptcha == 1) window._fmfOtpCaptcha = false; if (typeof grecaptcha !== 'undefined') { try { grecaptcha.reset(); } catch(e){} } @endif
                                var el = document.getElementById('otp-error-payment-pending');
                                if (el) el.style.display = 'flex';
                            } else if (data.type === 'sms_failed') {
                                if (otpSendBtn) otpSendBtn.disabled = false;
                                @if ($bs->is_recaptcha == 1) window._fmfOtpCaptcha = false; if (typeof grecaptcha !== 'undefined') { try { grecaptcha.reset(); } catch(e){} } @endif
                                var el = document.getElementById('otp-error-sms');
                                if (el) el.style.display = 'flex';
                            } else if (data.type === 'suspended') {
                                if (otpSendBtn) otpSendBtn.disabled = false;
                                @if ($bs->is_recaptcha == 1) window._fmfOtpCaptcha = false; if (typeof grecaptcha !== 'undefined') { try { grecaptcha.reset(); } catch(e){} } @endif
                                var el = document.getElementById('otp-error-suspended');
                                if (el) el.style.display = 'flex';
                            } else if (data.type === 'no_match') {
                                if (otpSendBtn) otpSendBtn.disabled = false;
                                @if ($bs->is_recaptcha == 1) window._fmfOtpCaptcha = false; if (typeof grecaptcha !== 'undefined') { try { grecaptcha.reset(); } catch(e){} } @endif
                                var el = document.getElementById('otp-error-nomatch');
                                if (el) el.style.display = 'flex';
                            } else {
                                if (otpSendBtn) otpSendBtn.disabled = false;
                                @if ($bs->is_recaptcha == 1) window._fmfOtpCaptcha = false; if (typeof grecaptcha !== 'undefined') { try { grecaptcha.reset(); } catch(e){} } @endif
                                var el = document.getElementById('otp-error-validation');
                                if (el) el.style.display = 'flex';
                            }
                        })
                        .catch(function() {
                            if (overlay) overlay.classList.remove('active');
                            if (procHeading) procHeading.style.display = 'none';
                            if (otpSendBtn) otpSendBtn.disabled = false;
                            var el = document.getElementById('otp-error-sms');
                            if (el) el.style.display = 'flex';
                        });
                });
            }

            // ── Step 2: Verify OTP ────────────────────────────────────────────
            function submitVerify() {
                if (!sessionToken) return;
                var code = getOtpCode();
                if (code.length !== 6) return;

                hideOtpAlerts();
                var overlay = document.getElementById('fmf-overlay');
                var procHeading = document.getElementById('fmf-processing-heading');
                if (overlay) overlay.classList.add('active');
                if (procHeading) procHeading.style.display = 'block';
                if (otpVerifyBtn) otpVerifyBtn.disabled = true;

                var body = new FormData();
                body.append('_token', '{{ csrf_token() }}');
                body.append('session_token', sessionToken);
                body.append('otp_code', code);

                fetch('{{ route('find_my_files.otp_verify') }}', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: body,
                    })
                    .then(function(res) {
                        if (!res.ok) throw new Error('HTTP ' + res.status);
                        return res.json();
                    })
                    .then(function(data) {
                        if (overlay) overlay.classList.remove('active');
                        if (procHeading) procHeading.style.display = 'none';

                        if (data.status === 'success' && data.downloads) {
                            showDownloads(data.downloads);
                            return;
                        }
                        if (data.status === 'success' && data.redirect) {
                            window.location.href = data.redirect;
                            return;
                        }

                        clearDigits();
                        if (otpVerifyBtn) otpVerifyBtn.disabled = true;

                        var errorMap = {
                            'otp_invalid': 'otp-error-invalid',
                            'otp_exhausted': 'otp-error-exhausted',
                            'otp_expired': 'otp-error-expired',
                            'session_invalid': 'otp-error-session',
                            'suspended': 'otp-error-suspended',
                            'regen_limit': 'otp-error-regenlimit',
                        };

                        var elId = errorMap[data.type];
                        if (elId) {
                            if (data.type === 'otp_invalid' && data.attempts_left !== undefined) {
                                var msg = document.getElementById('otp-attempts-msg');
                                if (msg) {
                                    msg.textContent = data.attempts_left === 1 ?
                                        '{{ __('1 attempt remaining.') }}' :
                                        data.attempts_left + ' {{ __('attempts remaining.') }}';
                                }
                            }
                            var errEl = document.getElementById(elId);
                            if (errEl) errEl.style.display = 'flex';
                        }
                    })
                    .catch(function() {
                        if (overlay) overlay.classList.remove('active');
                        if (procHeading) procHeading.style.display = 'none';
                        clearDigits();
                        var el = document.getElementById('otp-error-session');
                        if (el) el.style.display = 'flex';
                    });
            }

            if (otpVerifyBtn) {
                otpVerifyBtn.addEventListener('click', submitVerify);
            }

            // ── Resend OTP ────────────────────────────────────────────────────
            var otpResendLabel = otpResendBtn ? otpResendBtn.textContent.trim() : '';

            // Show/hide a spinner + "Sending…" state on the resend button.
            function setResendLoading(on) {
                if (!otpResendBtn) return;
                if (on) {
                    otpResendBtn.disabled = true;
                    otpResendBtn.classList.add('loading');
                    otpResendBtn.innerHTML =
                        '<span class="otp-resend-spin"></span>' + @json(__('Sending…'));
                } else {
                    otpResendBtn.classList.remove('loading');
                    otpResendBtn.textContent = otpResendLabel;
                }
            }

            if (otpResendBtn) {
                otpResendBtn.addEventListener('click', function() {
                    if (!sessionToken || otpResendBtn.disabled) return;

                    hideOtpAlerts();
                    setResendLoading(true);
                    if (otpResendTimer) otpResendTimer.textContent = '';

                    var body = new FormData();
                    body.append('_token', '{{ csrf_token() }}');
                    body.append('session_token', sessionToken);

                    fetch('{{ route('find_my_files.otp_resend') }}', {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },
                            body: body,
                        })
                        .then(function(res) {
                            return res.json();
                        })
                        .then(function(data) {
                            setResendLoading(false);
                            if (data.status === 'success') {
                                // New code issued → fully reset the verify UI for it:
                                // clear old digits/alerts, restart the 10-min expiry
                                // countdown and the resend cooldown from scratch.
                                hideOtpAlerts();
                                clearDigits();
                                if (otpVerifyBtn) otpVerifyBtn.disabled = true;
                                startCountdown({{ \App\OtpVerification::OTP_TTL_MIN }} * 60);
                                startResendCooldown(data.resend_after ||
                                    {{ \App\OtpVerification::RESEND_DELAY }});
                                // Visible confirmation that a new code went out.
                                var okEl = document.getElementById('otp-resend-success');
                                if (okEl) {
                                    okEl.style.display = 'flex';
                                    clearTimeout(window.__fmfResendOkTimer);
                                    window.__fmfResendOkTimer = setTimeout(function () {
                                        okEl.style.display = 'none';
                                    }, 5000);
                                }
                            } else if (data.type === 'resend_too_soon') {
                                // Too soon: keep the button locked and show the real
                                // remaining cooldown instead of silently unlocking.
                                startResendCooldown(data.resend_after ||
                                    {{ \App\OtpVerification::RESEND_DELAY }});
                            } else if (data.type === 'session_invalid') {
                                var el = document.getElementById('otp-error-session');
                                if (el) el.style.display = 'flex';
                            } else if (data.type === 'sms_failed') {
                                var el = document.getElementById('otp-error-sms');
                                if (el) el.style.display = 'flex';
                                otpResendBtn.disabled = false;
                            } else {
                                otpResendBtn.disabled = false;
                            }
                        })
                        .catch(function() {
                            setResendLoading(false);
                            otpResendBtn.disabled = false;
                        });
                });
            }

        })();

        // ══════════════════════════════════════════════════════════════════════
        // MODULE 3 — Email + Payment Reference JavaScript
        // ══════════════════════════════════════════════════════════════════════
        (function () {
            'use strict';

            var emailInput  = document.getElementById('payref-email');
            var refInput    = document.getElementById('payref-ref');
            var submitBtn   = document.getElementById('payref-submit-btn');
            var form        = document.getElementById('payref-form');

            // ── Normalise reference input to uppercase ────────────────────────
            if (refInput) {
                refInput.addEventListener('input', function () {
                    var pos = refInput.selectionStart;
                    refInput.value = refInput.value.toUpperCase().replace(/[^A-Z0-9\-_]/g, '');
                    refInput.setSelectionRange(pos, pos);
                    checkReady();
                });
            }

            // ── Enable submit when both fields valid ──────────────────────────
            function checkReady() {
                var emailOk   = emailInput && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value.trim());
                var refOk     = refInput   && refInput.value.trim().length >= 4;
                var captchaOk = {{ $bs->is_recaptcha == 1 ? '!!(window._fmfPayrefCaptcha)' : 'true' }};
                if (submitBtn) submitBtn.disabled = !(emailOk && refOk && captchaOk);
            }

            if (emailInput) emailInput.addEventListener('input', checkReady);

            // ── Hide alerts ───────────────────────────────────────────────────
            function hideAlerts() {
                ['payref-error-validation', 'payref-error-nomatch', 'payref-error-emailfailed', 'payref-error-ratelimit', 'payref-error-suspended', 'payref-error-regenlimit'].forEach(function (id) {
                    var el = document.getElementById(id);
                    if (el) el.style.display = 'none';
                });
            }

            // ── Form submit ───────────────────────────────────────────────────
            if (form) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    hideAlerts();

                    var overlay     = document.getElementById('fmf-overlay');
                    var procHeading = document.getElementById('fmf-processing-heading');
                    if (overlay)     overlay.classList.add('active');
                    if (procHeading) procHeading.style.display = 'block';
                    if (submitBtn)   submitBtn.disabled = true;

                    var body = new FormData(form);
                    body.set('_token', '{{ csrf_token() }}');

                    fetch('{{ route('find_my_files.payment_ref') }}', {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                        body: body,
                    })
                    .then(function (res) {
                        if (!res.ok) throw new Error('HTTP ' + res.status);
                        return res.json();
                    })
                    .then(function (data) {
                        if (overlay)     overlay.classList.remove('active');
                        if (procHeading) procHeading.style.display = 'none';

                        if (data.status === 'success' && data.redirect) {
                            window.location.href = data.redirect;
                            return;
                        }

                        if (submitBtn) submitBtn.disabled = false;
                        @if ($bs->is_recaptcha == 1)
                        window._fmfPayrefCaptcha = false;
                        if (typeof grecaptcha !== 'undefined') { try { grecaptcha.reset(); } catch(e){} }
                        @endif

                        if (data.type === 'rate_limited') {
                            var msg = document.getElementById('payref-ratelimit-msg');
                            if (msg) msg.textContent = '{{ __('Please try again in') }} ' + (data.wait_text || (data.minutes + ' {{ __('minutes.') }}'));
                            var el = document.getElementById('payref-error-ratelimit');
                            if (el) el.style.display = 'flex';
                        } else if (data.type === 'no_match') {
                            var el = document.getElementById('payref-error-nomatch');
                            if (el) el.style.display = 'flex';
                        } else if (data.type === 'email_failed') {
                            var el = document.getElementById('payref-error-emailfailed');
                            if (el) el.style.display = 'flex';
                        } else if (data.type === 'suspended') {
                            var el = document.getElementById('payref-error-suspended');
                            if (el) el.style.display = 'flex';
                        } else if (data.type === 'regen_limit') {
                            var el = document.getElementById('payref-error-regenlimit');
                            if (el) el.style.display = 'flex';
                        } else {
                            var el = document.getElementById('payref-error-validation');
                            if (el) el.style.display = 'flex';
                        }
                    })
                    .catch(function () {
                        if (overlay)     overlay.classList.remove('active');
                        if (procHeading) procHeading.style.display = 'none';
                        if (submitBtn)   submitBtn.disabled = false;
                        var el = document.getElementById('payref-error-validation');
                        if (el) el.style.display = 'flex';
                    });
                });
            }

        })();

        // ══════════════════════════════════════════════════════════════════════
        // MODULE 4 — Expired Link / Regenerate JavaScript
        // ══════════════════════════════════════════════════════════════════════
        (function () {
            'use strict';

            var emailInput    = document.getElementById('regen-email');
            var dateFromInput = document.getElementById('regen-date-from');
            var dateToInput   = document.getElementById('regen-date-to');
            var submitBtn     = document.getElementById('regen-submit-btn');
            var form          = document.getElementById('regen-form');

            var stepInput  = document.getElementById('regen-step-input');
            var stepVerify = document.getElementById('regen-step-verify');
            var digits     = document.querySelectorAll('.regen-otp-digit');
            var verifyBtn  = document.getElementById('regen-otp-verify-btn');
            var resendBtn  = document.getElementById('regen-otp-resend-btn');
            var backBtn    = document.getElementById('regen-otp-back-btn');
            var countdownEl = document.getElementById('regen-otp-countdown');
            var timerEl      = document.getElementById('regen-otp-timer');
            var resendTimerEl = document.getElementById('regen-otp-resend-timer');

            var sessionToken = null;
            var countdownInterval, resendInterval, expireAt;

            // ── Enable submit when email + date range are valid ───────────────
            function checkReady() {
                var emailOk   = emailInput && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value.trim());
                var datesOk   = dateFromInput && dateToInput && dateFromInput.value && dateToInput.value;
                var captchaOk = {{ $bs->is_recaptcha == 1 ? '!!(window._fmfRegenCaptcha)' : 'true' }};
                if (submitBtn) submitBtn.disabled = !(emailOk && datesOk && captchaOk);
            }
            if (emailInput) emailInput.addEventListener('input', checkReady);
            if (dateFromInput) dateFromInput.addEventListener('input', checkReady);
            if (dateToInput) dateToInput.addEventListener('input', checkReady);

            // ── Hide alerts (step A) ────────────────────────────────────────────
            function hideAlerts() {
                ['regen-error-validation', 'regen-error-ratelimit', 'regen-error-limit', 'regen-error-emailfailed', 'regen-error-suspended', 'regen-error-nomatch']
                    .forEach(function (id) {
                        var el = document.getElementById(id);
                        if (el) el.style.display = 'none';
                    });
            }

            // ── Hide OTP alerts (step B) ─────────────────────────────────────────
            function hideOtpAlerts() {
                ['regen-otp-error-invalid', 'regen-otp-error-exhausted', 'regen-otp-error-expired',
                    'regen-otp-error-session', 'regen-otp-error-sendfailed', 'regen-otp-resend-success']
                    .forEach(function (id) {
                        var el = document.getElementById(id);
                        if (el) el.style.display = 'none';
                    });
            }

            // ── Countdown timer (OTP expiry) ────────────────────────────────────
            function startCountdown(ttlSeconds) {
                clearInterval(countdownInterval);
                expireAt = new Date(Date.now() + ttlSeconds * 1000);

                function tick() {
                    var remaining = Math.max(0, Math.round((expireAt - Date.now()) / 1000));
                    var m = Math.floor(remaining / 60);
                    var s = remaining % 60;
                    if (countdownEl) countdownEl.textContent = m + ':' + (s < 10 ? '0' : '') + s;
                    if (timerEl) {
                        if (remaining <= 60) { timerEl.classList.add('urgent'); }
                        else { timerEl.classList.remove('urgent'); }
                    }
                    if (remaining === 0) {
                        clearInterval(countdownInterval);
                        hideOtpAlerts();
                        var el = document.getElementById('regen-otp-error-expired');
                        if (el) el.style.display = 'flex';
                        if (verifyBtn) verifyBtn.disabled = true;
                    }
                }
                tick();
                countdownInterval = setInterval(tick, 1000);
            }

            // ── Resend cooldown timer ───────────────────────────────────────────
            function startResendCooldown(seconds) {
                clearInterval(resendInterval);
                if (resendBtn) resendBtn.disabled = true;
                var end = Date.now() + seconds * 1000;

                function tick() {
                    var remaining = Math.max(0, Math.round((end - Date.now()) / 1000));
                    if (resendTimerEl) resendTimerEl.textContent = remaining > 0 ? '(' + remaining + 's)' : '';
                    if (remaining === 0) {
                        clearInterval(resendInterval);
                        if (resendBtn) resendBtn.disabled = false;
                    }
                }
                tick();
                resendInterval = setInterval(tick, 1000);
            }

            // ── Digit box behaviour ──────────────────────────────────────────────
            digits.forEach(function (input, index) {
                input.addEventListener('keydown', function (e) {
                    if (e.key === 'Backspace' && !input.value && index > 0) {
                        digits[index - 1].focus();
                    }
                });
                input.addEventListener('input', function () {
                    input.value = input.value.replace(/\D/g, '');
                    if (input.value) {
                        input.classList.add('filled');
                        if (index < digits.length - 1) digits[index + 1].focus();
                    } else {
                        input.classList.remove('filled');
                    }
                    checkOtpComplete();
                });
                input.addEventListener('paste', function (e) {
                    e.preventDefault();
                    var pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
                    pasted.split('').slice(0, digits.length).forEach(function (ch, i) {
                        if (digits[i]) {
                            digits[i].value = ch;
                            digits[i].classList.toggle('filled', !!ch);
                        }
                    });
                    checkOtpComplete();
                    var lastFilled = Math.min(pasted.length, digits.length - 1);
                    if (digits[lastFilled]) digits[lastFilled].focus();
                });
            });

            function getOtpCode() {
                return Array.from(digits).map(function (d) { return d.value; }).join('');
            }
            function clearDigits() {
                digits.forEach(function (d) { d.value = ''; d.classList.remove('filled'); });
                if (digits[0]) digits[0].focus();
            }
            function checkOtpComplete() {
                var code = getOtpCode();
                if (verifyBtn) verifyBtn.disabled = (code.length !== 6);
                if (code.length === 6) submitVerify();
            }

            // ── Show step B ──────────────────────────────────────────────────────
            function showVerifyStep(maskedEmail, resendAfter) {
                if (stepInput) stepInput.style.display = 'none';
                if (stepVerify) stepVerify.style.display = 'block';

                var hint = document.getElementById('regen-otp-sent-hint');
                if (hint) hint.textContent = '{{ __('A 6-digit code was sent to') }} ' + maskedEmail;

                hideOtpAlerts();
                clearDigits();
                startCountdown({{ \App\OtpVerification::OTP_TTL_MIN }} * 60);
                startResendCooldown(resendAfter || {{ \App\OtpVerification::RESEND_DELAY }});
            }

            // ── Form submit (step A) ─────────────────────────────────────────────
            if (form) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    hideAlerts();

                    var overlay     = document.getElementById('fmf-overlay');
                    var procHeading = document.getElementById('fmf-processing-heading');
                    if (overlay)     overlay.classList.add('active');
                    if (procHeading) procHeading.style.display = 'block';
                    if (submitBtn)   submitBtn.disabled = true;

                    var body = new FormData(form);
                    body.set('_token', '{{ csrf_token() }}');

                    fetch('{{ route('find_my_files.regenerate') }}', {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                        body: body,
                    })
                    .then(function (res) {
                        if (!res.ok) throw new Error('HTTP ' + res.status);
                        return res.json();
                    })
                    .then(function (data) {
                        if (overlay)     overlay.classList.remove('active');
                        if (procHeading) procHeading.style.display = 'none';

                        if (data.status === 'success' && data.otp_sent) {
                            sessionToken = data.session_token;
                            showVerifyStep(data.masked_email, data.resend_after);
                            return;
                        }
                        if (data.status === 'success' && data.redirect) {
                            window.location.href = data.redirect;
                            return;
                        }

                        if (submitBtn) submitBtn.disabled = false;
                        @if ($bs->is_recaptcha == 1)
                        window._fmfRegenCaptcha = false;
                        if (typeof grecaptcha !== 'undefined') { try { grecaptcha.reset(); } catch(e){} }
                        @endif

                        if (data.type === 'rate_limited') {
                            var msg = document.getElementById('regen-ratelimit-msg');
                            if (msg) msg.textContent = '{{ __('Please try again in') }} ' + (data.wait_text || (data.minutes + ' {{ __('minutes.') }}'));
                            var el = document.getElementById('regen-error-ratelimit');
                            if (el) el.style.display = 'flex';
                        } else if (data.type === 'regen_limit') {
                            var el = document.getElementById('regen-error-limit');
                            if (el) el.style.display = 'flex';
                        } else if (data.type === 'email_failed') {
                            var el = document.getElementById('regen-error-emailfailed');
                            if (el) el.style.display = 'flex';
                        } else if (data.type === 'suspended') {
                            var el = document.getElementById('regen-error-suspended');
                            if (el) el.style.display = 'flex';
                        } else if (data.type === 'no_match') {
                            var el = document.getElementById('regen-error-nomatch');
                            if (el) el.style.display = 'flex';
                        } else {
                            var el = document.getElementById('regen-error-validation');
                            if (el) el.style.display = 'flex';
                        }
                    })
                    .catch(function () {
                        if (overlay)     overlay.classList.remove('active');
                        if (procHeading) procHeading.style.display = 'none';
                        if (submitBtn)   submitBtn.disabled = false;
                        var el = document.getElementById('regen-error-validation');
                        if (el) el.style.display = 'flex';
                    });
                });
            }

            // ── Step B: Verify OTP ───────────────────────────────────────────────
            function submitVerify() {
                if (!sessionToken) return;
                var code = getOtpCode();
                if (code.length !== 6) return;

                hideOtpAlerts();
                var overlay     = document.getElementById('fmf-overlay');
                var procHeading = document.getElementById('fmf-processing-heading');
                if (overlay) overlay.classList.add('active');
                if (procHeading) procHeading.style.display = 'block';
                if (verifyBtn) verifyBtn.disabled = true;

                var body = new FormData();
                body.append('_token', '{{ csrf_token() }}');
                body.append('session_token', sessionToken);
                body.append('otp_code', code);

                fetch('{{ route('find_my_files.regenerate_otp_verify') }}', {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                        body: body,
                    })
                    .then(function (res) {
                        if (!res.ok) throw new Error('HTTP ' + res.status);
                        return res.json();
                    })
                    .then(function (data) {
                        if (overlay) overlay.classList.remove('active');
                        if (procHeading) procHeading.style.display = 'none';

                        if (data.status === 'success' && data.redirect) {
                            window.location.href = data.redirect;
                            return;
                        }

                        clearDigits();
                        if (verifyBtn) verifyBtn.disabled = true;

                        // These 4 come back from AFTER verification succeeded (link
                        // issuance itself failed) — their alert boxes live in Step A,
                        // so drop back to Step A to show them (matches Back button).
                        var stepAErrors = {
                            'suspended': 'regen-error-suspended',
                            'regen_limit': 'regen-error-limit',
                            'no_match': 'regen-error-nomatch',
                            'email_failed': 'regen-error-emailfailed',
                        };
                        if (stepAErrors[data.type]) {
                            clearInterval(countdownInterval);
                            clearInterval(resendInterval);
                            sessionToken = null;
                            if (stepVerify) stepVerify.style.display = 'none';
                            if (stepInput) stepInput.style.display = 'block';
                            if (submitBtn) submitBtn.disabled = false;
                            var errEl = document.getElementById(stepAErrors[data.type]);
                            if (errEl) errEl.style.display = 'flex';
                            return;
                        }

                        var errorMap = {
                            'otp_invalid': 'regen-otp-error-invalid',
                            'otp_exhausted': 'regen-otp-error-exhausted',
                            'otp_expired': 'regen-otp-error-expired',
                            'session_invalid': 'regen-otp-error-session',
                        };

                        var elId = errorMap[data.type];
                        if (elId === 'regen-otp-error-invalid' && data.attempts_left !== undefined) {
                            var msg = document.getElementById('regen-otp-attempts-msg');
                            if (msg) {
                                msg.textContent = data.attempts_left === 1 ?
                                    '{{ __('1 attempt remaining.') }}' :
                                    data.attempts_left + ' {{ __('attempts remaining.') }}';
                            }
                        }
                        var errEl = document.getElementById(elId || 'regen-otp-error-invalid');
                        if (errEl) errEl.style.display = 'flex';
                    })
                    .catch(function () {
                        if (overlay) overlay.classList.remove('active');
                        if (procHeading) procHeading.style.display = 'none';
                        clearDigits();
                        var el = document.getElementById('regen-otp-error-session');
                        if (el) el.style.display = 'flex';
                    });
            }

            if (verifyBtn) verifyBtn.addEventListener('click', submitVerify);

            // ── Resend OTP ───────────────────────────────────────────────────────
            var resendLabel = resendBtn ? resendBtn.textContent.trim() : '';

            function setResendLoading(on) {
                if (!resendBtn) return;
                if (on) {
                    resendBtn.disabled = true;
                    resendBtn.classList.add('loading');
                    resendBtn.innerHTML = '<span class="otp-resend-spin"></span>' + @json(__('Sending…'));
                } else {
                    resendBtn.classList.remove('loading');
                    resendBtn.textContent = resendLabel;
                }
            }

            if (resendBtn) {
                resendBtn.addEventListener('click', function () {
                    if (!sessionToken || resendBtn.disabled) return;

                    hideOtpAlerts();
                    setResendLoading(true);
                    if (resendTimerEl) resendTimerEl.textContent = '';

                    var body = new FormData();
                    body.append('_token', '{{ csrf_token() }}');
                    body.append('session_token', sessionToken);

                    fetch('{{ route('find_my_files.regenerate_otp_resend') }}', {
                            method: 'POST',
                            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                            body: body,
                        })
                        .then(function (res) { return res.json(); })
                        .then(function (data) {
                            setResendLoading(false);
                            if (data.status === 'success') {
                                hideOtpAlerts();
                                clearDigits();
                                if (verifyBtn) verifyBtn.disabled = true;
                                startCountdown({{ \App\OtpVerification::OTP_TTL_MIN }} * 60);
                                startResendCooldown(data.resend_after || {{ \App\OtpVerification::RESEND_DELAY }});
                                var okEl = document.getElementById('regen-otp-resend-success');
                                if (okEl) {
                                    okEl.style.display = 'flex';
                                    clearTimeout(window.__regenResendOkTimer);
                                    window.__regenResendOkTimer = setTimeout(function () {
                                        okEl.style.display = 'none';
                                    }, 5000);
                                }
                            } else if (data.type === 'resend_too_soon') {
                                startResendCooldown(data.resend_after || {{ \App\OtpVerification::RESEND_DELAY }});
                            } else if (data.type === 'session_invalid') {
                                var el = document.getElementById('regen-otp-error-session');
                                if (el) el.style.display = 'flex';
                            } else if (data.type === 'email_failed') {
                                var el = document.getElementById('regen-otp-error-sendfailed');
                                if (el) el.style.display = 'flex';
                                resendBtn.disabled = false;
                            } else {
                                resendBtn.disabled = false;
                            }
                        })
                        .catch(function () {
                            setResendLoading(false);
                            resendBtn.disabled = false;
                        });
                });
            }

            // ── Back button ──────────────────────────────────────────────────────
            if (backBtn) {
                backBtn.addEventListener('click', function () {
                    sessionToken = null;
                    clearInterval(countdownInterval);
                    clearInterval(resendInterval);
                    hideOtpAlerts();
                    if (stepVerify) stepVerify.style.display = 'none';
                    if (stepInput) stepInput.style.display = 'block';
                });
            }

            // ── Hash-based auto-panel activation (#method=expired_link) ──────
            function activatePanelFromHash() {
                var hash = window.location.hash;
                if (!hash) return;
                var match = hash.match(/method=([a-z_]+)/);
                if (!match) return;
                var method = match[1];

                var btn = document.querySelector('.fmf-method-btn[data-method="' + method + '"]');
                if (!btn || btn.classList.contains('disabled-method')) return;

                // Deactivate all
                document.querySelectorAll('.fmf-method-btn').forEach(function (b) {
                    b.classList.remove('active');
                });
                document.querySelectorAll('[id^="panel-"]').forEach(function (p) {
                    p.style.display = 'none';
                });

                // Activate target
                btn.classList.add('active');
                var panel = document.getElementById('panel-' + method);
                if (panel) panel.style.display = 'block';

                // Scroll card into view
                var card = document.querySelector('.fmf-card');
                if (card) card.scrollIntoView({ behavior: 'smooth', block: 'start' });

                // Clean hash without page jump
                history.replaceState(null, '', window.location.pathname + window.location.search);
            }

            document.addEventListener('DOMContentLoaded', activatePanelFromHash);

        })();
    </script>
@endsection
