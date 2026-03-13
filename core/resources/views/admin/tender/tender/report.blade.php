@extends('admin.layout')

@section('content')
<div class="page-header">
  <h4 class="page-title">Report</h4>
  <ul class="breadcrumbs">
    <li class="nav-home">
      <a href="{{ route('admin.dashboard') }}"><i class="flaticon-home"></i></a>
    </li>
    <li class="separator"><i class="flaticon-right-arrow"></i></li>
    <li class="nav-item"><a href="#">Tender Management</a></li>
    <li class="separator"><i class="flaticon-right-arrow"></i></li>
    <li class="nav-item"><a href="#">Enrolls</a></li>
  </ul>
</div>

<div class="row">
  <div class="col-md-12">
    <div class="card">

      <div class="card-header p-1">
        <div class="row align-items-center">

          {{-- Filters --}}
          <div class="col-lg-10">
            <form action="{{ url()->full() }}" class="form-inline flex-wrap" style="gap: 6px;">
              <div class="form-group">
                <label class="mr-1">From</label>
                <input class="form-control datepicker" type="text" name="from_date"
                  placeholder="From"
                  value="{{ request()->input('from_date') }}"
                  required autocomplete="off">
              </div>

              <div class="form-group">
                <label class="mr-1">To</label>
                <input class="form-control datepicker" type="text" name="to_date"
                  placeholder="To"
                  value="{{ request()->input('to_date') }}"
                  required autocomplete="off">
              </div>

              <div class="form-group">
                <label class="mr-1">Payment Method</label>
                <select name="payment_method" class="form-control">
                  <option value="">All</option>
                  @if (!empty($onPms))
                    @foreach ($onPms as $onPm)
                      <option value="{{ $onPm->keyword }}"
                        {{ request()->input('payment_method') == $onPm->keyword ? 'selected' : '' }}>
                        {{ $onPm->name }}
                      </option>
                    @endforeach
                  @endif
                  @if (!empty($offPms))
                    @foreach ($offPms as $offPm)
                      <option value="{{ $offPm->name }}"
                        {{ request()->input('payment_method') == $offPm->name ? 'selected' : '' }}>
                        {{ $offPm->name }}
                      </option>
                    @endforeach
                  @endif
                </select>
              </div>

              <div class="form-group">
                <label class="mr-1">Payment Status</label>
                <select name="payment_status" class="form-control">
                  <option value="">All</option>
                  <option value="Pending"   {{ request()->input('payment_status') == 'Pending'   ? 'selected' : '' }}>Pending</option>
                  <option value="Completed" {{ request()->input('payment_status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                </select>
              </div>

              <div class="form-group">
                <button type="submit" class="btn btn-primary btn-sm">Submit</button>
              </div>
            </form>
          </div>

          {{-- Export --}}
          <div class="col-lg-2 text-right">
            <form action="{{ route('admin.tender.enrolls.export') }}" method="GET">
              <button type="submit" class="btn btn-success btn-sm" title="CSV Format">Export</button>
            </form>
          </div>

        </div>
      </div>

      <div class="card-body">
        <div class="row">
          <div class="col-lg-12">
            @if (!empty($enrolls) && count($enrolls) > 0)
            <div class="table-responsive">
              <table class="table table-striped mt-3">
                <thead>
                  <tr>
                    <th scope="col">Order Number</th>
                    <th scope="col">Name</th>
                    <th scope="col">Email</th>
                    <th scope="col">Tender</th>
                    <th scope="col">Technical Proposal Fee</th>
                    <th scope="col">Financial Proposal Fee</th>
                    <th scope="col">Gateway</th>
                    <th scope="col">Payment Status</th>
                    <th scope="col">Date</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($enrolls as $enroll)
                  <tr>
                    <td>#{{ $enroll->order_number }}</td>
                    <td>{{ $enroll->first_name }} {{ $enroll->last_name }}</td>
                    <td>{{ $enroll->email }}</td>
                    <td>
                      {{ !empty($enroll->tender)
                          ? (strlen($enroll->tender->title) > 35
                              ? mb_substr($enroll->tender->title, 0, 35, 'utf-8') . '...'
                              : $enroll->tender->title)
                          : '-' }}
                    </td>
                    <td>
                      {{ $bex->base_currency_symbol_position == 'left' ? $bex->base_currency_symbol : '' }}{{ number_format($enroll->technical_proposal_fee, 0, '.', ' ') }}{{ $bex->base_currency_symbol_position == 'right' ? ' ' . $bex->base_currency_symbol : '' }}
                    </td>
                    <td>
                      {{ $bex->base_currency_symbol_position == 'left' ? $bex->base_currency_symbol : '' }}{{ number_format($enroll->financial_proposal_fee, 0, '.', ' ') }}{{ $bex->base_currency_symbol_position == 'right' ? ' ' . $bex->base_currency_symbol : '' }}
                    </td>
                    <td>{{ $enroll->payment_method }}</td>
                    <td>
                      <span class="{{ strtolower($enroll->payment_status) == 'completed' ? 'badge badge-success' : 'badge badge-warning' }}">
                        {{ $enroll->payment_status }}
                      </span>
                    </td>
                    <td>{{ $enroll->created_at->format('Y-m-d') }}</td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            @endif
          </div>
        </div>
      </div>

      @if (!empty($enrolls) && count($enrolls) > 0)
      <div class="card-footer">
        <div class="row">
          <div class="d-inline-block mx-auto">
            {{ $enrolls->links() }}
          </div>
        </div>
      </div>
      @endif

    </div>
  </div>
</div>
@endsection
