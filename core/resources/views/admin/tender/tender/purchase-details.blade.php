<!-- Receipt Details Modal -->
<div class="modal fade" id="detailsModal{{ $purchase->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Receipt details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container">

                    <div class="row">
                        <div class="col-lg-5"><strong>Tender Title:</strong></div>
                        <div class="col-lg-7">{{ !empty($purchase->tender) ? convertUtf8($purchase->tender->title) : '-' }}</div>
                    </div>
                    <hr>

                    <div class="row">
                        <div class="col-lg-5"><strong>First Name:</strong></div>
                        <div class="col-lg-7">{{ convertUtf8($purchase->first_name) }}</div>
                    </div>
                    <hr>

                    <div class="row">
                        <div class="col-lg-5"><strong>Last Name:</strong></div>
                        <div class="col-lg-7">{{ convertUtf8($purchase->last_name) }}</div>
                    </div>
                    <hr>

                    <div class="row">
                        <div class="col-lg-5"><strong>Email Address:</strong></div>
                        <div class="col-lg-7">{{ $purchase->email }}</div>
                    </div>
                    <hr>

                    <div class="row">
                        <div class="col-lg-5"><strong>City:</strong></div>
                        <div class="col-lg-7">{{ $purchase->city ?? '-' }}</div>
                    </div>
                    <hr>

                    <div class="row">
                        <div class="col-lg-5"><strong>Qty:</strong></div>
                        <div class="col-lg-7">{{ $purchase->qty }}</div>
                    </div>
                    <hr>

                    <div class="row">
                        <div class="col-lg-5"><strong>Technical Proposal Fee:</strong></div>
                        <div class="col-lg-7">
                            {{ number_format($purchase->technical_proposal_fee, 0, '.', ' ') }}
                            {{ $purchase->currency_code }}
                        </div>
                    </div>
                    <hr>

                    <div class="row">
                        <div class="col-lg-5"><strong>Financial Proposal Fee:</strong></div>
                        <div class="col-lg-7">
                            {{ number_format($purchase->financial_proposal_fee, 0, '.', ' ') }}
                            {{ $purchase->currency_code }}
                        </div>
                    </div>
                    <hr>

                    <div class="row">
                        <div class="col-lg-5"><strong>Summary Fee:</strong></div>
                        <div class="col-lg-7">
                            {{ number_format($purchase->summary_fee, 0, '.', ' ') }}
                            {{ $purchase->currency_code }}
                        </div>
                    </div>
                    <hr>

                    <div class="row">
                        <div class="col-lg-5"><strong>Tender Notice Publication Fee:</strong></div>
                        <div class="col-lg-7">
                            {{ number_format($purchase->tender_notice_publication_fee, 0, '.', ' ') }}
                            {{ $purchase->currency_code }}
                        </div>
                    </div>
                    <hr>

                    <div class="row">
                        <div class="col-lg-5"><strong>Payment Method:</strong></div>
                        <div class="col-lg-7">{{ convertUtf8($purchase->payment_method) }}</div>
                    </div>
                    <hr>

                    <div class="row">
                        <div class="col-lg-5"><strong>Status:</strong></div>
                        <div class="col-lg-7">
                            @if (strtolower($purchase->payment_status) == 'completed')
                                <span class="badge badge-success">Completed</span>
                            @else
                                <span class="badge badge-warning">Pending</span>
                            @endif
                        </div>
                    </div>
                    <hr>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
