@extends("front.$version.layout")

@php
    // This page opens the whole visual "Security Verification" hero itself
    // (icon, title, subtitle inside .fmf-otp-top below) — the layout's own
    // breadcrumb banner on top of that reads as a redundant duplicate
    // header rather than page context.
    $hideBreadcrumb = true;
@endphp

@section('pagename')
    - {{ __('Verify This Device') }}
@endsection

@section('styles')
<style>
.fmf-otp-section {
    padding: 60px 0 80px;
    background: #f4f6f9;
    min-height: 65vh;
}
.fmf-otp-top {
    text-align: center;
    max-width: 560px;
    margin: 0 auto 28px;
}
.fmf-otp-top-icon {
    width: 60px;
    height: 60px;
    margin: 0 auto 18px;
    background: #2563eb;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 20px rgba(37, 99, 235, .28);
}
.fmf-otp-top-icon svg {
    width: 28px;
    height: 28px;
    fill: none;
    stroke: #fff;
    stroke-width: 2.2;
    stroke-linecap: round;
    stroke-linejoin: round;
}
.fmf-otp-top h1 {
    font-size: 28px;
    font-weight: 800;
    color: #111827;
    margin: 0 0 8px;
}
.fmf-otp-top p {
    font-size: 15px;
    color: #6b7280;
    line-height: 1.6;
    margin: 0;
}

.fmf-otp-card {
    max-width: 560px;
    margin: 0 auto;
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 4px 24px rgba(0,0,0,.08);
    padding: 26px 30px 32px;
}

.fmf-otp-banner {
    display: flex;
    gap: 14px;
    align-items: flex-start;
    background: #eff6ff;
    border: 1px solid #dbeafe;
    border-radius: 10px;
    padding: 16px 18px;
    margin-bottom: 18px;
}
.fmf-otp-banner svg {
    flex-shrink: 0;
    width: 24px;
    height: 24px;
    fill: none;
    stroke: #2563eb;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
    margin-top: 2px;
}
.fmf-otp-banner strong {
    display: block;
    font-size: 14px;
    color: #1d4ed8;
    margin-bottom: 3px;
}
.fmf-otp-banner span {
    font-size: 13px;
    color: #475569;
    line-height: 1.5;
}

.fmf-otp-email-box {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 14px 16px;
    margin-bottom: 20px;
}
.fmf-otp-email-box svg {
    flex-shrink: 0;
    width: 20px;
    height: 20px;
    fill: none;
    stroke: #64748b;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
}
.fmf-otp-email-box div span:first-child {
    display: block;
    font-size: 12px;
    color: #6b7280;
    margin-bottom: 2px;
}
.fmf-otp-email-box strong {
    font-size: 14px;
    color: #111827;
    font-weight: 700;
}

.fmf-otp-label {
    font-size: 13px;
    font-weight: 700;
    color: #374151;
    margin-bottom: 10px;
    display: block;
}

.otp-digits {
    display: flex;
    gap: 10px;
    justify-content: center;
    margin: 0 0 14px;
}
.otp-digit {
    width: 15%;
    max-width: 54px;
    aspect-ratio: 1 / 1;
    text-align: center;
    font-size: 22px;
    font-weight: 700;
    border-radius: 9px;
    outline: none;
    transition: border-color .2s, box-shadow .2s;
}
/* dark-glass.css has a sitewide `input[type="text"] { ... !important }`
   rule (near-white-on-white: rgba(255,255,255,.06) bg,
   rgba(255,255,255,.14) border, white text) at equal specificity to a bare
   .otp-digit class selector — confirmed via computed style that it was
   winning on source order and made the digit boxes invisible on this
   card's white background. Anchoring to the parent id raises specificity
   enough to beat it regardless of stylesheet order. */
#fmf-otp-digits .otp-digit {
    border: 2px solid #d1d9e0 !important;
    color: #1f2937 !important;
    background: #fff !important;
}
#fmf-otp-digits .otp-digit:focus {
    border-color: #2563eb !important;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, .14);
}
#fmf-otp-digits .otp-digit.filled {
    border-color: #2563eb !important;
}

.fmf-otp-error {
    font-size: 13px;
    color: #dc2626;
    min-height: 18px;
    text-align: center;
    margin-bottom: 6px;
}

.otp-timer {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-size: 13px;
    color: #6b7280;
    margin: 6px 0 20px;
}
.otp-timer-badge {
    display: inline-block;
    background: #eff6ff;
    color: #1d4ed8;
    font-weight: 800;
    font-size: 13px;
    padding: 3px 10px;
    border-radius: 20px;
    font-variant-numeric: tabular-nums;
}
.otp-timer.urgent .otp-timer-badge {
    background: #fef2f2;
    color: #dc2626;
}

