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
                                        <table class="table table-striped" style="table-layout: fixed; width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th style="width: 130px;">Order</th>
                                                    <th style="width: 220px;">Tender</th>
                                                    <th style="width: 110px;">Status</th>
                                                    <th style="width: 110px;">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($tpurchases as $tpurchase)
                                                    <tr>
                                                        <td style="width: 130px; word-break: break-word;">{{ $tpurchase->order_number }}</td>
                                                        <td style="width: 220px;">
                                                            @if (!empty($tpurchase->tender))
                                                                @php $tTitle = $tpurchase->tender->title; @endphp
                                                                @if (mb_strlen($tTitle, 'utf-8') > 25)
                                                                    <span class="tender-title-short">{{ mb_substr($tTitle, 0, 25, 'utf-8') }}&hellip;</span><span class="tender-title-full d-none">{{ $tTitle }}</span>
                                                                    <a href="javascript:void(0)" class="tender-title-toggle" style="font-size: 11px; white-space: nowrap;">Show more</a>
                                                                @else
                                                                    {{ $tTitle }}
                                                                @endif
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        <td style="width: 110px;">
                                                            <span
                                                                class="{{ strtolower($tpurchase->payment_status) == 'completed' ? 'badge badge-success' : 'badge badge-danger' }}">
                                                                {{ strtolower($tpurchase->payment_status) == 'completed' ? 'Completed' : 'Pending' }}
                                                            </span>
                                                        </td>
                                                        <td style="width: 110px;">
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
