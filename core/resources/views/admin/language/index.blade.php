@extends('admin.layout')

@section('styles')
<style>
    .switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 24px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
    }

    input:checked+.slider {
        background-color: #1572E8;
    }

    input:focus+.slider {
        box-shadow: 0 0 1px #1572E8;
    }

    input:checked+.slider:before {
        transform: translateX(26px);
    }

    .slider.round {
        border-radius: 24px;
    }

    .slider.round:before {
        border-radius: 50%;
    }
</style>
@endsection

@section('content')
<div class="page-header">
  <h4 class="page-title">Languages</h4>
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
      <a href="#">Language Management</a>
    </li>
  </ul>
</div>

<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header">
        <div class="card-title d-inline-block">Languages</div>
        <a
          href="#"
          class="btn btn-primary float-right"
          data-toggle="modal"
          data-target="#createModal"
        ><i class="fas fa-plus"></i> Add Language</a>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-lg-12">
            @if (count($languages) == 0)
            <h3 class="text-center">NO LANGUAGE FOUND</h3>
            @else
            <div class="table-responsive">
              <table class="table table-striped mt-3">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Name</th>
                    <th scope="col">Code</th>
                    <th scope="col">Status</th>
                    <th scope="col">Appearance in Website</th>
                    <th scope="col">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($languages as $key => $language)
                  <tr>
                    <td>{{$loop->iteration + 1}}</td>
                    <td>{{convertUtf8($language->name)}}</td>
                    <td>{{$language->code}}</td>
                    <td>
                      <label class="switch">
                        <input type="checkbox" class="status-toggle"
                            data-id="{{ $language->id }}"
                            data-is-default="{{ $language->is_default }}"
                            {{ $language->status == 1 ? 'checked' : '' }}>
                        <span class="slider round"></span>
                      </label>
                    </td>
                    <td>
                      @if ($language->is_default == 1)
                      <strong class="badge badge-success">Default</strong>
                      @else
                      <form
                        class="d-inline-block"
                        action="{{route('admin.language.default', $language->id)}}"
                        method="post"
                      >
                        @csrf
                        <button
                          class="btn btn-primary btn-sm"
                          type="submit"
                          name="button"
                        >Make Default</button>
                      </form>
                      @endif
                    </td>
                    <td>
                      <a
                        class="btn btn-secondary btn-sm"
                        href="{{route('admin.language.editKeyword', $language->id)}}"
                      >
                        <span class="btn-label">
                          <i class="fas fa-edit"></i>
                        </span>
                        Edit Keyword
                      </a>
                      <a
                        class="btn btn-secondary btn-sm"
                        href="{{route('admin.language.edit', $language->id)}}"
                      >
                        <span class="btn-label">
                          <i class="fas fa-edit"></i>
                        </span>
                        Edit
                      </a>
                      <form
                        class="deleteform d-inline-block"
                        action="{{route('admin.language.delete', $language->id)}}"
                        method="post"
                      >
                        @csrf
                        <button
                          type="submit"
                          class="btn btn-danger btn-sm deletebtn"
                        >
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
    </div>
  </div>
</div>


<!-- Create Language Modal -->
@includeif('admin.language.create')
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('.status-toggle').on('change', function() {
            let status = $(this).is(':checked') ? 1 : 0;
            let id = $(this).data('id');
            let isDefault = $(this).data('is-default');
            let toggle = $(this);

            // Prevent disabling default language
            if (isDefault == 1 && status == 0) {
                toggle.prop('checked', true);
                $.notify({
                    title: 'Warning',
                    message: 'Default language cannot be deactivated!',
                    icon: 'fa fa-exclamation-triangle'
                }, {
                    type: 'warning',
                    placement: { from: 'top', align: 'right' },
                    showProgressbar: true,
                    time: 1000,
                    delay: 3000
                });
                return;
            }

            $.ajax({
                url: '{{ route('admin.language.status') }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    status: status
                },
                success: function(response) {
                    if (response.success) {
                        $.notify({
                            title: 'Success',
                            message: 'Status updated successfully!',
                            icon: 'fa fa-check'
                        }, {
                            type: 'success',
                            placement: { from: 'top', align: 'right' },
                            showProgressbar: true,
                            time: 1000,
                            delay: 3000
                        });
                    }
                },
                error: function(xhr) {
                    // Revert the toggle on error
                    toggle.prop('checked', !toggle.is(':checked'));
                    $.notify({
                        title: 'Error',
                        message: xhr.responseJSON?.message || 'Error updating status!',
                        icon: 'fa fa-times'
                    }, {
                        type: 'danger',
                        placement: { from: 'top', align: 'right' },
                        showProgressbar: true,
                        time: 1000,
                        delay: 3000
                    });
                }
            });
        });
    });
</script>
@endsection
