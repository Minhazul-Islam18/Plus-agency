@extends('admin.layout')

@if (!empty($page->language) && $page->language->rtl == 1)
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
        <h4 class="page-title">Pages</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="flaticon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">Edit Page</a>
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
                    <div class="card-title d-inline-block">Edit Page</div>
                    <a class="btn btn-info btn-sm float-right d-inline-block"
                        href="{{ route('admin.page.index') . '?language=' . request()->input('language') }}">
                        <span class="btn-label">
                            <i class="fas fa-backward" style="font-size: 12px;"></i>
                        </span>
                        Back
                    </a>
                </div>
                <div class="card-body pt-5 pb-4">
                    <div class="row">
                        <div class="col-lg-10 offset-lg-1">
                            <form id="ajaxForm" action="{{ route('admin.page.update') }}" method="post"
                                enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="pageid" value="{{ $page->id }}">
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="">Name **</label>
                                            <input type="text" name="name" class="form-control"
                                                placeholder="Enter Name" value="{{ $page->name }}">
                                            <p id="errname" class="em text-danger mb-0"></p>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="">Breadcrumb Title </label>
                                            <input type="text" name="breadcrumb_title" class="form-control"
                                                placeholder="Enter Name" value="{{ $page->title }}">
                                            <p id="errbreadcrumb_title" class="em text-danger mb-0"></p>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="">Breadcrumb Subtitle </label>
                                            <input type="text" name="breadcrumb_subtitle" class="form-control"
                                                placeholder="Enter Name" value="{{ $page->subtitle }}">
                                            <p id="errbreadcrumb_subtitle" class="em text-danger mb-0"></p>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="">Status **</label>
                                            <select class="form-control ltr" name="status">
                                                <option value="1" {{ $page->status == 1 ? 'selected' : '' }}>Active
                                                </option>
                                                <option value="0" {{ $page->status == 0 ? 'selected' : '' }}>Deactive
                                                </option>
                                            </select>
                                            <p id="errstatus" class="em text-danger mb-0"></p>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="">Serial Number **</label>
                                            <input type="number" class="form-control ltr" name="serial_number"
                                                value="{{ $page->serial_number }}" placeholder="Enter Serial Number">
                                            <p id="errserial_number" class="mb-0 text-danger em"></p>
                                            <p class="text-warning mb-0"><small>The higher the serial number is, the later
                                                    the page will be shown in menu.</small></p>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="">{{ __('Page Type') }}</label>
                                            <select class="form-control ltr" name="page_type">
                                                <option value="" {{ empty($page->page_type) ? 'selected' : '' }}>
                                                    {{ __('Normal') }}</option>
                                                @foreach (\App\Page::SPECIAL_TYPES as $key => $label)
                                                    <option value="{{ $key }}"
                                                        {{ $page->page_type == $key ? 'selected' : '' }}>
                                                        {{ __($label) }}</option>
                                                @endforeach
                                            </select>
                                            <p id="errpage_type" class="em text-danger mb-0"></p>
                                            <p class="text-warning mb-0"><small>{{ __('Special types (e.g. Terms & Conditions) allow only one page per language. Setting it here replaces any existing one.') }}</small></p>
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
                                                @if ($page->breadcrumb_image)
                                                    <img src="{{ asset('assets/front/img/pages/' . $page->breadcrumb_image) }}"
                                                        alt="Breadcrumb" class="uploaded-img">
                                                    <button type="button" class="btn btn-danger btn-sm remove-img-btn"
                                                        data-page-id="{{ $page->id }}">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                @else
                                                    <img src="{{ asset('assets/admin/img/noimage.jpg') }}"
                                                        alt="Breadcrumb" class="uploaded-img">
                                                @endif
                                            </div>
                                            <br>
                                            <br>
                                            <input id="fileInput1" type="hidden" name="breadcrumb_image"
                                                value="">
                                            <button id="chooseImage1" class="choose-image btn btn-primary" type="button"
                                                data-multiple="false" data-toggle="modal" data-target="#lfmModal1">Choose
                                                Image</button>
                                            <p class="text-warning mb-0"><small>Leave empty to keep current image or use
                                                    default.</small></p>
                                            <p class="text-info mb-0"><small><strong>Recommended size:</strong> 1920px ×
                                                    350px (Width × Height)</small></p>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="">Overlay Color</label>
                                            <input type="text" name="breadcrumb_overlay_color"
                                                class="form-control ltr jscolor"
                                                value="{{ $page->breadcrumb_overlay_color ?? '' }}"
                                                placeholder="e.g. 000000">
                                            <p class="text-warning mb-0"><small>Leave empty to use default overlay
                                                    color.</small></p>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="">Overlay Opacity</label>
                                            <input type="number" step="0.01" min="0" max="1"
                                                name="breadcrumb_overlay_opacity" class="form-control ltr"
                                                value="{{ $page->breadcrumb_overlay_opacity ?? '' }}"
                                                placeholder="e.g. 0.5">
                                            <p class="text-warning mb-0"><small>Value between 0 and 1. Leave empty for
                                                    default.</small></p>
                                        </div>
                                    </div>
                                </div>

                                @if ($bex->custom_page_pagebuilder == 0)
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label for="">Body **</label>
                                                <textarea id="body" class="form-control summernote" name="body" data-height="500">{{ replaceBaseUrl($page->body) }}</textarea>
                                                <p id="errbody" class="em text-danger mb-0"></p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <div class="form-group">
                                    <label>Meta Keywords</label>
                                    <input class="form-control" name="meta_keywords" value="{{ $page->meta_keywords }}"
                                        placeholder="Enter meta keywords" data-role="tagsinput">
                                </div>
                                <div class="form-group">
                                    <label>Meta Description</label>
                                    <textarea class="form-control" name="meta_description" rows="5" placeholder="Enter meta description">{{ $page->meta_description }}</textarea>
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

