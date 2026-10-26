@extends('admin.layout')

@section('styles')
<style>
    @if(!empty($video->language) && $video->language->rtl == 1)
    form input,
    form textarea,
    form select,
    select {
        direction: rtl;
    }
    @endif

    .video-source-toggle {
        display: flex;
        gap: 12px;
    }
    .video-source-toggle-item {
        flex: 1;
        margin: 0;
        cursor: pointer;
    }
    .video-source-toggle-item input {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }
    .video-source-toggle-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 14px 10px;
        border: 1px solid #ffffff2a;
        border-radius: 8px;
        font-weight: 500;
        transition: border-color 0.15s ease, background 0.15s ease;
    }
    .video-source-toggle-btn svg {
        width: 20px;
        height: 20px;
        flex-shrink: 0;
    }
    .video-source-toggle-item input:checked + .video-source-toggle-btn {
        border-color: #4b6bfb;
        background: rgba(75, 107, 251, 0.1);
    }
    .video-thumb-dropzone {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 10px;
        text-align: center;
        padding: 30px 16px;
        border: 1px dashed #ffffff3a;
        border-radius: 8px;
        cursor: pointer;
    }
    .video-thumb-dropzone svg {
        width: 34px;
        height: 34px;
        opacity: 0.6;
    }
    .video-thumb-dropzone p {
        margin: 0;
        font-size: 13px;
        color: #aab0c0;
        max-width: 240px;
    }
    .video-thumb-preview {
        position: relative;
        border-radius: 8px;
        overflow: hidden;
        line-height: 0;
    }
    .video-thumb-preview img {
        width: 100%;
        max-height: 260px;
        object-fit: cover;
    }
    @media (max-width: 991px) {
        .video-source-row .col-lg-6:last-child {
            margin-top: 20px;
        }
    }
