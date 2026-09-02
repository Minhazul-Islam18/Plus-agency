@php
    $purchase = $purchases->get($device->order_id);
    $recentAccess = $device->accessLogs()->limit(5)->get();

    $deviceIcon = $device->device_type == 'mobile' ? 'fa-mobile-alt' : ($device->device_type == 'tablet' ? 'fa-tablet-alt' : 'fa-desktop');

    $statusMeta = [
        'active'  => ['label' => 'Active', 'class' => 'ddm-badge-active', 'sub' => 'Recognized device'],
        'pending' => ['label' => 'Pending OTP', 'class' => 'ddm-badge-pending', 'sub' => 'Awaiting verification'],
        'revoked' => ['label' => 'Revoked', 'class' => 'ddm-badge-revoked', 'sub' => 'Access blocked'],
    ][$device->status] ?? ['label' => ucfirst($device->status), 'class' => '', 'sub' => ''];

    $validationLabel = [
        'auto_first'   => 'First device (auto-trusted)',
        'otp_email'    => 'Email OTP',
        'admin_manual' => 'Added manually by admin',
    ][$device->validation_method] ?? '-';
@endphp
<div class="modal fade" id="deviceDetailsModal{{ $device->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    {{-- Bootstrap 4.1.3 (bundled here) predates .modal-xl (added in 4.4) —
         the class is a no-op and the dialog silently falls back to the
         default ~500px width. Forcing the width inline since upgrading the
         shared bundled Bootstrap is out of scope here. --}}
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 1100px;">
        <div class="modal-content ddm">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas {{ $deviceIcon }} mr-2"></i> Device Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <div class="ddm-head">
                    <div class="ddm-head-icon"><i class="fas {{ $deviceIcon }}"></i></div>
                    <div>
                        <h5 class="ddm-head-title">{{ $device->device_label ?: 'Unknown device' }}</h5>
                        <span class="ddm-badge {{ $statusMeta['class'] }}">
                            <i class="fas {{ $device->status == 'active' ? 'fa-check-circle' : ($device->status == 'pending' ? 'fa-clock' : 'fa-ban') }}"></i>
                            {{ $statusMeta['label'] }}
                        </span>
                        @if ($device->is_primary)
                            <span class="ddm-badge ddm-badge-primary"><i class="fas fa-star"></i> Primary</span>
                        @endif
                        <div class="ddm-head-sub">{{ $statusMeta['sub'] }}</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-7">
                        <div class="ddm-card mb-3">
                        <div class="ddm-section-title">General Information</div>
                        <div class="ddm-info-list">
                            <div class="ddm-info-row">
                                <div class="ddm-info-label"><i class="fas {{ $deviceIcon }}"></i> Device Name</div>
                                <div class="ddm-info-value">{{ $device->device_label ?: '-' }}</div>
                            </div>
                            <div class="ddm-info-row">
                                <div class="ddm-info-label"><i class="fas {{ $deviceIcon }}"></i> Device Type</div>
                                <div class="ddm-info-value">{{ ucfirst($device->device_type ?: '-') }}</div>
                            </div>
                            <div class="ddm-info-row">
                                <div class="ddm-info-label"><i class="fas fa-globe"></i> Browser</div>
                                <div class="ddm-info-value">
                                    @if ($device->browser_name)
                                        <i class="{{ \App\TenderDeviceRegistration::browserIcon($device->browser_name) }}"
                                            style="color: {{ \App\TenderDeviceRegistration::browserColor($device->browser_name) }};"></i>
                                        {{ $device->browser_name }} {{ $device->browser_version }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                            <div class="ddm-info-row">
                                <div class="ddm-info-label"><i class="fas fa-cog"></i> Operating System</div>
                                <div class="ddm-info-value">{{ $device->os_name ?: '-' }}</div>
                            </div>
                            <div class="ddm-info-row">
                                <div class="ddm-info-label"><i class="fas fa-map-marker-alt"></i> IP (last access)</div>
                                <div class="ddm-info-value">{{ $device->ip ?: '-' }}</div>
                            </div>
                            <div class="ddm-info-row">
                                <div class="ddm-info-label"><i class="fas fa-map-pin"></i> Location (last access)</div>
                                <div class="ddm-info-value">
                                    @if ($device->city || $device->country)
                                        {{ trim(collect([$device->city, $device->country])->filter()->implode(', ')) }}
                                    @else
                                        Not available
                                    @endif
                                </div>
                            </div>
                            <div class="ddm-info-row">
                                <div class="ddm-info-label"><i class="fas fa-wifi"></i> ISP (last access)</div>
                                <div class="ddm-info-value">{{ $device->isp ?: 'Not available' }}</div>
                            </div>
                            <div class="ddm-info-row">
                                <div class="ddm-info-label"><i class="fas fa-broadcast-tower"></i> Network (last access)</div>
                                <div class="ddm-info-value">{{ $device->network_label ?: 'Not available' }}</div>
                            </div>
                            <div class="ddm-info-row">
                                <div class="ddm-info-label"><i class="fas fa-user"></i> User</div>
                                <div class="ddm-info-value">
                                    @if ($purchase)
                                        {{ trim($purchase->first_name . ' ' . $purchase->last_name) }}
                                        <br><small class="ddm-muted">{{ $purchase->email }}</small>
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                            <div class="ddm-info-row">
                                <div class="ddm-info-label"><i class="fas fa-calendar-alt"></i> Registered</div>
                                <div class="ddm-info-value">{{ optional($device->registered_at)->format('d M Y, H:i') ?: '-' }}</div>
                            </div>
                            <div class="ddm-info-row">
                                <div class="ddm-info-label"><i class="fas fa-clock"></i> Last Access</div>
                                <div class="ddm-info-value">{{ optional($device->last_used_at)->format('d M Y, H:i') ?: '-' }}</div>
                            </div>
                            <div class="ddm-info-row">
                                <div class="ddm-info-label"><i class="fas fa-circle"></i> Status</div>
                                <div class="ddm-info-value">
                                    <span class="ddm-dot ddm-dot-{{ $device->status }}"></span> {{ $statusMeta['label'] }}
                                </div>
                            </div>
                            <div class="ddm-info-row">
                                <div class="ddm-info-label"><i class="fas fa-shield-alt"></i> Validation Method</div>
                                <div class="ddm-info-value">{{ $validationLabel }}</div>
                            </div>
                            <div class="ddm-info-row">
                                <div class="ddm-info-label"><i class="fas fa-key"></i> OTP Codes Used</div>
                                <div class="ddm-info-value">
                                    {{ $device->otp_uses }}
                                    @if ($device->otp_uses > 0 && $device->validation_method == 'otp_email')
                                        ({{ optional($device->last_used_at)->format('d M Y, H:i') }})
                                    @endif
                                </div>
                            </div>
                        </div>
                        </div>

                        <div class="ddm-card">
                        <div class="ddm-section-title">Recent Access History</div>
                        @if ($recentAccess->isEmpty())
                            <p class="ddm-muted" style="font-size: 13px;">No access recorded yet.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm ddm-history-table">
                                    <thead>
                                        <tr>
                                            <th>Date &amp; Time</th>
                                            <th>IP</th>
                                            <th>Location</th>
                                            <th>ISP</th>
                                            <th>Network</th>
                                            <th>Result</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($recentAccess as $log)
                                            <tr>
                                                <td>{{ optional($log->accessed_at)->format('d M Y, H:i') }}</td>
                                                <td>{{ $log->ip ?: '-' }}</td>
                                                <td>{{ trim(collect([$log->city, $log->country])->filter()->implode(', ')) ?: '-' }}</td>
                                                <td>{{ $log->isp ?: '-' }}</td>
                                                <td>{{ $log->network_label ?: '-' }}</td>
                                                <td>
                                                    @if ($log->result == 'success')
                                                        <span class="ddm-result-ok"><i class="fas fa-check-circle"></i> Success</span>
                                                    @else
                                                        <span class="ddm-result-fail"><i class="fas fa-times-circle"></i> Failed</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="ddm-section-title">Available Actions</div>

                        @if ($device->status == 'pending')
                            <button type="button" class="ddm-action ddm-action-amber"
                                onclick="resendDeviceOtp({{ $device->id }}, 'deviceDetailsModal{{ $device->id }}')">
                                <i class="fas fa-envelope"></i>
                                <span>
                                    <strong>Resend OTP</strong>
                                    <small>Send a new verification code to the user.</small>
                                </span>
                            </button>
                        @endif

                        <form action="{{ route('admin.tender.devices.reset') }}" method="POST">
                            @csrf
                            <input type="hidden" name="device_id" value="{{ $device->id }}">
                            <button type="submit" class="ddm-action ddm-action-amber"
                                onclick="return confirm('Reset this device? It will need a new email code to access this order again.')">
                                <i class="fas fa-sync-alt"></i>
                                <span>
                                    <strong>Reset Device</strong>
                                    <small>Remove this device from the recognized list.</small>
                                </span>
                            </button>
                        </form>

                        @if ($device->status != 'revoked')
                            <form action="{{ route('admin.tender.devices.revoke') }}" method="POST">
                                @csrf
                                <input type="hidden" name="device_id" value="{{ $device->id }}">
                                <button type="submit" class="ddm-action ddm-action-red"
                                    onclick="return confirm('Revoke access from this device? Only an admin can undo this.')">
                                    <i class="fas fa-ban"></i>
                                    <span>
                                        <strong>Revoke Access</strong>
                                        <small>Block access from this device.</small>
                                    </span>
                                </button>
                            </form>
                        @endif

                        <form action="{{ route('admin.tender.devices.destroy') }}" method="POST">
                            @csrf
                            <input type="hidden" name="device_id" value="{{ $device->id }}">
                            <button type="submit" class="ddm-action ddm-action-red"
                                onclick="return confirm('Permanently delete this device and its access history? This cannot be undone.')">
                                <i class="fas fa-trash"></i>
                                <span>
                                    <strong>Delete</strong>
                                    <small>Permanently remove this device from the list.</small>
                                </span>
                            </button>
                        </form>

                        <div class="ddm-section-title mt-4">Additional Information</div>

                        <div class="ddm-note ddm-note-blue">
                            <i class="fas fa-info-circle"></i>
                            <div>
                                <strong>About this device</strong>
                                <span>
                                    @if ($device->status == 'active')
                                        This device is recognized and can access downloads without an OTP, even if the network or IP address changes.
                                    @elseif ($device->status == 'pending')
                                        This device is waiting on email OTP verification before it can access downloads.
                                    @else
                                        Access from this device has been blocked. It needs to be reset before it can be used again.
                                    @endif
                                </span>
                            </div>
                        </div>

                        <div class="ddm-section-title mt-3" style="color: inherit; font-weight: 700;">Security Tips</div>
                        <div class="ddm-note ddm-note-green">
                            <i class="fas fa-check-circle"></i>
                            <span>Revoke access if this device is lost or no longer used.</span>
                        </div>
                        <div class="ddm-note ddm-note-green">
                            <i class="fas fa-check-circle"></i>
                            <span>Reset the device if the user runs into access issues.</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
    .ddm-head { display: flex; gap: 16px; align-items: flex-start; margin-bottom: 22px; }
    .ddm-head-icon {
        flex-shrink: 0; width: 56px; height: 56px; border-radius: 12px;
        background: rgba(21, 114, 232, .14); color: #1572E8;
        display: flex; align-items: center; justify-content: center; font-size: 22px;
    }
    .ddm-head-title { font-size: 19px; font-weight: 700; margin: 2px 0 8px; }
    .ddm-head-sub { font-size: 12px; opacity: .6; margin-top: 6px; }
    .ddm-badge {
        display: inline-flex; align-items: center; gap: 6px;
        font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 20px;
        margin-right: 6px; text-transform: uppercase; letter-spacing: .03em;
    }
    .ddm-badge-active { background: rgba(49, 206, 54, .15); color: #31CE36; }
    .ddm-badge-pending { background: rgba(255, 173, 70, .15); color: #FFAD46; }
    .ddm-badge-revoked { background: rgba(242, 89, 97, .15); color: #F25961; }
    .ddm-badge-primary { background: rgba(21, 114, 232, .15); color: #1572E8; }

    .ddm-section-title {
        font-size: 12px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase;
        color: #1572E8; margin-bottom: 14px;
    }
    .ddm-card {
        background: rgba(255, 255, 255, .025);
        border: 1px solid rgba(255, 255, 255, .07);
        border-radius: 12px;
        padding: 18px 20px;
    }

    .ddm-info-list { display: flex; flex-direction: column; }
    .ddm-info-row {
        display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;
        padding: 9px 0; border-bottom: 1px solid rgba(255, 255, 255, .06); font-size: 13px;
    }
    .ddm-info-row:last-child { border-bottom: none; }
    .ddm-info-label { opacity: .6; display: flex; align-items: center; gap: 8px; flex-shrink: 0; min-width: 40%; }
    .ddm-info-label i { width: 14px; text-align: center; font-size: 12px; }
    .ddm-info-value { text-align: right; font-weight: 600; }
    .ddm-muted { opacity: .6; font-weight: 400; }

    .ddm-dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; margin-right: 4px; }
    .ddm-dot-active { background: #31CE36; }
    .ddm-dot-pending { background: #FFAD46; }
    .ddm-dot-revoked { background: #F25961; }

    .ddm-history-table { font-size: 12.5px; }
    .ddm-result-ok { color: #31CE36; font-weight: 600; }
    .ddm-result-fail { color: #F25961; font-weight: 600; }

    .ddm-action {
        display: flex; align-items: flex-start; gap: 12px; width: 100%; text-align: left;
        border-radius: 10px; padding: 12px 14px; margin-bottom: 10px; cursor: pointer;
        background: transparent; transition: background .15s;
    }
    .ddm-action i { font-size: 16px; margin-top: 2px; flex-shrink: 0; }
    .ddm-action span { display: flex; flex-direction: column; gap: 2px; }
    .ddm-action strong { font-size: 13.5px; }
    .ddm-action small { font-size: 11.5px; opacity: .75; font-weight: 400; line-height: 1.4; }
    .ddm-action-amber {
        border: 1px solid rgba(255, 173, 70, .35); background: rgba(255, 173, 70, .08); color: #FFAD46;
    }
    .ddm-action-amber:hover { background: rgba(255, 173, 70, .16); }
    .ddm-action-red {
        border: 1px solid rgba(242, 89, 97, .35); background: rgba(242, 89, 97, .08); color: #F25961;
    }
    .ddm-action-red:hover { background: rgba(242, 89, 97, .16); }

    .ddm-note {
        display: flex; align-items: flex-start; gap: 10px; border-radius: 10px;
        padding: 12px 14px; margin-bottom: 10px; font-size: 12.5px; line-height: 1.5;
    }
    .ddm-note i { margin-top: 2px; flex-shrink: 0; }
    .ddm-note-blue { background: rgba(21, 114, 232, .08); border: 1px solid rgba(21, 114, 232, .25); color: inherit; }
    .ddm-note-blue i { color: #1572E8; }
    .ddm-note-blue strong { display: block; color: #1572E8; margin-bottom: 2px; font-size: 13px; }
    .ddm-note-green { background: rgba(49, 206, 54, .08); border: 1px solid rgba(49, 206, 54, .25); color: inherit; }
    .ddm-note-green i { color: #31CE36; }
</style>
