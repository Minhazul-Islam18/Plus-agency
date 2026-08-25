{{-- Manual "Mark as Paid" modal — requires a proof file/image upload via LFM.
     serial={{ $purchase->id }} keeps each row's LFM picker independent — the
     purchases list is paginated with many rows per page, so a fixed serial
     (like the "1"/"2" pattern used on single-record edit forms) would let
     one row's file selection land in a different row's hidden input.

     fileInput{serial} / thumbPreview{serial} / chooseImage{serial} are not
     arbitrary names — LFM's own view (resources/views/vendor/laravel-filemanager/index.blade.php)
     writes the selected file straight into those exact parent-document
     element ids via cross-frame jQuery, the same convention every other LFM
     picker in this admin panel uses (see tender/edit.blade.php's tender_image
     field) — there's no callback function to hook, so the ids must match
     exactly or the selection silently goes nowhere.

     Deliberately no <img> inside thumbPreview: LFM's "use" handler always
     does thumbPreview{serial}.find('img').attr('src', item.url) regardless
     of file type, so a real <img> tag here shows a broken-image icon for
     any non-image proof (PDF, Word, Excel...). The tender module's own
     arbitrary-file field (admin/tender/module) sidesteps the exact same
     bug by not having a thumbPreview element at all; a filename label
     (proofFileName{serial}, refreshed once the LFM modal closes) does the
     same job without depending on any file-type assumption. --}}
<div class="modal fade" id="markPaidModal{{ $purchase->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form action="{{ route('admin.tender.purchasePaymentStatus') }}" method="POST">
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
                        <label>Payment Proof (image, PDF, Word, Excel, or other document) **</label>
                        <br>
                        <div class="thumb-preview" id="thumbPreview{{ $purchase->id }}"></div>
                        <p class="mb-2" id="proofFileName{{ $purchase->id }}">
                            <span class="text-muted">No file chosen yet.</span>
                        </p>
                        <input id="fileInput{{ $purchase->id }}" type="hidden" name="proof" required>
                        <button id="chooseImage{{ $purchase->id }}" class="choose-image btn btn-primary" type="button"
                            data-multiple="false" data-toggle="modal" data-target="#lfmModal{{ $purchase->id }}">
                            Choose File
                        </button>
                        <p id="errproof{{ $purchase->id }}" class="mb-0 text-danger em"></p>
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

{{-- LFM picker for the proof field above — same lfm-modal styling (custom.css)
     and close-icon behavior (custom.js) as every other LFM modal here. --}}
<div class="modal fade lfm-modal" id="lfmModal{{ $purchase->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <i class="fas fa-times-circle"></i>
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body p-0">
                <iframe src="{{ url('laravel-filemanager') }}?type=file&serial={{ $purchase->id }}"
                    style="width:100%;height:500px;overflow:hidden;border:none;"></iframe>
            </div>
        </div>
    </div>
</div>
