<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Payment Receipt {{ $order->order_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #2d3748;
            background: #ffffff;
            margin: 0;
            padding: 0;
        }

        .content-wrap {
            padding: 0 22px;
        }

        /* ── Header (dark navy full-width banner) ────────────────────── */
        .header-outer {
            width: 100%;
            border-collapse: collapse;
        }

        .header-outer td {
            background-color: #1a2f45;
            padding: 12px 22px 10px 22px;
        }

        .header-inner {
            width: 100%;
            border-collapse: collapse;
        }

        .logo-cell {
            vertical-align: middle;
            width: 40%;
        }

        .logo-cell img {
            max-height: 50px;
            max-width: 110px;
        }

        .logo-cell .site-name {
            font-size: 20px;
            font-weight: 700;
            color: #ffffff;
        }

        .title-cell {
            vertical-align: middle;
            text-align: right;
        }

        .receipt-title {
            font-size: 20px;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: 0.07em;
            text-transform: uppercase;
        }

        .receipt-order-num {
            font-size: 13px;
            font-weight: 700;
            color: #38bdf8;
            letter-spacing: 0.04em;
            margin-top: 3px;
        }

        /* ── Divider ─────────────────────────────────────────────────── */
        .hdivider {
            border: none;
            border-top: 1px solid #d1d5db;
            margin: 6px 0;
        }

        /* ── Status bar ──────────────────────────────────────────────── */
        .status-table {
            width: 100%;
            margin-bottom: 8px;
            background-color: #16a34a6b;
            padding: 4px 22px;
        }

        .paid-badge {
            display: inline-block;
            background: #16a34a;
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 18px;
            border-radius: 4px;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .issued-label {
            font-size: 11px;
            color: #374151;
            font-weight: 600;
            text-align: right;
        }

        /* ── Info section ────────────────────────────────────────────── */
        .info-table {
            width: 100%;
            margin-bottom: 10px;
        }

        .section-header {
            font-size: 9px;
            font-weight: 700;
            color: #1a2f45;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            padding-bottom: 3px;
            border-bottom: 1.5px solid #1a2f45;
            margin-bottom: 6px;
        }

        .section-header-right {
            font-size: 9px;
            font-weight: 700;
            color: #1a2f45;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            padding-bottom: 3px;
            border-bottom: 1.5px solid #1a2f45;
            margin-bottom: 6px;
            text-align: right;
        }

        .billing-name {
            font-size: 12px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 2px;
        }

        .billing-company {
            font-size: 12px;
            font-weight: 700;
            color: #111827;
            margin-top: 2px;
            margin-bottom: 2px;
        }

        .billing-line {
            font-size: 11px;
            color: #4b5563;
            line-height: 1.85;
        }

        .order-detail-table {
            width: 100%;
            margin-top: 8px;
            table-layout: fixed;
        }

        .od-label {
            font-size: 10px;
            color: #6b7280;
            padding-bottom: 3px;
            vertical-align: top;
            padding-right: 8px;
            width: 110px;
        }

        .od-value {
            font-size: 10px;
            font-weight: 700;
            color: #111827;
            text-align: right;
            padding-bottom: 3px;
            vertical-align: top;
            word-break: break-word;
        }

        /* ── Tender title bar (full-width table — negative margins fail in dompdf) */
        .tender-bar-outer {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }

        .tender-bar-outer td {
            background-color: #f3f4f6;
            padding: 6px 22px;
        }

        .tender-bar-title {
            font-size: 12px;
            font-weight: 700;
            color: #d97706;
        }

        .tender-summary {
            font-size: 11px;
            color: #374151;
            line-height: 1.75;
            margin-bottom: 5px;
        }

        .tender-code {
            font-size: 11px;
            font-weight: 700;
            color: #111827;
        }

        /* ── Fees section (with watermark) ──────────────────────────── */
        .fees-outer {
            position: relative;
            margin-top: 6px;
        }

        .fees-watermark {
            position: fixed;
            top: 30%;
            left: 37.5%;
            right: 37.5%;
            width: 25%;
            height: auto;
            opacity: 0.07;
            z-index: 0;
        }

        .fees-table {
            width: 100%;
            border-collapse: collapse;
        }

        .fees-table td {
            padding: 5px 14px;
        }

        .fee-row td {
            border-bottom: 1px solid #e5e7eb;
        }

        .fee-label {
            font-size: 11px;
            color: #374151;
        }

        .fee-value {
            font-size: 11px;
            color: #374151;
            text-align: right;
        }

        .fee-deadline-value {
            font-size: 11px;
            color: #dc2626;
            font-weight: 600;
            text-align: right;
        }

        .subtotal-label {
            font-size: 11px;
            font-weight: 700;
            color: #111827;
        }

        .subtotal-value {
            font-size: 11px;
            font-weight: 700;
            color: #111827;
            text-align: right;
        }

        .vat-label {
            font-size: 11px;
            color: #6b7280;
        }

        .vat-value {
            font-size: 11px;
            color: #6b7280;
            text-align: right;
        }

        /* ── Total row ───────────────────────────────────────────────── */
        .total-table {
            width: 100%;
            border-collapse: collapse;
        }

        .total-row td {
            background: #1a2f45;
            color: #ffffff;
            font-size: 12px;
            font-weight: 700;
            padding: 7px 14px;
        }

        .total-row-value {
            text-align: right;
        }

        /* ── Terms ───────────────────────────────────────────────────── */
        .terms {
            font-size: 9px;
            font-style: italic;
            font-weight: 600;
            color: #1a2f45;
            margin-top: 8px;
            line-height: 1.5;
        }

        /* ── Stamp area ──────────────────────────────────────────────── */
        .stamp-area {
            margin-top: 8px;
            text-align: right;
        }

        .stamp-img {
            max-width: 140px;
            max-height: 140px;
            width: auto;
            height: auto;
        }

        /* ── Page number ─────────────────────────────────────────────── */
        .pagenum-table {
            border-collapse: collapse;
            margin-top: 8px;
            width: 24px;
        }

        .pagenum {
            background: #1a2f45;
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            width: 24px;
            height: 24px;
            border-radius: 12px;
            text-align: center;
            vertical-align: middle;
            padding: 0;
        }

        /* ── Footer wavy bg div ─────────────────────────────────────── */
        .footer-wave-div {
            margin-top: 10px;
            padding: 22px 28px 19px 28px;
            background-size: 100% 100%;
            background-repeat: no-repeat;
        }

        .footer-text {
            font-size: 9px;
            color: #ffffff;
            line-height: 1.85;
            text-align: center;
            vertical-align: middle;
        }

        .footer-logo-cell {
            width: 60px;
            vertical-align: middle;
            text-align: right;
        }

        .footer-logo-cell img {
            max-height: 44px;
            max-width: 56px;
        }

        .footer-spacer-cell {
            width: 60px;
            vertical-align: middle;
        }
    </style>
</head>

<body>

    @php
        $tender = $order->tender;
        $tenderTitle = optional($tender)->title ?: 'Tender Document';
        $tenderCode = optional($tender)->tender_code ?: '';
        $tenderSummary = optional($tender)->summary ?: '';
        $deadline = optional($tender)->submission_deadline;

        $currency = $order->currency_code ?? '';
        $isCompleted = strtolower($order->payment_status) === 'completed';
        $siteTitle = optional($bse)->website_title ?? config('app.name');

        // Currency symbol & position
        $sym = optional($bse)->base_currency_symbol ?: $currency;
        $symPos = optional($bse)->base_currency_symbol_position ?? 'right';
        $symL = $symPos === 'left' ? $sym . ' ' : '';
        $symR = $symPos === 'right' ? ' ' . $sym : '';

        $fmt = function ($n) use ($symL, $symR) {
            return $symL . number_format((float) $n, 0, ',', ' ') . $symR;
        };

        // Purchased modules from JSON; fall back to all tender modules
        $purchasedModules = [];
        if (!empty($order->purchased_modules)) {
            $purchasedModules = json_decode($order->purchased_modules, true) ?: [];
        }
        if (empty($purchasedModules) && $tender) {
            foreach (\App\TenderModule::where('tender_id', $tender->id)->get() as $m) {
                $purchasedModules[] = ['name' => $m->name, 'cost' => (float) $m->cost];
            }
        }
        $subtotal = array_sum(array_column($purchasedModules, 'cost'));

        $_invoiceDir = base_path('../assets/admin/img/invoice/');
        $_loadImg = function ($field, $default) use ($bse, $_invoiceDir) {
            $paths = [];
            $val = optional($bse)->$field ?? null;
            if ($val) {
                $paths[] = $_invoiceDir . $val;
            }
            $paths[] = base_path('../assets/admin/img/defaults/' . $default);
            foreach ($paths as $_p) {
                if (file_exists($_p)) {
                    $_ext = strtolower(pathinfo($_p, PATHINFO_EXTENSION));
                    $_mime = $_ext === 'png' ? 'image/png' : 'image/jpeg';
                    return 'data:' . $_mime . ';base64,' . base64_encode(file_get_contents($_p));
                }
            }
            return null;
        };

        $watermarkSrc = $_loadImg('invoice_watermark', 'invoice-watermark.png');
        $signSrc = $_loadImg('invoice_sign', 'invoice-sign.png');
        $wavySrc = $_loadImg('invoice_footer_wavy', 'footer-wavy.png');
    @endphp

    {{-- ── HEADER (dark navy banner — table bg for dompdf compat) ── --}}
    <table class="header-outer" cellpadding="0" cellspacing="0">
        <tr>
            <td class="header-bg-td">
                <table class="header-inner" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="logo-cell" style="padding: 20px 28px 18px 0;">
                            @if (!empty($logoSrc))
                                <img src="{{ $logoSrc }}" alt="{{ $siteTitle }}">
                            @else
                                <span class="site-name">{{ $siteTitle }}</span>
                            @endif
                        </td>
                        <td class="title-cell" style="padding: 20px 0 18px 28px;">
                            <div class="receipt-title">Payment Receipt</div>
                            <div class="receipt-order-num"># {{ $order->order_number }}</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- ── STATUS BAR ───────────────────────────────────────────────── --}}
    <table class="status-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="vertical-align:middle;">
                <span class="paid-badge">{{ $isCompleted ? 'Paid' : 'Pending' }}</span>
            </td>
            <td class="issued-label" style="vertical-align:middle;">
                Issued : {{ $order->created_at->format('d M Y') }}
            </td>
        </tr>
    </table>

    <div class="content-wrap">
        {{-- ── BILLING + ORDER DETAILS ─────────────────────────────────── --}}
        <table class="info-table" cellpadding="0" cellspacing="0">
            <tr>
                {{-- Left: billed to --}}
                <td style="width:50%; vertical-align:top; padding-right:24px;">
                    <div class="section-header">Bill To</div>
                    <div class="billing-name">{{ $order->first_name }} {{ $order->last_name }}</div>
                    <div class="billing-line">{{ $order->email }}</div>
                    @if (!empty($order->phone_number))
                        <div class="billing-line">{{ $order->phone_number }}</div>
                    @endif
                    @php
                        $location = trim(collect([$order->city, $order->country])->filter()->implode(', '));
                    @endphp
                    @if (!empty($location))
                        <div class="billing-line">{{ $location }}</div>
                    @endif
                    @if (!empty($order->company_name))
                        <div class="billing-company">{{ $order->company_name }}</div>
                    @endif
                    @if (!empty($order->company_address))
                        <div class="billing-line">{{ $order->company_address }}</div>
                    @endif
                </td>

                {{-- Right: order details --}}
                <td style="width:50%; vertical-align:top;">
                    <div class="section-header-right">Order Details</div>
                    <table class="order-detail-table" cellpadding="0" cellspacing="0">
                        <tr>
                            <td class="od-label">Order No.</td>
                            <td class="od-value">{{ $order->order_number }}</td>
                        </tr>
                        @if (!empty($order->payment_reference))
                            <tr>
                                <td class="od-label">Payment Reference</td>
                                <td class="od-value">
                                    {{ \Illuminate\Support\Str::limit($order->payment_reference, 30, '...') }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td class="od-label">Payment</td>
                            <td class="od-value">{{ $order->payment_method }}</td>
                        </tr>
                        <tr>
                            <td class="od-label">Currency</td>
                            <td class="od-value">{{ $currency ?: '—' }}</td>
                        </tr>
                        <tr>
                            <td class="od-label">Order Date</td>
                            <td class="od-value">{{ $order->created_at->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <td class="od-label">Payment Date</td>
                            <td class="od-value">{{ $isCompleted ? optional($order->paid_at ?? $order->updated_at)->format('d M Y') : '' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- close content-wrap before full-width tender bar --}}
    </div>

    {{-- ── TENDER SECTION (full-width gray bar via table) ─────────── --}}
    <table cellpadding="0" cellspacing="0" style="width: 100%">
        <tr class="tender-bar-outer">
            <td><span class="tender-bar-title">Tender title</span></td>
        </tr>
        <tr>
            <td style="padding: 9px 28px">
                {{ $tenderTitle }}
            </td>
        </tr>
    </table>

    {{-- tender summary + code back inside padded wrap --}}
    <div class="content-wrap">
        @if (!empty($tenderSummary) || !empty($tenderCode))
            <div style="padding: 4px 2px 12px 2px;">
                @if (!empty($tenderSummary))
                    <div class="tender-summary">{{ $tenderSummary }}</div>
                @endif
                @if (!empty($tenderCode))
                    <div class="tender-code">{{ $tenderCode }}</div>
                @endif
            </div>
        @endif

        {{-- ── FEES TABLE (watermark behind) ──────────────────────────── --}}
        <div class="fees-outer">
            @if (!empty($watermarkSrc))
                <img src="{{ $watermarkSrc }}" class="fees-watermark" alt="">
            @endif

            <table class="fees-table" cellpadding="0" cellspacing="0">
                @if ($deadline)
                    <tr class="fee-row">
                        <td class="fee-label">Tender Deadline :</td>
                        <td class="fee-deadline-value">{{ \Carbon\Carbon::parse($deadline)->format('d-m-Y H:i') }}</td>
                    </tr>
                @endif
                @foreach ($purchasedModules as $mod)
                    <tr class="fee-row">
                        <td class="fee-label">{{ $mod['name'] }} :</td>
                        <td class="fee-value">
                            @if ((float) $mod['cost'] === 0.0)
                                <span style="color:#16a34a; font-weight:600;">Free</span>
                            @else
                                {{ $fmt($mod['cost']) }}
                            @endif
                        </td>
                    </tr>
                @endforeach
                <tr>
                    <td class="subtotal-label" style="padding:9px 14px;">Subtotal</td>
                    <td class="subtotal-value" style="padding:9px 14px;">{{ $fmt($subtotal) }}</td>
                </tr>
                <tr>
                    <td class="vat-label" style="padding:4px 14px 9px;">Tax / VAT</td>
                    <td class="vat-value" style="padding:4px 14px 9px;">Included</td>
                </tr>
            </table>

            {{-- Total row sits inside fees-outer so watermark is behind it --}}
            <table class="total-table" cellpadding="0" cellspacing="0">
                <tr class="total-row">
                    <td>Total Amount :</td>
                    <td class="total-row-value">{{ $fmt($subtotal) }}</td>
                </tr>
            </table>
        </div>

        {{-- ── TERMS LINK ───────────────────────────────────────────────── --}}
        @php
            $tplLangId = optional($order->tender)->language_id;
            $termsPage = $tplLangId ? \App\Page::forType('terms', $tplLangId) : null;
            $termsLink = $termsPage ? route('front.dynamicPage', $termsPage->slug) : null;
        @endphp
        <div class="terms">
            <em>
                @if ($termsLink)
                    <a href="{{ $termsLink }}" style="color:#2c7be5;">Click on this link</a> to read the general
                    terms and conditions related to the purchase of tender documents.
                @else
                    Click on this link to read the general terms and conditions related to the purchase of tender
                    documents.
                @endif
            </em>
        </div>

        {{-- ── SIGNATURE ─────────────────────────────────────────────────── --}}
        <div class="stamp-area">
            @if (!empty($signSrc))
                <img src="{{ $signSrc }}" class="stamp-img" alt="Signature">
            @endif
        </div>

        {{-- ── PAGE NUMBER ──────────────────────────────────────────────── --}}
        <table class="pagenum-table" cellpadding="0" cellspacing="0">
            <tr>
                <td class="pagenum">1</td>
            </tr>
        </table>

    </div>{{-- /content-wrap --}}

    {{-- ── FOOTER (wavy bg div, address centered, logo pinned right) ── --}}
    <div
        class="footer-wave-div"@if (!empty($wavySrc)) style="background-image: url('{{ $wavySrc }}');" @endif>
        <table style="width:100%;" cellpadding="0" cellspacing="0">
            <tr>
                {{-- spacer mirrors logo width so address is truly centered --}}
                <td class="footer-spacer-cell"></td>
                <td class="footer-text">
                    @if (!empty($bse->invoice_footer_address))
                        {!! $bse->invoice_footer_address !!}
                    @else
                        <strong>Adresse :</strong> Hôpital Pédiatrique Charles De Gaule, Rue 28.390, Porte N°12. -
                        <strong>Boite Postale:</strong> 09 BP1719 Ouagadougou 09,<br>
                        <strong>N° Identifiant Foncier Unique (IFU) :</strong> 00214837X - <strong>Compte bancaire
                            VISTA Bank:</strong> BF023010530700010968598I<br>
                        <strong>Tél :</strong> +226 25 44 44 79 - <strong>Mobile :</strong> +226 70 79 80 87 -
                        <strong>Email :</strong> bandaogo@icagroupe.com - <strong>Site web :</strong>
                        www.icagroupe.com
                    @endif
                </td>
                <td class="footer-logo-cell">
                    @if (!empty($logoSrc))
                        <img src="{{ $logoSrc }}" alt="{{ $siteTitle }}">
                    @endif
                </td>
            </tr>
        </table>
    </div>

</body>

</html>
