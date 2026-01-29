@extends('admin.layout')

@section('styles')
    <style>
        .thumb-preview {
            position: relative;
            display: inline-block;
        }

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

@section('content')
    <div class="page-header">
        <h4 class="page-title">Settings</h4>
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
                <a href="#">Blogs</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">Settings</a>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title d-inline-block">Blog Page Settings</div>
                </div>

                <div class="card-body pt-5 pb-5">
                    <div class="row">
                        <div class="col-lg-6 offset-lg-3">
                            <form id="settingsForm" action="{{ route('admin.blog.update_settings') }}" method="post">
                                @csrf
                                <div class="form-group">
                                    <label for="">Blog Breadcrumb Background Image **</label>
                                    <br>
                                    <div class="thumb-preview" id="thumbPreview1">
                                        @if (!empty($bs->blog_breadcrumb_bg))
                                            <img src="{{ asset('assets/front/img/' . $bs->blog_breadcrumb_bg) }}"
                                                alt="Breadcrumb Background" class="uploaded-img">
                                            <button type="button" class="btn btn-danger btn-sm remove-img-btn">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @else
                                            <img src="{{ asset('assets/admin/img/noimage.jpg') }}" alt="..."
                                                class="uploaded-img">
                                        @endif
                                    </div>
                                    <br>
                                    <br>
                                    <input id="fileInput1" type="hidden" name="blog_breadcrumb_bg" value="">
                                    <button id="chooseImage1" class="choose-image btn btn-primary" type="button"
                                        data-multiple="false" data-toggle="modal" data-target="#lfmModal1">Choose
                                        Image</button>
                                    <p class="text-warning mb-0">JPG, PNG, JPEG images are allowed</p>
                                    <p class="text-info mb-0"><small><strong>Recommended size:</strong> 1920px x 350px
                                            (Width x Height)</small></p>
                                    @if ($errors->has('blog_breadcrumb_bg'))
                                        <p class="text-danger mb-0">{{ $errors->first('blog_breadcrumb_bg') }}</p>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label>Breadcrumb Overlay Color Code</label>
                                    <input class="form-control jscolor ltr" name="blog_breadcrumb_overlay_color"
                                        value="{{ $bs->blog_breadcrumb_overlay_color ?? '000000' }}"
                                        placeholder="Enter Color Code">
                                    @if ($errors->has('blog_breadcrumb_overlay_color'))
                                        <p class="mb-0 text-danger">
                                            {{ $errors->first('blog_breadcrumb_overlay_color') }}</p>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label>Breadcrumb Overlay Opacity</label>
                                    <input type="number" class="form-control" name="blog_breadcrumb_overlay_opacity"
                                        value="{{ $bs->blog_breadcrumb_overlay_opacity ?? 0.5 }}" step="0.01"
                                        min="0" max="1" placeholder="Enter opacity (0 to 1)">
                                    <p class="text-warning mb-0">Value must be between 0 to 1 (e.g. 0.5 for 50% opacity)</p>
                                    @if ($errors->has('blog_breadcrumb_overlay_opacity'))
                                        <p class="mb-0 text-danger">
                                            {{ $errors->first('blog_breadcrumb_overlay_opacity') }}</p>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="form">
                        <div class="form-group from-show-notify row">
                            <div class="col-12 text-center">
                                <button type="submit" form="settingsForm" class="btn btn-success">
                                    Submit
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Image LFM Modal -->
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
@endsection

@section('scripts')
    <script>
        // Laravel File Manager SetUrl handler
        window.SetUrl = function(items) {
            var currentModal = $('.lfm-modal.show');
            var serial = currentModal.attr('id').replace('lfmModal', '');
            var fileInput = $('#fileInput' + serial);
            var thumbPreview = document.getElementById('thumbPreview' + serial);

            if (items.length > 0) {
                var firstItem = items[0];
                var fileUrl = firstItem.url;
                fileInput.val(fileUrl);

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

                currentModal.modal('hide');
            }
        };

        $(document).ready(function() {
            // Delete breadcrumb background image
            $(document).on('click', '.remove-img-btn', function(e) {
                e.preventDefault();

                swal({
                    title: 'Are you sure?',
                    text: "You want to delete this background image?",
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
                            url: '{{ route('admin.blog.delete_breadcrumb_bg') }}',
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                swal.close();
                                if (response.success) {
                                    $('#thumbPreview1 img').attr('src',
                                        '{{ asset('assets/admin/img/noimage.jpg') }}');
                                    $('#thumbPreview1 .remove-img-btn').remove();
                                    $('#fileInput1').val('');
                                    $.notify({
                                        message: 'Background image has been deleted.',
                                        title: 'Success!',
                                        icon: 'fa fa-check'
                                    }, {
                                        type: 'success',
                                        placement: {
                                            from: 'top',
                                            align: 'right'
                                        },
                                        showProgressbar: true,
                                        time: 1000,
                                        delay: 3000
                                    });
                                }
                            },
                            error: function(xhr) {
                                swal.close();
                                $.notify({
                                    message: 'Failed to delete background image.',
                                    title: 'Error!',
                                    icon: 'fa fa-times'
                                }, {
                                    type: 'danger',
                                    placement: {
                                        from: 'top',
                                        align: 'right'
                                    },
                                    showProgressbar: true,
                                    time: 1000,
                                    delay: 3000
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
