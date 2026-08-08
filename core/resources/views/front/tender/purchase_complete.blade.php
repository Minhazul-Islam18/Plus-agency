@extends("front.$version.layout")

@section('pagename')
    - {{ __('Purchase Submitted') }}
@endsection

@section('breadcrumb-title', __('Tender Purchase'))
@section('breadcrumb-subtitle', __('Request submitted successfully'))
@section('breadcrumb-link', __('Purchase Complete'))

@section('styles')
    <style>
        .pc-section {
            padding: 60px 0 80px;
            background: #f4f6f9;
            min-height: 70vh;
        }

        /* ── Success badge ── */
        .pc-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: #dcfce7;
            border: 1px solid #86efac;
            border-radius: 999px;
            padding: 5px 14px;
            font-size: 13px;
            font-weight: 600;
            color: #15803d;
            margin-bottom: 18px;
        }

        .pc-badge-dot {
            width: 8px;
            height: 8px;
            background: #16a34a;
            border-radius: 50%;
            flex-shrink: 0;
        }

        /* ── Heading ── */
        .pc-heading {
            font-size: 30px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 6px;
            line-height: 1.2;
        }

        .pc-subheading {
            font-size: 15px;
            color: #64748b;
            margin-bottom: 32px;
        }

        /* ── Card ── */
        .pc-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, .08);
            overflow: hidden;
        }

        /* ── Card header ── */
        .pc-card-header {
            background: linear-gradient(135deg, #1a2a4a 0%, #2d4a7a 100%);
            padding: 24px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .pc-card-header-left strong {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #94a3b8;
            letter-spacing: .06em;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .pc-card-header-left span {
            font-size: 22px;
            font-weight: 700;
            color: #fff;
            letter-spacing: .02em;
        }

        .pc-card-header-right {
            flex-shrink: 0;
            width: 46px;
            height: 46px;
            background: rgba(255, 255, 255, .12);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pc-card-header-right svg {
            width: 22px;
            height: 22px;
            stroke: #fff;
            fill: none;
            stroke-width: 2.5;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        /* ── Two-column grid ── */
        .pc-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
        }

        .pc-col {
            padding: 28px 32px;
        }

        .pc-col:first-child {
            border-right: 1px solid #f1f5f9;
        }

        .pc-col-title {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .07em;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 18px;
        }

        /* ── Row items ── */
        .pc-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid #f8fafc;
            font-size: 13px;
        }

        .pc-row:last-child {
            border-bottom: none;
        }

        .pc-row-label {
            color: #64748b;
            flex-shrink: 0;
        }

        .pc-row-value {
            color: #0f172a;
            font-weight: 600;
            text-align: right;
            word-break: break-word;
        }

        /* Status pill */
        .pc-status {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
        }

        .pc-status-pending {
            background: #fef9c3;
            color: #854d0e;
        }

        .pc-status-completed {
            background: #dcfce7;
            color: #15803d;
        }

        /* ── Notice bar ── */
        .pc-notice {
            margin: 0 32px 28px;
            background: #fffbeb;
            border-left: 4px solid #f59e0b;
            border-radius: 0 6px 6px 0;
            padding: 14px 18px;
            font-size: 13px;
            color: #78350f;
            line-height: 1.6;
        }

        /* ── Action buttons ── */
        .pc-actions {
            padding: 24px 32px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-pc-primary {
            padding: 11px 24px;
            background: #2563eb;
            color: #fff;
            border-radius: 7px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: background .2s;
        }

        .btn-pc-primary:hover {
            background: #1d4ed8;
            color: #fff;
            text-decoration: none;
        }

        .btn-pc-outline {
            padding: 11px 24px;
            border: 1px solid #cbd5e1;
            color: #475569;
            border-radius: 7px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            transition: border-color .2s, color .2s;
        }

        .btn-pc-outline:hover {
            border-color: #94a3b8;
            color: #1e293b;
            text-decoration: none;
        }

        /* ── Find My Files callout ── */
        .pc-fmf-box {
            margin-top: 24px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 10px;
            padding: 20px 24px;
            display: flex;
            align-items: flex-start;
            gap: 16px;
        }

        .pc-fmf-icon {
            flex-shrink: 0;
            width: 40px;
            height: 40px;
            background: #2563eb;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pc-fmf-icon svg {
            width: 20px;
            height: 20px;
            stroke: #fff;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .pc-fmf-text p {
            margin: 0 0 4px;
            font-size: 14px;
            font-weight: 700;
            color: #1e40af;
        }

        .pc-fmf-text span {
            font-size: 13px;
            color: #3b82f6;
        }

        .pc-fmf-text a {
            color: #2563eb;
            font-weight: 600;
            text-decoration: underline;
        }

        /* ── Mobile ── */
        @media (max-width: 600px) {
            .pc-section {
                padding: 30px 0 50px;
            }

            .pc-heading {
                font-size: 24px;
            }

            .pc-grid {
                grid-template-columns: 1fr;
            }

            .pc-col:first-child {
                border-right: none;
                border-bottom: 1px solid #f1f5f9;
            }

            .pc-col {
                padding: 22px 20px;
            }

            .pc-card-header {
                padding: 20px;
                flex-direction: column;
                align-items: flex-start;
            }

            .pc-actions {
                padding: 18px 20px;
            }

            .pc-notice {
                margin: 0 20px 22px;
            }

            .pc-fmf-box {
                flex-direction: column;
            }
        }
    </style>
@endsection

@section('content')
    <section class="pc-section @if ($be->theme_version == 'dark') dark-pc @endif"
        @if ($be->theme_version == 'dark') data-particle-network @endif>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10">

                    {{-- Success badge + heading --}}
                    <div class="pc-badge">
                        <span class="pc-badge-dot"></span>
                        {{ __('Request Submitted') }}
                    </div>
                    <h1 class="pc-heading">{{ __('Thank you for your purchase!') }}</h1>
                    <p class="pc-subheading">
                        {{ __('Your tender purchase request has been received. Our team will review it and update you shortly.') }}
                    </p>

                    @if ($purchase)
                        <div class="pc-card">

                            {{-- Card header: Order number --}}
                            <div class="pc-card-header">
                                <div class="pc-card-header-left">
                                    <strong>{{ __('Order Number') }}</strong>
                                    <span>#{{ $purchase->order_number }}</span>
                                </div>
                                <div class="pc-card-header-right">
                                    {{-- Receipt icon --}}
                                    <svg viewBox="0 0 24 24">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                        <polyline points="14 2 14 8 20 8" />
                                        <line x1="16" y1="13" x2="8" y2="13" />
                                        <line x1="16" y1="17" x2="8" y2="17" />
                                        <polyline points="10 9 9 9 8 9" />
                                    </svg>
                                </div>
                            </div>

                            {{-- Two column grid --}}
                            <div class="pc-grid">

                                {{-- Left: Buyer info --}}
                                <div class="pc-col">
                                    <div class="pc-col-title">{{ __('Your Information') }}</div>

                                    <div class="pc-row">
                                        <span class="pc-row-label">{{ __('Full Name') }}</span>
                                        <span class="pc-row-value">{{ $purchase->first_name }}
                                            {{ $purchase->last_name }}</span>
                                    </div>
                                    <div class="pc-row">
                                        <span class="pc-row-label">{{ __('Email') }}</span>
                                        <span class="pc-row-value">{{ $purchase->email }}</span>
                                    </div>
                                    <div class="pc-row">
                                        <span class="pc-row-label">{{ __('Phone') }}</span>
                                        <span class="pc-row-value">{{ $purchase->phone_number }}</span>
                                    </div>
                                    <div class="pc-row">
                                        <span class="pc-row-label">{{ __('Country') }}</span>
                                        <span
                                            class="pc-row-value">{{ $purchase->country }}{{ $purchase->city ? ', ' . $purchase->city : '' }}</span>
                                    </div>
                                </div>

                                {{-- Right: Order info --}}
                                <div class="pc-col">
                                    <div class="pc-col-title">{{ __('Order Details') }}</div>

                                    @if ($purchase->tender)
                                        <div class="pc-row">
                                            <span class="pc-row-label">{{ __('Tender') }}</span>
                                            <span class="pc-row-value">{{ $purchase->tender->title }}</span>
                                        </div>
                                    @endif
                                    <div class="pc-row">
                                        <span class="pc-row-label">{{ __('Payment Method') }}</span>
                                        <span class="pc-row-value">{{ $purchase->payment_method }}</span>
                                    </div>
                                    <div class="pc-row">
                                        <span class="pc-row-label">{{ __('Total Price') }}</span>
                                        <span class="pc-row-value">
                                            @php
                                                $price = collect(
                                                    json_decode($purchase->purchased_modules, true) ?: [],
                                                )->sum('cost');
                                                $sym = $bse->base_currency_symbol ?? '';
                                                $pos = $bse->base_currency_symbol_position ?? 'left';
                                                $code = $purchase->currency_code ?? '';
                                            @endphp
                                            @if ($price)
                                                @if ($pos === 'left')
                                                    {{ $sym }}
                                                    @endif{{ number_format($price, 2) }}@if ($pos === 'right')
                                                        {{ $sym }}
                                                    @endif
                                                    <span style="font-size:12px; color:#64748b; font-weight:500;">
                                                        {{ $code }}</span>
                                                @else
                                                    {{ __('Free') }}
                                                @endif
                                        </span>
                                    </div>
                                    <div class="pc-row">
                                        <span class="pc-row-label">{{ __('Status') }}</span>
                                        <span class="pc-row-value">
                                            <span
                                                class="pc-status {{ $purchase->payment_status === 'Completed' ? 'pc-status-completed' : 'pc-status-pending' }}">
                                                {{ __($purchase->payment_status) }}
                                            </span>
                                        </span>
                                    </div>
                                    <div class="pc-row">
                                        <span class="pc-row-label">{{ __('Submitted') }}</span>
                                        <span class="pc-row-value">{{ $purchase->created_at->format('d M Y, H:i') }}</span>
                                    </div>
                                </div>

                            </div>
                            {{-- /grid --}}

                            {{-- Notice --}}
                            @if (!empty($downloadUrl))
                                <div class="pc-notice"
                                    style="background:#ecfdf5; border-left-color:#16a34a; color:#065f46;">
                                    <strong>{{ __('Payment confirmed!') }}</strong>
                                    {{ __('Your download is starting automatically. We have also emailed your secure download link and payment receipt to') }}
                                    <strong>{{ $purchase->email }}</strong>.
                                </div>
                            @else
                                <div class="pc-notice">
                                    <strong>{{ __('What happens next?') }}</strong>
                                    {{ __('Our team will verify your payment and activate your order. You will receive a secure download link via email once approved.') }}
                                </div>
                            @endif

                            {{-- Actions --}}
                            <div class="pc-actions">
                                @if (!empty($streamUrl))
                                    <a href="{{ $streamUrl }}" id="pc-download-btn"
                                        class="btn-pc-primary">{{ __('Download Now') }}</a>
                                @endif
                                <a href="{{ route('tenders') }}"
                                    class="btn-pc-outline">{{ __('Browse More Tenders') }}</a>
                                <a href="{{ route('front.index') }}" class="btn-pc-outline">{{ __('Return to Home') }}</a>
                            </div>

                        </div>
                        {{-- /card --}}

                        {{-- Find My Files callout --}}
                        <div class="pc-fmf-box">
                            <div class="pc-fmf-icon">
                                <svg viewBox="0 0 24 24">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                    <polyline points="7 10 12 15 17 10" />
                                    <line x1="12" y1="15" x2="12" y2="3" />
                                </svg>
                            </div>
                            <div class="pc-fmf-text">
                                <p>{{ __('Need to re-download your files later?') }}</p>
                                <span>
                                    {{ __('Use the') }}
                                    <a href="{{ route('find_my_files') }}">{{ __('Secure File Recovery') }}</a>
                                    {{ __('tool with your email and order number') }}
                                    <strong>#{{ $purchase->order_number }}</strong>
                                    {{ __('to get a new download link anytime.') }}
                                </span>
                            </div>
                        </div>
                    @elseif ($be->theme_version == 'dark')
                        {{-- Fallback if session expired (dark) --}}
                        <div class="pc-card dark-pc-fallback">
                            <div class="dark-pc-fallback-icon">
                                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke-width="2.5"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                            </div>
                            <h3 class="dark-pc-fallback-title">{{ __('Request Submitted Successfully!') }}</h3>
                            <p class="dark-pc-fallback-text">
                                {{ __('Our team will review your request and contact you shortly.') }}</p>
                            <a href="{{ route('tenders') }}" class="btn-pc-primary">{{ __('Browse More Tenders') }}</a>
                        </div>
                    @else
                        {{-- Fallback if session expired --}}
                        <div class="pc-card" style="padding: 48px 36px; text-align: center;">
                            <div
                                style="width:56px; height:56px; background:#dcfce7; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
                                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#16a34a"
                                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                            </div>
                            <h3 style="font-size:20px; font-weight:700; color:#0f172a; margin-bottom:10px;">
                                {{ __('Request Submitted Successfully!') }}</h3>
                            <p style="font-size:14px; color:#64748b; margin-bottom:28px;">
                                {{ __('Our team will review your request and contact you shortly.') }}</p>
                            <a href="{{ route('tenders') }}" class="btn-pc-primary">{{ __('Browse More Tenders') }}</a>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </section>

    @if (!empty($streamUrl))
        {{-- Automatic download trigger: hidden iframe fetches the ZIP without leaving this page --}}
        <iframe id="pc-auto-download" src="about:blank" style="display:none;width:0;height:0;border:0;"></iframe>
        <script>
            (function() {
                var streamUrl = @json($streamUrl);
                // Kick off the download shortly after the page paints
                setTimeout(function() {
                    var f = document.getElementById('pc-auto-download');
                    if (f) {
                        f.src = streamUrl;
                    }
                }, 1200);
            })();
        </script>
    @endif
@endsection
