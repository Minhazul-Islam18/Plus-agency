@extends("front.$version.layout")

@section('pagename')
    - {{ __('Server-side Verification') }}
@endsection

@section('breadcrumb-title', __('Secure File Recovery'))
@section('breadcrumb-subtitle', __('How your request is verified'))
@section('breadcrumb-link', __('Security Verification'))

@section('styles')
<style>
.fmf-sv-section {
    padding: 60px 0 80px;
    background: #f4f6f9;
    min-height: 65vh;
}

/* ── Page heading ── */
.fmf-sv-title {
    font-size: 34px;
    font-weight: 700;
    color: #1a2a4a;
    margin-bottom: 28px;
}

/* ── Subtitle label ── */
.fmf-sv-label {
    display: inline-block;
    color: #3b6cf8;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 18px;
    letter-spacing: .01em;
}

/* ── Card ── */
.fmf-sv-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 24px rgba(0,0,0,.08);
    padding: 8px 0;
}

/* ── Check items ── */
.fmf-sv-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 14px 28px;
    border-bottom: 1px solid #f3f4f6;
    font-size: 14px;
    color: #374151;
    line-height: 1.5;
}
.fmf-sv-item:last-child {
    border-bottom: none;
}

/* Green checkmark icon */
.fmf-sv-check {
    flex-shrink: 0;
    margin-top: 2px;
    color: #16a34a;
    font-size: 15px;
    font-weight: 700;
    width: 18px;
    text-align: center;
}

.fmf-sv-item-text {
    flex: 1;
}
.fmf-sv-item-text strong {
    font-weight: 600;
    color: #1a2a4a;
}

/* ── Back link ── */
.fmf-sv-back {
    margin-top: 24px;
    font-size: 14px;
}
.fmf-sv-back a {
    color: #3b6cf8;
    text-decoration: none;
    font-weight: 500;
}
.fmf-sv-back a:hover {
    text-decoration: underline;
}

/* ── Mobile ── */
@media (max-width: 575px) {
    .fmf-sv-section { padding: 30px 0 50px; }
    .fmf-sv-title   { font-size: 26px; }
    .fmf-sv-item    { padding: 13px 20px; }
}
</style>
@endsection

