@extends("front.$version.layout")

@section('pagename')
    - {{ __('Request Received') }}
@endsection

@section('breadcrumb-title', __('Secure File Recovery'))
@section('breadcrumb-subtitle', __('Recover your purchased tender documents securely'))
@section('breadcrumb-link', __('Find My Files'))

@section('styles')
<style>
.fmf-sent-section {
    padding: 60px 0 80px;
    background: #f4f6f9;
    min-height: 65vh;
}

/* ── Green heading ── */
.fmf-sent-title {
    font-size: 32px;
    font-weight: 700;
    color: #16a34a;
    margin-bottom: 28px;
}

/* ── Main card ── */
.fmf-sent-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 24px rgba(0,0,0,.09);
    padding: 36px 40px 32px;
}

/* ── Checkmark row ── */
.fmf-check-row {
    display: flex;
    align-items: flex-start;
    gap: 20px;
    margin-bottom: 24px;
}

.fmf-check-icon {
    flex-shrink: 0;
    width: 52px;
    height: 52px;
    background: #16a34a;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.fmf-check-icon svg {
    width: 26px;
    height: 26px;
    fill: none;
    stroke: #fff;
    stroke-width: 3;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.fmf-check-text {
    padding-top: 4px;
}

.fmf-check-text strong {
    display: block;
    font-size: 16px;
    font-weight: 700;
    color: #1a2a4a;
    line-height: 1.4;
    margin-bottom: 2px;
}

.fmf-check-text span {
    font-size: 15px;
    color: #4b5563;
}

/* ── Bullet list ── */
.fmf-info-list {
    list-style: none;
    padding: 0;
    margin: 0 0 28px;
}

.fmf-info-list li {
    position: relative;
    padding-left: 18px;
    margin-bottom: 8px;
    font-size: 14px;
    color: #374151;
    line-height: 1.5;
}

.fmf-info-list li::before {
    content: '●';
    position: absolute;
    left: 0;
    color: #1a2a4a;
    font-size: 8px;
    top: 5px;
}

/* ── Action buttons ── */
.fmf-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.btn-fmf-outline {
    padding: 11px 22px;
    border: 1px solid #9ca3af;
    background: transparent;
    color: #374151;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    transition: border-color .2s, color .2s;
    display: inline-block;
}

.btn-fmf-outline:hover {
    border-color: #374151;
    color: #111827;
    text-decoration: none;
}

.btn-fmf-green {
    padding: 11px 22px;
    background: #16a34a;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: background .2s;
    display: inline-block;
}

.btn-fmf-green:hover {
    background: #15803d;
    color: #fff;
    text-decoration: none;
}

/* ── Mobile ── */
@media (max-width: 575px) {
    .fmf-sent-section {
        padding: 30px 0 50px;
    }
    .fmf-sent-card {
        padding: 24px 20px 22px;
    }
    .fmf-sent-title {
        font-size: 24px;
    }
    .fmf-check-icon {
        width: 42px;
        height: 42px;
    }
    .fmf-check-icon svg {
        width: 20px;
        height: 20px;
    }
    .fmf-check-text strong {
        font-size: 15px;
    }
    .fmf-check-text span {
        font-size: 14px;
    }
}
</style>
@endsection

@section('content')
<section class="fmf-sent-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7 col-md-9">

                <h1 class="fmf-sent-title">{{ __('Request Received') }}</h1>

                <div class="fmf-sent-card">

                    {{-- Checkmark + neutral message --}}
                    <div class="fmf-check-row">
                        <div class="fmf-check-icon">
                            <svg viewBox="0 0 24 24">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                        </div>
                        <div class="fmf-check-text">
                            <strong>{{ __('Your request has been processed.') }}</strong>
                            <span>{{ __('If a paid order matching your details exists, a secure download link has been sent to your email address.') }}</span>
                        </div>
                    </div>

                    {{-- Info bullets --}}
                    <ul class="fmf-info-list">
                        <li>{{ __('Please check your inbox (and spam folder).') }}</li>
                        <li>{{ __('The link is valid for 24 hours.') }}</li>
                        <li>{{ __('Downloads are limited to 3 attempts.') }}</li>
                        <li>{{ __('You may submit a new request if no email arrives.') }}</li>
                    </ul>

                    {{-- Action buttons --}}
                    <div class="fmf-actions">
                        <a href="{{ route('find_my_files') }}" class="btn-fmf-outline">
                            {{ __('Submit Another Request') }}
                        </a>
                        <a href="{{ route('front.index') }}" class="btn-fmf-green">
                            {{ __('Return to Home') }}
                        </a>
                    </div>

                </div>
                {{-- /card --}}

            </div>
        </div>
    </div>
</section>
@endsection
