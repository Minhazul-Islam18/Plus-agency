@extends('admin.layout')

@php
    $selLang = \App\Language::where('code', request()->input('language'))->first();
@endphp
@if (!empty($selLang) && $selLang->rtl == 1)
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
        <h4 class="page-title">Contact Page</h4>
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
                <a href="#">Contact Page</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <form class="mb-3 dm-uploader drag-and-drop-zone" enctype="multipart/form-data"
                    action="{{ route('admin.contact.update', $lang_id) }}" method="POST">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-10">
                                <div class="card-title">Contact Page</div>
                            </div>
                            <div class="col-lg-2">
                                @if (!empty($langs))
                                    <select name="language" class="form-control"
                                        onchange="window.location='{{ url()->current() . '?language=' }}'+this.value">
                                        <option value="" selected disabled>Select a Language</option>
                                        @foreach ($langs as $lang)
                                            <option value="{{ $lang->code }}"
                                                {{ $lang->code == request()->input('language') ? 'selected' : '' }}>
                                                {{ $lang->name }}</option>
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
                                    <label for="">Contact Breadcrumb Background Image **</label>
                                    <br>
                                    <div class="thumb-preview" id="thumbPreview1">
                                        @if (!empty($abs->contact_breadcrumb_bg))
                                            <img src="{{ asset('assets/front/img/' . $abs->contact_breadcrumb_bg) }}"
                                                alt="Breadcrumb Background" class="uploaded-img">
                                            <button type="button" class="btn btn-danger btn-sm remove-img-btn"
                                                data-lang-id="{{ $lang_id }}">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @else
                                            <img src="{{ asset('assets/admin/img/noimage.jpg') }}" alt="..."
                                                class="uploaded-img">
                                        @endif
                                    </div>
                                    <br>
                                    <br>
                                    <input id="fileInput1" type="hidden" name="contact_breadcrumb_bg" value="">
                                    <button id="chooseImage1" class="choose-image btn btn-primary" type="button"
                                        data-multiple="false" data-toggle="modal" data-target="#lfmModal1">Choose
                                        Image</button>
                                    <p class="text-warning mb-0">{{ allowed_image_extensions_label() }} images are allowed</p>
                                    <p class="text-info mb-0"><small><strong>Recommended size:</strong> 1920px × 350px
                                            (Width × Height)</small></p>
                                    @if ($errors->has('contact_breadcrumb_bg'))
                                        <p class="text-danger mb-0">{{ $errors->first('contact_breadcrumb_bg') }}</p>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label>Breadcrumb Overlay Color Code</label>
                                    <input class="form-control jscolor ltr" name="contact_breadcrumb_overlay_color"
                                        value="{{ $abs->contact_breadcrumb_overlay_color }}"
                                        placeholder="Enter Color Code">
                                    @if ($errors->has('contact_breadcrumb_overlay_color'))
                                        <p class="mb-0 text-danger">
                                            {{ $errors->first('contact_breadcrumb_overlay_color') }}</p>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label>Breadcrumb Overlay Opacity</label>
                                    <input type="number" class="form-control" name="contact_breadcrumb_overlay_opacity"
                                        value="{{ $abs->contact_breadcrumb_overlay_opacity }}" step="0.01"
                                        min="0" max="1" placeholder="Enter opacity (0 to 1)">
                                    <p class="text-warning mb-0">Value must be between 0 to 1 (e.g. 0.5 for 50% opacity)</p>
                                    @if ($errors->has('contact_breadcrumb_overlay_opacity'))
                                        <p class="mb-0 text-danger">
                                            {{ $errors->first('contact_breadcrumb_overlay_opacity') }}</p>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label>Form Title **</label>
                                    <input class="form-control" name="contact_form_title"
                                        value="{{ $abs->contact_form_title }}" placeholder="Enter Titlte">
                                    @if ($errors->has('contact_form_title'))
                                        <p class="mb-0 text-danger">{{ $errors->first('contact_form_title') }}</p>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label>Form Subtitle **</label>
                                    <input class="form-control" name="contact_form_subtitle"
                                        value="{{ $abs->contact_form_subtitle }}" placeholder="Enter Subtitlte">
                                    @if ($errors->has('contact_form_subtitle'))
                                        <p class="mb-0 text-danger">{{ $errors->first('contact_form_subtitle') }}</p>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label>Address **</label>
                                    <textarea class="form-control" name="contact_addresses" rows="3">{{ $abex->contact_addresses }}</textarea>
                                    <p class="mb-0 text-warning">Use newline to seperate multiple addresses.</p>
                                    @if ($errors->has('contact_addresses'))
                                        <p class="mb-0 text-danger">{{ $errors->first('contact_addresses') }}</p>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label>Phone **</label>
                                    <input class="form-control" name="contact_numbers" data-role="tagsinput"
                                        value="{{ $abex->contact_numbers }}" placeholder="Enter Phone Number">
                                    <p class="mb-0 text-warning">Use comma (,) to seperate multiple contact numbers.</p>
                                    @if ($errors->has('contact_numbers'))
                                        <p class="mb-0 text-danger">{{ $errors->first('contact_numbers') }}</p>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label>Email **</label>
                                    <input class="form-control ltr" name="contact_mails" data-role="tagsinput"
                                        value="{{ $abex->contact_mails }}" placeholder="Enter Email Address">
                                    <p class="mb-0 text-warning">Use comma (,) to seperate multiple contact mails.</p>
                                    @if ($errors->has('contact_mails'))
                                        <p class="mb-0 text-danger">{{ $errors->first('contact_mails') }}</p>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label>Latitude </label>
                                    <input class="form-control" name="latitude" value="{{ $abex->latitude }}"
                                        placeholder="Enter Google Map Address">
                                    @if ($errors->has('latitude'))
                                        <p class="mb-0 text-danger">{{ $errors->first('latitude') }}</p>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label>Longitude</label>
                                    <input class="form-control" name="longitude" value="{{ $abex->longitude }}"
                                        placeholder="Enter Google Map Address">
                                    @if ($errors->has('longitude'))
                                        <p class="mb-0 text-danger">{{ $errors->first('longitude') }}</p>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label>Map Zoom</label>
                                    <input class="form-control" name="map_zoom" value="{{ $abex->map_zoom }}"
                                        placeholder="Enter Google Map Address">
                                    @if ($errors->has('map_zoom'))
                                        <p class="mb-0 text-danger">{{ $errors->first('map_zoom') }}</p>
                                    @endif
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="card-footer pt-3">
                        <div class="form">
                            <div class="form-group from-show-notify row">
                                <div class="col-12 text-center">
                                    <button id="displayNotif" class="btn btn-success">Update</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
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
                            deleteBtn.setAttribute('data-lang-id', '{{ $lang_id }}');
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
            $("input[name='contact_addresses']").tagsinput({
                delimiter: '|'
            });

            // Delete contact background image
            $(document).on('click', '.remove-img-btn', function(e) {
                e.preventDefault();
                var langId = $(this).data('lang-id');

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
                            url: '{{ route('admin.contact.deletebg', ':langid') }}'
                                .replace(':langid', langId),
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    $('#thumbPreview1 img').attr('src',
                                        '{{ asset('assets/admin/img/noimage.jpg') }}'
                                        );
                                    $('#thumbPreview1 .remove-img-btn').remove();
                                    $('#fileInput1').val('');
                                    swal({
                                        title: 'Deleted!',
                                        text: 'Background image has been deleted.',
                                        icon: 'success',
                                        button: 'OK'
                                    });
                                }
                            },
                            error: function(xhr) {
                                swal({
                                    title: 'Error!',
                                    text: 'Failed to delete background image.',
                                    icon: 'error',
                                    button: 'OK'
                                });
                            }
                        });
                    }
                });
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