.btn-fmf-otp-verify {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 14px 24px;
    background: #2563eb;
    color: #fff;
    font-size: 15px;
    font-weight: 700;
    text-align: center;
    border: none;
    border-radius: 8px;
    letter-spacing: .01em;
    transition: background .2s;
    cursor: pointer;
}
.btn-fmf-otp-verify svg {
    width: 17px;
    height: 17px;
    fill: none;
    stroke: #fff;
    stroke-width: 2.4;
    stroke-linecap: round;
    stroke-linejoin: round;
}
.btn-fmf-otp-verify.disabled {
    background: #93b4f8;
    pointer-events: none;
    cursor: default;
}
.btn-fmf-otp-verify:hover {
    background: #1d4ed8;
}

.fmf-otp-divider {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 18px 0;
    color: #9ca3af;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: .08em;
}
.fmf-otp-divider::before,
.fmf-otp-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: #e5e7eb;
}

.btn-fmf-otp-resend {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 13px 24px;
    background: #fff;
    color: #2563eb;
    font-size: 14px;
    font-weight: 700;
    text-align: center;
    border: 1.5px solid #2563eb;
    border-radius: 8px;
    cursor: pointer;
    transition: background .2s;
}
.btn-fmf-otp-resend svg {
    width: 15px;
    height: 15px;
    fill: none;
    stroke: #2563eb;
    stroke-width: 2.2;
    stroke-linecap: round;
    stroke-linejoin: round;
}
.btn-fmf-otp-resend:disabled {
    color: #9ca3af;
    border-color: #d1d5db;
    cursor: default;
}
.btn-fmf-otp-resend:disabled svg {
    stroke: #9ca3af;
}
.btn-fmf-otp-resend:not(:disabled):hover {
    background: #eff6ff;
}

.fmf-otp-why {
    max-width: 560px;
    margin: 20px auto 0;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,.05);
    padding: 18px 22px;
}
.fmf-otp-why-head {
    display: flex;
    gap: 12px;
    align-items: flex-start;
    margin-bottom: 14px;
}
.fmf-otp-why-head svg {
    flex-shrink: 0;
    width: 20px;
    height: 20px;
    fill: none;
    stroke: #2563eb;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
}
.fmf-otp-why-head strong {
    display: block;
    font-size: 14px;
    color: #111827;
    margin-bottom: 2px;
}
.fmf-otp-why-head span {
    font-size: 13px;
    color: #6b7280;
    line-height: 1.5;
}
.fmf-otp-why-row {
    display: flex;
    flex-wrap: wrap;
    gap: 22px;
    padding-top: 12px;
    border-top: 1px solid #f1f5f9;
}
.fmf-otp-why-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12.5px;
    color: #475569;
    font-weight: 600;
}
.fmf-otp-why-item svg {
    width: 18px;
    height: 18px;
    fill: none;
    stroke: #2563eb;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
}
@media (max-width: 575px) {
    .fmf-otp-card { padding: 22px 18px 26px; }
    .otp-digit { font-size: 18px; }
}

/* ---- Dark theme override (this site runs dark-glass by default) ----
   Mirrors the exact tokens the rest of this page family already uses
   (.dark-fmf .fmf-card / .otp-digit / .btn-fmf-submit / .fmf-security-box
   in dark-glass.css) instead of the light-mockup colors above, so this
   page reads as part of the same app instead of a pasted-in light panel. */
