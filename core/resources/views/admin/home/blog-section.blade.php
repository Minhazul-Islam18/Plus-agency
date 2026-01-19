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
    <h4 class="page-title">Blog Section</h4>
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
        <a href="#">Blog Section</a>
      </li>
    </ul>
  </div>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-lg-10">
                    <div class="card-title">Update Blog Section</div>
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

              <form id="blogForm" action="{{route('admin.blogsection.update', $lang_id)}}" method="post">
                @csrf

                {{-- Background Part --}}
                <div class="form-group">
                    <label for="">Background (Optional)</label>
                    <br>
                    <div class="thumb-preview" id="thumbPreview1" style="position: relative; display: inline-block;">
                        @if (!empty($abe->blog_bg))
                            <img src="{{asset('assets/front/img/'.$abe->blog_bg)}}" alt="Background">
                        @else
                            <img src="{{asset('assets/admin/img/noimage.jpg')}}" alt="No Image">
                        @endif
                        <button type="button" class="btn btn-danger btn-sm delete-image" id="deleteBlogBgBtn"
                            style="position: absolute; top: 10px; right: 10px; opacity: 0; transition: opacity 0.3s;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <br>
                    <br>

                    <input id="fileInput1" type="hidden" name="background">
                    <input id="deleteBackground" type="hidden" name="delete_background" value="0">
                    <button id="chooseImage1" class="choose-image btn btn-primary" type="button" data-multiple="false" data-toggle="modal" data-target="#lfmModal1">Choose Image</button>

                    <p class="text-warning mb-0">JPG, PNG, JPEG, SVG images are allowed</p>
                    <p id="errbackground" class="em text-danger mb-0"></p>

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

                <div class="form-group">
                  <label for="">Title **</label>
                  <input name="blog_section_title" class="form-control" value="{{$abs->blog_section_title}}">
                  <p id="errblog_section_title" class="em text-danger mb-0"></p>
                </div>
                <div class="form-group">
                  <label for="">Text **</label>
                  <input name="blog_section_subtitle" class="form-control" value="{{$abs->blog_section_subtitle}}">
                  <p id="errblog_section_subtitle" class="em text-danger mb-0"></p>
                </div>

                <div class="form-group">
                    <label>Blog Area Overlay Color Code **</label>
                    <input class="jscolor form-control ltr" name="blog_overlay_color" value="{{$abe->blog_overlay_color ?? '000000'}}">
                    <p id="errblog_overlay_color" class="em text-danger mb-0"></p>
                </div>
                <div class="form-group">
                    <label>Blog Area Overlay Opacity **</label>
                    <input type="text" class="form-control ltr" name="blog_overlay_opacity" value="{{$abe->blog_overlay_opacity ?? '0.6'}}">
                    <p class="text-warning mb-0">Opacity can be between 0 to 1.</p>
                    <p id="errblog_overlay_opacity" class="em text-danger mb-0"></p>
                </div>
              </form>

            </div>
          </div>
        </div>

        <div class="card-footer">
          <div class="form">
            <div class="form-group from-show-notify row">
              <div class="col-12 text-center">
                <button type="submit" id="blogSubmitBtn" class="btn btn-success">Update</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

@endsection

@section('scripts')
    <script type="text/javascript">
        // Laravel File Manager SetUrl handler
        window.SetUrl = function(items) {
            // Get the active modal's serial number
            var activeModal = $('.lfm-modal.show');
            var modalId = activeModal.attr('id');
            var serial = modalId ? modalId.replace('lfmModal', '') : '';

            if (items && items.length > 0) {
                var fileUrl = items[0].url;

                // Set value to corresponding fileInput
                if (serial) {
                    var fileInput = document.getElementById('fileInput' + serial);
                    var thumbPreview = document.getElementById('thumbPreview' + serial);

                    if (fileInput) {
                        fileInput.value = fileUrl;
                    }

                    if (thumbPreview) {
                        var img = thumbPreview.querySelector('img');
                        if (img) {
                            img.src = fileUrl;
                        }
                    }

                    // Close the modal
                    if (typeof window.closeLfmModal === 'function') {
                        window.closeLfmModal(serial);
                    }
                }
            }
        };

        // Wait for DOM to be ready
        document.addEventListener('DOMContentLoaded', function() {
            // Attach event listener to blog background delete button
            var deleteBlogBgBtn = document.getElementById('deleteBlogBgBtn');
            if (deleteBlogBgBtn) {
                deleteBlogBgBtn.addEventListener('click', function(e) {
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
                            document.getElementById('deleteBackground').value = '1';
                            document.getElementById('fileInput1').value = '';
                            document.getElementById('blogForm').submit();
                        }
                    });
                });
            }

            // Handle Update button click
            var blogSubmitBtn = document.getElementById('blogSubmitBtn');
            if (blogSubmitBtn) {
                blogSubmitBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    // Submit the form with page reload
                    document.getElementById('blogForm').submit();
                });
            }

            // Show delete button on hover for blog background
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
