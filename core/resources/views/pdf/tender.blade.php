<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Invoice {{ $order->order_number }}</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }

  body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 12px;
    color: #374151;
    background: #ffffff;
  }

  /* ── Page wrapper ── */
  .wrap { padding: 0; }

  /* ── Top accent bar ── */
  .accent-bar {
    background: #1e3a5f;
    height: 6px;
    width: 100%;
  }

  /* ── Header ── */
  .header {
    background: #1e3a5f;
    padding: 32px 40px 28px;
  }
  .header-table { width: 100%; }
  .header-logo img {
    max-height: 48px;
    max-width: 160px;
  }
  .header-logo-text {
    font-size: 20px;
    font-weight: 700;
    color: #ffffff;
    letter-spacing: 0.03em;
  }
  .header-invoice-block { text-align: right; }
  .header-invoice-label {
    font-size: 28px;
    font-weight: 700;
    color: #ffffff;
    letter-spacing: 0.08em;
    text-transform: uppercase;
  }
  .header-invoice-num {
    font-size: 13px;
    color: #93c5fd;
    margin-top: 4px;
  }

  /* ── Status ribbon ── */
  .status-bar {
    padding: 10px 40px;
    background: #f0fdf4;
    border-bottom: 1px solid #bbf7d0;
  }
  .status-bar-pending {
    background: #fefce8;
    border-bottom: 1px solid #fde68a;
  }
  .status-label {
    display: inline-block;
    padding: 3px 14px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
  }
  .status-completed { background: #16a34a; color: #ffffff; }
  .status-pending   { background: #d97706; color: #ffffff; }
  .status-bar-date  { float: right; font-size: 11px; color: #6b7280; }

  /* ── Body ── */
  .body { padding: 32px 40px; }

  /* ── Info grid (Billed To + Order Info) ── */
  .info-table { width: 100%; margin-bottom: 28px; }
  .info-cell { width: 50%; vertical-align: top; padding: 0; }
  .info-cell-right { text-align: right; }
  .info-heading {
    font-size: 9px;
    font-weight: 700;
    color: #9ca3af;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    margin-bottom: 8px;
    padding-bottom: 6px;
    border-bottom: 1px solid #e5e7eb;
  }
  .info-name {
    font-size: 14px;
    font-weight: 700;
    color: #111827;
    margin-bottom: 4px;
  }
  .info-line { font-size: 12px; color: #4b5563; line-height: 1.7; }
  .info-row-label { font-size: 11px; color: #9ca3af; }
  .info-row-value { font-size: 12px; color: #111827; font-weight: 600; }

  /* ── Divider ── */
  .divider {
    border: none;
    border-top: 1px solid #e5e7eb;
    margin: 0 0 24px 0;
  }

  /* ── Items table ── */
  .items-table { width: 100%; border-collapse: collapse; margin-bottom: 0; }
  .items-head { background: #1e3a5f; }
  .items-head th {
    padding: 10px 14px;
    font-size: 10px;
    font-weight: 700;
    color: #ffffff;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    text-align: left;
  }
  .items-head th.right { text-align: right; }
  .items-body td {
    padding: 12px 14px;
    font-size: 12px;
    color: #374151;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
  }
  .items-body tr.alt td { background: #f9fafb; }
  .item-title { font-weight: 600; color: #111827; }
  .item-sub   { font-size: 10px; color: #9ca3af; margin-top: 2px; }
  .amount-cell { text-align: right; font-weight: 600; color: #111827; white-space: nowrap; }

  /* ── Totals block ── */
  .totals-table { width: 100%; border-collapse: collapse; margin-top: 0; }
  .totals-spacer td { border-bottom: 2px solid #1e3a5f; padding: 0; }
  .totals-row td { padding: 8px 14px; font-size: 12px; color: #374151; }
  .totals-row td.right { text-align: right; }
  .total-final { background: #1e3a5f; }
  .total-final td {
    padding: 13px 14px;
    font-size: 14px;
    font-weight: 700;
    color: #ffffff;
  }
  .total-final td.right { text-align: right; }

  /* ── Note box ── */
  .note-box {
    margin-top: 28px;
    border-left: 4px solid #1e3a5f;
    background: #f0f4ff;
    padding: 12px 16px;
    border-radius: 0 6px 6px 0;
  }
  .note-box p { font-size: 11px; color: #374151; line-height: 1.6; }

  /* ── Footer ── */
  .footer {
    margin-top: 40px;
    padding: 18px 40px;
    background: #f9fafb;
    border-top: 1px solid #e5e7eb;
    text-align: center;
  }
  .footer p { font-size: 10px; color: #9ca3af; line-height: 1.8; }
</style>
</head>
<body>
<div class="wrap">

@php
  $tender       = $order->tender;
  $tenderTitle  = optional($tender)->title ?: 'Tender Document';
  $tenderCode   = optional($tender)->tender_code ?: '';
  $currency     = $order->currency_code ?? '';
  $symLeft      = ($bse->base_currency_symbol_position == 'left')  ? $currency . ' ' : '';
  $symRight     = ($bse->base_currency_symbol_position == 'right') ? ' ' . $currency : '';
  $isCompleted  = strtolower($order->payment_status) === 'completed';
  $siteTitle    = $bse->website_title ?? config('app.name');

  // Build line items from fee columns; fall back to tender price
  $items = [];
  if (!empty($order->technical_proposal_fee) && $order->technical_proposal_fee > 0) {
      $items[] = ['desc' => 'Technical Proposal Fee', 'sub' => $tenderTitle, 'amount' => (float) $order->technical_proposal_fee];
  }
  if (!empty($order->financial_proposal_fee) && $order->financial_proposal_fee > 0) {
      $items[] = ['desc' => 'Financial Proposal Fee', 'sub' => $tenderTitle, 'amount' => (float) $order->financial_proposal_fee];
  }
  if (!empty($order->summary_fee) && $order->summary_fee > 0) {
      $items[] = ['desc' => 'Summary Fee', 'sub' => $tenderTitle, 'amount' => (float) $order->summary_fee];
  }
  if (!empty($order->tender_notice_publication_fee) && $order->tender_notice_publication_fee > 0) {
      $items[] = ['desc' => 'Tender Notice Publication Fee', 'sub' => $tenderTitle, 'amount' => (float) $order->tender_notice_publication_fee];
  }
  // Fallback: use tender's current_price
  if (empty($items)) {
      $price = optional($tender)->current_price;
      $items[] = ['desc' => $tenderTitle, 'sub' => $tenderCode ?: 'Tender Document', 'amount' => $price ? (float) $price : null];
  }

  $subtotal = array_sum(array_column($items, 'amount'));

  $fmt = function($n) use ($symLeft, $symRight) {
      return $symLeft . number_format((float)$n, 2) . $symRight;
  };
@endphp

  <!-- Header -->
  <div class="header">
    <table class="header-table" cellpadding="0" cellspacing="0">
      <tr>
        <td class="header-logo" style="vertical-align:middle;">
          @if (!empty($logoSrc))
            <img src="{{ $logoSrc }}" alt="{{ $siteTitle }}">
          @else
            <span class="header-logo-text">{{ $siteTitle }}</span>
          @endif
        </td>
        <td class="header-invoice-block" style="vertical-align:middle;">
          <div class="header-invoice-label">Invoice</div>
          <div class="header-invoice-num"># {{ $order->order_number }}</div>
        </td>
      </tr>
    </table>
  </div>

  <!-- Status bar -->
  <div class="{{ $isCompleted ? 'status-bar' : 'status-bar status-bar-pending' }}">
    <span class="status-label {{ $isCompleted ? 'status-completed' : 'status-pending' }}">
      {{ $isCompleted ? 'Paid' : 'Pending Payment' }}
    </span>
    <span class="status-bar-date">Issued: {{ $order->created_at->format('d M Y') }}</span>
  </div>

  <!-- Body -->
  <div class="body">

    <!-- Billed To + Order Info -->
    <table class="info-table" cellpadding="0" cellspacing="0">
      <tr>
        <td class="info-cell" style="padding-right:24px;">
          <div class="info-heading">Billed To</div>
          <div class="info-name">{{ $order->first_name }} {{ $order->last_name }}</div>
          <div class="info-line">{{ $order->email }}</div>
          @if (!empty($order->phone_number))
            <div class="info-line">{{ $order->phone_number }}</div>
          @endif
          @if (!empty($order->country))
            <div class="info-line">
              {{ $order->country }}@if (!empty($order->city)), {{ $order->city }}@endif
            </div>
          @endif
        </td>
        <td class="info-cell info-cell-right">
          <div class="info-heading">Order Details</div>
          <table cellpadding="0" cellspacing="0" style="width:100%;">
            <tr>
              <td class="info-row-label" style="padding-bottom:5px;">Order No.</td>
              <td class="info-row-value" style="text-align:right; padding-bottom:5px;">{{ $order->order_number }}</td>
            </tr>
            <tr>
              <td class="info-row-label" style="padding-bottom:5px;">Payment</td>
              <td class="info-row-value" style="text-align:right; padding-bottom:5px;">{{ $order->payment_method }}</td>
            </tr>
            <tr>
              <td class="info-row-label" style="padding-bottom:5px;">Currency</td>
              <td class="info-row-value" style="text-align:right; padding-bottom:5px;">{{ $currency ?: '—' }}</td>
            </tr>
            <tr>
              <td class="info-row-label">Order Date</td>
              <td class="info-row-value" style="text-align:right;">{{ $order->created_at->format('d M Y') }}</td>
            </tr>
          </table>
        </td>
      </tr>
    </table>

    <hr class="divider">

    <!-- Line items -->
    <table class="items-table" cellpadding="0" cellspacing="0">
      <thead class="items-head">
        <tr>
          <th style="width:6%;">#</th>
          <th style="width:55%;">Description</th>
          <th style="width:13%; text-align:right;">Qty</th>
          <th style="width:26%; text-align:right;">Amount</th>
        </tr>
      </thead>
      <tbody class="items-body">
        @foreach ($items as $i => $item)
          <tr class="{{ $i % 2 === 1 ? 'alt' : '' }}">
            <td style="color:#9ca3af;">{{ $i + 1 }}</td>
            <td>
              <div class="item-title">{{ $item['desc'] }}</div>
              @if ($item['desc'] !== $item['sub'] && !empty($item['sub']))
                <div class="item-sub">{{ $item['sub'] }}</div>
              @endif
            </td>
            <td style="text-align:right; color:#6b7280;">1</td>
            <td class="amount-cell">
              {{ $item['amount'] !== null ? $fmt($item['amount']) : '—' }}
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <!-- Totals -->
    <table class="totals-table" cellpadding="0" cellspacing="0">
      <tr class="totals-spacer"><td colspan="2"></td></tr>
      @if ($subtotal > 0)
        <tr class="totals-row">
          <td>Subtotal</td>
          <td class="right">{{ $fmt($subtotal) }}</td>
        </tr>
        <tr class="totals-row">
          <td style="color:#9ca3af; font-size:11px;">Tax / VAT</td>
          <td class="right" style="color:#9ca3af; font-size:11px;">Included</td>
        </tr>
      @endif
      <tr class="total-final">
        <td>Total Amount</td>
        <td class="right">{{ $subtotal > 0 ? $fmt($subtotal) : '—' }}</td>
      </tr>
    </table>

    <!-- Note -->
    <div class="note-box">
      <p>
        <strong>Note:</strong> This is a system-generated invoice for order
        <strong>{{ $order->order_number }}</strong>.
        @if ($isCompleted)
          Payment has been received and confirmed. Thank you for your purchase.
        @else
          Payment is currently pending. Please contact us with your order number if you have any queries.
        @endif
      </p>
    </div>

  </div><!-- /body -->

  <!-- Footer -->
  <div class="footer">
    <p>
      {{ $siteTitle }}&nbsp;&nbsp;&bull;&nbsp;&nbsp;This invoice was generated automatically.&nbsp;&nbsp;&bull;&nbsp;&nbsp;&copy; {{ date('Y') }} {{ $siteTitle }}. All rights reserved.
    </p>
  </div>

</div><!-- /wrap -->
</body>
</html>