.dark-fmf-dl .fmf-otp-top h1 { color: #fff; }
.dark-fmf-dl .fmf-otp-top p { color: rgba(255, 255, 255, .55); }
.dark-fmf-dl .fmf-otp-top-icon {
    background: linear-gradient(135deg, var(--accent), var(--accent-2));
    box-shadow: 0 8px 24px rgba(var(--accent-2-rgb), .3);
}
.dark-fmf-dl .fmf-otp-top-icon svg { stroke: #04101f; }

.dark-fmf-dl .fmf-otp-card {
    background: rgba(15, 20, 30, .72);
    border: 1px solid var(--glass-border);
    border-radius: 22px;
    box-shadow: none;
    backdrop-filter: blur(var(--glass-blur));
    -webkit-backdrop-filter: blur(var(--glass-blur));
}

.dark-fmf-dl .fmf-otp-banner {
    background: rgba(var(--accent-2-rgb), .08);
    border-color: rgba(var(--accent-2-rgb), .3);
}
.dark-fmf-dl .fmf-otp-banner svg { stroke: var(--accent-2); }
.dark-fmf-dl .fmf-otp-banner strong { color: var(--accent-2); }
.dark-fmf-dl .fmf-otp-banner span { color: rgba(255, 255, 255, .65); }

.dark-fmf-dl .fmf-otp-email-box {
    background: rgba(255, 255, 255, .04);
    border-color: var(--glass-border);
}
.dark-fmf-dl .fmf-otp-email-box svg { stroke: rgba(255, 255, 255, .5); }
.dark-fmf-dl .fmf-otp-email-box div span:first-child { color: rgba(255, 255, 255, .5); }
.dark-fmf-dl .fmf-otp-email-box strong { color: #fff; }

.dark-fmf-dl .fmf-otp-label { color: rgba(255, 255, 255, .75); }

/* Beats the sitewide input[type=text]{...!important} rule in dark-glass.css
   (equal specificity, loses on source order otherwise — confirmed via
   computed style earlier) by anchoring to the parent id. Dark-appropriate
   values instead of the light-mode #fff/#1f2937 used above. */
.dark-fmf-dl #fmf-otp-digits .otp-digit {
    border: 2px solid var(--glass-border) !important;
    color: #fff !important;
    background: rgba(255, 255, 255, .05) !important;
}
.dark-fmf-dl #fmf-otp-digits .otp-digit:focus {
    border-color: var(--accent-2) !important;
    box-shadow: 0 0 0 3px rgba(var(--accent-2-rgb), .18);
}
.dark-fmf-dl #fmf-otp-digits .otp-digit.filled {
    border-color: var(--accent-2) !important;
}