</style>
@endsection

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
            <div class="col-lg-10 offset-lg-1">

              <form id="ajaxForm" action="{{route('admin.darkherovideo.update', $lang_id)}}" method="post">
                @csrf

                <div class="alert alert-warning">
                    <strong>Performance matters here.</strong> Keep the video short (5–15s), compressed (a few MB, not tens), and silent — it loops muted in the background. A Poster Image is required: it shows instantly while the video loads in behind it, and is all mobile visitors see by default.
                </div>

                <div class="row video-source-row">
                    <div class="col-lg-6">
                        {{-- Video source --}}
                        <div class="form-group">
                            <label>Video Source **</label>
                            <div class="video-source-toggle">
                                <label class="video-source-toggle-item">
                                    <input type="radio" name="video_source" value="local" data-video-source-toggle {{ ($video->video_source ?? 'local') == 'local' ? 'checked' : '' }}>
                                    <span class="video-source-toggle-btn">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                        Local file
                                    </span>
                                </label>
                                <label class="video-source-toggle-item">
                                    <input type="radio" name="video_source" value="youtube" data-video-source-toggle {{ ($video->video_source ?? 'local') == 'youtube' ? 'checked' : '' }}>
                                    <span class="video-source-toggle-btn">
                                        <svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10" fill="#ff0000"/><path d="M10 8.5v7l6-3.5z" fill="#fff"/></svg>
                                        YouTube
                                    </span>
                                </label>
                            </div>
                        </div>

                        {{-- Video file (local) --}}
                        <div class="form-group" data-video-source-panel="local">
                            <label for="">Background Video (MP4) **</label>
                            @if (!empty($video->video_file))
                                <p class="mb-2"><a href="{{ asset('assets/front/img/hero-video/' . $video->video_file) }}" target="_blank">Current video file &rarr;</a></p>
                            @endif
                            <input id="fileInput1" type="hidden" name="video_file">
                            <button id="chooseImage1" class="choose-image btn btn-primary" type="button"
                                data-multiple="false" data-toggle="modal" data-target="#lfmModal1">Choose Video</button>
                            <p class="text-warning mb-0"><small>MP4 only.</small></p>
                            <p class="text-warning mb-0"><small>Recommended size: 1920x1080px (16:9 landscape), same frame as the Poster Image.</small></p>
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

                        {{-- YouTube URL --}}
                        <div class="form-group" data-video-source-panel="youtube">
                            <label for="">YouTube URL **</label>
                            <input type="text" class="form-control ltr" name="youtube_url" value="{{ $video->youtube_url }}" placeholder="https://www.youtube.com/watch?v=...">
                            <p class="text-warning mb-0"><small>Accepts a watch, youtu.be, or /shorts/ link. Plays muted and looped in the background automatically, same as a Local file video — nothing is fetched from YouTube until the page has finished loading.</small></p>
                            <p id="erryoutube_url" class="em text-danger mb-0"></p>
                        </div>

                        {{-- Poster image: dropzone --}}
                        <div class="form-group">
                            <label for="">Poster Image **</label>
                            <div class="video-thumb-dropzone choose-image" id="chooseImage2" role="button"
                                data-multiple="false" data-toggle="modal" data-target="#lfmModal2">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
                                <p>Upload an optimized image displayed before the video starts</p>
                                <span class="btn btn-primary btn-sm">Choose Image</span>
                            </div>
                            <input id="fileInput2" type="hidden" name="poster_image">
                            <p class="text-warning mb-0 mt-2"><small>{{ allowed_image_extensions_label() }} images are allowed. Shown instantly, and used instead of the video on mobile (see below).</small></p>
                            <p class="text-warning mb-0"><small>Recommended size: 1920x1080px (full-bleed background, same as the static Hero Image).</small></p>
                            <p class="text-warning mb-0" data-video-source-panel="youtube"><small>Optional for YouTube — leave empty to use YouTube's own thumbnail automatically.</small></p>
                            <p id="errposter_image" class="em text-danger mb-0"></p>

                            <div class="modal fade lfm-modal" id="lfmModal2" tabindex="-1" role="dialog" aria-hidden="true">
                                <i class="fas fa-times-circle"></i>
                                <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                    <div class="modal-content">
                                        <div class="modal-body p-0">
                                            <iframe src="{{ url('laravel-filemanager') }}?serial=2&rw=1920&rh=1080&rl=Poster+Image"
                                                style="width: 100%; height: 500px; overflow: hidden; border: none;"></iframe>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <label>Live Preview</label>
                        <div class="video-thumb-preview" id="thumbPreview2">
                            <img src="{{ !empty($video->poster_image) ? asset('assets/front/img/hero-video/' . $video->poster_image) : asset('assets/admin/img/noimage.jpg') }}" alt="Poster Image">
                        </div>
                        <p class="text-muted mb-0 mt-2"><small>This is shown instantly while the video loads in behind it — no icon is overlaid on the live site, it plays automatically.</small></p>
                    </div>
                </div>

                <div class="form-group">
                    <label for="">Scroll Prompt Text</label>
                    <input type="text" class="form-control" name="scroll_text" value="{{ $video->scroll_text }}" placeholder="SCROLL TO EXPLORE" maxlength="100">
                    <p class="text-warning mb-0"><small>The small orbiting text at the bottom of the hero. Leave empty for the default "SCROLL TO EXPLORE".</small></p>
                    <p id="errscroll_text" class="em text-danger mb-0"></p>
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

  <script>
      (function () {
          var toggles = document.querySelectorAll('[data-video-source-toggle]');
          var panels = document.querySelectorAll('[data-video-source-panel]');

          function sync() {
              var checked = document.querySelector('[data-video-source-toggle]:checked');
              var source = checked ? checked.value : 'local';
              panels.forEach(function (panel) {
                  panel.style.display = panel.getAttribute('data-video-source-panel') === source ? '' : 'none';
              });
          }

          toggles.forEach(function (el) {
              el.addEventListener('change', sync);
          });
          sync();
      })();
  </script>

@endsection
