@extends('admin.layout')

@if(!empty($abs->language) && $abs->language->rtl == 1)
@section('styles')
<style>
    form:not(.modal-form) input,
    form:not(.modal-form) textarea,
    form:not(.modal-form) select,
    select[name='language'] {
        direction: rtl;
    }
    form:not(.modal-form) .note-editor.note-frame .note-editing-area .note-editable {
        direction: rtl;
        text-align: right;
    }
</style>
@endsection
@endif

@section('content')
  <div class="page-header">
    <h4 class="page-title">Call to Action Section</h4>
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
        <a href="#">Call to Action Section</a>
      </li>
    </ul>
  </div>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-lg-10">
                    <div class="card-title">Update Call to Action Section</div>
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

              <form id="ctaForm" action="{{route('admin.cta.update', $lang_id)}}" method="post">
                @csrf

                @if ($abe->theme_version != 'gym' && $abe->theme_version != 'car')
                {{-- Background Part --}}
                <div class="form-group">
                    <label for="">Background ** </label>
                    <br>
                    <div class="thumb-preview" id="thumbPreview1"
                        style="position: relative; display: inline-block;">
                        @if (!empty($abs->cta_bg))
                            <img src="{{asset('assets/front/img/' . $abs->cta_bg)}}" alt="Background">
                        @else
                            <img src="{{asset('assets/admin/img/noimage.jpg')}}" alt="No Image">
                        @endif
                        <button type="button" class="btn btn-danger btn-sm delete-image" id="deleteCtaBgBtn"
                            style="position: absolute; top: 10px; right: 10px; opacity: 0; transition: opacity 0.3s;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <br>
                    <br>


                    <input id="fileInput1" type="hidden" name="background">
                    <input id="deleteCtaBg" type="hidden" name="delete_cta_bg" value="0">
                    <button id="chooseImage1" class="choose-image btn btn-primary" type="button" data-multiple="false" data-toggle="modal" data-target="#lfmModal1">Choose Image</button>


                    <p class="text-warning mb-0">JPG, PNG, JPEG, SVG images are allowed</p>
                    @if ($errors->has('background'))
                    <p class="text-danger mb-0">{{$errors->first('background')}}</p>
                    @endif

                    <!-- Background LFM Modal -->
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
                @endif

                <div class="form-group">
                  <label for="">Text **</label>
                  <input name="cta_section_text" class="form-control" value="{{$abs->cta_section_text}}">
                  <p id="errcta_section_text" class="em text-danger mb-0"></p>
                </div>
                <div class="form-group">
                  <label for="">Button Text **</label>
                  <input type="text" class="form-control" name="cta_section_button_text" value="{{$abs->cta_section_button_text}}">
                  <p id="errcta_section_button_text" class="em text-danger mb-0"></p>
                </div>
                <div class="form-group">
                  <label for="">Button URL **</label>
                  <input type="text" class="form-control ltr" name="cta_section_button_url" value="{{$abs->cta_section_button_url}}">
                  <p id="errcta_section_button_url" class="em text-danger mb-0"></p>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>CTA Area Overlay Color Code **</label>
                            <input class="jscolor form-control ltr" name="cta_overlay_color"
                                value="{{ $abe->cta_overlay_color ?? '000000' }}">
                            <p id="errcta_overlay_color" class="em text-danger mb-0"></p>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>CTA Area Overlay Opacity **</label>
                            <input type="text" class="form-control ltr" name="cta_overlay_opacity"
                                value="{{ $abe->cta_overlay_opacity ?? '0.6' }}">
                            <p class="text-warning mb-0">Opacity can be between 0 to 1.</p>
                            <p id="errcta_overlay_opacity" class="em text-danger mb-0"></p>
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
                <button type="submit" form="ctaForm" class="btn btn-success">Update</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Inline scripts for CTA section --}}
  <script type="text/javascript">
      // Wait for DOM to be ready
      document.addEventListener('DOMContentLoaded', function() {
          // Attach event listener to CTA background delete button
          var deleteCtaBgBtn = document.getElementById('deleteCtaBgBtn');
          if (deleteCtaBgBtn) {
              deleteCtaBgBtn.addEventListener('click', function(e) {
                  e.preventDefault();
                  swal({
                      title: 'Are you sure?',
                      text: 'You want to delete this background image?',
                      icon: 'warning',
                      buttons: {
                          cancel: { text: "Cancel", visible: true, closeModal: true },
                          confirm: { text: "Yes, delete it!", closeModal: true }
                      },
                      dangerMode: true,
                  }).then(function(willDelete) {
                      if (willDelete) {
                          document.getElementById('deleteCtaBg').value = '1';
                          document.getElementById('fileInput1').value = '';
                          document.getElementById('ctaForm').submit();
                      }
                  });
              });
          }

          // Show delete button on hover for CTA background
          var thumbPreview1 = document.getElementById('thumbPreview1');
          if (thumbPreview1) {
              thumbPreview1.addEventListener('mouseenter', function() {
                  var deleteBtn = this.querySelector('.delete-image');
                  if (deleteBtn) deleteBtn.style.opacity = '1';
              });

              thumbPreview1.addEventListener('mouseleave', function() {
                  var deleteBtn = this.querySelector('.delete-image');
                  if (deleteBtn) deleteBtn.style.opacity = '0';
              });
          }
      });
  </script>

@endsection
