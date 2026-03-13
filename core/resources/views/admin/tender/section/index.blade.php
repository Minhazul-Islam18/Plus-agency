@extends('admin.layout')

@section('content')
<div class="page-header">
  <h4 class="page-title">{{ $module->name }}</h4>
  <ul class="breadcrumbs">
    <li class="nav-home">
      <a href="{{ route('admin.dashboard') }}"><i class="flaticon-home"></i></a>
    </li>
    <li class="separator"><i class="flaticon-right-arrow"></i></li>
    <li class="nav-item">
      <a href="{{ route('admin.tender.module.index', $module->tender_id) . '?language=' . request()->input('language') }}">Modules</a>
    </li>
    <li class="separator"><i class="flaticon-right-arrow"></i></li>
    <li class="nav-item"><a href="#">Sections</a></li>
  </ul>
</div>

<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header">
        <div class="row">
          <div class="col-lg-4">
            <div class="card-title d-inline-block">Sections</div>
          </div>
          <div class="col-lg-3"></div>
          <div class="col-lg-4 offset-lg-1 mt-2 mt-lg-0">
            <a href="#" class="btn btn-primary float-right btn-sm"
              data-toggle="modal" data-target="#createModal">
              <i class="fas fa-plus"></i> Add Section
            </a>
            <button class="btn btn-danger float-right btn-sm mr-2 d-none bulk-delete"
              data-href="{{ route('admin.tender.module.section.bulk_delete') }}">
              <i class="flaticon-interface-5"></i> Delete
            </button>
          </div>
        </div>
      </div>

      <div class="card-body">
        <div class="row">
          <div class="col-lg-12">
            @if (count($sections) == 0)
              <h3 class="text-center">NO SECTION FOUND</h3>
            @else
            <div class="table-responsive">
              <table class="table table-striped mt-3" id="basic-datatables">
                <thead>
                  <tr>
                    <th scope="col">
                      <input type="checkbox" class="bulk-check" data-val="all">
                    </th>
                    <th scope="col">Section Name</th>
                    <th scope="col">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($sections as $section)
                  <tr>
                    <td>
                      <input type="checkbox" class="bulk-check" data-val="{{ $section->id }}">
                    </td>
                    <td>{{ convertUtf8($section->name) }}</td>
                    <td>
                      <a class="btn btn-secondary btn-sm editbtn" href="#editModal"
                        data-toggle="modal"
                        data-section_id="{{ $section->id }}"
                        data-name="{{ $section->name }}">
                        <span class="btn-label"><i class="fas fa-edit"></i></span> Edit
                      </a>

                      <form class="deleteform d-inline-block"
                        action="{{ route('admin.tender.module.section.delete') }}" method="post">
                        @csrf
                        <input type="hidden" name="section_id" value="{{ $section->id }}">
                        <button type="submit" class="btn btn-danger btn-sm deletebtn">
                          <span class="btn-label"><i class="fas fa-trash"></i></span> Delete
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
    </div>
  </div>
</div>


<!-- Add Module Section Modal -->
<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Module Section</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="ajaxForm" class="modal-form create"
          action="{{ route('admin.tender.module.section.store') }}" method="POST">
          @csrf
          <input type="hidden" name="module_id" value="{{ $module->id }}">

          <div class="form-group">
            <label>Section Name **</label>
            <input type="text" class="form-control" name="name"
              placeholder="Enter Section Name">
            <p id="errname" class="mb-0 text-danger em"></p>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button id="submitBtn" type="button" class="btn btn-primary">Submit</button>
      </div>
    </div>
  </div>
</div>


<!-- Edit Module Section Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Module Section</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="ajaxEditForm"
          action="{{ route('admin.tender.module.section.update') }}" method="POST">
          @csrf
          <input id="insection_id" type="hidden" name="section_id" value="">

          <div class="form-group">
            <label>Section Name **</label>
            <input id="inname" type="text" class="form-control" name="name"
              placeholder="Enter Section Name">
            <p id="eerrname" class="mb-0 text-danger em"></p>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button id="updateBtn" type="button" class="btn btn-primary">Save Changes</button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
  $(document).ready(function () {
    $(document).on('click', '.editbtn', function () {
      $('#insection_id').val($(this).data('section_id'));
      $('#inname').val($(this).data('name'));
    });
  });
</script>
@endsection
