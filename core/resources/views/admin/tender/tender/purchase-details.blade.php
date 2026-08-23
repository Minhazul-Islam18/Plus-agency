@php
    // Modules the buyer actually purchased (paid + free), as stored at checkout.
    $moduleList = collect(json_decode($purchase->purchased_modules, true) ?: []);
    $qty = $moduleList->count();
    $summaryFee = $moduleList->sum('cost');
    $currency = $purchase->currency_code ?? '';
@endphp

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
                        <div class="col-lg-7">
                            {{ !empty($purchase->tender) ? convertUtf8($purchase->tender->title) : '-' }}</div>
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
                        <div class="col-lg-5"><strong>Phone Number:</strong></div>
                        <div class="col-lg-7">{{ $purchase->phone_number ?: '-' }}</div>
                    </div>
                    <hr>

                    <div class="row">
                        <div class="col-lg-5"><strong>Country:</strong></div>
                        <div class="col-lg-7">{{ $purchase->country ?: '-' }}</div>
                    </div>
                    <hr>

                    <div class="row">
                        <div class="col-lg-5"><strong>City:</strong></div>
                        <div class="col-lg-7">{{ $purchase->city ?: '-' }}</div>
                    </div>
                    <hr>

                    <div class="row">
                        <div class="col-lg-5"><strong>Company Name:</strong></div>
                        <div class="col-lg-7">{{ $purchase->company_name ? convertUtf8($purchase->company_name) : '-' }}</div>
                    </div>
                    <hr>

                    <div class="row">
                        <div class="col-lg-5"><strong>Company Registration No.:</strong></div>
                        <div class="col-lg-7">{{ $purchase->company_registration_no ?: '-' }}</div>
                    </div>
                    <hr>

                    <div class="row">
                        <div class="col-lg-5"><strong>Company Address:</strong></div>
                        <div class="col-lg-7">{{ $purchase->company_address ? convertUtf8($purchase->company_address) : '-' }}</div>
                    </div>
                    <hr>

                    <div class="row">
                        <div class="col-lg-5"><strong>Qty:</strong></div>
                        <div class="col-lg-7">{{ $qty }}</div>
                    </div>
                    <hr>

                    {{-- Purchased module fee rows (paid + free) --}}
                    @foreach ($moduleList as $module)
                        <div class="row">
                            <div class="col-lg-5"><strong>{{ convertUtf8($module['name'] ?? '') }}:</strong></div>
                            <div class="col-lg-7">
                                @if (!empty($module['cost']) && $module['cost'] > 0)
                                    {{ number_format($module['cost'], 2) }} {{ $currency }}
                                @else
                                    Free
                                @endif
                            </div>
                        </div>
                        <hr>
                    @endforeach

                    <div class="row">
                        <div class="col-lg-5"><strong>Summary Fee:</strong></div>
                        <div class="col-lg-7">
                            @if ($summaryFee > 0)
                                {{ number_format($summaryFee, 2) }} {{ $currency }}
                            @else
                                -
                            @endif
                        </div>
                    </div>
                    <hr>

                    <div class="row">
                        <div class="col-lg-5"><strong>Payment Method:</strong></div>
                        <div class="col-lg-7">{{ convertUtf8($purchase->payment_method) }}</div>
                    </div>
                    <hr>

                    @if (!empty($purchase->receipt))
                        <div class="row">
                            <div class="col-lg-5"><strong>Uploaded Receipt:</strong></div>
                            <div class="col-lg-7">
                                <a href="{{ asset('assets/front/receipt/' . $purchase->receipt) }}" target="_blank">
                                    View receipt
                                </a>
                            </div>
                        </div>
                        <hr>
                    @endif

                    <div class="row align-items-center">
                        <div class="col-lg-5"><strong>Payment Status:</strong></div>
                        <div class="col-lg-7">
                            @if (strtolower($purchase->payment_status) == 'completed')
                                <span class="badge badge-success mr-2">Completed</span>
                                @if (!empty($purchase->admin_proof))
                                    <a href="#" data-toggle="modal"
                                        data-target="#proofModal{{ $purchase->id }}"
                                        style="font-size: 12px;">View Proof</a>
                                @endif
                                <form action="{{ route('admin.tender.purchasePaymentStatus') }}"
                                    method="POST" class="d-inline-block ml-2">
                                    @csrf
                                    <input type="hidden" name="purchase_id" value="{{ $purchase->id }}">
                                    <input type="hidden" name="payment_status" value="Pending">
                                    <button type="submit" class="btn btn-link btn-sm p-0" style="font-size: 12px;">
                                        Revert to Pending
                                    </button>
                                </form>
                            @else
                                <span class="badge badge-warning mr-2">Pending</span>
                                <button type="button" class="btn btn-success btn-sm"
                                    data-open-target="#markPaidModal{{ $purchase->id }}">
                                    <i class="fas fa-check mr-1"></i> Mark as Paid
                                </button>
                            @endif
                        </div>
                    </div>
                    <hr>

                    <div class="row">
                        <div class="col-lg-5"><strong>Access:</strong></div>
                        <div class="col-lg-7">
                            @if ($purchase->isSuspended())
                                <span class="badge badge-dark">Suspended</span>
                                @if ($purchase->suspend_reason)
                                    <small class="text-muted d-block mt-1">{{ $purchase->suspend_reason }}</small>
                                @endif
                            @else
                                <span class="badge badge-success">Active</span>
                            @endif
                        </div>
                    </div>
                    <hr>

                    <div class="row">
                        <div class="col-lg-5"><strong>Buyer Status:</strong></div>
                        <div class="col-lg-7">
                            @if (\App\TenderBlacklist::matches($purchase->company_registration_no))
                                <span class="badge badge-danger">Blacklisted</span>
                            @else
                                <span class="badge badge-success">Not blacklisted</span>
                            @endif
                        </div>
                    </div>
                    <hr>

                    <div class="row align-items-center">
                        <div class="col-lg-5"><strong>Payment Reference:</strong></div>
                        <div class="col-lg-7">{{ $purchase->payment_reference }}</div>

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
