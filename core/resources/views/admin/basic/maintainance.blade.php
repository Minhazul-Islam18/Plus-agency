@extends('admin.layout')

@if(!empty($abs->language) && $abs->language->rtl == 1)
@section('styles')
<style>
    form input,
    form textarea,
    form select {
        direction: rtl;
    }
    form .note-editor.note-frame .note-editing-area .note-editable {
        direction: rtl;
        text-align: right;
    }
</style>
@endsection
@endif

@section('content')
  <div class="page-header">
    <h4 class="page-title">Maintenance Mode</h4>
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
        <a href="#">Maintenance Mode</a>
      </li>
    </ul>
  </div>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <form id="maintenanceForm" action="{{route('admin.maintainance.update', $lang_id)}}" method="post">
          <div class="card-header">
              <div class="row">
                  <div class="col-lg-10">
                      <div class="card-title">Update Maintenance Page & Mode</div>
                  </div>
                  <div class="col-lg-2">
                      @if (!empty($langs))
                          <select name="language" class="form-control" onchange="window.location='{{url()->current() . '?language='}}'+this.value">
                              <option value="" selected disabled>Select a Language</option>
                              <option value="" {{empty(request()->input('language')) ? 'selected' : ''}}>Default</option>
                              @foreach ($langs as $lang)
                                  <option value="{{$lang->code}}" {{$lang->code == request()->input('language') ? 'selected' : ''}}>{{$lang->name}}</option>
                              @endforeach
                          </select>
                      @endif
                  </div>
              </div>
          </div>
          <div class="card-body pt-5 pb-5">
            <div class="row">
              <div class="col-lg-6 offset-lg-3">
                @csrf

                <div class="form-group">
                    <label for="">Maintenance ** </label>
                    <br>
                    <div class="thumb-preview" id="thumbPreview1">
                        <img src="{{asset('assets/front/img/maintainance.png?'.time())}}" alt="Maintenance">
                    </div>
                    <br>
                    <br>


                    <input id="fileInput1" type="hidden" name="maintenance">
                    <button id="chooseImage1" class="choose-image btn btn-primary" type="button" data-multiple="false" data-toggle="modal" data-target="#lfmModal1">Choose Image</button>


                    <p class="text-warning mb-0">JPG, PNG, JPEG images are allowed</p>
                    @if ($errors->has('maintenance'))
                    <p class="text-danger mb-0">{{$errors->first('maintenance')}}</p>
                    @endif

                    <!-- Maintenance LFM Modal -->
                    <div class="modal fade lfm-modal" id="lfmModal1" tabindex="-1" role="dialog" aria-labelledby="lfmModalTitle" aria-hidden="true">
                        <i class="fas fa-times-circle"></i>
                        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-body p-0">
                                    <iframe src="{{url('laravel-filemanager')}}?serial=1" style="width: 100%; height: 500px; overflow: hidden; border: none;"></iframe>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                  <label>Maintenance Mode **</label>
                  <div class="selectgroup w-100">
                    <label class="selectgroup-item">
                      <input type="radio" name="maintainance_mode" value="1" class="selectgroup-input" {{$abs->maintainance_mode == 1 ? 'checked' : ''}}>
                      <span class="selectgroup-button">Active</span>
                    </label>
                    <label class="selectgroup-item">
                      <input type="radio" name="maintainance_mode" value="0" class="selectgroup-input" {{$abs->maintainance_mode == 0 ? 'checked' : ''}}>
                      <span class="selectgroup-button">Deactive</span>
                    </label>
                  </div>
                  @if ($errors->has('maintainance_mode'))
                    <p class="mb-0 text-danger">{{$errors->first('maintainance_mode')}}</p>
                  @endif
                </div>
                <div class="form-group">
                  <label>Maintenance Text</label>
                  <textarea class="form-control" name="maintainance_text" rows="3" cols="80">{!! ($abs->maintainance_text) !!}</textarea>
                  @if ($errors->has('maintainance_text'))
                    <p class="mb-0 text-danger">{{$errors->first('maintainance_text')}}</p>
                  @endif
                </div>

                <div class="form-group">
                  <label>Secret Path</label>
                  <input name="secret_path" type="text" class="form-control" value="{{$abs->secret_path}}">
                  <p class="text-warning">After activating maintenance mode, You can access the website via <strong class="text-danger">{{url('{secret_path}')}}</strong></p>
                  <p class="text-warning">Try to avoid using special characters in {secret path}</p>
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
                  <button type="submit" class="btn btn-success">Update</button>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

@endsection