@section('scripts')
    <!-- LFM Modal -->
    <div class="modal fade lfm-modal" id="lfmModal1" tabindex="-1" role="dialog" aria-labelledby="lfmModalTitle"
        aria-hidden="true">
        <i class="fas fa-times-circle"></i>
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <iframe src="{{ url('laravel-filemanager') }}?serial=1"
                        style="width: 100%; height: 500px; overflow: hidden; border: none;"></iframe>
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
                        } else {
                            // Remove data-page-id for newly selected images (not saved yet)
                            thumbPreview.querySelector('.remove-img-btn').removeAttribute('data-page-id');
                        }
                    }

                    // Close the modal
                    activeModal.modal('hide');
                }
            }
        };

        $(document).ready(function() {
            // Delete breadcrumb image
            $(document).on('click', '.remove-img-btn', function(e) {
                e.preventDefault();
                var btn = $(this);
                var pageId = btn.data('page-id');
                var thumbPreview = btn.closest('.thumb-preview');
                var serial = thumbPreview.attr('id').replace('thumbPreview', '');

                if (pageId) {
                    // Existing image - delete via AJAX with SweetAlert confirmation
                    swal({
                        title: 'Are you sure?',
                        text: "You want to delete this breadcrumb image?",
                        icon: 'warning',
                        buttons: {
                            cancel: {
                                text: "Cancel",
                                visible: true,
                                closeModal: true,
                            },
                            confirm: {
                                text: "Yes, delete it!",
                                closeModal: false,
                            }
                        },
                        dangerMode: true,
                    }).then((willDelete) => {
                        if (willDelete) {
                            $.ajax({
                                url: '{{ route('admin.page.deleteBreadcrumb', ':id') }}'
                                    .replace(':id', pageId),
                                type: 'POST',
                                data: {
                                    _token: '{{ csrf_token() }}'
                                },
                                success: function(response) {
                                    if (response.success) {
                                        thumbPreview.find('img').attr('src',
                                            '{{ asset('assets/admin/img/noimage.jpg') }}'
                                            );
                                        btn.remove();
                                        $('#fileInput' + serial).val('');
                                        swal({
                                            title: 'Deleted!',
                                            text: 'Breadcrumb image has been deleted.',
                                            icon: 'success',
                                            button: 'OK'
                                        });
                                    }
                                },
                                error: function(xhr) {
                                    swal({
                                        title: 'Error!',
                                        text: 'Failed to delete breadcrumb image.',
                                        icon: 'error',
                                        button: 'OK'
                                    });
                                }
                            });
                        }
                    });
                } else {
                    // Newly selected image - just remove from preview
                    thumbPreview.find('img').attr('src', '{{ asset('assets/admin/img/noimage.jpg') }}');
                    $('#fileInput' + serial).val('');
                    btn.remove();
                }
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
