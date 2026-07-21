@extends('admin.layout')
@section('content')
    @php
        $admin = Auth::guard('admin')->user();
        if (!empty($admin->role)) {
            $permissions = $admin->role->permissions;
            $permissions = json_decode($permissions, true);
        }
    @endphp
    <div class="mt-2 mb-4">
        <h2 class="text-white pb-2">Welcome back, {{ Auth::guard('admin')->user()->first_name }}
            {{ Auth::guard('admin')->user()->last_name }}!</h2>
    </div>
    <div class="row">
        @if (empty($admin->role) || (!empty($permissions) && in_array('Users Management', $permissions)))
            <div class="col-sm-6 col-md-3">
                <a href="{{ route('admin.subscriber.index') }}" class="d-block">
                    <div class="card card-stats card-info card-round">
                        <div class="card-body ">
                            <div class="row">
                                <div class="col-3">
                                    <div class="icon-big text-center">
                                        <i class="fas fa-bell"></i>
                                    </div>
                                </div>
                                <div class="col-9 col-stats pl-1">
                                    <div class="numbers">
                                        <p class="card-category">Subscribers</p>
                                        <h4 class="card-title">{{ \App\Subscriber::count() }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endif


        @if (empty($admin->role) || (!empty($permissions) && in_array('Content Management', $permissions)))
            <div class="col-sm-6 col-md-3">
                <a href="{{ route('admin.blog.index', ['language' => $default->code]) }}" class="d-block">
                    <div class="card card-stats card-dark card-round">
                        <div class="card-body ">
                            <div class="row">
                                <div class="col-3">
                                    <div class="icon-big text-center">
                                        <i class="fab fa-blogger-b"></i>
                                    </div>
                                </div>
                                <div class="col-9 col-stats pl-1">
                                    <div class="numbers">
                                        <p class="card-category">Blogs</p>
                                        <h4 class="card-title">{{ $default->blogs()->count() }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="card card-stats card-success card-round">
                    <div class="card-body ">
                        <div class="row">
                            <div class="col-3">
                                <div class="icon-big text-center">
                                    <i class="fas fa-briefcase"></i>
                                </div>
                            </div>
                            <div class="col-9 col-stats">
                                <div class="numbers">
                                    <p class="card-category">Projects</p>
                                    <h4 class="card-title">{{ $default->portfolios()->count() }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="card card-stats card-secondary card-round">
                    <div class="card-body ">
                        <div class="row">
                            <div class="col-3">
                                <div class="icon-big text-center">
                                    <i class="far fa-users-cog"></i>
                                </div>
                            </div>
                            <div class="col-9 col-stats">
                                <div class="numbers">
                                    <p class="card-category">Services</p>
                                    <h4 class="card-title">{{ $default->services()->count() }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="row row-card-no-pd">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-head-row">
                                <h4 class="card-title">Tender Purchases</h4>
                            </div>
                            <p class="card-category">
                                Top 10 latest tender purchases
                            </p>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Order</th>
                                                    <th>Tender</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($tpurchases as $tpurchase)
                                                    <tr>
                                                        <td>{{ $tpurchase->order_number }}</td>
                                                        <td>
                                                            {{ !empty($tpurchase->tender)
                                                                ? (strlen($tpurchase->tender->title) > 25
                                                                    ? mb_substr($tpurchase->tender->title, 0, 25, 'utf-8') . '...'
                                                                    : $tpurchase->tender->title)
                                                                : '-' }}
                                                        </td>
                                                        <td>
                                                            <span
                                                                class="{{ strtolower($tpurchase->payment_status) == 'completed' ? 'badge badge-success' : 'badge badge-danger' }}">
                                                                {{ strtolower($tpurchase->payment_status) == 'completed' ? 'Completed' : 'Pending' }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="dropdown">
                                                                <button class="btn btn-info btn-sm dropdown-toggle"
                                                                    type="button" data-toggle="dropdown"
                                                                    aria-haspopup="true" aria-expanded="false">
                                                                    Actions
                                                                </button>
                                                                <div class="dropdown-menu">
                                                                    <a class="dropdown-item"
                                                                        href="{{ route('admin.tender.purchaseLog') }}?order_number={{ $tpurchase->order_number }}">Details</a>
                                                                    @if (!empty($tpurchase->invoice))
                                                                        <a class="dropdown-item"
                                                                            href="{{ route('admin.tender.invoiceDownload', $tpurchase->id) }}"
                                                                            target="_blank">Invoice</a>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center">No tender purchases yet.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
