@php
    $moduleList = ($purchase->tender && $purchase->tender->tenderModules)
        ? $purchase->tender->tenderModules
        : collect([]);
    $qty        = $moduleList->count();
    $summaryFee = $moduleList->sum('cost');
    $currency   = $purchase->currency_code ?? '';
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
                        <div class="col-lg-7">{{ $qty }}</div>
                    </div>
                    <hr>

                    {{-- Dynamic module fee rows --}}
                    @foreach ($moduleList as $module)
                        <div class="row">
                            <div class="col-lg-5"><strong>{{ $module->name }}:</strong></div>
                            <div class="col-lg-7">
                                @if (!is_null($module->cost) && $module->cost > 0)
                                    {{ number_format($module->cost, 2) }} {{ $currency }}
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
                        <div class="col-lg-7">
                            <form action="{{ route('admin.tender.purchaseUpdateReference') }}" method="POST"
                                class="d-flex align-items-center" style="gap:6px;">
                                @csrf
                                <input type="hidden" name="purchase_id" value="{{ $purchase->id }}">
                                <input type="text" name="payment_reference"
                                    class="form-control form-control-sm"
                                    value="{{ $purchase->payment_reference }}"
                                    placeholder="e.g. FLW-XXXX or TRF-2026-001"
                                    maxlength="100"
                                    style="text-transform:uppercase; font-family:monospace;">
                                <button type="submit" class="btn btn-sm btn-primary text-nowrap">Save</button>
                            </form>
                            <small class="text-muted">Gateway txn ID or bank transfer reference.</small>
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