.dark-fmf-dl .fmf-otp-error { color: #ffb3ab; }

.dark-fmf-dl .otp-timer { color: rgba(255, 255, 255, .55); }
.dark-fmf-dl .otp-timer-badge {
    background: rgba(var(--accent-2-rgb), .12);
    color: var(--accent-2);
}
.dark-fmf-dl .otp-timer.urgent .otp-timer-badge {
    background: rgba(255, 122, 112, .12);
    color: #ffb3ab;
}

.dark-fmf-dl .btn-fmf-otp-verify {
    background: linear-gradient(135deg, var(--accent), var(--accent-2));
    color: #04101f;
    font-family: "Plex Mono", monospace;
    letter-spacing: .03em;
}
.dark-fmf-dl .btn-fmf-otp-verify svg { stroke: #04101f; }
.dark-fmf-dl .btn-fmf-otp-verify:hover { filter: brightness(1.08); background: linear-gradient(135deg, var(--accent), var(--accent-2)); }
.dark-fmf-dl .btn-fmf-otp-verify.disabled { opacity: .4; background: linear-gradient(135deg, var(--accent), var(--accent-2)); }

.dark-fmf-dl .fmf-otp-divider { color: rgba(255, 255, 255, .35); }
.dark-fmf-dl .fmf-otp-divider::before,
.dark-fmf-dl .fmf-otp-divider::after { background: var(--glass-border); }

.dark-fmf-dl .btn-fmf-otp-resend {
    background: transparent;
    color: var(--accent-2);
    border-color: var(--accent-2);
}
.dark-fmf-dl .btn-fmf-otp-resend svg { stroke: var(--accent-2); }
.dark-fmf-dl .btn-fmf-otp-resend:disabled {
    color: rgba(255, 255, 255, .3);
    border-color: var(--glass-border);
}
.dark-fmf-dl .btn-fmf-otp-resend:disabled svg { stroke: rgba(255, 255, 255, .3); }
.dark-fmf-dl .btn-fmf-otp-resend:not(:disabled):hover {
    background: rgba(var(--accent-2-rgb), .08);
}

.dark-fmf-dl .fmf-otp-why {
    background: rgba(var(--accent-2-rgb), .06);
    border: 1px solid rgba(var(--accent-2-rgb), .25);
    box-shadow: none;
}
.dark-fmf-dl .fmf-otp-why-head svg { stroke: var(--accent-2); }
.dark-fmf-dl .fmf-otp-why-head strong { color: var(--accent-2); }
.dark-fmf-dl .fmf-otp-why-head span { color: rgba(255, 255, 255, .6); }
.dark-fmf-dl .fmf-otp-why-row { border-top-color: rgba(255, 255, 255, .1); }
.dark-fmf-dl .fmf-otp-why-item { color: rgba(255, 255, 255, .65); }
.dark-fmf-dl .fmf-otp-why-item svg { stroke: var(--accent-2); }
</style>
@endsection

@section('content')
<section class="fmf-otp-section @if ($be->theme_version == 'dark') dark-fmf-dl @endif"
    @if ($be->theme_version == 'dark') data-particle-network @endif>
    <div class="container">

        <div class="fmf-otp-top">
            <div class="fmf-otp-top-icon">
                <svg viewBox="0 0 24 24">
                    <path d="M12 2l7 4v6c0 4.5-3.1 8.7-7 10-3.9-1.3-7-5.5-7-10V6l7-4z"/>
                    <rect x="9.5" y="11" width="5" height="4.2" rx="1"/>
                    <path d="M10.3 11V9.6a1.7 1.7 0 0 1 3.4 0V11"/>
                </svg>
            </div>
            <h1>{{ __('Security Verification') }}</h1>
            <p>{{ __('To access your files, please enter the OTP code sent to your email address.') }}</p>
        </div>

        <div class="fmf-otp-card">

            <div class="fmf-otp-banner">
                <svg viewBox="0 0 24 24">
                    <rect x="2" y="4" width="14" height="10" rx="1.5"/>
                    <path d="M2 17h18"/>
                    <rect x="15.5" y="9" width="7" height="10" rx="1.5"/>
                </svg>
                <div>
                    <strong>{{ __('New connection detected') }}</strong>
                    <span>{{ __('Device/browser not recognized. For security reasons, we need to verify your identity.') }}</span>
                </div>
            </div>

            <div class="fmf-otp-email-box">
                <svg viewBox="0 0 24 24">
                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                    <path d="m2 6 10 7 10-7"/>
                </svg>
                <div>
                    <span>{{ __('We sent an OTP code to:') }}</span>
                    <strong id="fmf-otp-email">&hellip;</strong>
                </div>
            </div>

            <label class="fmf-otp-label">{{ __('Enter the OTP Code') }}</label>

            <div class="otp-digits" id="fmf-otp-digits">
                <input type="text" inputmode="numeric" maxlength="1" class="otp-digit" data-i="0">
                <input type="text" inputmode="numeric" maxlength="1" class="otp-digit" data-i="1">
                <input type="text" inputmode="numeric" maxlength="1" class="otp-digit" data-i="2">
                <input type="text" inputmode="numeric" maxlength="1" class="otp-digit" data-i="3">
                <input type="text" inputmode="numeric" maxlength="1" class="otp-digit" data-i="4">
                <input type="text" inputmode="numeric" maxlength="1" class="otp-digit" data-i="5">
            </div>

            <div class="fmf-otp-error" id="fmf-otp-error"></div>

            <div class="otp-timer" id="fmf-otp-timer">
                {{ __('Code expires in') }} <span class="otp-timer-badge" id="fmf-otp-countdown">10:00</span>
            </div>

            <button type="button" class="btn-fmf-otp-verify" id="fmf-otp-submit">
                <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                {{ __('Verify Code') }}
            </button>

            <div class="fmf-otp-divider">{{ __('or') }}</div>

            <button type="button" class="btn-fmf-otp-resend" id="fmf-otp-resend" disabled>
                <svg viewBox="0 0 24 24"><path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4 20-7z"/></svg>
                {{ __('Resend a new code') }}
            </button>

        </div>

        <div class="fmf-otp-why">
            <div class="fmf-otp-why-head">
                <svg viewBox="0 0 24 24"><path d="M12 2l7 4v6c0 4.5-3.1 8.7-7 10-3.9-1.3-7-5.5-7-10V6l7-4z"/><path d="m9 12 2 2 4-4"/></svg>
                <div>
                    <strong>{{ __('Why this verification?') }}</strong>
                    <span>{{ __('We use OTP verification to protect your files from unauthorized access.') }}</span>
                </div>
            </div>
            <div class="fmf-otp-why-row">
                <div class="fmf-otp-why-item">
                    <svg viewBox="0 0 24 24"><path d="M20 21a8 8 0 1 0-16 0"/><circle cx="12" cy="7" r="4"/></svg>
                    {{ __('Your data is secured') }}
                </div>
                <div class="fmf-otp-why-item">
                    <svg viewBox="0 0 24 24"><path d="M12 2l7 4v6c0 4.5-3.1 8.7-7 10-3.9-1.3-7-5.5-7-10V6l7-4z"/></svg>
                    {{ __('Authorized access only') }}
                </div>
                <div class="fmf-otp-why-item">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
                    {{ __('Code valid') }} {{ $otpTtlMinutes }} {{ __('minutes') }}
                </div>
            </div>
        </div>

    </div>
</section>

<script>
(function () {
    var rawToken   = @json($rawToken);
    var requestUrl = @json(route('find_my_files.device_otp_request'));
    var verifyUrl  = @json(route('find_my_files.device_otp_verify'));
    var downloadUrl = @json(route('find_my_files.download', ['t' => $rawToken]));

    var digits    = Array.prototype.slice.call(document.querySelectorAll('.otp-digit'));
    var errorEl   = document.getElementById('fmf-otp-error');
    var emailEl   = document.getElementById('fmf-otp-email');
    var submitBtn = document.getElementById('fmf-otp-submit');
    var resendBtn = document.getElementById('fmf-otp-resend');
    var timerEl   = document.getElementById('fmf-otp-timer');
    var countdownEl = document.getElementById('fmf-otp-countdown');

    var countdownInterval = null;

    function startCountdown(seconds) {
        clearInterval(countdownInterval);
        var remaining = seconds;
        function tick() {
            var m = Math.floor(remaining / 60);
            var s = remaining % 60;
            countdownEl.textContent = m + ':' + (s < 10 ? '0' : '') + s;
            timerEl.classList.toggle('urgent', remaining <= 60);
            if (remaining <= 0) {
                clearInterval(countdownInterval);
                resendBtn.disabled = false;
            }
            remaining--;
        }
        tick();
        countdownInterval = setInterval(tick, 1000);
    }

    function startResendCooldown(seconds) {
        resendBtn.disabled = true;
        var remaining = seconds;
        var label = resendBtn.innerHTML;
        var interval = setInterval(function () {
            remaining--;
            if (remaining <= 0) {
                clearInterval(interval);
                resendBtn.disabled = false;
            }
        }, 1000);
    }

    function requestOtp() {
        errorEl.textContent = '';
        fetch(requestUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': @json(csrf_token()) },
            body: 't=' + encodeURIComponent(rawToken),
        }).then(function (r) { return r.json(); }).then(function (data) {
            if (data.status === 'success') {
                emailEl.textContent = data.masked_email;
                startCountdown(600);
                startResendCooldown(60);
            } else if (data.type === 'rate_limited') {
                errorEl.textContent = @json(__('Too many attempts. Please try again later.'));
            } else if (data.type === 'device_limit') {
                errorEl.textContent = @json(__('This order has reached its limit of recognized devices. Please contact support.'));
            } else if (data.type === 'resend_cooldown') {
                startResendCooldown(data.seconds || 60);
            } else {
                errorEl.textContent = @json(__('Could not send the code. Please try again.'));
            }
        }).catch(function () {
            errorEl.textContent = @json(__('Network error. Please try again.'));
        });
    }

    digits.forEach(function (input, idx) {
        input.addEventListener('input', function () {
            input.value = input.value.replace(/\D/g, '').slice(0, 1);
            input.classList.toggle('filled', input.value !== '');
            if (input.value && digits[idx + 1]) digits[idx + 1].focus();
        });
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace' && !input.value && digits[idx - 1]) digits[idx - 1].focus();
        });
        input.addEventListener('paste', function (e) {
            var text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
            if (text.length >= 6) {
                e.preventDefault();
                for (var i = 0; i < 6; i++) {
                    digits[i].value = text[i] || '';
                    digits[i].classList.toggle('filled', digits[i].value !== '');
                }
                digits[5].focus();
            }
        });
    });

    submitBtn.addEventListener('click', function () {
        var code = digits.map(function (d) { return d.value; }).join('');
        if (code.length !== 6) {
            errorEl.textContent = @json(__('Enter all 6 digits.'));
            return;
        }
        errorEl.textContent = '';
        submitBtn.classList.add('disabled');

        fetch(verifyUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': @json(csrf_token()) },
            body: 't=' + encodeURIComponent(rawToken) + '&otp_code=' + encodeURIComponent(code),
        }).then(function (r) { return r.json(); }).then(function (data) {
            if (data.status === 'success') {
                window.location.href = downloadUrl;
                return;
            }
            submitBtn.classList.remove('disabled');
            if (data.type === 'invalid_code') {
                errorEl.textContent = @json(__('Incorrect code. Please try again.'));
            } else if (data.type === 'exhausted') {
                errorEl.textContent = @json(__('Too many incorrect attempts. Request a new code.'));
            } else if (data.type === 'expired') {
                errorEl.textContent = @json(__('This code has expired. Request a new one.'));
            } else {
                errorEl.textContent = @json(__('Could not verify. Please try again.'));
            }
        }).catch(function () {
            submitBtn.classList.remove('disabled');
            errorEl.textContent = @json(__('Network error. Please try again.'));
        });
    });

    resendBtn.addEventListener('click', requestOtp);

    requestOtp();
})();
</script>
@endsection
