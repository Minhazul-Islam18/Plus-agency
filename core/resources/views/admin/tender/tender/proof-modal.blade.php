{{-- Manual Payment Proof Modal --}}
@if (!empty($purchase->admin_proof))
    <div class="modal fade" id="proofModal{{ $purchase->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Payment Proof</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @php
                        $proofExt = strtolower(pathinfo($purchase->admin_proof, PATHINFO_EXTENSION));
                        $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'svg'];
                    @endphp
                    @if (in_array($proofExt, $imageExts, true))
                        <img style="width: 100%;" src="{{ asset('assets/front/tender_proofs/' . $purchase->admin_proof) }}" alt="Payment Proof">
                    @else
                        {{-- PDF, Word, Excel, or any other document type — no
                             inline preview, just a direct link to open/download it. --}}
                        <a href="{{ asset('assets/front/tender_proofs/' . $purchase->admin_proof) }}" target="_blank" class="btn btn-outline-primary">
                            Open {{ strtoupper($proofExt) ?: 'File' }}
                        </a>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endif
