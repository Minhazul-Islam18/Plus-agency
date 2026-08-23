{{-- Manual "Mark as Paid" modal — requires a proof file/image upload --}}
<div class="modal fade" id="markPaidModal{{ $purchase->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form action="{{ route('admin.tender.purchasePaymentStatus') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="purchase_id" value="{{ $purchase->id }}">
            <input type="hidden" name="payment_status" value="Completed">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Mark as Paid</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Confirm order <strong>{{ $purchase->order_number }}</strong> was paid outside the automated flow. A proof of payment is required.</p>
                    <div class="form-group">
                        <label>Payment Proof (image or PDF) **</label>
                        <input type="file" name="proof" class="form-control" accept="image/*,.pdf" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Mark as Paid</button>
                </div>
            </div>
        </form>
    </div>
</div>
