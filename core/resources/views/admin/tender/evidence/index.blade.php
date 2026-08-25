@extends('admin.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">Payment Evidence Tracking</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('admin.dashboard') }}"><i class="flaticon-home"></i></a>
            </li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="#">Tenders</a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="#">Payment Evidence</a></li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-info d-flex align-items-center" role="alert">
                <i class="fas fa-shield-alt mr-2"></i>
                <div>
                    <strong>No evidence can be deleted.</strong>
                    Payment evidence is permanent and cannot be modified.
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title d-inline-block">Payment Evidence</div>
                    <div class="text-muted" style="font-size: 12px;">
                        Complete and immutable history of payment evidence from the admin panel for tender documents.
                    </div>
                </div>

                <div class="card-body border-bottom pb-3 mb-3">
                    <form action="{{ route('admin.tender.paymentEvidence') }}" method="GET" class="row align-items-end">
                        <div class="form-group col-md-3 col-lg-2 mb-2">
                            <label class="small text-muted mb-1">From</label>
                            <input type="date" name="from" class="form-control form-control-sm"
                                value="{{ request('from') }}">
                        </div>
                        <div class="form-group col-md-3 col-lg-2 mb-2">
                            <label class="small text-muted mb-1">To</label>
                            <input type="date" name="to" class="form-control form-control-sm"
                                value="{{ request('to') }}">
                        </div>
                        <div class="form-group col-md-3 col-lg-2 mb-2">
                            <label class="small text-muted mb-1">Status</label>
                            <select name="status" class="form-control form-control-sm">
                                <option value="">All statuses</option>
                                <option value="validated" @if (request('status') == 'validated') selected @endif>
                                    Validated</option>
                                <option value="canceled" @if (request('status') == 'canceled') selected @endif>
                                    Canceled</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4 col-lg-3 mb-2">
                            <label class="small text-muted mb-1">Search</label>
                            <input name="q" type="text" class="form-control form-control-sm"
                                placeholder="Order No., user, tender..." value="{{ request('q') }}">
                        </div>
                        <div class="form-group col-md-12 col-lg-3 mb-2 d-flex">
                            <button type="submit" class="btn btn-primary btn-sm mr-2">
                                <i class="fas fa-filter mr-1"></i> Apply
                            </button>
                            @if (request()->anyFilled(['from', 'to', 'status', 'q']))
                                <a href="{{ route('admin.tender.paymentEvidence') }}" class="btn btn-outline-secondary btn-sm mr-2">
                                    Clear
                                </a>
                            @endif
                            @if ($evidences->total() > 0)
                                <div class="dropdown ml-auto">
                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button"
                                        id="exportDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fas fa-file-export mr-1"></i> Export
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="exportDropdown">
                                        <a class="dropdown-item"
                                            href="{{ route('admin.tender.paymentEvidence.exportCsv', request()->query()) }}">
                                            <i class="fas fa-file-csv mr-1"></i> Excel / CSV
                                        </a>
                                        <a class="dropdown-item"
                                            href="{{ route('admin.tender.paymentEvidence.exportWord', request()->query()) }}">
                                            <i class="fas fa-file-word mr-1"></i> Word
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="card-body pt-0">
                    <div class="row">
                        <div class="col-lg-12">
                            @if ($evidences->count() == 0)
                                <h3 class="text-center">NO EVIDENCE FOUND</h3>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-striped mt-3">
                                        <thead>
                                            <tr>
                                                <th scope="col">Order No.</th>
                                                <th scope="col">User</th>
                                                <th scope="col">Tender Document</th>
                                                <th scope="col">Amount</th>
                                                <th scope="col">Payment Date</th>
                                                <th scope="col">Payment Evidence</th>
                                                <th scope="col">Status</th>
                                                <th scope="col">Validated By</th>
                                                <th scope="col">Cancellation Date</th>
                                                <th scope="col">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($evidences as $row)
                                                @php
                                                    $purchase = $row->purchase;
                                                    $ext = strtolower(pathinfo($row->proof_original_name ?? $row->proof_path ?? '', PATHINFO_EXTENSION));
                                                @endphp
                                                <tr>
                                                    <td>{{ $row->order_number }}</td>
                                                    <td>
                                                        @if ($purchase)
                                                            {{ trim($purchase->first_name . ' ' . $purchase->last_name) }}
                                                            <br>
                                                            <small class="text-muted">{{ $purchase->email }}</small>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($purchase && $purchase->tender)
                                                            <a href="{{ route('admin.tender.edit', $purchase->tender->id) }}">
                                                                {{ $purchase->tender->tender_code }}
                                                            </a>
                                                            <br>
                                                            @php $tTitle = convertUtf8($purchase->tender->title); @endphp
                                                            @if (mb_strlen($tTitle, 'utf-8') > 30)
                                                                <small>
                                                                    <span class="tender-title-short">{{ mb_substr($tTitle, 0, 30, 'utf-8') }}&hellip;</span><span class="tender-title-full d-none">{{ $tTitle }}</span>
                                                                </small>
                                                                <br>
                                                                <a href="javascript:void(0)" class="tender-title-toggle" style="font-size: 11px; white-space: nowrap;">Show more</a>
                                                            @else
                                                                <small>{{ $tTitle }}</small>
                                                            @endif
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ number_format($row->amount, 2) }} {{ $row->currency_code }}</td>
                                                    <td>{{ optional($row->created_at)->format('d M Y, H:i') }}</td>
                                                    <td>
                                                        @if ($row->proof_path)
                                                            <i class="fas {{ $ext == 'pdf' ? 'fa-file-pdf text-danger' : 'fa-file-image text-success' }} mr-1"></i>
                                                            <a href="{{ asset('assets/front/tender_proofs/' . $row->proof_path) }}"
                                                                target="_blank">{{ $row->proof_original_name ?: $row->proof_path }}</a>
                                                            @if ($row->proof_size)
                                                                <small class="text-muted">({{ round($row->proof_size / 1024, 1) }} KB)</small>
                                                            @endif
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($row->action == 'validated')
                                                            <span class="badge badge-success">Validated</span>
                                                        @else
                                                            <span class="badge badge-danger">Canceled</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($row->action == 'validated')
                                                            {{ $row->admin_name ?: '-' }}
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($row->action == 'canceled')
                                                            {{ optional($row->created_at)->format('d M Y, H:i') }}
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.tender.paymentEvidence.show', $row->id) }}"
                                                            class="btn btn-outline-primary btn-sm">
                                                            <i class="fas fa-eye"></i> View
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="d-flex justify-content-end mt-3">
                                    {{ $evidences->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .tender-title-full {
            word-break: break-word;
            white-space: normal;
        }
    </style>
    <script>
        document.addEventListener('click', function(e) {
            var toggle = e.target.closest('.tender-title-toggle');
            if (!toggle) return;

            var cell = toggle.closest('td');
            var short = cell.querySelector('.tender-title-short');
            var full = cell.querySelector('.tender-title-full');
            var expanded = !full.classList.contains('d-none');

            short.classList.toggle('d-none', !expanded);
            full.classList.toggle('d-none', expanded);
            toggle.textContent = expanded ? 'Show more' : 'Show less';
        });
    </script>
@endsection
