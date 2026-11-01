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
                                                    $uName = $purchase ? trim($purchase->first_name . ' ' . $purchase->last_name) : '';
                                                    $aName = (string) $row->admin_name;
                                                @endphp
                                                <tr>
                                                    <td><span class="pe-order">{{ $row->order_number }}</span></td>
                                                    <td>
                                                        @if ($purchase)
                                                            <div class="pe-person">
                                                                <span class="pe-avatar">{{ mb_strtoupper(mb_substr($uName ?: '?', 0, 1, 'utf-8'), 'utf-8') }}</span>
                                                                <span class="pe-person-text">
                                                                    <span class="pe-strong">{{ $uName }}</span>
                                                                    <a href="mailto:{{ $purchase->email }}" class="pe-sub">{{ $purchase->email }}</a>
                                                                </span>
                                                            </div>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($purchase && $purchase->tender)
                                                            <a href="{{ route('admin.tender.edit', $purchase->tender->id) }}" class="pe-code">
                                                                {{ $purchase->tender->tender_code }}
                                                            </a>
                                                            @php $tTitle = convertUtf8($purchase->tender->title); @endphp
                                                            @if (mb_strlen($tTitle, 'utf-8') > 30)
                                                                <span class="pe-sub pe-title">
                                                                    <span class="tender-title-short">{{ mb_substr($tTitle, 0, 30, 'utf-8') }}&hellip;</span><span class="tender-title-full d-none">{{ $tTitle }}</span>
                                                                    <a href="javascript:void(0)" class="tender-title-toggle">Show more</a>
                                                                </span>
                                                            @else
                                                                <span class="pe-sub pe-title">{{ $tTitle }}</span>
                                                            @endif
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="pe-amount">{{ number_format($row->amount, 2) }}</span>
                                                        <span class="pe-cur">{{ $row->currency_code }}</span>
                                                    </td>
                                                    <td>
                                                        @if ($row->created_at)
                                                            <span class="pe-strong pe-nowrap">{{ $row->created_at->format('d M Y') }}</span>
                                                            <span class="pe-sub"><i class="far fa-clock"></i> {{ $row->created_at->format('H:i') }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($row->proof_path)
                                                            @php
                                                                $fname = $row->proof_original_name ?: $row->proof_path;
                                                                $fnameDisplay = $fname;
                                                                if (mb_strlen($fname, 'utf-8') > 24) {
                                                                    // Shorten from the middle, not the end — the
                                                                    // extension (and any distinguishing suffix) at
                                                                    // the tail stays visible, unlike a plain "...".
                                                                    $fnameDisplay = mb_substr($fname, 0, 12, 'utf-8') . '…' . mb_substr($fname, -8, null, 'utf-8');
                                                                }
                                                                // Original names can lack an extension — fall back to the stored file's.
                                                                $fext = $ext ?: strtolower(pathinfo($row->proof_path, PATHINFO_EXTENSION));
                                                                $isImg = in_array($fext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'], true);
                                                                $proofUrl = asset('assets/front/tender_proofs/' . $row->proof_path);
                                                            @endphp
                                                            <a href="{{ $proofUrl }}" target="_blank" title="{{ $fname }}" class="pe-file">
                                                                <span class="pe-file-tile {{ $fext == 'pdf' ? 'is-pdf' : '' }}">
                                                                    <span class="pe-file-ext">{{ strtoupper($fext ?: 'FILE') }}</span>
                                                                    @if ($isImg)
                                                                        <img src="{{ $proofUrl }}" alt="" loading="lazy"
                                                                            onerror="this.remove()">
                                                                    @endif
                                                                </span>
                                                                <span class="pe-file-meta">
                                                                    <span class="pe-file-name">{{ $fnameDisplay }}</span>
                                                                    <span class="pe-sub">
                                                                        {{ strtoupper($fext ?: 'file') }}@if ($row->proof_size) &middot; {{ $row->proof_size >= 1048576 ? round($row->proof_size / 1048576, 1) . ' MB' : round($row->proof_size / 1024, 1) . ' KB' }}@endif
                                                                    </span>
                                                                </span>
                                                                <i class="fas fa-external-link-alt pe-file-open"></i>
                                                            </a>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($row->action == 'validated')
                                                            <span class="badge badge-success"><i class="fas fa-check-circle"></i> Validated</span>
                                                        @elseif ($row->action == 'rejected')
                                                            <span class="badge badge-danger"><i class="fas fa-ban"></i> Rejected</span>
                                                        @else
                                                            <span class="badge badge-danger"><i class="fas fa-undo-alt"></i> Canceled</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($row->action == 'validated' && $aName !== '')
                                                            <div class="pe-person">
                                                                <span class="pe-avatar pe-avatar-admin">{{ mb_strtoupper(mb_substr($aName, 0, 1, 'utf-8'), 'utf-8') }}</span>
                                                                <span class="pe-person-text">
                                                                    <span class="pe-strong">{{ $aName }}</span>
                                                                    @if (optional($row->admin)->email)
                                                                        <a href="mailto:{{ $row->admin->email }}" class="pe-sub">{{ $row->admin->email }}</a>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($row->action == 'canceled' && $row->created_at)
                                                            <span class="pe-strong pe-nowrap">{{ $row->created_at->format('d M Y') }}</span>
                                                            <span class="pe-sub"><i class="far fa-clock"></i> {{ $row->created_at->format('H:i') }}</span>
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
        /* Cell content only — the table, rows and headers keep the admin's default look. */
        .pe-strong { display: block; font-weight: 600; line-height: 1.3; }
        .pe-sub { display: block; font-size: 12px; opacity: .65; line-height: 1.35; overflow-wrap: anywhere; }
        a.pe-sub { opacity: 1; overflow-wrap: normal; word-break: break-all; }
        .pe-nowrap { white-space: nowrap; }
        .pe-sub i { font-size: 11px; margin-right: 3px; }
        .pe-title { word-break: break-word; margin-top: 2px; }
        .pe-title .tender-title-toggle { font-size: 11px; white-space: nowrap; margin-left: 4px; }
        .pe-order {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 6px;
            background: rgba(91, 141, 239, .16);
            color: #5b8def;
            font-family: SFMono-Regular, Menlo, Consolas, monospace;
            font-weight: 700;
            font-size: 12px;
            letter-spacing: .03em;
            white-space: nowrap;
        }
        .pe-person { display: flex; align-items: center; gap: 10px; min-width: 230px; }
        .pe-person-text { min-width: 0; flex: 1; }
        .pe-avatar {
            flex: 0 0 32px;
            width: 32px; height: 32px;
            display: inline-flex; align-items: center; justify-content: center;
            border-radius: 50%;
            background: linear-gradient(135deg, #5b8def, #1b4fd8);
            color: #fff; font-weight: 700; font-size: 13px;
        }
        .pe-avatar-admin { background: linear-gradient(135deg, #34c38f, #0f8a5f); }
        .pe-code { display: block; font-weight: 700; white-space: nowrap; }
        .pe-amount { font-weight: 700; white-space: nowrap; }
        .pe-cur { display: block; font-size: 11px; opacity: .6; letter-spacing: .04em; }
        .pe-file {
            display: inline-flex; align-items: center; gap: 10px;
            padding: 6px 12px 6px 6px;
            border: 1px solid rgba(181, 181, 181, .22); border-radius: 10px;
            background: rgba(255, 255, 255, .04);
            min-width: 190px; max-width: 250px;
            transition: border-color .15s, background .15s;
        }
        .pe-file:hover { border-color: #5b8def; background: rgba(91, 141, 239, .1); text-decoration: none; }
        .pe-file-tile {
            position: relative; flex: 0 0 42px; width: 42px; height: 42px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 8px; overflow: hidden;
            background: rgba(63, 185, 80, .16); color: #3FB950;
        }
        .pe-file-tile.is-pdf { background: rgba(255, 88, 88, .16); color: #ff6b6b; }
        .pe-file-ext { font-size: 10px; font-weight: 800; letter-spacing: .05em; }
        .pe-file-tile img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
        .pe-file-meta { min-width: 0; flex: 1; }
        .pe-file-name { display: block; font-weight: 600; font-size: 12.5px; white-space: nowrap; line-height: 1.3; }
        .pe-file .pe-sub { white-space: nowrap; margin-top: 1px; }
        .pe-file-open { font-size: 11px; opacity: .45; }
        .pe-file:hover .pe-file-open { opacity: 1; color: #5b8def; }
        .badge i { margin-right: 3px; }
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
