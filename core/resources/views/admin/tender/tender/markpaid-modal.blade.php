{{-- Validate Payment — the admin reviews the buyer's own uploaded receipt, the
     gateway they chose and the payment instructions they were shown, then
     confirms. Nothing is uploaded here; the receipt on file is copied as the
     evidence record. Orders with no receipt rely on the written reason.
     Styling lives in purchase.blade.php (.vp-modal); injected on demand. --}}
@php
    // Orders placed before gateway tracking have no offline_gateway_id; fall
    // back to the gateway whose name matches the stored payment method.
    $mpGateway = $purchase->offlineGateway
        ?: (!empty($purchase->payment_method) ? \App\OfflineGateway::where('name', $purchase->payment_method)->first() : null);
    $mpExt     = strtolower(pathinfo((string) $purchase->receipt, PATHINFO_EXTENSION));
    $mpIsImg   = in_array($mpExt, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'], true);
    $mpReceipt = !empty($purchase->receipt) ? asset('assets/front/receipt/' . $purchase->receipt) : null;
    $mpMethod  = convertUtf8($purchase->payment_method) ?: '-';
    $mpAmount  = number_format(collect(json_decode($purchase->purchased_modules, true) ?: [])->sum('cost'), 2);
    $mpRows    = $mpGateway ? $mpGateway->instructionRows() : null;
    $mpHasText = $mpGateway && !empty(trim(strip_tags((string) $mpGateway->instructions)));

    // Initials badge for the method pill: "Western Union" → WU, "Bank" → BA.
    $mpWords = preg_split('/\s+/u', trim($mpMethod)) ?: [];
    $mpInit  = count($mpWords) > 1
        ? mb_substr($mpWords[0], 0, 1) . mb_substr($mpWords[1], 0, 1)
        : mb_substr($mpMethod, 0, 2);
    $mpInit  = mb_strtoupper($mpInit);

    // Icon per row label (EN + FR keywords); falls back to an info glyph.
    $mpIcon = function (string $label): string {
        $l = mb_strtolower($label);
        $map = [
            'fa-phone'          => ['phone', 'téléphone', 'telephone', 'mobile', 'tel'],
            'fa-plane'          => ['country', 'pays'],
            'fa-map-marker-alt' => ['city', 'ville', 'address', 'adresse'],
            'fa-coins'          => ['amount', 'montant', 'fee', 'frais'],
            'fa-money-bill-wave'=> ['mode', 'reception', 'réception', 'withdraw', 'retrait', 'cash'],
            'fa-id-card'        => ['first', 'prénom', 'prenom', 'given'],
            'fa-user-friends'   => ['last', 'family', 'surname', 'nom', 'name'],
            'fa-university'     => ['bank', 'banque', 'iban', 'swift', 'account', 'compte', 'rib'],
        ];
        foreach ($map as $icon => $words) {
            foreach ($words as $w) {
                if (mb_strpos($l, $w) !== false) {
                    return $icon;
                }
            }
        }
        return 'fa-info-circle';
    };
@endphp
<div class="modal fade vp-modal" id="markPaidModal{{ $purchase->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <form action="{{ route('admin.tender.purchasePaymentStatus') }}" method="POST" class="vp-form"
            data-amount="{{ $mpAmount }} {{ $purchase->currency_code }}">
            @csrf
            <input type="hidden" name="purchase_id" value="{{ $purchase->id }}">
            <input type="hidden" name="payment_status" value="Completed">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="far fa-credit-card"></i> Validate Payment &ndash; {{ $purchase->order_number }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-6 mb-3">
                            <div class="vp-card">
                                <div class="vp-card-head"><i class="far fa-credit-card"></i> Payment Details</div>
                                <div class="vp-kv">
                                    <span class="vp-k"><i class="far fa-envelope"></i> Payment method</span>
                                    <span class="vp-pill vp-pill-method"><b>{{ $mpInit }}</b> {{ $mpMethod }}</span>
                                </div>
                                <div class="vp-kv">
                                    <span class="vp-k"><i class="far fa-check-square"></i> Receipt required</span>
                                    @if ($mpGateway)
                                        <span class="vp-pill {{ $mpGateway->is_receipt == 1 ? 'vp-pill-yes' : 'vp-pill-no' }}">
                                            <i class="far {{ $mpGateway->is_receipt == 1 ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                                            {{ $mpGateway->is_receipt == 1 ? 'Yes' : 'No' }}
                                        </span>
                                    @else
                                        <span class="vp-pill vp-pill-no" title="No offline gateway is linked to this order">
                                            <i class="far fa-question-circle"></i> Not recorded
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 mb-3">
                            <div class="vp-receipt-title"><i class="fas fa-wallet"></i> Buyer's receipt</div>
                            @if ($mpReceipt)
                                @if ($mpIsImg)
                                    <a href="{{ $mpReceipt }}" target="_blank" class="vp-receipt" title="Open full size">
                                        <img src="{{ $mpReceipt }}" alt="Buyer's receipt" loading="lazy">
                                    </a>
                                @else
                                    <a href="{{ $mpReceipt }}" target="_blank" class="vp-receipt vp-receipt-file">
                                        <i class="far fa-file-alt"></i>
                                        <span>Open {{ strtoupper($mpExt) ?: 'file' }}</span>
                                    </a>
                                @endif
                            @else
                                <div class="vp-receipt vp-receipt-file vp-receipt-missing">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <span>No receipt was uploaded for this order</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if ($mpRows)
                        <div class="vp-card vp-card-wide mb-3">
                            <div class="vp-card-head vp-card-head-lg"><i class="far fa-user"></i> Recipient information</div>
                            <div class="vp-rows">
                                @foreach ($mpRows as [$rLabel, $rValue])
                                    <div class="vp-row">
                                        <span class="vp-row-k"><i class="fas {{ $mpIcon($rLabel) }}"></i><span>{{ $rLabel }}</span></span>
                                        <span class="vp-row-v">{{ $rValue }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @elseif ($mpHasText)
                        <div class="vp-card vp-card-wide mb-3">
                            <div class="vp-card-head vp-card-head-lg"><i class="far fa-file-alt"></i> Payment instructions the buyer saw</div>
                            <div class="vp-instr">{!! clean(replaceBaseUrl($mpGateway->instructions)) !!}</div>
                        </div>
                    @endif

                    <div class="vp-strip">
                        <div class="vp-strip-cell">
                            <i class="far fa-file-alt vp-strip-ico"></i>
                            <div>
                                <div class="vp-strip-title">Buyer's payment reference</div>
                                <div class="vp-strip-val">{{ $purchase->payment_reference ?: '–' }}</div>
                            </div>
                        </div>
                        <div class="vp-strip-cell">
                            <i class="fas fa-coins vp-strip-ico"></i>
                            <div>
                                <div class="vp-strip-sub">Amount due</div>
                                <div class="vp-strip-val vp-strip-amount">{{ $mpAmount }} {{ $purchase->currency_code }}</div>
                            </div>
                        </div>
                        <div class="vp-strip-cell vp-strip-reason">
                            <i class="far fa-calendar-check vp-strip-ico"></i>
                            <div class="w-100">
                                <label class="vp-strip-title" for="vpNote{{ $purchase->id }}">Reason for approval <span class="vp-req">*</span></label>
                                <textarea id="vpNote{{ $purchase->id }}" name="note" class="vp-note" rows="2" maxlength="500"
                                    placeholder="Required — e.g. matched against bank statement line 14"
                                    required minlength="3"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn vp-btn-cancel" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn vp-btn-confirm"><i class="far fa-check-circle"></i> Confirm Payment</button>
                </div>
            </div>
        </form>
    </div>
</div>
