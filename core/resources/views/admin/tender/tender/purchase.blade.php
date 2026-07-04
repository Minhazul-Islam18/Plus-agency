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
                                    onchange="window.location='{{ route('admin.tender.purchaseLog') }}?language='+this.value+'&order_number={{ request()->input('order_number') }}'">
                                    <option value="">All Languages</option>
                                    @foreach ($langs as $lang)
                                        <option value="{{ $lang->code }}"
                                            {{ request()->input('language') == $lang->code ? 'selected' : '' }}>
                                            {{ $lang->name }}
                                        </option>
                                    @endforeach
                                </select>

                                {{-- Order number search --}}
                                <form action="{{ route('admin.tender.purchaseLog') }}" method="GET" class="d-flex">
                                    <input type="hidden" name="language" value="{{ request()->input('language') }}">
                                    <input name="order_number" type="text" class="form-control form-control-sm"
                                        placeholder="Search Order Number" value="{{ request()->input('order_number') }}">
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
                                    <table class="table table-striped mt-3">
                                        <thead>
                                            <tr>
                                                <th scope="col">
                                                    <input type="checkbox" class="bulk-check" data-val="all">
                                                </th>
                                                <th scope="col">Order Number</th>
                                                <th scope="col">Tender</th>
                                                <th scope="col">Name</th>
                                                <th scope="col">Payment Status</th>
                                                <th scope="col">Access</th>
                                                <th scope="col">Receipt</th>
                                                <th scope="col">Details</th>
                                                <th scope="col">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($purchases as $purchase)
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" class="bulk-check"
                                                            data-val="{{ $purchase->id }}">
                                                    </td>
                                                    <td>{{ $purchase->order_number }}</td>
                                                    <td>
                                                        {{ !empty($purchase->tender)
                                                            ? (strlen($purchase->tender->title) > 30
                                                                ? mb_substr($purchase->tender->title, 0, 30, 'utf-8') . '...'
                                                                : $purchase->tender->title)
                                                            : '-' }}
                                                    </td>
                                                    <td>{{ $purchase->first_name }} {{ $purchase->last_name }}</td>
                                                    <td>
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
                                                    <td>
                                                        @if ($purchase->isSuspended())
                                                            <span class="badge badge-dark" data-toggle="tooltip"
                                                                title="{{ $purchase->suspend_reason ?: 'Suspended by admin' }}">Suspended</span>
                                                        @else
                                                            <span class="badge badge-success">Active</span>
                                                        @endif
                                                    </td>
                                                    <td>
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
                                                    <td>
                                                        <a href="#" class="btn btn-primary btn-sm" data-toggle="modal"
                                                            data-target="#detailsModal{{ $purchase->id }}">
                                                            Details
                                                        </a>
                                                    </td>
                                                    <td style="min-width: 250px;">
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

                                                            {{-- Quick blacklist this buyer --}}
                                                            @if (\App\TenderBlacklist::matches($purchase->email, $purchase->phone_number, null))
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
                                ])->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
