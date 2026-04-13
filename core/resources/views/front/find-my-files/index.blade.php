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
    </style>
@endsection

@section('content')
    <section class="fmf-section">
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

                                <button type="button" class="fmf-method-btn disabled-method" data-method="phone_otp"
                                    disabled>
                                    {{ __('Email + Phone (OTP') }})
                                    <span
                                        style="display:block; font-size:11px; color:#b0b8c9; font-weight:400; margin-top:2px;">{{ __('Coming soon') }}</span>
                                </button>

                                <button type="button" class="fmf-method-btn disabled-method" data-method="payment_ref"
                                    disabled>
                                    {{ __('Email + Payment Reference') }}
                                    <span
                                        style="display:block; font-size:11px; color:#b0b8c9; font-weight:400; margin-top:2px;">{{ __('Coming soon') }}</span>
                                </button>

                                <button type="button" class="fmf-method-btn disabled-method" data-method="expired_link"
                                    disabled>
                                    {{ __('Expired Link / Regenerate') }}
                                    <span
                                        style="display:block; font-size:11px; color:#b0b8c9; font-weight:400; margin-top:2px;">{{ __('Coming soon') }}</span>
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
                                                <strong id="fmf-error-title">{{ __("We couldn't verify your information.") }}</strong>
                                                <p id="fmf-error-body">{{ __('Please check your details and try again.') }}</p>
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

                                {{-- Contact Support placeholder --}}
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
                                    rateLimitMsg.textContent = '{{ __('Please try again in') }} ' + data.minutes + ' {{ __('minutes.') }}';
                                }
                                if (submitArea) submitArea.style.display = 'none';
                                if (errRatelimit) errRatelimit.style.display = 'flex';
                            } else if (data.status === 'error') {
                                hideOverlay();
                                resetCaptcha();
                                submitBtn.disabled = false;

                                var titles = {
                                    'validation':      '{{ __('Please fill in all required fields correctly.') }}',
                                    'order_not_found': '{{ __('Order not found.') }}',
                                    'email_mismatch':  '{{ __('Email address does not match.') }}',
                                    'invalid_status':  '{{ __('Order not eligible.') }}',
                                };
                                var bodies = {
                                    'validation':      '{{ __('Check your email and order number, then try again.') }}',
                                    'order_not_found': data.message || '{{ __('No order was found with this order number. Please check and try again.') }}',
                                    'email_mismatch':  data.message || '{{ __('The email address does not match the one used for this order.') }}',
                                    'invalid_status':  data.message || '{{ __('This order has not been completed. Only paid orders are eligible for file recovery.') }}',
                                };

                                var errTitle = document.getElementById('fmf-error-title');
                                var errBody  = document.getElementById('fmf-error-body');
                                if (errTitle) errTitle.textContent = titles[data.type] || '{{ __('An error occurred.') }}';
                                if (errBody)  errBody.textContent  = bodies[data.type] || data.message || '{{ __('Please try again.') }}';
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
                            var errBody  = document.getElementById('fmf-error-body');
                            if (errTitle) errTitle.textContent = '{{ __('Connection error.') }}';
                            if (errBody)  errBody.textContent  = '{{ __('Could not reach the server. Please check your connection and try again.') }}';
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
    </script>
@endsection
