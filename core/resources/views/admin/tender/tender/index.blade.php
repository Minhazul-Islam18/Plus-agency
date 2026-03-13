@extends('admin.layout')

@php
$selLang = \App\Language::where('code', request()->input('language'))->first();
@endphp

@section('content')
<div class="page-header">
  <h4 class="page-title">Tenders</h4>
  <ul class="breadcrumbs">
    <li class="nav-home">
      <a href="{{ route('admin.dashboard') }}"><i class="flaticon-home"></i></a>
    </li>
    <li class="separator"><i class="flaticon-right-arrow"></i></li>
    <li class="nav-item"><a href="#">Tender Management</a></li>
    <li class="separator"><i class="flaticon-right-arrow"></i></li>
    <li class="nav-item"><a href="#">All Tenders</a></li>
  </ul>
</div>

<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header">
        <div class="row">
          <div class="col-lg-4">
            <div class="card-title d-inline-block">All Tenders</div>
          </div>

          <div class="col-lg-3">
            @if (!empty($langs))
            <select name="language" class="form-control"
              onchange="window.location='{{ url()->current() . '?language=' }}'+this.value">
              <option value="" selected disabled>Select a Language</option>
              @foreach ($langs as $lang)
              <option value="{{ $lang->code }}"
                {{ $lang->code == request()->input('language') ? 'selected' : '' }}>
                {{ $lang->name }}
              </option>
              @endforeach
            </select>
            @endif
          </div>

          <div class="col-lg-4 offset-lg-1 mt-2 mt-lg-0">
            <a href="{{ route('admin.tender.create') . '?language=' . request()->input('language') }}"
              class="btn btn-primary float-right btn-sm">
              <i class="fas fa-plus"></i> Add Tender
            </a>
            <button class="btn btn-danger float-right btn-sm mr-2 d-none bulk-delete"
              data-href="{{ route('admin.tender.bulk_delete') }}">
              <i class="flaticon-interface-5"></i> Delete
            </button>
          </div>
        </div>
      </div>

      <div class="card-body">
        <div class="row">
          <div class="col-lg-12">
            @if (count($tenders) == 0)
              <h3 class="text-center">NO TENDER FOUND</h3>
            @else
            <div class="table-responsive">
              <table class="table table-striped mt-3" id="basic-datatables">
                <thead>
                  <tr>
                    <th scope="col">
                      <input type="checkbox" class="bulk-check" data-val="all">
                    </th>
                    <th scope="col">Image</th>
                    <th scope="col">Country</th>
                    <th scope="col">Title</th>
                    <th scope="col">Featured</th>
                    <th scope="col">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($tenders as $tender)
                  <tr>
                    <td>
                      <input type="checkbox" class="bulk-check" data-val="{{ $tender->id }}">
                    </td>

                    <td>
                      @if (!empty($tender->tender_image))
                        <img src="{{ asset('assets/front/img/tenders/' . $tender->tender_image) }}"
                          alt="" width="70" style="border-radius:4px;">
                      @else
                        <img src="{{ asset('assets/admin/img/noimage.jpg') }}" alt="" width="70"
                          style="border-radius:4px;">
                      @endif
                    </td>

                    <td>{{ $tender->country }}</td>

                    <td>
                      <a href="#" class="text-primary"
                        data-toggle="modal" data-target="#detailsModal{{ $tender->id }}">
                        {{ strlen($tender->title) > 35 ? mb_substr($tender->title, 0, 35, 'utf-8') . '...' : $tender->title }}
                      </a>
                    </td>

                    <td>
                      <form id="featureForm{{ $tender->id }}" class="d-inline-block"
                        action="{{ route('admin.tender.featured') }}" method="POST">
                        @csrf
                        <input type="hidden" name="tender_id" value="{{ $tender->id }}">
                        <select
                          class="form-control {{ $tender->is_featured == 1 ? 'bg-success' : 'bg-danger' }}"
                          name="is_featured"
                          onchange="document.getElementById('featureForm{{ $tender->id }}').submit();">
                          <option value="1" {{ $tender->is_featured == 1 ? 'selected' : '' }}>Yes</option>
                          <option value="0" {{ $tender->is_featured == 0 ? 'selected' : '' }}>No</option>
                        </select>
                      </form>
                    </td>

                    <td>
                      <a class="btn btn-secondary btn-sm"
                        href="{{ route('admin.tender.edit', $tender->id) . '?language=' . request()->input('language') }}">
                        <span class="btn-label"><i class="fas fa-edit"></i></span> Edit
                      </a>

                      <form class="deleteform d-inline-block"
                        action="{{ route('admin.tender.delete') }}" method="POST">
                        @csrf
                        <input type="hidden" name="tender_id" value="{{ $tender->id }}">
                        <button type="submit" class="btn btn-danger btn-sm deletebtn">
                          <span class="btn-label"><i class="fas fa-trash"></i></span> Delete
                        </button>
                      </form>

                      <a class="btn btn-success btn-sm"
                        href="{{ route('admin.tender.module.index', $tender->id) . '?language=' . request()->input('language') }}">
                        <span class="btn-label"><i class="fas fa-list-alt"></i></span> Modules
                      </a>
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

    </div>
  </div>
</div>

{{-- Tender Details Modals — must be outside the table --}}
@foreach ($tenders as $tender)
<div class="modal fade" id="detailsModal{{ $tender->id }}" tabindex="-1"
  role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Tender Details</h5>
        <button type="button" class="close" data-dismiss="modal">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <table class="table table-sm table-bordered mb-0">
          <tr>
            <th>Country</th>
            <td>{{ $tender->country }}</td>
          </tr>
          <tr>
            <th>Tender Title</th>
            <td>{{ convertUtf8($tender->title) }}</td>
          </tr>
          <tr>
            <th>Submission Deadline</th>
            <td>{{ $tender->submission_deadline ? \Carbon\Carbon::parse($tender->submission_deadline)->format('d-m-Y H:i') : '-' }}</td>
          </tr>
          @foreach ($tender->tenderModules as $module)
          <tr>
            <th>{{ convertUtf8($module->name) }}</th>
            <td>
              {{ !is_null($module->cost)
                  ? number_format($module->cost, 0, '.', ' ') . ' ' . $bex->base_currency_text
                  : '0 ' . $bex->base_currency_text }}
            </td>
          </tr>
          @endforeach
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
@endforeach

@endsection
