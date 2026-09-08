@extends('admin.layout')

@php
  $selLang = \App\Language::where('code', request()->input('language'))->first();
@endphp

@if(!empty($selLang) && $selLang->rtl == 1)
  @section('styles')
    <style>
      form:not(.modal-form) input,
      form:not(.modal-form) textarea,
      form:not(.modal-form) select,
      select[name='language'] {
        direction: rtl;
      }
    </style>
  @endsection
@endif

@section('content')
  <div class="page-header">
    <h4 class="page-title">Portfolio Statuses</h4>
    <ul class="breadcrumbs">
      <li class="nav-home">
        <a href="{{route('admin.dashboard')}}">
          <i class="flaticon-home"></i>
        </a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">Portfolios</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">Statuses</a>
      </li>
    </ul>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <div class="row">
            <div class="col-lg-4">
              <div class="card-title d-inline-block">Portfolio Statuses</div>
            </div>

            <div class="col-lg-3">
              @if (!empty($langs))
                <select name="language" class="form-control" onchange="window.location='{{url()->current() . '?language='}}' + this.value">
                  <option selected disabled>Select a Language</option>
                  @foreach ($langs as $lang)
                    <option value="{{$lang->code}}" {{$lang->code == request()->input('language') ? 'selected' : ''}}>
                      {{$lang->name}}
                    </option>
                  @endforeach
                </select>
              @endif
            </div>

            <div class="col-lg-4 offset-lg-1 mt-2 mt-lg-0">
              <a href="#" data-toggle="modal" data-target="#createStatusModal" class="btn btn-primary btn-sm float-lg-right float-left">
                <i class="fas fa-plus"></i> Add Status
              </a>

              <button class="btn btn-danger float-right btn-sm mr-2 d-none bulk-delete" data-href="{{ route('admin.portfolio.status.bulk_delete') }}">
                <i class="flaticon-interface-5"></i> Delete
              </button>
            </div>
          </div>
        </div>

        <div class="card-body">
          <div class="row">
            <div class="col-lg-12">
              @if (count($statuses) == 0)
                <h3 class="text-center">NO STATUS FOUND!</h3>
              @else
                <div class="table-responsive">
                  <table class="table table-striped mt-3">
                    <thead>
                      <tr>
                        <th scope="col">
                          <input type="checkbox" class="bulk-check" data-val="all">
                        </th>
                        <th scope="col">Name</th>
                        <th scope="col">Status</th>
                        <th scope="col">Serial Number</th>
                        <th scope="col">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($statuses as $status)
                        <tr>
                          <td>
                            <input type="checkbox" class="bulk-check" data-val="{{ $status->id }}">
                          </td>
                          <td>{{ convertUtf8($status->name) }}</td>
                          <td>
                            @if ($status->status == 1)
                              <span class="badge badge-success">Active</span>
                            @else
                              <span class="badge badge-danger">Deactive</span>
                            @endif
                          </td>
                          <td>{{ $status->serial_number }}</td>
                          <td>
                            <a class="btn btn-secondary btn-sm mr-1 editbtn" href="#" data-toggle="modal" data-target="#editStatusModal" data-id="{{ $status->id }}" data-name="{{ $status->name }}" data-status="{{ $status->status }}" data-serial_number="{{ $status->serial_number }}">
                              <span class="btn-label">
                                <i class="fas fa-edit"></i>
                              </span>
                              Edit
                            </a>

                            <form class="deleteform d-inline-block" action="{{ route('admin.portfolio.status.delete') }}" method="post">
                              @csrf
                              <input type="hidden" name="statusId" value="{{ $status->id }}">

                              <button type="submit" class="btn btn-danger btn-sm deletebtn">
                                <span class="btn-label">
                                  <i class="fas fa-trash"></i>
                                </span>
                                Delete
                              </button>
                            </form>
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
              {{ $statuses->appends(['language' => request()->input('language')])->links() }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- create modal --}}
  @include('admin.portfolio.status_create')

  {{-- edit modal --}}
  @include('admin.portfolio.status_edit')
@endsection