@section('content')
@if ($be->theme_version == 'dark')
    <section class="dark-sv-section" data-particle-network>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10">

                    <div class="dark-sv-hero">
                        <div class="dark-sv-badge">
                            <svg viewBox="0 0 24 24"><path d="M12 2l7 4v6c0 4.5-3.1 8.7-7 10-3.9-1.3-7-5.5-7-10V6l7-4z" /><path d="M9 12l2 2 4-4" /></svg>
                        </div>
                        <span class="dark-sv-eyebrow">{{ __('Secure File Recovery') }}</span>
                        <h1>{{ __('Server-side verification') }}</h1>
                        <p>{{ __('How your request for the secure download link was checked before we sent it.') }}</p>
                        <div class="dark-sv-stat"><b>6/6</b><span>{{ __('Checks Passed') }}</span></div>
                    </div>

                    <div class="dark-sv-ledger-label">{{ __('Verification Ledger') }}</div>
                    <div class="dark-sv-ledger">
                        <div class="dark-sv-row">
                            <span class="dark-sv-num">01</span>
                            <div class="dark-sv-body">
                                <div class="dark-sv-title">{{ __('CAPTCHA successfully verified') }}</div>
                            </div>
                            <span class="dark-sv-status">{{ __('Passed') }}</span>
                        </div>
                        <div class="dark-sv-row">
                            <span class="dark-sv-num">02</span>
                            <div class="dark-sv-body">
                                <div class="dark-sv-title">{{ __('Email format and order reference validated') }}</div>
                            </div>
                            <span class="dark-sv-status">{{ __('Passed') }}</span>
                        </div>
                        <div class="dark-sv-row">
                            <span class="dark-sv-num">03</span>
                            <div class="dark-sv-body">
                                <div class="dark-sv-title">{{ __('Attempt threshold respected') }}</div>
                            </div>
                            <span class="dark-sv-status">{{ __('Passed') }}</span>
                        </div>
                        <div class="dark-sv-row">
                            <span class="dark-sv-num">04</span>
                            <div class="dark-sv-body">
                                <div class="dark-sv-title">{{ __('Valid file access record found') }}</div>
                            </div>
                            <span class="dark-sv-status">{{ __('Passed') }}</span>
                        </div>
                        <div class="dark-sv-row">
                            <span class="dark-sv-num">05</span>
                            <div class="dark-sv-body">
                                <div class="dark-sv-title">{{ __('Payment status confirmed') }}</div>
                                <div class="dark-sv-detail">{{ __('paid') }} · {{ __('not refunded') }} · {{ __('not cancelled') }}</div>
                            </div>
                            <span class="dark-sv-status">{{ __('Passed') }}</span>
                        </div>
                        <div class="dark-sv-row">
                            <span class="dark-sv-num">06</span>
                            <div class="dark-sv-body">
                                <div class="dark-sv-title">{{ __('Rate-limit & abuse checks passed') }}</div>
                                <div class="dark-sv-detail">{{ __('per-IP and per-email request throttling') }}</div>
                            </div>
                            <span class="dark-sv-status">{{ __('Passed') }}</span>
                        </div>
                    </div>

                    <div class="dark-sv-back-wrap">
                        <a href="{{ route('find_my_files') }}" class="dark-sv-back">
                            <svg viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7" /></svg>{{ __('Back to Secure File Recovery') }}
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </section>
@else
    <section class="fmf-sv-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-7 col-md-9">

                    <h1 class="fmf-sv-title">{{ __('Server-side verification') }}</h1>

                    <div class="fmf-sv-card">

                        <span class="fmf-sv-label" style="padding:14px 28px 0; display:block;">
                            {{ __('Secure files access verification') }}
                        </span>

                        {{-- Item 1 --}}
                        <div class="fmf-sv-item">
                            <span class="fmf-sv-check">✓</span>
                            <div class="fmf-sv-item-text">
                                {{ __('CAPTCHA successfully verified') }}
                            </div>
                        </div>

                        {{-- Item 2 --}}
                        <div class="fmf-sv-item">
                            <span class="fmf-sv-check">✓</span>
                            <div class="fmf-sv-item-text">
                                {{ __('Email format and order reference validated') }}
                            </div>
                        </div>

                        {{-- Item 3 --}}
                        <div class="fmf-sv-item">
                            <span class="fmf-sv-check">✓</span>
                            <div class="fmf-sv-item-text">
                                {{ __('Attempt threshold respected') }}
                            </div>
                        </div>

                        {{-- Item 4 --}}
                        <div class="fmf-sv-item">
                            <span class="fmf-sv-check">✓</span>
                            <div class="fmf-sv-item-text">
                                {{ __('Valid file access record found') }}
                            </div>
                        </div>

                        {{-- Item 5 --}}
                        <div class="fmf-sv-item">
                            <span class="fmf-sv-check">✓</span>
                            <div class="fmf-sv-item-text">
                                {{ __('Payment status confirmed') }}
                                <span style="color:#6b7280; font-size:13px;">({{ __('paid, not refunded, not cancelled') }})</span>
                            </div>
                        </div>

                        {{-- Item 6 --}}
                        <div class="fmf-sv-item">
                            <span class="fmf-sv-check">✓</span>
                            <div class="fmf-sv-item-text">
                                {{ __('Rate-limit & abuse checks passed') }}
                                <span style="color:#6b7280; font-size:13px;">({{ __('per-IP and per-email request throttling') }})</span>
                            </div>
                        </div>

                    </div>
                    {{-- /card --}}

                    <div class="fmf-sv-back">
                        ← <a href="{{ route('find_my_files') }}">{{ __('Back to Secure File Recovery') }}</a>
                    </div>

                </div>
            </div>
        </div>
    </section>
@endif
@endsection
