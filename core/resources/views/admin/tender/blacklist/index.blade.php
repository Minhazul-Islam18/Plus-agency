@extends('admin.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">Tenders</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('admin.dashboard') }}"><i class="flaticon-home"></i></a>
            </li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="#">Tenders</a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="#">Blacklist</a></li>
        </ul>
    </div>

    <div class="row">
        {{-- Add entry — kept narrow on wide screens so the table gets the room --}}
        <div class="col-md-4 col-xl-3">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Add to Blacklist</div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.tender.blacklist.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label>Company Registration No. <span class="text-danger">*</span></label>
                            <input type="text" name="company_registration_no" class="form-control"
                                placeholder="AB12CD34" maxlength="100" required pattern="[A-Za-z0-9]+"
                                title="Letters and numbers only — no spaces or special characters."
                                style="text-transform:uppercase;">
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" placeholder="buyer@example.com">
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" name="phone_number" class="form-control" placeholder="+1 555 0100">
                        </div>
                        <div class="form-group">
                            <label>Company Name</label>
                            <input type="text" name="company_name" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Reason</label>
                            <input type="text" name="reason" class="form-control" placeholder="Rule violation">
                        </div>
                        <small class="text-muted d-block mb-2">
                            Orders are refused by Company Registration No. only. Email, phone and company
                            name are optional and kept for your reference — they are not matched on.
                        </small>
                        <button type="submit" class="btn btn-primary btn-sm">Add Entry</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Entry list --}}
        <div class="col-md-8 col-xl-9">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-lg-4">
                            <div class="card-title d-inline-block">Blacklisted Buyers</div>
                        </div>
                        <div class="col-lg-8 mt-2 mt-lg-0">
                            <form action="{{ route('admin.tender.blacklist') }}" method="GET" class="d-flex float-right">
                                <input name="q" type="text" value="{{ $q }}" class="form-control form-control-sm"
                                    placeholder="Search reg. no. / email / phone / company">
                                <button class="btn btn-secondary btn-sm ml-2" type="submit">Search</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if ($entries->count() == 0)
                        <h5 class="text-center my-4">No blacklist entries.</h5>
                    @else
                        {{-- Long company names used to wrap into a tall column and push the
                             Action buttons off-screen. Truncate them (full text on hover) and
                             keep the short columns on one line. --}}
                        <style>
                            .blacklist-table td,
                            .blacklist-table th { vertical-align: middle; }
                            .blacklist-table .col-nowrap { white-space: nowrap; }
                            .blacklist-table .col-clip {
                                max-width: 200px; overflow: hidden;
                                text-overflow: ellipsis; white-space: nowrap;
                            }
                        </style>
                        <div class="table-responsive">
                            <table class="table table-striped mt-2 blacklist-table">
                                <thead>
                                    <tr>
                                        <th class="col-nowrap">Reg. No.</th>
                                        <th>Email</th>
                                        <th class="col-nowrap">Phone</th>
                                        <th>Company</th>
                                        <th>Reason</th>
                                        <th class="col-nowrap">Added</th>
                                        <th class="col-nowrap">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($entries as $entry)
                                        <tr>
                                            <td class="col-nowrap">
                                                <strong>{{ $entry->company_registration_no ?: '-' }}</strong>
                                            </td>
                                            <td class="col-clip" title="{{ $entry->email }}">
                                                {{ $entry->email ?: '-' }}
                                            </td>
                                            <td class="col-nowrap">{{ $entry->phone_number ?: '-' }}</td>
                                            <td class="col-clip" title="{{ $entry->company_name }}">
                                                {{ $entry->company_name ?: '-' }}
                                            </td>
                                            <td class="col-clip" title="{{ $entry->reason }}">
                                                {{ $entry->reason ?: '-' }}
                                            </td>
                                            <td class="col-nowrap">
                                                {{ $entry->created_at ? $entry->created_at->format('d M Y') : '-' }}
                                            </td>
                                            <td class="col-nowrap">
                                                <div class="d-flex flex-row" style="gap: 6px;">
                                                    <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                                                        data-target="#editBlacklist{{ $entry->id }}">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                    <form action="{{ route('admin.tender.blacklist.delete') }}" method="POST"
                                                        class="deleteform m-0">
                                                        @csrf
                                                        <input type="hidden" name="id" value="{{ $entry->id }}">
                                                        <button type="submit" class="deletebtn btn btn-danger btn-sm">Remove</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Edit modals — kept outside the table --}}
                        @foreach ($entries as $entry)
                            <div class="modal fade" id="editBlacklist{{ $entry->id }}" tabindex="-1" role="dialog"
                                aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.tender.blacklist.update') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $entry->id }}">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Blacklist Entry</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <label>Company Registration No. <span class="text-danger">*</span></label>
                                                    <input type="text" name="company_registration_no"
                                                        class="form-control" maxlength="100" required
                                                        pattern="[A-Za-z0-9]+"
                                                        title="Letters and numbers only — no spaces or special characters."
                                                        style="text-transform:uppercase;"
                                                        value="{{ $entry->company_registration_no }}">
                                                </div>
                                                <div class="form-group">
                                                    <label>Email</label>
                                                    <input type="email" name="email" class="form-control"
                                                        value="{{ $entry->email }}">
                                                </div>
                                                <div class="form-group">
                                                    <label>Phone Number</label>
                                                    <input type="text" name="phone_number" class="form-control"
                                                        value="{{ $entry->phone_number }}">
                                                </div>
                                                <div class="form-group">
                                                    <label>Company Name</label>
                                                    <input type="text" name="company_name" class="form-control"
                                                        value="{{ $entry->company_name }}">
                                                </div>
                                                <div class="form-group">
                                                    <label>Reason</label>
                                                    <input type="text" name="reason" class="form-control"
                                                        value="{{ $entry->reason }}" placeholder="Reason for blacklisting">
                                                </div>
                                                <small class="text-muted">
                                                    Orders are refused by Company Registration No. only. The other
                                                    fields are optional and kept for your reference — they are not
                                                    matched on.
                                                </small>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <div class="mt-3">{{ $entries->appends(['q' => $q])->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
