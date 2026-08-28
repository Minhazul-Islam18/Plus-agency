@extends('admin.layout')

@section('content')
    @php
        $validatedEvent = $row->action === 'validated'
            ? $row
            : $history->where('action', 'validated')->where('created_at', '<=', $row->created_at)->last();

        $fileName = $row->proof_original_name ?: optional($validatedEvent)->proof_original_name;
        $fileSize = $row->proof_size ?: optional($validatedEvent)->proof_size;
        $filePath = $row->proof_path ?: optional($validatedEvent)->proof_path;
        $ext      = strtolower(pathinfo($fileName ?? $filePath ?? '', PATHINFO_EXTENSION));
        $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'];
        $purchase = $row->purchase;
    @endphp

    <div class="page-header">
        <h4 class="page-title">Payment Evidence Details</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('admin.dashboard') }}"><i class="flaticon-home"></i></a>
            </li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="{{ route('admin.tender.paymentEvidence') }}">Payment Evidence</a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="#">{{ $row->order_number }}</a></li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12 mb-3">
            <a href="{{ route('admin.tender.paymentEvidence') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> Back to list
            </a>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="window.print()">
                <i class="fas fa-print mr-1"></i> Print
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="card mb-4">
                <div class="card-header"><div class="card-title">Order Information</div></div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 text-muted">Order No.</div>
                        <div class="col-md-8"><strong>{{ $row->order_number }}</strong></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 text-muted">Tender Document</div>
                        <div class="col-md-8">
                            @if ($purchase && $purchase->tender)
                                <a href="{{ route('admin.tender.edit', $purchase->tender->id) }}">{{ $purchase->tender->tender_code }}</a>
                                <br><small>{{ convertUtf8($purchase->tender->title) }}</small>
                            @else
                                -
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 text-muted">User</div>
                        <div class="col-md-8">
                            @if ($purchase)
                                {{ trim($purchase->first_name . ' ' . $purchase->last_name) }}
                                <br><small class="text-muted">{{ $purchase->email }}</small>
                            @else
                                -
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 text-muted">Amount</div>
                        <div class="col-md-8">{{ number_format($row->amount, 2) }} {{ $row->currency_code }}</div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-muted">Event Date</div>
                        <div class="col-md-8">{{ optional($row->created_at)->format('d M Y \a\t H:i') }}</div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header"><div class="card-title">Evidence Information</div></div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 text-muted">File Name</div>
                        <div class="col-md-8">{{ $fileName ?: '-' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 text-muted">File Type</div>
                        <div class="col-md-8">
                            @if ($ext)
                                <i class="fas {{ $ext == 'pdf' ? 'fa-file-pdf text-danger' : (in_array($ext, $imageExts) ? 'fa-file-image text-success' : 'fa-file-alt text-secondary') }} mr-1"></i>
                                {{ strtoupper($ext) }}@if ($fileSize) ({{ round($fileSize / 1024, 1) }} KB) @endif
                            @else
                                -
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 text-muted">Uploaded By</div>
                        <div class="col-md-8">
                            @if ($validatedEvent)
                                {{ $validatedEvent->admin_name }}
                                @if (optional($validatedEvent->admin)->email)
                                    <small class="text-muted">({{ $validatedEvent->admin->email }})</small>
                                @endif
                            @else
                                -
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-muted">Upload Date</div>
                        <div class="col-md-8">{{ $validatedEvent ? optional($validatedEvent->created_at)->format('d M Y \a\t H:i') : '-' }}</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><div class="card-title">Status &amp; Validation</div></div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 text-muted">Status</div>
                        <div class="col-md-8">
                            @if ($row->action == 'validated')
                                <span class="badge badge-success">Validated</span>
                            @else
                                <span class="badge badge-danger">Canceled</span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 text-muted">Validated By</div>
                        <div class="col-md-8">{{ $validatedEvent->admin_name ?? '-' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 text-muted">Validation Date</div>
                        <div class="col-md-8">{{ $validatedEvent ? optional($validatedEvent->created_at)->format('d M Y \a\t H:i') : '-' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 text-muted">Cancellation Date</div>
                        <div class="col-md-8">{{ $row->action == 'canceled' ? optional($row->created_at)->format('d M Y \a\t H:i') : '-' }}</div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-muted">Cancellation Reason</div>
                        <div class="col-md-8">{{ $row->action == 'canceled' ? $row->reason : '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card mb-4">
                <div class="card-header"><div class="card-title">Payment Evidence</div></div>
                <div class="card-body">
                    @if ($filePath)
                        <div class="border rounded p-3 text-center mb-3">
                            @if (in_array($ext, $imageExts))
                                <img src="{{ asset('assets/front/tender_proofs/' . $filePath) }}"
                                    style="max-width: 100%; max-height: 400px; object-fit: contain;">
                            @else
                                <div class="py-5">
                                    <i class="fas {{ $ext == 'pdf' ? 'fa-file-pdf text-danger' : 'fa-file-alt text-secondary' }}"
                                        style="font-size: 72px;"></i>
                                    <p class="mt-2 mb-0">{{ $fileName ?: $filePath }}</p>
                                </div>
                            @endif
                        </div>
                        <div class="d-flex">
                            <a href="{{ asset('assets/front/tender_proofs/' . $filePath) }}" download
                                class="btn btn-outline-secondary btn-sm mr-2">
                                <i class="fas fa-download mr-1"></i> Download
                            </a>
                            <a href="{{ asset('assets/front/tender_proofs/' . $filePath) }}" target="_blank"
                                class="btn btn-primary btn-sm">
                                <i class="fas fa-external-link-alt mr-1"></i> Open in new tab
                            </a>
                        </div>
                    @else
                        <p class="text-muted mb-0">No evidence file on record.</p>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header"><div class="card-title">Action History</div></div>
                <div class="card-body">
                    <style>
                        .evidence-timeline { list-style: none; margin: 0; padding: 0; }
                        .evidence-timeline li { position: relative; padding-left: 26px; padding-bottom: 22px; }
                        .evidence-timeline li:last-child { padding-bottom: 0; }
                        .evidence-timeline li::before {
                            content: ''; position: absolute; left: 0; top: 3px;
                            width: 12px; height: 12px; border-radius: 50%;
                            background: var(--dot-color, #6c757d); z-index: 1;
                        }
                        .evidence-timeline li::after {
                            content: ''; position: absolute; left: 5px; top: 15px; bottom: -6px;
                            width: 2px; background: rgba(181, 181, 181, 0.2);
                        }
                        .evidence-timeline li:last-child::after { display: none; }
                    </style>
                    <ul class="evidence-timeline">
                        @foreach ($history as $event)
                            @if ($event->action == 'validated')
                                {{-- Mark as Paid uploads the proof and validates the
                                     payment in one single action here (no separate
                                     upload step like the offline-receipt flow), so
                                     both events share this row's own timestamp. --}}
                                <li style="--dot-color: #28a745;">
                                    <strong>Evidence Uploaded</strong>
                                    <div class="text-muted" style="font-size: 12px;">
                                        {{ optional($event->created_at)->format('d M Y \a\t H:i') }} by {{ $event->admin_name }}
                                    </div>
                                    @if ($event->proof_path)
                                        <div style="font-size: 12px;">
                                            <i class="fas fa-paperclip mr-1"></i>
                                            <a href="{{ asset('assets/front/tender_proofs/' . $event->proof_path) }}" target="_blank">
                                                {{ $event->proof_original_name ?: $event->proof_path }}
                                            </a>
                                        </div>
                                    @endif
                                </li>
                                <li style="--dot-color: #28a745;">
                                    <strong>Payment Validated</strong>
                                    <div class="text-muted" style="font-size: 12px;">
                                        {{ optional($event->created_at)->format('d M Y \a\t H:i') }} by {{ $event->admin_name }}
                                    </div>
                                </li>
                            @else
                                <li style="--dot-color: #dc3545;">
                                    <strong>Payment Canceled</strong>
                                    <div class="text-muted" style="font-size: 12px;">
                                        {{ optional($event->created_at)->format('d M Y \a\t H:i') }} by {{ $event->admin_name }}
                                    </div>
                                    @if ($event->reason)
                                        <div style="font-size: 12px;">Reason: {{ $event->reason }}</div>
                                    @endif
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
