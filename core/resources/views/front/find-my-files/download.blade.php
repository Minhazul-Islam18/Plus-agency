@extends("front.$version.layout")

@section('pagename')
    - {{ __('Order Confirmed') }}
@endsection

@section('breadcrumb-title', __('Secure File Recovery'))
@section('breadcrumb-subtitle', __('Your download is ready'))
@section('breadcrumb-link', __('Download'))

@section('styles')
<style>
.fmf-dl-section {
    padding: 60px 0 80px;
    background: #f4f6f9;
    min-height: 65vh;
}

/* ── Outer card ── */
.fmf-dl-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 24px rgba(0,0,0,.09);
    overflow: hidden;
}

/* ── Header ── */
.fmf-dl-header {
    padding: 28px 32px 22px;
    border-bottom: 1px solid #f0f2f5;
    display: flex;
    align-items: center;
    gap: 16px;
}
.fmf-dl-shield {
    flex-shrink: 0;
    width: 50px;
    height: 50px;
    background: #16a34a;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.fmf-dl-shield svg {
    width: 26px;
    height: 26px;
    fill: none;
    stroke: #fff;
    stroke-width: 2.5;
    stroke-linecap: round;
    stroke-linejoin: round;
}
.fmf-dl-header-text strong {
    display: block;
    font-size: 20px;
    font-weight: 700;
    color: #1a2a4a;
    line-height: 1.3;
}
.fmf-dl-header-text span {
    font-size: 13px;
    color: #6b7280;
}

/* ── Two panels ── */
.fmf-dl-panels {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0;
    border-bottom: 1px solid #f0f2f5;
}
.fmf-dl-panel {
    padding: 24px 28px;
}
.fmf-dl-panel:first-child {
    border-right: 1px solid #f0f2f5;
}
.fmf-dl-panel-title {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 16px;
}
.fmf-dl-panel-title svg {
    width: 20px;
    height: 20px;
    flex-shrink: 0;
}
.fmf-dl-panel-title span {
    font-size: 16px;
    font-weight: 700;
    color: #1a2a4a;
}

/* ── Panel items ── */
.fmf-dl-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
    font-size: 13px;
    color: #374151;
    line-height: 1.4;
}
.fmf-dl-item:last-child { margin-bottom: 0; }

