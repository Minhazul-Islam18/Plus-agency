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
            <li class="nav-item"><a href="#">Purchases</a></li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="card-title d-inline-block">Purchases</div>
                        </div>

                        <div class="col-lg-9 mt-2 mt-lg-0">
                            <div class="d-flex float-right">
                                <button class="btn btn-danger btn-sm d-none bulk-delete mr-2"
                                    data-href="{{ route('admin.tender.purchaseBulkOrderDelete') }}">
                                    <i class="flaticon-interface-5"></i> Delete
                                </button>

                                {{-- Language filter --}}
                                <select name="language" class="form-control form-control-sm mr-2" style="width: auto;"
                                    onchange="window.location='{{ route('admin.tender.purchaseLog') }}?language='+this.value+'&order_number={{ request()->input('order_number') }}&registration_no={{ request()->input('registration_no') }}'">
                                    <option value="">All Languages</option>
                                    @foreach ($langs as $lang)
                                        <option value="{{ $lang->code }}"
                                            {{ request()->input('language') == $lang->code ? 'selected' : '' }}>
                                            {{ $lang->name }}
                                        </option>
                                    @endforeach
                                </select>

                                {{-- Order number search --}}
                                <form action="{{ route('admin.tender.purchaseLog') }}" method="GET" class="d-flex mr-2">
                                    <input type="hidden" name="language" value="{{ request()->input('language') }}">
                                    <input type="hidden" name="registration_no" value="{{ request()->input('registration_no') }}">
                                    <input name="order_number" type="text" class="form-control form-control-sm"
                                        placeholder="Search Order Number" value="{{ request()->input('order_number') }}">
                                </form>

                                {{-- Company registration number search --}}
                                <form action="{{ route('admin.tender.purchaseLog') }}" method="GET" class="d-flex">
                                    <input type="hidden" name="language" value="{{ request()->input('language') }}">
                                    <input type="hidden" name="order_number" value="{{ request()->input('order_number') }}">
                                    <input name="registration_no" type="text" class="form-control form-control-sm"
                                        placeholder="Search Registration No." value="{{ request()->input('registration_no') }}">
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">
                            @if (count($purchases) == 0)
                                <h3 class="text-center">NO ENROLL FOUND</h3>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-striped mt-3" style="table-layout: fixed; width: 100%;">
                                        <thead>
                                            <tr>
                                                <th scope="col" style="width: 40px;">
                                                    <input type="checkbox" class="bulk-check" data-val="all">
                                                </th>
                                                <th scope="col" style="width: 120px;">Order Number</th>
                                                <th scope="col" style="width: 190px;">Tender</th>
                                                <th scope="col" style="width: 140px;">Name</th>
                                                <th scope="col" style="width: 130px;">Payment Status</th>
                                                <th scope="col" style="width: 90px;">Access</th>
                                                <th scope="col" style="width: 140px;">Receipt</th>
                                                <th scope="col" style="width: 90px;">Details</th>
                                                <th scope="col" style="width: 260px;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($purchases as $purchase)
                                                <tr>
                                                    <td style="width: 40px;">
                                                        <input type="checkbox" class="bulk-check"
                                                            data-val="{{ $purchase->id }}">
                                                    </td>
                                                    <td style="width: 120px; word-break: break-word;">{{ $purchase->order_number }}</td>
                                                    <td style="width: 190px;">
                                                        @if (!empty($purchase->tender))
                                                            @php $tTitle = $purchase->tender->title; @endphp
                                                            @if (mb_strlen($tTitle, 'utf-8') > 30)
                                                                <span class="tender-title-short">{{ mb_substr($tTitle, 0, 30, 'utf-8') }}&hellip;</span><span class="tender-title-full d-none">{{ $tTitle }}</span>
                                                                <a href="javascript:void(0)" class="tender-title-toggle" style="font-size: 11px; white-space: nowrap;">Show more</a>
                                                            @else
                                                                {{ $tTitle }}
                                                            @endif
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td style="width: 140px; word-break: break-word;">{{ $purchase->first_name }} {{ $purchase->last_name }}</td>
                                                    <td style="width: 130px;">
                                                        @if ($purchase->gateway_type == 'offline')
                                                            <form
                                                                action="{{ route('admin.tender.purchasePaymentStatus') }}"
                                                                id="paymentStatusForm{{ $purchase->id }}" method="POST">
                                                                @csrf
                                                                <input type="hidden" name="purchase_id"
                                                                    value="{{ $purchase->id }}">
                                                                <select
                                                                    class="{{ strtolower($purchase->payment_status) == 'completed' ? 'bg-success' : 'bg-danger' }} form-control-sm text-white border-0"
                                                                    name="payment_status"
                                                                    onchange="document.getElementById('paymentStatusForm{{ $purchase->id }}').submit();">
                                                                    <option value="Completed"
                                                                        {{ strtolower($purchase->payment_status) == 'completed' ? 'selected' : '' }}>
                                                                        Completed</option>
                                                                    <option value="Pending"
                                                                        {{ strtolower($purchase->payment_status) == 'pending' ? 'selected' : '' }}>
                                                                        Pending</option>
                                                                </select>
                                                            </form>
                                                        @else
                                                            <span
                                                                class="{{ strtolower($purchase->payment_status) == 'completed' ? 'badge badge-success' : 'badge badge-danger' }}">
                                                                {{ strtolower($purchase->payment_status) == 'completed' ? 'Completed' : 'Pending' }}
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td style="width: 90px;">
                                                        @if ($purchase->isSuspended())
                                                            <span class="badge badge-dark" data-toggle="tooltip"
                                                                title="{{ $purchase->suspend_reason ?: 'Suspended by admin' }}">Suspended</span>
                                                        @else
                                                            <span class="badge badge-success">Active</span>
                                                        @endif
                                                    </td>
                                                    <td style="width: 140px;">
                                                        <div>
                                                            @if (!empty($purchase->invoice))
                                                                <a href="{{ route('admin.tender.invoiceDownload', $purchase->id) }}"
                                                                    target="_blank" class="btn btn-success btn-sm"
                                                                    data-toggle="tooltip" title="Download Invoice">
                                                                    <i class="fas fa-download"></i> Invoice
                                                                </a>
                                                            @elseif (strtolower($purchase->payment_status) === 'completed')
                                                                <form
                                                                    action="{{ route('admin.tender.purchaseGenerateInvoice', $purchase->id) }}"
                                                                    method="POST">
                                                                    @csrf
                                                                    <button type="submit"
                                                                        class="btn btn-outline-success btn-sm">
                                                                        Generate Invoice
                                                                    </button>
                                                                </form>
                                                            @else
                                                                -
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td style="width: 90px;">
                                                        <a href="#" class="btn btn-primary btn-sm" data-toggle="modal"
                                                            data-target="#detailsModal{{ $purchase->id }}">
                                                            Details
                                                        </a>
                                                    </td>
                                                    <td style="width: 260px;">
                                                        <div class="d-flex flex-row flex-wrap align-items-center" style="gap: 6px;">
                                                            {{-- Suspend / Reactivate this transaction --}}
                                                            <form action="{{ route('admin.tender.purchaseSuspend') }}"
                                                                method="POST" class="suspendform m-0">
                                                                @csrf
                                                                <input type="hidden" name="purchase_id"
                                                                    value="{{ $purchase->id }}">
                                                                @if ($purchase->isSuspended())
                                                                    <button type="submit"
                                                                        class="btn btn-success btn-sm m-0">
                                                                        <i class="fas fa-unlock mr-1"></i> Reactivate
                                                                    </button>
                                                                @else
                                                                    <button type="submit"
                                                                        class="suspendbtn btn btn-warning btn-sm m-0">
                                                                        <i class="fas fa-ban mr-1"></i> Suspend
                                                                    </button>
                                                                @endif
                                                            </form>

                                                            {{-- Quick blacklist this company (keyed on its registration no.) --}}
                                                            @if (\App\TenderBlacklist::matches($purchase->company_registration_no))
                                                                <a href="{{ route('admin.tender.blacklist') }}"
                                                                    class="btn btn-secondary btn-sm m-0"
                                                                    data-toggle="tooltip" title="Already blacklisted — manage on the Blacklist page">
                                                                    <i class="fas fa-user-slash mr-1"></i> Blacklisted
                                                                </a>
                                                            @else
                                                                <form
                                                                    action="{{ route('admin.tender.blacklist.fromPurchase') }}"
                                                                    method="POST" class="blacklistform m-0">
                                                                    @csrf
                                                                    <input type="hidden" name="purchase_id"
                                                                        value="{{ $purchase->id }}">
                                                                    <input type="hidden" name="reason" value="">
                                                                    <button type="submit"
                                                                        class="blacklistbtn btn btn-dark btn-sm m-0">
                                                                        <i class="fas fa-user-slash mr-1"></i> Blacklist
                                                                    </button>
                                                                </form>
                                                            @endif

                                                            <form class="deleteform m-0"
                                                                action="{{ route('admin.tender.purchaseDelete') }}"
                                                                method="POST">
                                                                @csrf
                                                                <input type="hidden" name="purchase_id"
                                                                    value="{{ $purchase->id }}">
                                                                <button type="submit"
                                                                    class="deletebtn btn btn-danger btn-sm m-0">
                                                                    <i class="fas fa-trash mr-1"></i> Delete
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>

                                                @includeIf('admin.tender.tender.receipt')
                                                @includeIf('admin.tender.tender.purchase-details')
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="row">
                        <div class="d-inline-block mx-auto">
                            {{ $purchases->appends([
                                    'language' => request()->input('language'),
                                    'order_number' => request()->input('order_number'),
                                    'registration_no' => request()->input('registration_no'),
                                ])->links() }}
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
