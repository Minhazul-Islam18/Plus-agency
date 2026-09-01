@php
    // Modules the buyer actually purchased (paid + free), as stored at checkout.
    $moduleList = collect(json_decode($purchase->purchased_modules, true) ?: []);
    $qty = $moduleList->count();
    $summaryFee = $moduleList->sum('cost');
    $currency = $purchase->currency_code ?? '';

    // Total actual file opens across every download link this order has ever
    // had (a new SecureToken is issued each time the order is (re)validated,
    // and a revoked token's count still reflects real downloads that
    // happened before it was revoked, so this sums all of them, not just
    // the currently-active one).
    $downloadCount = \App\SecureToken::where('order_id', $purchase->order_number)->sum('download_count');

    // Devices/browsers recognized for this order's secure download link —
    // see FindMyFilesController::deviceAccessState().
    $recognizedDevices = \App\TenderDeviceRegistration::where('order_id', $purchase->order_number)
        ->orderByDesc('registered_at')
        ->get();
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
                        <div class="col-lg-5"><strong>Order Number Generation Date &amp; Time:</strong></div>
                        <div class="col-lg-7">{{ $purchase->created_at->format('d M Y, H:i:s') }}</div>
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

                    <div class="row">
                        <div class="col-lg-5"><strong>Payment Date &amp; Time:</strong></div>
                        <div class="col-lg-7">{{ $purchase->paid_at ? $purchase->paid_at->format('d M Y, H:i:s') : '-' }}</div>
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
                                {{-- Only a manual admin validation can be reversed — a real
                                     gateway payment (Moneroo etc.) genuinely charged the
                                     buyer, there's no corresponding refund happening here. --}}
                                @if (!empty($purchase->validated_by_admin_id))
                                    <form action="{{ route('admin.tender.purchasePaymentStatus') }}"
                                        method="POST" class="d-inline-block ml-2 revertform">
                                        @csrf
                                        <input type="hidden" name="purchase_id" value="{{ $purchase->id }}">
                                        <input type="hidden" name="payment_status" value="Pending">
                                        <input type="hidden" name="reason">
                                        <button type="button" class="revertbtn btn btn-link btn-sm p-0" style="font-size: 12px;">
                                            Cancel Payment Validation
                                        </button>
                                    </form>
                                @endif
                            @else
                                <span class="badge badge-warning mr-2">Pending</span>
                                @if (\Illuminate\Support\Facades\Auth::guard('admin')->user()->hasPermission('Manual Payment Completion'))
                                    <button type="button" class="btn btn-success btn-sm"
                                        data-toggle="modal" data-target="#markPaidModal{{ $purchase->id }}">
                                        <i class="fas fa-check mr-1"></i> Mark as Paid
                                    </button>
                                @else
                                    <span class="text-muted" style="font-size: 12px;" data-toggle="tooltip"
                                        title="You don't have permission to manually complete a payment.">
                                        <i class="fas fa-lock mr-1"></i> Mark as Paid
                                    </span>
                                @endif
                            @endif
                        </div>
                    </div>
                    <hr>

                    @if (!empty($purchase->reversal_reason))
                        <div class="row">
                            <div class="col-lg-5"><strong>Last Cancellation Reason:</strong></div>
                            <div class="col-lg-7">{{ $purchase->reversal_reason }}</div>
                        </div>
                        <hr>
                    @endif

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
                        <div class="col-lg-5"><strong>Download Count:</strong></div>
                        <div class="col-lg-7">{{ $downloadCount }}</div>
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

                    <div class="row">
                        <div class="col-lg-5">
                            <strong>Recognized Devices:</strong>
                            <br>
                            <a href="{{ route('admin.tender.devices', ['q' => $purchase->order_number]) }}"
                                style="font-size: 11px;">View in Authorized Devices &rarr;</a>
                        </div>
                        <div class="col-lg-7">
                            @if ($recognizedDevices->isEmpty())
                                <span class="text-muted">None yet</span>
                            @else
                                @foreach ($recognizedDevices as $device)
                                    <div class="d-flex justify-content-between align-items-center mb-1"
                                        style="font-size: 12px;">
                                        <span>
                                            {{ $device->device_label ?: 'Unknown device' }}
                                            @if ($device->status == 'pending')
                                                <span class="badge badge-warning">Pending OTP</span>
                                            @elseif ($device->status == 'revoked')
                                                <span class="badge badge-danger">Revoked</span>
                                            @endif
                                            <span class="text-muted">
                                                &middot; last used {{ optional($device->last_used_at)->format('d M Y, H:i') }}
                                            </span>
                                        </span>
                                        <form action="{{ route('admin.tender.devices.reset') }}" method="POST"
                                            class="d-inline-block ml-2 revokedeviceform">
                                            @csrf
                                            <input type="hidden" name="device_id" value="{{ $device->id }}">
                                            <button type="submit" class="btn btn-link btn-sm p-0 text-danger"
                                                onclick="return confirm('Reset this device? It will need a new email code to access this order\'s link again.')">
                                                Reset
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
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
