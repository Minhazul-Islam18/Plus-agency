@extends('admin.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">Basic Informations</h4>
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
        <a href="#">Basic Informations</a>
      </li>
    </ul>
  </div>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <form class="" action="{{route('admin.basicinfo.update.default')}}" method="post">
          @csrf
          <div class="card-header">
              <div class="row">
                  <div class="col-lg-10">
                      <div class="card-title">Update Basic Informations</div>
                  </div>
              </div>
          </div>
          <div class="card-body pt-5 pb-5">
            <div class="row">
              <div class="col-lg-8 offset-lg-2">
                @csrf
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                          <label>Website Title **</label>
                          <input class="form-control" name="website_title" value="{{$abs->website_title}}">
                          @if ($errors->has('website_title'))
                            <p class="mb-0 text-danger">{{$errors->first('website_title')}}</p>
                          @endif
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="">Timezone</label>
                            <select class="form-control select2" name="timezone">
                                <option value="" selected disabled>Select a Timezone</option>
                                @foreach ($timezones as $timezone)
                                <option value="{{$timezone->timezone}}" {{$abx->timezone == $timezone->timezone ? 'selected' : ''}}>{{$timezone->timezone}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>


                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>Base Currency Symbol **</label>
                            <input type="text" class="form-control ltr" name="base_currency_symbol" value="{{$abx->base_currency_symbol}}">
                            @if ($errors->has('base_currency_symbol'))
                              <p class="mb-0 text-danger">{{$errors->first('base_currency_symbol')}}</p>
                            @endif
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>Base Currency Symbol Position **</label>
                            <select name="base_currency_symbol_position" class="form-control ltr">
                                <option value="left" {{$abx->base_currency_symbol_position == 'left' ? 'selected' : ''}}>Left</option>
                                <option value="right" {{$abx->base_currency_symbol_position == 'right' ? 'selected' : ''}}>Right</option>
                            </select>
                            @if ($errors->has('base_currency_symbol_position'))
                              <p class="mb-0 text-danger">{{$errors->first('base_currency_symbol_position')}}</p>
                            @endif
                        </div>
                    </div>
                </div>



                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label>Base Currency Text **</label>
                            <input type="text" class="form-control ltr" name="base_currency_text" value="{{$abx->base_currency_text}}">
                            @if ($errors->has('base_currency_text'))
                              <p class="mb-0 text-danger">{{$errors->first('base_currency_text')}}</p>
                            @endif
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label>Base Currency Text Position **</label>
                            <select name="base_currency_text_position" class="form-control ltr">
                                <option value="left" {{$abx->base_currency_text_position == 'left' ? 'selected' : ''}}>Left</option>
                                <option value="right" {{$abx->base_currency_text_position == 'right' ? 'selected' : ''}}>Right</option>
                            </select>
                            @if ($errors->has('base_currency_text_position'))
                              <p class="mb-0 text-danger">{{$errors->first('base_currency_text_position')}}</p>
                            @endif
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label>Base Currency Rate **</label>
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                  <span class="input-group-text">1 USD =</span>
                                </div>
                                <input type="text" name="base_currency_rate" class="form-control ltr" value="{{$abx->base_currency_rate}}">
                                <div class="input-group-append">
                                  <span class="input-group-text">{{$abx->base_currency_text}}</span>
                                </div>
                            </div>

                            @if ($errors->has('base_currency_rate'))
                              <p class="mb-0 text-danger">{{$errors->first('base_currency_rate')}}</p>
                            @endif
                        </div>
                    </div>
                </div>



                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                          <label>Base Color Code **</label>
                          <input class="jscolor form-control ltr" name="base_color" value="{{$abs->base_color}}">
                          @if ($errors->has('base_color'))
                            <p class="mb-0 text-danger">{{$errors->first('base_color')}}</p>
                          @endif
                        </div>
                    </div>
                    <div class="col-lg-6">

                        <div class="form-group">
                            <label>{{ $abe->theme_version == 'dark' ? 'Gradient / Glow Color Code **' : 'Secondary Base Color Code **' }}</label>
                            <input class="jscolor form-control ltr" name="secondary_base_color" value="{{$abs->secondary_base_color}}">
                            @if ($errors->has('secondary_base_color'))
                                <p class="mb-0 text-danger">{{$errors->first('secondary_base_color')}}</p>
                            @endif
                        </div>
                    </div>
                </div>


                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>Hero Area Overlay Color Code **</label>
                            <input class="jscolor form-control ltr" name="hero_area_overlay_color" value="{{$abe->hero_overlay_color}}">
                            @if ($errors->has('hero_area_overlay_color'))
                            <p class="mb-0 text-danger">{{$errors->first('hero_area_overlay_color')}}</p>
                            @endif
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>Hero Area Overlay Opacity **</label>
                            <input type="text" class="form-control ltr" name="hero_area_overlay_opacity" value="{{$abe->hero_overlay_opacity}}">
                            <p class="text-warning mb-0">Opacity can be between 0 to 1.</p>
                            @if ($errors->has('hero_area_overlay_opacity'))
                            <p class="mb-0 text-danger">{{$errors->first('hero_area_overlay_opacity')}}</p>
                            @endif
                        </div>
                    </div>
                </div>


                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>Breadcrumb Area Overlay Color Code **</label>
                            <input class="jscolor form-control ltr" name="breadcrumb_area_overlay_color" value="{{$abe->breadcrumb_overlay_color}}">
                            @if ($errors->has('breadcrumb_area_overlay_color'))
                              <p class="mb-0 text-danger">{{$errors->first('breadcrumb_area_overlay_color')}}</p>
                            @endif
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>Breadcrumb Area Overlay Opacity **</label>
                            <input type="text" class="form-control ltr" name="breadcrumb_area_overlay_opacity" value="{{$abe->breadcrumb_overlay_opacity}}">
                            <p class="text-warning mb-0">Opacity can be between 0 to 1.</p>
                            @if ($errors->has('breadcrumb_area_overlay_opacity'))
                              <p class="mb-0 text-danger">{{$errors->first('breadcrumb_area_overlay_opacity')}}</p>
                            @endif
                        </div>
                    </div>
                </div>

              </div>
            </div>
          </div>
          <div class="card-footer">
            <div class="form">
              <div class="form-group from-show-notify row">
                <div class="col-12 text-center">
                  <button type="submit" id="displayNotif" class="btn btn-success">Update</button>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <form action="{{ route('admin.file-manager.image-settings') }}" method="POST">
          @csrf
          <div class="card-header">
            <div class="card-title">Image Optimization</div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-lg-4">
                <div class="form-group">
                  <label>Auto Compress &amp; Convert</label>
                  <div class="selectgroup w-100">
                    <label class="selectgroup-item">
                      <input type="radio" name="image_convert_enabled" value="1"
                        class="selectgroup-input" {{ $abx->image_convert_enabled == 1 ? 'checked' : '' }}>
                      <span class="selectgroup-button">Active</span>
                    </label>
                    <label class="selectgroup-item">
                      <input type="radio" name="image_convert_enabled" value="0"
                        class="selectgroup-input" {{ $abx->image_convert_enabled == 0 ? 'checked' : '' }}>
                      <span class="selectgroup-button">Deactive</span>
                    </label>
                  </div>
                  @if ($errors->has('image_convert_enabled'))
                    <p class="mb-0 text-danger">{{ $errors->first('image_convert_enabled') }}</p>
                  @endif
                  <small class="text-muted">Converts new jpg/png uploads in File Manager. SVG and animated GIF are always left as-is.</small>
                </div>
              </div>
              <div class="col-lg-4">
                <div class="form-group">
                  <label>Convert To</label>
                  <select class="form-control" name="image_convert_format">
                    <option value="webp" {{ $abx->image_convert_format == 'webp' ? 'selected' : '' }}>WebP</option>
                    <option value="avif" {{ $abx->image_convert_format == 'avif' ? 'selected' : '' }}>AVIF</option>
                  </select>
                  @if ($errors->has('image_convert_format'))
                    <p class="mb-0 text-danger">{{ $errors->first('image_convert_format') }}</p>
                  @endif
                </div>
              </div>
              <div class="col-lg-4">
                <div class="form-group">
                  <label>Compression Quality (1-100)</label>
                  <input type="number" class="form-control" name="image_convert_quality" min="1" max="100"
                    value="{{ $abx->image_convert_quality ?? 80 }}">
                  @if ($errors->has('image_convert_quality'))
                    <p class="mb-0 text-danger">{{ $errors->first('image_convert_quality') }}</p>
                  @endif
                </div>
              </div>
            </div>
          </div>
          <div class="card-footer">
            <div class="form">
              <div class="form-group from-show-notify row">
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

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <form action="{{ route('admin.file-manager.upload-limits') }}" method="POST">
          @csrf
          <div class="card-header">
            <div class="card-title">Upload Size Limit</div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-lg-6">
                <div class="form-group">
                  <label>Max Image Upload Size (MB)</label>
                  <input type="number" class="form-control" name="lfm_max_image_size_mb" min="1" max="500"
                    value="{{ $abx->lfm_max_image_size_mb ?? 20 }}">
                  @if ($errors->has('lfm_max_image_size_mb'))
                    <p class="mb-0 text-danger">{{ $errors->first('lfm_max_image_size_mb') }}</p>
                  @endif
                  <small class="text-muted">Applies to jpg/png/svg/webp/avif uploads in File Manager.</small>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="form-group">
                  <label>Max Other File Upload Size (MB)</label>
                  <input type="number" class="form-control" name="lfm_max_file_size_mb" min="1" max="500"
                    value="{{ $abx->lfm_max_file_size_mb ?? 50 }}">
                  @if ($errors->has('lfm_max_file_size_mb'))
                    <p class="mb-0 text-danger">{{ $errors->first('lfm_max_file_size_mb') }}</p>
                  @endif
                  <small class="text-muted">Applies to pdf/zip/txt/mp4 uploads in File Manager.</small>
                </div>
              </div>
            </div>
            <p class="text-warning mb-0"><small><i class="fas fa-info-circle"></i> Cannot exceed the server's own PHP upload limit ({{ ini_get('upload_max_filesize') }} / {{ ini_get('post_max_size') }}) — raise those in php.ini too if you need more.</small></p>
          </div>
          <div class="card-footer">
            <div class="form">
              <div class="form-group from-show-notify row">
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
