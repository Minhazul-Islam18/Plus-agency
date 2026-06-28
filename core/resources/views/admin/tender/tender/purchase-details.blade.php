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
