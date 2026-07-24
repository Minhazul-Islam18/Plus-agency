@extends('admin.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">Users Management</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('admin.dashboard') }}"><i class="flaticon-home"></i></a>
            </li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="#">Users Management</a></li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="card-title">Users</div>
                        </div>
                        <div class="col-6 mt-2 mt-lg-0">
                            <form action="{{ url()->current() }}" class="float-right">
                                <input type="text" name="term" class="form-control" value="{{ request()->input('term') }}"
                                    placeholder="Search by Company / Registration No. / Email" style="min-width: 280px;">
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">
                            @if (count($companies) == 0)
                                <h3 class="text-center">NO USER FOUND</h3>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-striped mt-3">
                                        <thead>
                                            <tr>
                                                <th scope="col">Company name</th>
                                                <th scope="col">Country</th>
                                                <th scope="col">Company registration No</th>
                                                <th scope="col">Email ID</th>
                                                <th scope="col">Telephone</th>
                                                <th scope="col">Purchased Tenders</th>
                                                <th scope="col">Statut</th>
                                                <td scope="col">Action</td>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($companies as $company)
                                                @php $blacklisted = $company->isBlacklisted(); @endphp
                                                <tr>
                                                    <td>{{ $company->company_name ?: '--' }}</td>
                                                    <td>{{ $company->country ?: '--' }}</td>
                                                    <td>{{ $company->company_registration_no }}</td>
                                                    <td>{{ $company->email ?: '--' }}</td>
                                                    <td>{{ $company->phone_number ?: '--' }}</td>
                                                    <td>{{ $company->purchasedTendersCount() }}</td>
                                                    <td>
                                                        <span class="badge {{ $blacklisted ? 'badge-danger' : 'badge-success' }}">
                                                            {{ $blacklisted ? 'Blacklisted' : 'Not blacklisted' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="dropdown">
                                                            <button class="btn btn-info btn-sm dropdown-toggle" type="button"
                                                                id="companyActions{{ $company->id }}" data-toggle="dropdown"
                                                                aria-haspopup="true" aria-expanded="false">
                                                                Actions
                                                            </button>
                                                            <div class="dropdown-menu" aria-labelledby="companyActions{{ $company->id }}">
                                                                <a class="dropdown-item"
                                                                    href="{{ route('admin.tender.purchaseLog', ['registration_no' => $company->company_registration_no]) }}">
                                                                    Details
                                                                </a>
                                                                <form class="d-block m-0" method="POST"
                                                                    action="{{ route('admin.tender.company.toggleBlacklist') }}">
                                                                    @csrf
                                                                    <input type="hidden" name="company_id" value="{{ $company->id }}">
                                                                    <button type="submit" class="dropdown-item">
                                                                        {{ $blacklisted ? 'Not blacklisted' : 'Blacklist' }}
                                                                    </button>
                                                                </form>
                                                                <form class="deleteform d-block m-0" method="POST"
                                                                    action="{{ route('admin.tender.company.delete') }}">
                                                                    @csrf
                                                                    <input type="hidden" name="company_id" value="{{ $company->id }}">
                                                                    <button type="submit" class="dropdown-item deletebtn">
                                                                        Delete
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
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="d-inline-block mx-auto">
                            {{ $companies->appends(['term' => request()->input('term')])->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
