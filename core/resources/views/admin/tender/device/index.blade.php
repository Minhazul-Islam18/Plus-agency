@extends('admin.layout')

@section('content')
    <style>
        /* .table-responsive's overflow-x:auto forces overflow-y to also
           clip (can't have one axis auto and the other visible per CSS
           spec), which cuts off the per-row actions dropdown's menu even
           with data-boundary="window" on the toggle. This table doesn't
           need horizontal scrolling at normal admin panel widths, so drop
           the clipping outright rather than fight it. */
        #devicesTableWrap {
            overflow: visible;
        }
    </style>
    <div class="page-header">
        <h4 class="page-title">Authorized Devices</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('admin.dashboard') }}"><i class="flaticon-home"></i></a>
            </li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="#">Tenders</a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="#">Authorized Devices</a></li>
        </ul>
    </div>

    <div class="row">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="mr-3 text-primary"><i class="fas fa-desktop fa-2x"></i></div>
                    <div>
                        <div class="h4 mb-0">{{ $stats['total'] }}</div>
                        <div class="text-muted" style="font-size: 12px;">Total devices</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="mr-3 text-success"><i class="fas fa-check-circle fa-2x"></i></div>
                    <div>
                        <div class="h4 mb-0">{{ $stats['active'] }}</div>
                        <div class="text-muted" style="font-size: 12px;">Active</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="mr-3 text-warning"><i class="fas fa-clock fa-2x"></i></div>
                    <div>
                        <div class="h4 mb-0">{{ $stats['pending'] }}</div>
                        <div class="text-muted" style="font-size: 12px;">Pending OTP</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="mr-3 text-danger"><i class="fas fa-ban fa-2x"></i></div>
                    <div>
                        <div class="h4 mb-0">{{ $stats['revoked'] }}</div>
                        <div class="text-muted" style="font-size: 12px;">Revoked</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title d-inline-block">Authorized Devices</div>
                    <div class="text-muted" style="font-size: 12px;">
                        Devices/browsers recognized for secure download links. Manage access or reset if needed.
                    </div>
                </div>

                <div class="card-body border-bottom pb-3 mb-3">
                    <form action="{{ route('admin.tender.devices') }}" method="GET" class="row align-items-end">
                        <div class="form-group col-md-4 col-lg-4 mb-2">
                            <label class="small text-muted mb-1">Search</label>
                            <input name="q" type="text" class="form-control form-control-sm"
                                placeholder="Device, order no., user..." value="{{ request('q') }}">
                        </div>
                        <div class="form-group col-md-3 col-lg-2 mb-2">
                            <label class="small text-muted mb-1">Status</label>
                            <select name="status" class="form-control form-control-sm">
                                <option value="">All statuses</option>
                                <option value="active" @if (request('status') == 'active') selected @endif>Active</option>
                                <option value="pending" @if (request('status') == 'pending') selected @endif>Pending OTP</option>
                                <option value="revoked" @if (request('status') == 'revoked') selected @endif>Revoked</option>
                            </select>
                        </div>
                        <div class="form-group col-md-5 col-lg-6 mb-2 d-flex">
                            <button type="submit" class="btn btn-primary btn-sm mr-2">
                                <i class="fas fa-filter mr-1"></i> Apply
                            </button>
                            @if (request()->anyFilled(['q', 'status']))
                                <a href="{{ route('admin.tender.devices') }}" class="btn btn-outline-secondary btn-sm mr-2">Clear</a>
                            @endif
                            <button type="button" class="btn btn-success btn-sm ml-auto" data-toggle="modal"
                                data-target="#addDeviceModal">
                                <i class="fas fa-plus mr-1"></i> Add Manually
                            </button>
                        </div>
                    </form>
                </div>

                <div class="card-body pt-0">
                    @if ($devices->count() == 0)
                        <h3 class="text-center">NO DEVICES FOUND</h3>
                    @else
                        <div class="table-responsive" id="devicesTableWrap">
                            <table class="table table-striped mt-3">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Device</th>
                                        <th>Browser</th>
                                        <th>Network / IP (last access)</th>
                                        <th>User</th>
                                        <th>Status</th>
                                        <th>Last Access</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($devices as $i => $device)
                                        @php $purchase = $purchases->get($device->order_id); @endphp
                                        <tr>
                                            <td>{{ $devices->firstItem() + $i }}</td>
                                            <td>
                                                <i class="fas {{ $device->device_type == 'mobile' ? 'fa-mobile-alt' : ($device->device_type == 'tablet' ? 'fa-tablet-alt' : 'fa-desktop') }} mr-1"></i>
                                                {{ $device->device_label ?: 'Unknown device' }}
                                                @if ($device->is_primary)
                                                    <span class="badge badge-info">Primary</span>
                                                @endif
                                                <br><small class="text-muted">{{ $device->order_id }}</small>
                                            </td>
                                            <td>
                                                @if ($device->browser_name)
                                                    <i class="{{ \App\TenderDeviceRegistration::browserIcon($device->browser_name) }} mr-1"
                                                        style="color: {{ \App\TenderDeviceRegistration::browserColor($device->browser_name) }};"></i>
                                                    {{ $device->browser_name }}
                                                @else
                                                    -
                                                @endif
                                                @if ($device->browser_version)
                                                    <br><small class="text-muted">v{{ $device->browser_version }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $device->network_label ?: '-' }}
                                                <br><small class="text-muted">{{ $device->ip ?: '-' }}</small>
                                            </td>
                                            <td>
                                                @if ($purchase)
                                                    {{ trim($purchase->first_name . ' ' . $purchase->last_name) }}
                                                    <br><small class="text-muted">{{ $purchase->email }}</small>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($device->status == 'active')
                                                    <span class="badge badge-success">Active</span>
                                                @elseif ($device->status == 'pending')
                                                    <span class="badge badge-warning">Pending OTP</span>
                                                @else
                                                    <span class="badge badge-danger">Revoked</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ optional($device->last_used_at)->format('d M Y, H:i') ?: '-' }}
                                                <br><small class="text-muted">{{ optional($device->last_used_at)->diffForHumans() }}</small>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-outline-primary btn-sm" data-toggle="modal"
                                                    data-target="#deviceDetailsModal{{ $device->id }}">
                                                    <i class="fas fa-eye"></i> Details
                                                </button>
                                                <div class="dropdown d-inline-block">
                                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button"
                                                        data-toggle="dropdown" data-boundary="window">...</button>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        @if ($device->status == 'pending')
                                                            <button type="button" class="dropdown-item text-warning"
                                                                onclick="resendDeviceOtp({{ $device->id }})">
                                                                <i class="fas fa-envelope mr-1"></i> Resend OTP
                                                            </button>
                                                        @endif
                                                        <form action="{{ route('admin.tender.devices.reset') }}" method="POST">
                                                            @csrf
                                                            <input type="hidden" name="device_id" value="{{ $device->id }}">
                                                            <button type="submit" class="dropdown-item text-info"
                                                                onclick="return confirm('Reset this device? It will need a new email code to access this order again.')">
                                                                <i class="fas fa-sync-alt mr-1"></i> Reset Device
                                                            </button>
                                                        </form>
                                                        @if ($device->status != 'revoked')
                                                            <form action="{{ route('admin.tender.devices.revoke') }}" method="POST">
                                                                @csrf
                                                                <input type="hidden" name="device_id" value="{{ $device->id }}">
                                                                <button type="submit" class="dropdown-item text-danger"
                                                                    onclick="return confirm('Revoke access from this device? Only an admin can undo this.')">
                                                                    <i class="fas fa-ban mr-1"></i> Revoke Access
                                                                </button>
                                                            </form>
                                                        @endif
                                                        <form action="{{ route('admin.tender.devices.destroy') }}" method="POST">
                                                            @csrf
                                                            <input type="hidden" name="device_id" value="{{ $device->id }}">
                                                            <button type="submit" class="dropdown-item text-danger"
                                                                onclick="return confirm('Permanently delete this device and its access history? This cannot be undone.')">
                                                                <i class="fas fa-trash mr-1"></i> Delete
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end mt-3">
                            {{ $devices->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-lg-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="font-weight-bold">Available Actions</h6>
                            <p style="font-size: 13px;"><span class="text-warning font-weight-bold">Resend OTP</span> — send a new verification code to the user.</p>
                            <p style="font-size: 13px;"><span class="text-info font-weight-bold">Reset Device</span> — remove this device from the recognized list; it needs a new OTP to access again.</p>
                            <p style="font-size: 13px;"><span class="text-danger font-weight-bold">Revoke Access</span> — block access from this device outright. Only an admin can undo this.</p>
                            <p class="mb-0" style="font-size: 13px;"><span class="text-danger font-weight-bold">Delete</span> — permanently remove the device and its access history.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="font-weight-bold"><i class="fas fa-shield-alt text-primary mr-1"></i> Security Rule</h6>
                            <ul class="pl-3 mb-0" style="font-size: 13px;">
                                <li>Recognized devices stay authorized even if the network, IP address, or WiFi changes.</li>
                                <li>An OTP is required only for a new, not-yet-recognized device or browser.</li>
                                <li>Once the OTP is validated, the device is registered and authorized automatically.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Add Device modal --}}
    <div class="modal fade" id="addDeviceModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <form action="{{ route('admin.tender.devices.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Device Manually</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted" style="font-size: 12px;">
                            Skips the OTP step — the device is authorized immediately. Use for support cases where the
                            buyer can't complete email verification themself.
                        </p>
                        <div class="form-group">
                            <label>Order Number</label>
                            <input type="text" class="form-control" name="order_id" required>
                        </div>
                        <div class="form-group mb-0">
                            <label>Device Label</label>
                            <input type="text" class="form-control" name="device_label" placeholder="e.g. Chrome on Windows" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Add &amp; Authorize</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @foreach ($devices as $device)
        @includeIf('admin.tender.device.details-modal')
    @endforeach

    {{-- Shared "Enter Verification Code" modal — one instance reused for
         whichever device's Resend OTP was just clicked (see resendDeviceOtp
         below). Lets the admin type back a code the customer reads out over
         phone/chat, completing device recognition without the customer
         clicking any link themself. --}}
    <div class="modal fade" id="deviceOtpModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-envelope mr-2"></i> Enter Verification Code</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-muted" id="deviceOtpSentText" style="font-size: 13px;">
                        A verification code was sent to the customer's email.
                    </p>
                    <div class="form-group">
                        <label>6-Digit Code</label>
                        <input type="text" class="form-control text-center" id="deviceOtpInput"
                            inputmode="numeric" maxlength="6" placeholder="000000"
                            style="font-size: 22px; letter-spacing: 0.3em; font-weight: 700;">
                    </div>
                    <div class="text-danger" id="deviceOtpError" style="font-size: 13px; min-height: 18px;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-outline-warning" id="deviceOtpResendBtn">Resend</button>
                    <button type="button" class="btn btn-success" id="deviceOtpVerifyBtn">Verify &amp; Authorize</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var currentDeviceId = null;

            function csrfToken() {
                return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            }

            function post(url, data) {
                return fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': csrfToken(),
                        'Accept': 'application/json',
                    },
                    body: Object.keys(data).map(function (k) { return k + '=' + encodeURIComponent(data[k]); }).join('&'),
                }).then(function (r) { return r.json(); });
            }

            window.resendDeviceOtp = function (deviceId, fromModalId) {
                currentDeviceId = deviceId;
                var openOtpModal = function () {
                    document.getElementById('deviceOtpInput').value = '';
                    document.getElementById('deviceOtpError').textContent = '';
                    document.getElementById('deviceOtpSentText').textContent = 'Sending verification code…';
                    $('#deviceOtpModal').modal('show');

                    post(@json(route('admin.tender.devices.resendOtp')), { device_id: deviceId })
                        .then(function (data) {
                            if (data.status === 'success') {
                                document.getElementById('deviceOtpSentText').textContent =
                                    'A verification code was sent to ' + data.masked_email + '. Valid for ' + data.ttl_minutes + ' minutes.';
                            } else {
                                document.getElementById('deviceOtpError').textContent = data.message || 'Could not send the code.';
                            }
                        })
                        .catch(function () {
                            document.getElementById('deviceOtpError').textContent = 'Network error. Please try again.';
                        });
                };

                if (fromModalId) {
                    var $from = $('#' + fromModalId);
                    $from.one('hidden.bs.modal', openOtpModal);
                    $from.modal('hide');
                } else {
                    openOtpModal();
                }
            };

            document.getElementById('deviceOtpResendBtn').addEventListener('click', function () {
                if (currentDeviceId) window.resendDeviceOtp(currentDeviceId);
            });

            document.getElementById('deviceOtpVerifyBtn').addEventListener('click', function () {
                var code = document.getElementById('deviceOtpInput').value.trim();
                var errorEl = document.getElementById('deviceOtpError');
                if (!/^\d{6}$/.test(code)) {
                    errorEl.textContent = 'Enter the 6-digit code.';
                    return;
                }
                errorEl.textContent = '';

                post(@json(route('admin.tender.devices.verifyOtp')), { device_id: currentDeviceId, otp_code: code })
                    .then(function (data) {
                        if (data.status === 'success') {
                            window.location.reload();
                            return;
                        }
                        var messages = {
                            invalid_code: 'Incorrect code. Please try again.',
                            expired: 'This code has expired. Click Resend.',
                            exhausted: 'Too many incorrect attempts. Click Resend for a new code.',
                        };
                        errorEl.textContent = messages[data.type] || 'Could not verify. Please try again.';
                    })
                    .catch(function () {
                        errorEl.textContent = 'Network error. Please try again.';
                    });
            });
        })();
    </script>
@endsection
