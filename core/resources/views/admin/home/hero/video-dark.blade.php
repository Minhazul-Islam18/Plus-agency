@extends('admin.layout')

@if(!empty($video->language) && $video->language->rtl == 1)
@section('styles')
<style>
    form input,
    form textarea,
    form select,
    select {
        direction: rtl;
    }
</style>
@endsection
@endif

@section('content')
  <div class="page-header">
    <h4 class="page-title">Dark Theme Hero — Video</h4>
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
        <a href="#">Home Page</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">Dark Theme Hero — Video</a>
      </li>
    </ul>
  </div>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-lg-10">
                    <div class="card-title">Update Video Hero</div>
                    <p class="text-muted mb-0">Only used when Theme & Home &rarr; Settings has Dark theme + Video selected.</p>
                </div>
                <div class="col-lg-2">
                    @if (!empty($langs))
                        <select name="language" class="form-control" onchange="window.location='{{url()->current() . '?language='}}'+this.value">
                            <option value="" selected disabled>Select a Language</option>
                            @foreach ($langs as $lang)
                                <option value="{{$lang->code}}" {{$lang->code == request()->input('language') ? 'selected' : ''}}>{{$lang->name}}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
            </div>
        </div>
        <div class="card-body pt-5 pb-4">
          <div class="row">
            <div class="col-lg-6 offset-lg-3">

              <form id="ajaxForm" action="{{route('admin.darkherovideo.update', $lang_id)}}" method="post">
                @csrf

                <div class="alert alert-warning">
                    <strong>Performance matters here.</strong> Keep the video short (5–15s), compressed (a few MB, not tens), and silent — it loops muted in the background. A Poster Image is required: it shows instantly while the video loads in behind it, and is all mobile visitors see by default.
                </div>

                {{-- Video file --}}
                <div class="form-group">
                    <label for="">Background Video (MP4) **</label>
                    <br>
                    @if (!empty($video->video_file))
                        <p class="mb-2"><a href="{{ asset('assets/front/img/hero-video/' . $video->video_file) }}" target="_blank">Current video file &rarr;</a></p>
                    @endif
                    <input id="fileInput1" type="hidden" name="video_file">
                    <button id="chooseImage1" class="choose-image btn btn-primary" type="button"
                        data-multiple="false" data-toggle="modal" data-target="#lfmModal1">Choose Video</button>
                    <p class="text-warning mb-0"><small>MP4 only.</small></p>
                    <p id="errvideo_file" class="em text-danger mb-0"></p>

                    <div class="modal fade lfm-modal" id="lfmModal1" tabindex="-1" role="dialog" aria-hidden="true">
                        <i class="fas fa-times-circle"></i>
                        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-body p-0">
                                    <iframe src="{{ url('laravel-filemanager') }}?type=file&serial=1"
                                        style="width: 100%; height: 500px; overflow: hidden; border: none;"></iframe>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Poster image --}}
                <div class="form-group">
                    <label for="">Poster Image **</label>
                    <br>
                    <div class="thumb-preview" id="thumbPreview2">
                        <img src="{{ !empty($video->poster_image) ? asset('assets/front/img/hero-video/' . $video->poster_image) : asset('assets/admin/img/noimage.jpg') }}" alt="Poster Image">
                    </div>
                    <br>
                    <input id="fileInput2" type="hidden" name="poster_image">
                    <button id="chooseImage2" class="choose-image btn btn-primary" type="button"
                        data-multiple="false" data-toggle="modal" data-target="#lfmModal2">Choose Image</button>
                    <p class="text-warning mb-0"><small>{{ allowed_image_extensions_label() }} images are allowed. Shown instantly, and used instead of the video on mobile (see below).</small></p>
                    <p id="errposter_image" class="em text-danger mb-0"></p>

                    <div class="modal fade lfm-modal" id="lfmModal2" tabindex="-1" role="dialog" aria-hidden="true">
                        <i class="fas fa-times-circle"></i>
                        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-body p-0">
                                    <iframe src="{{ url('laravel-filemanager') }}?serial=2"
                                        style="width: 100%; height: 500px; overflow: hidden; border: none;"></iframe>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>Overlay Color Code **</label>
                            <input class="jscolor form-control ltr" name="overlay_color" value="{{ $video->overlay_color ?? '090d16' }}">
                            <p id="erroverlay_color" class="em text-danger mb-0"></p>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>Overlay Opacity **</label>
                            <input type="text" class="form-control ltr" name="overlay_opacity" value="{{ $video->overlay_opacity ?? '0.55' }}">
                            <p class="text-warning mb-0">Opacity can be between 0 to 1. A flat tint over the video for text legibility.</p>
                            <p id="erroverlay_opacity" class="em text-danger mb-0"></p>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Disable video on mobile **</label>
                    <div class="selectgroup w-100">
                        <label class="selectgroup-item">
                            <input type="radio" name="disable_on_mobile" value="1" class="selectgroup-input" {{ ($video->disable_on_mobile ?? 1) == 1 ? 'checked' : '' }}>
                            <span class="selectgroup-button">Active (recommended)</span>
                        </label>
                        <label class="selectgroup-item">
                            <input type="radio" name="disable_on_mobile" value="0" class="selectgroup-input" {{ ($video->disable_on_mobile ?? 1) == 0 ? 'checked' : '' }}>
                            <span class="selectgroup-button">Deactive</span>
                        </label>
                    </div>
                    <p class="text-warning mb-0"><small>Mobile visitors see the Poster Image only — no video ever loads. Keeps the page fast on cellular data; most browsers restrict autoplaying video on mobile anyway.</small></p>
                </div>

                <div class="form-group">
                    <label for="">Eyebrow Badge <small class="text-muted">(small label above the title)</small></label>
                    <input type="text" class="form-control" name="eyebrow" value="{{$video->eyebrow}}" placeholder="e.g. Financial &amp; Strategic Advisory">
                    <p id="erreyebrow" class="em text-danger mb-0"></p>
                </div>

                <div class="form-group">
                    <label for="">Titles</label>
                    @include('admin.partials.hero-titles-repeater', [
                        'fieldName' => 'titles',
                        'titles' => $video->titles ?? [],
                        'withAccent' => true,
                    ])
                    <p id="errtitles" class="em text-danger mb-0"></p>
                </div>

                <div class="form-group">
                    <label for="">Text</label>
                    <input type="text" class="form-control" name="text" value="{{$video->text}}">
                    <p id="errtext" class="em text-danger mb-0"></p>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="">Button Text</label>
                            <input type="text" class="form-control" name="button_text" value="{{$video->button_text}}">
                            <p id="errbutton_text" class="em text-danger mb-0"></p>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="">Button URL</label>
                            <input type="text" class="form-control ltr" name="button_url" value="{{$video->button_url}}">
                            <p id="errbutton_url" class="em text-danger mb-0"></p>
                        </div>
                    </div>
                </div>

              </form>

            </div>
          </div>
        </div>

        <div class="card-footer">
          <div class="form">
            <div class="form-group from-show-notify row">
              <div class="col-12 text-center">
                <button type="submit" id="submitBtn" class="btn btn-success">Update</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

@endsection
