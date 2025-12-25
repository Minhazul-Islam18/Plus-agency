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

  input:checked + .slider {
    background-color: #1572E8;
  }

  input:focus + .slider {
    box-shadow: 0 0 1px #1572E8;
  }

  input:checked + .slider:before {
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
    <h4 class="page-title">Social Links</h4>
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
        <a href="#">Basic Settings</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">Social Links</a>
      </li>
    </ul>
  </div>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <form id="socialForm" action="{{route('admin.social.store')}}" method="post" onsubmit="store(event)">
          <div class="card-header">
            <div class="card-title">Add Social Link</div>
          </div>
          <div class="card-body pt-5 pb-5">
            <div class="row">
              <div class="col-lg-6 offset-lg-3">
                @csrf
                <div class="form-group">
                  <label for="">Social Icon **</label>
                  <div class="btn-group d-block">
                      <button type="button" class="btn btn-primary iconpicker-component"><i
                              class="fa fa-fw fa-heart"></i></button>
                      <button type="button" class="icp icp-dd btn btn-primary dropdown-toggle"
                              data-selected="fa-car" data-toggle="dropdown">
                      </button>
                      <div class="dropdown-menu"></div>
                  </div>
                  <input id="inputIcon" type="hidden" name="icon" value="">
                  @if ($errors->has('icon'))
                    <p class="mb-0 text-danger">{{$errors->first('icon')}}</p>
                  @endif
                  <div class="mt-2">
                    <small>NB: click on the dropdown icon to select a social link icon.</small>
                  </div>
                </div>
                <div class="form-group">
                  <label for="">URL **</label>
                  <input type="text" class="form-control" name="url" value="" placeholder="Enter URL of social media account">
                  @if ($errors->has('url'))
                    <p class="mb-0 text-danger">{{$errors->first('url')}}</p>
                  @endif
                </div>
                <div class="form-group">
                  <label for="">Serial Number **</label>
                  <input type="number" class="form-control ltr" name="serial_number" value="" placeholder="Enter Serial Number">
                  <p id="errserial_number" class="mb-0 text-danger em"></p>
                  <p class="text-warning"><small>The higher the serial number is, the later the social link will be shown.</small></p>
                </div>
              </div>
            </div>
          </div>
          <div class="card-footer pt-3">
            <div class="form">
              <div class="form-group from-show-notify row">
                <div class="col-lg-3 col-md-3 col-sm-12">

                </div>
                <div class="col-12 text-center">
                  <button type="submit" id="displayNotif" class="btn btn-success">Submit</button>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>

      <div class="card">
        <div class="card-header">
          <div class="card-title">Social Links</div>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-lg-12">
              @if (count($socials) == 0)
                <h2 class="text-center">NO LINK ADDED</h2>
              @else
                <div class="table-responsive">
                  <table class="table table-striped mt-3">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Icon</th>
                            <th scope="col">URL</th>
                            <th scope="col">Serial Number</th>
                            <th scope="col">Status</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                      @foreach ($socials as $key => $social)
                        <tr>
                            <td>{{$loop->iteration}}</td>
                            <td><i class="{{ $social->icon }}"></i></td>
                            <td>{{$social->url}}</td>
                            <td>{{$social->serial_number}}</td>
                            <td>
                              <label class="switch">
                                <input type="checkbox" class="status-toggle" data-id="{{$social->id}}" {{$social->status == 1 ? 'checked' : ''}}>
                                <span class="slider round"></span>
                              </label>
                            </td>
                            <td>
                            <a class="btn btn-secondary btn-sm" href="{{route('admin.social.edit', $social->id)}}">
                                <span class="btn-label">
                                    <i class="fas fa-edit"></i>
                                </span>
                                Edit
                            </a>
                            <form class="d-inline-block deleteform" action="{{route('admin.social.delete')}}" method="post">
                              @csrf
                              <input type="hidden" name="socialid" value="{{$social->id}}">
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
      </div>
    </div>
  </div>

@endsection


@section('scripts')
  <script>
    function store(e) {
      e.preventDefault();
      $("#inputIcon").val($(".iconpicker-component").find('i').attr('class'));
      document.getElementById('socialForm').submit();
    }

    $(document).ready(function() {
      $('.status-toggle').on('change', function() {
        let status = $(this).is(':checked') ? 1 : 0;
        let id = $(this).data('id');

        $.ajax({
          url: '{{route("admin.social.status")}}',
          method: 'POST',
          data: {
            _token: '{{csrf_token()}}',
            id: id,
            status: status
          },
          success: function(response) {
            if (response.success) {
              alert('Status updated successfully!');
            }
          },
          error: function(xhr) {
            alert('Error updating status!');
          }
        });
      });
    });
  </script>
@endsection
