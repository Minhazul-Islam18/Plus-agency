{{-- Mark as Paid — the admin reviews the buyer's own uploaded receipt (and
     the gateway they chose) and confirms. Nothing is uploaded here; the
     receipt on file is copied as the evidence record. Orders with no
     receipt need a written note instead. --}}
@php
    $mpGateway = $purchase->offlineGateway;
    $mpExt     = strtolower(pathinfo((string) $purchase->receipt, PATHINFO_EXTENSION));
    $mpIsImg   = in_array($mpExt, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'], true);
@endphp
<div class="modal fade" id="markPaidModal{{ $purchase->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <form action="{{ route('admin.tender.purchasePaymentStatus') }}" method="POST">
            @csrf
            <input type="hidden" name="purchase_id" value="{{ $purchase->id }}">
            <input type="hidden" name="payment_status" value="Completed">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Validate Payment — {{ $purchase->order_number }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <p class="mb-1"><strong>Payment method:</strong> {{ convertUtf8($purchase->payment_method) ?: '-' }}</p>
                            @if ($mpGateway)
                                <p class="mb-1"><strong>Receipt required:</strong>
                                    {{ $mpGateway->is_receipt == 1 ? 'Yes' : 'No' }}</p>
                                @if (!empty($mpGateway->short_description))
                                    <p class="mb-1 text-muted">{{ convertUtf8($mpGateway->short_description) }}</p>
                                @endif
                                @if (!empty(trim(strip_tags((string) $mpGateway->instructions))))
                                    <p class="mb-1"><strong>Instructions the buyer saw:</strong></p>
                                    <div class="border rounded p-2 mb-2" style="max-height:180px;overflow:auto;font-size:13px;">
                                        {!! clean(replaceBaseUrl($mpGateway->instructions)) !!}
                                    </div>
                                @endif
                            @endif
                            <p class="mb-1"><strong>Buyer's payment reference:</strong>
                                {{ $purchase->payment_reference ?: '-' }}</p>
                            <p class="mb-0"><strong>Amount due:</strong>
                                {{ number_format(collect(json_decode($purchase->purchased_modules, true) ?: [])->sum('cost'), 2) }}
                                {{ $purchase->currency_code }}</p>
                        </div>
                        <div class="col-lg-6">
                            <p class="mb-1"><strong>Buyer's receipt:</strong></p>
                            @if (!empty($purchase->receipt))
                                @if ($mpIsImg)
                                    <a href="{{ asset('assets/front/receipt/' . $purchase->receipt) }}" target="_blank">
                                        <img src="{{ asset('assets/front/receipt/' . $purchase->receipt) }}" alt="Receipt"
                                            style="max-width:100%;max-height:260px;border:1px solid #ddd;">
                                    </a>
                                @else
                                    <a href="{{ asset('assets/front/receipt/' . $purchase->receipt) }}" target="_blank"
                                        class="btn btn-outline-primary btn-sm">Open {{ strtoupper($mpExt) ?: 'file' }}</a>
                                @endif
                            @else
                                <p class="text-danger mb-0">No receipt was uploaded for this order.</p>
                            @endif
                        </div>
                    </div>
                    <hr>
                    <div class="form-group mb-0">
                        <label>Note {{ empty($purchase->receipt) ? '**' : '(optional)' }}</label>
                        <textarea name="note" class="form-control" rows="2" maxlength="500"
                            placeholder="{{ empty($purchase->receipt) ? 'Required — how was this payment verified?' : 'e.g. matched against bank statement line 14' }}"
                            {{ empty($purchase->receipt) ? 'required' : '' }}></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Confirm Payment</button>
                </div>
            </div>
        </form>
    </div>
</div>