.fmf-dl-item-left {
    display: flex;
    align-items: center;
    gap: 8px;
}
.fmf-dot {
    width: 9px;
    height: 9px;
    border-radius: 50%;
    background: #22c55e;
    flex-shrink: 0;
}
.fmf-val-ok    { color: #16a34a; font-weight: 600; }
.fmf-val-blue  { color: #3b6cf8; font-weight: 600; }
.fmf-val-green { color: #16a34a; font-weight: 600; }

/* Green tick on right */
.fmf-tick {
    width: 20px;
    height: 20px;
    background: #16a34a;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.fmf-tick svg {
    width: 11px;
    height: 11px;
    fill: none;
    stroke: #fff;
    stroke-width: 3;
    stroke-linecap: round;
    stroke-linejoin: round;
}

/* ── Download button ── */
.fmf-dl-btn-wrap {
    padding: 24px 28px;
    border-bottom: 1px solid #f0f2f5;
}
.btn-fmf-dl {
    display: block;
    width: 100%;
    padding: 15px 24px;
    background: #3b6cf8;
    color: #fff;
    font-size: 16px;
    font-weight: 700;
    text-align: center;
    border: none;
    border-radius: 7px;
    text-decoration: none;
    letter-spacing: .02em;
    transition: background .2s;
    cursor: pointer;
}
.btn-fmf-dl:hover {
    background: #2a58e0;
    color: #fff;
    text-decoration: none;
}
.fmf-dl-autohint {
    margin: 10px 0 0;
    font-size: 12.5px;
    color: #94a3b8;
    text-align: center;
}

/* ── Footer ── */
.fmf-dl-footer {
    padding: 20px 28px;
    display: flex;
    align-items: center;
    gap: 14px;
}
.fmf-dl-logo {
    width: 46px;
    height: 46px;
    background: #1a2a4a;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.fmf-dl-logo span {
    font-size: 11px;
    font-weight: 700;
    color: #fff;
    letter-spacing: .05em;
}
.fmf-dl-footer-right {}
.fmf-dl-socials {
    display: flex;
    gap: 6px;
    margin-bottom: 6px;
}
.fmf-dl-socials a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    color: #fff;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
}
.fmf-dl-footer-right p {
    margin: 0;
    font-size: 12px;
    color: #9ca3af;
    line-height: 1.4;
}

/* ── Mobile ── */
@media (max-width: 600px) {
    .fmf-dl-section { padding: 30px 0 50px; }
    .fmf-dl-panels  { grid-template-columns: 1fr; }
    .fmf-dl-panel:first-child { border-right: none; border-bottom: 1px solid #f0f2f5; }
    .fmf-dl-header  { padding: 22px 20px 18px; }
    .fmf-dl-panel   { padding: 20px; }
    .fmf-dl-btn-wrap { padding: 18px 20px; }
    .fmf-dl-footer  { padding: 18px 20px; }
    .fmf-dl-header-text strong { font-size: 17px; }
}
</style>
@endsection

@section('content')
<section class="fmf-dl-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7 col-md-9">

                <div class="fmf-dl-card">

                    {{-- ── Header ── --}}
                    <div class="fmf-dl-header">
                        <div class="fmf-dl-shield">
                            {{-- Shield + checkmark icon --}}
                            <svg viewBox="0 0 24 24">
                                <path d="M12 2l7 4v6c0 4.5-3.1 8.7-7 10-3.9-1.3-7-5.5-7-10V6l7-4z"/>
                                <polyline points="9 12 11 14 15 10"/>
                            </svg>
                        </div>
                        <div class="fmf-dl-header-text">
                            <strong>{{ __('Order Confirmed.') }}</strong>
                            <span>{{ __('Your secure link has been verified.') }}</span>
                        </div>
                    </div>

                    {{-- ── Two panels ── --}}
                    <div class="fmf-dl-panels">

                        {{-- Left: Validated Token --}}
                        <div class="fmf-dl-panel">
                            <div class="fmf-dl-panel-title">
                                {{-- Lock icon --}}
                                <svg viewBox="0 0 24 24" style="stroke:#1a2a4a; fill:none; stroke-width:2; stroke-linecap:round; stroke-linejoin:round;">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                                <span>{{ __('Validated Token') }}</span>
                            </div>

                            <div class="fmf-dl-item">
                                <div class="fmf-dl-item-left">
                                    <span class="fmf-dot"></span>
                                    <span>{{ __('Cryptographic signature:') }}</span>
                                </div>
                                <span class="fmf-val-ok">{{ __('OK') }}</span>
                            </div>

                            <div class="fmf-dl-item">
                                <div class="fmf-dl-item-left">
                                    <span class="fmf-dot"></span>
                                    <span>{{ __('Expiration:') }}</span>
                                </div>
                                <span class="fmf-val-blue">{{ __('24 hours') }}</span>
                            </div>

                            <div class="fmf-dl-item">
                                <div class="fmf-dl-item-left">
                                    <span class="fmf-dot"></span>
                                    <span>{{ __('Remaining downloads:') }}</span>
                                </div>
                                <span class="fmf-val-blue">
                                    <span id="fmf-remaining">{{ $token->max_downloads - $token->download_count }}</span> / {{ $token->max_downloads }}
                                </span>
                            </div>
                        </div>

                        {{-- Right: Security Analysis --}}
                        <div class="fmf-dl-panel">
                            <div class="fmf-dl-panel-title">
                                {{-- Shield icon --}}
                                <svg viewBox="0 0 24 24" style="stroke:#1a2a4a; fill:none; stroke-width:2; stroke-linecap:round; stroke-linejoin:round;">
                                    <path d="M12 2l7 4v6c0 4.5-3.1 8.7-7 10-3.9-1.3-7-5.5-7-10V6l7-4z"/>
                                </svg>
                                <span>{{ __('Security Analysis') }}</span>
                            </div>

                            <div class="fmf-dl-item">
                                <div class="fmf-dl-item-left">
                                    <span class="fmf-dot"></span>
                                    <span>{{ __('IP address verified') }}</span>
                                </div>
                                <div class="fmf-tick">
                                    <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                                </div>
                            </div>

                            <div class="fmf-dl-item">
                                <div class="fmf-dl-item-left">
                                    <span class="fmf-dot"></span>
                                    <span>{{ __('Device fingerprint validated') }}</span>
                                </div>
                                <div class="fmf-tick">
                                    <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                                </div>
                            </div>

                            <div class="fmf-dl-item">
                                <div class="fmf-dl-item-left">
                                    <span class="fmf-dot"></span>
                                    <span>{{ __('Session locked to this browser') }}</span>
                                </div>
                                <div class="fmf-tick">
                                    <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                                </div>
                            </div>
                        </div>

                    </div>
                    {{-- /panels --}}

                    {{-- ── Download button ── --}}
                    <div class="fmf-dl-btn-wrap">
                        <a href="{{ $streamUrl }}" class="btn-fmf-dl" id="fmf-dl-btn">
                            {{ __('Download the Secure Files') }}
                        </a>
                        <p class="fmf-dl-autohint" id="fmf-dl-autohint">{{ __('Your download is starting automatically…') }}</p>
                    </div>

                    {{-- Hidden frame that auto-starts the download on page load --}}
                    <iframe id="fmf-dl-frame" title="download" style="display:none; width:0; height:0; border:0;"></iframe>
                    <script>
                        (function () {
                            var url    = @json($streamUrl);
                            var frame  = document.getElementById('fmf-dl-frame');
                            var hint   = document.getElementById('fmf-dl-autohint');
                            var remEl  = document.getElementById('fmf-remaining');
                            var btn    = document.getElementById('fmf-dl-btn');

                            // Keep the on-screen "remaining" in sync with each actual
                            // download the user triggers (each stream = one count on the
                            // server). Never drops below 0.
                            function spendOne() {
                                if (!remEl) return;
                                var n = parseInt(remEl.textContent, 10);
                                if (!isNaN(n) && n > 0) remEl.textContent = (n - 1);
                            }

                            // Auto-start the download once, only on a genuine page load
                            // (not on bfcache back/forward restores), so a single redirect
                            // from the email = one download.
                            function autoStart() {
                                if (window.__fmfAutoStarted) return;
                                window.__fmfAutoStarted = true;
                                if (frame) frame.src = url;
                                spendOne();
                                if (hint) {
                                    setTimeout(function () {
                                        hint.textContent = @json(__('If your download did not start, use the button above.'));
                                    }, 2500);
                                }
                            }

                            // Manual re-download also spends one.
                            if (btn) btn.addEventListener('click', function () { spendOne(); });

                            if (document.readyState === 'complete') {
                                setTimeout(autoStart, 400);
                            } else {
                                window.addEventListener('load', function () { setTimeout(autoStart, 400); });
                            }
                        })();
                    </script>

                    {{-- ── Footer ── --}}
                    <div class="fmf-dl-footer">
                        <div class="fmf-dl-logo"><span>ICA</span></div>
                        <div class="fmf-dl-footer-right">
                            <div class="fmf-dl-socials">
                                <a href="#" style="background:#1877f2;" title="Facebook">f</a>
                                <a href="#" style="background:#1da1f2;" title="Twitter">𝕏</a>
                                <a href="#" style="background:#25d366;" title="WhatsApp">W</a>
                            </div>
                            <p>{{ __('This is an automated email. Please do not reply to this message.') }}</p>
                        </div>
                    </div>

                </div>
                {{-- /card --}}

            </div>
        </div>
    </div>
</section>
@endsection
