@extends('admin.layout')
@section('content')
<div class="page-header">
   <h4 class="page-title">Pages</h4>
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
         <a href="#">Create Page</a>
      </li>
      <li class="separator">
         <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
         <a href="#">Pages</a>
      </li>
   </ul>
</div>
<div class="row">
   <div class="col-md-12">
      <div class="card">
         <div class="card-header">
            <div class="card-title">Create Page</div>
         </div>
         <div class="card-body pt-5 pb-4">
            <div class="row">
               <div class="col-lg-10 offset-lg-1">
                  <form id="ajaxForm" action="{{route('admin.page.store')}}" method="post" enctype="multipart/form-data">
                     @csrf
                    <div class="form-group">
                        <label for="">Language **</label>
                        <select id="language" name="language_id" class="form-control">
                            <option value="" selected disabled>Select a language</option>
                            @foreach ($langs as $lang)
                            <option value="{{$lang->id}}">{{$lang->name}}</option>
                            @endforeach
                        </select>
                        <p id="errlanguage_id" class="mb-0 text-danger em"></p>
                    </div>

                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label for="">Name **</label>
                                <input type="text" name="name" class="form-control" placeholder="Enter Name" value="">
                                <p id="errname" class="em text-danger mb-0"></p>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label for="">Breadcrumb Title </label>
                                <input type="text" name="breadcrumb_title" class="form-control" placeholder="Enter Name" value="">
                                <p id="errbreadcrumb_title" class="em text-danger mb-0"></p>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label for="">Breadcrumb Subtitle </label>
                                <input type="text" name="breadcrumb_subtitle" class="form-control" placeholder="Enter Name" value="">
                                <p id="errbreadcrumb_subtitle" class="em text-danger mb-0"></p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="">Status **</label>
                                <select class="form-control ltr" name="status">
                                    <option value="1">Active</option>
                                    <option value="0">Deactive</option>
                                </select>
                                <p id="errstatus" class="em text-danger mb-0"></p>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                               <label for="">Serial Number **</label>
                               <input type="number" class="form-control ltr" name="serial_number" value="" placeholder="Enter Serial Number">
                               <p id="errserial_number" class="mb-0 text-danger em"></p>
                               <p class="text-warning mb-0"><small>The higher the serial number is, the later the page will be shown in menu.</small></p>
                            </div>
                        </div>
                    </div>

                    {{-- Breadcrumb Section --}}
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5 class="text-primary mb-3">Breadcrumb Settings</h5>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="">Breadcrumb Image</label>
                                <br>
                                <div class="thumb-preview" id="thumbPreview1">
                                    <img src="{{asset('assets/admin/img/noimage.jpg')}}" alt="Breadcrumb" class="uploaded-img">
                                </div>
                                <br>
                                <br>
                                <input id="fileInput1" type="hidden" name="breadcrumb_image" value="">
                                <button id="chooseImage1" class="choose-image btn btn-primary" type="button" data-multiple="false" data-toggle="modal" data-target="#lfmModal1">Choose Image</button>
                                <p class="text-warning mb-0"><small>Leave empty to use default breadcrumb image.</small></p>
                                <p class="text-info mb-0"><small><strong>Recommended size:</strong> 1920px × 350px (Width × Height)</small></p>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label for="">Overlay Color</label>
                                <input type="text" name="breadcrumb_overlay_color" class="form-control ltr jscolor" value="" placeholder="e.g. 000000">
                                <p class="text-warning mb-0"><small>Leave empty to use default overlay color.</small></p>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label for="">Overlay Opacity</label>
                                <input type="number" step="0.01" min="0" max="1" name="breadcrumb_overlay_opacity" class="form-control ltr" value="" placeholder="e.g. 0.5">
                                <p class="text-warning mb-0"><small>Value between 0 and 1. Leave empty for default.</small></p>
                            </div>
                        </div>
                    </div>

                    @if ($bex->custom_page_pagebuilder == 0)
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="">Body **</label>
                                    <textarea id="body" class="form-control summernote" name="body" data-height="500"></textarea>
                                    <p id="errbody" class="em text-danger mb-0"></p>
                                </div>
                            </div>
                        </div>
                    @endif

                     <div class="form-group">
                        <label>Meta Keywords</label>
                        <input class="form-control" name="meta_keywords" value="" placeholder="Enter meta keywords" data-role="tagsinput">
                     </div>
                     <div class="form-group">
                        <label>Meta Description</label>
                        <textarea class="form-control" name="meta_description" rows="5" placeholder="Enter meta description"></textarea>
                     </div>
                  </form>
               </div>
            </div>
         </div>
         <div class="card-footer">
            <div class="form">
               <div class="form-group from-show-notify row">
                  <div class="col-12 text-center">
                     <button type="submit" id="submitBtn" class="btn btn-success">Submit</button>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
@endsection
@section('scripts')
    <!-- LFM Modal -->
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

    <script>
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

                        // Add delete button if not exists
                        if (thumbPreview.querySelector('.remove-img-btn') === null) {
                            var deleteBtn = document.createElement('button');
                            deleteBtn.type = 'button';
                            deleteBtn.className = 'btn btn-danger btn-sm remove-img-btn';
                            deleteBtn.innerHTML = '<i class="fas fa-times"></i>';
                            thumbPreview.appendChild(deleteBtn);
                        }
                    }

                    // Close the modal
                    activeModal.modal('hide');
                }
            }
        };

        $(document).ready(function() {
            // Remove image button click handler (for newly selected images)
            $(document).on('click', '.remove-img-btn', function(e) {
                e.preventDefault();
                var thumbPreview = $(this).closest('.thumb-preview');
                var serial = thumbPreview.attr('id').replace('thumbPreview', '');

                thumbPreview.find('img').attr('src', '{{asset("assets/admin/img/noimage.jpg")}}');
                $('#fileInput' + serial).val('');
                $(this).remove();
            });

            // make input fields RTL
            $("select[name='language_id']").on('change', function() {
                $(".request-loader").addClass("show");
                let url = "{{url('/')}}/admin/rtlcheck/" + $(this).val();
                console.log(url);
                $.get(url, function(data) {
                    $(".request-loader").removeClass("show");
                    if (data == 1) {
                        $("form input").each(function() {
                            if (!$(this).hasClass('ltr')) {
                                $(this).addClass('rtl');
                            }
                        });
                        $("form select").each(function() {
                            if (!$(this).hasClass('ltr')) {
                                $(this).addClass('rtl');
                            }
                        });
                        $("form textarea").each(function() {
                            if (!$(this).hasClass('ltr')) {
                                $(this).addClass('rtl');
                            }
                        });
                        $("form .summernote").each(function() {
                            $(this).siblings('.note-editor').find('.note-editable').addClass('rtl text-right');
                        });

                    } else {
                        $("form input, form select, form textarea").removeClass('rtl');
                        $("form .summernote").siblings('.note-editor').find('.note-editable').removeClass('rtl text-right');
                    }
                })
            });
        });
    </script>

    <style>
        .thumb-preview {
            position: relative;
            display: inline-block;
        }

        .thumb-preview .uploaded-img,
        .thumb-preview img {
            max-width: 100%;
            max-height: 300px;
            border: 2px solid #ddd;
            border-radius: 5px;
        }

        .thumb-preview .remove-img-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            opacity: 0;
            transition: opacity 0.3s;
            z-index: 10;
        }

        .thumb-preview:hover .remove-img-btn {
            opacity: 1;
        }
    </style>
@endsection
