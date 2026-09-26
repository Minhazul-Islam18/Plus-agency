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
                <a href="#">
                    <i class="flaticon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">FAQ Management</a>
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
                    <div class="row">
                        <div class="col-lg-10">
                            <div class="card-title d-inline-block">Settings</div>
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

                    <div class="card-body pt-5 pb-5">
                        <div class="row">
                            <div class="col-lg-6 offset-lg-3">
                                <form id="settingsForm" action="{{ route('admin.faq.update_settings', $lang_id) }}"
                                    method="post">
                                    @csrf
                                    <div class="form-group">
                                        <label for="">FAQ Breadcrumb Background Image **</label>
                                        <!-- DEBUG: lang_id={{ $lang_id }}, bs_id={{ $bsData->id ?? 'NULL' }}, faq_breadcrumb_bg={{ $bsData->faq_breadcrumb_bg ?? 'NULL' }} -->
                                        <br>
                                        <div class="thumb-preview" id="thumbPreview1">
                                            @if (!empty($bsData->faq_breadcrumb_bg))
                                                <img src="{{ asset('assets/front/img/' . $bsData->faq_breadcrumb_bg) }}"
                                                    alt="Breadcrumb Background" class="uploaded-img">
                                                <button type="button" class="btn btn-danger btn-sm remove-img-btn"
                                                    data-kind="bg">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            @else
                                                <img src="{{ asset('assets/admin/img/noimage.jpg') }}" alt="..."
                                                    class="uploaded-img">
                                            @endif
                                        </div>
                                        <br>
                                        <br>
                                        <input id="fileInput1" type="hidden" name="faq_breadcrumb_bg" value="">
                                        <button id="chooseImage1" class="choose-image btn btn-primary" type="button"
                                            data-multiple="false" data-toggle="modal" data-target="#lfmModal1">Choose
                                            Image</button>
                                        <p class="text-warning mb-0">{{ allowed_image_extensions_label() }} images are allowed</p>
                                        <p class="text-info mb-0"><small><strong>Recommended size:</strong> 1920px × 350px
                                                (Width × Height)</small></p>
                                        @if ($errors->has('faq_breadcrumb_bg'))
                                            <p class="text-danger mb-0">{{ $errors->first('faq_breadcrumb_bg') }}</p>
                                        @endif
                                    </div>

                                    <div class="form-group">
                                        <label>Breadcrumb Overlay Color Code</label>
                                        <input class="form-control jscolor ltr" name="faq_breadcrumb_overlay_color"
                                            value="{{ $bsData->faq_breadcrumb_overlay_color ?? '000000' }}"
                                            placeholder="Enter Color Code">
                                        @if ($errors->has('faq_breadcrumb_overlay_color'))
                                            <p class="mb-0 text-danger">
                                                {{ $errors->first('faq_breadcrumb_overlay_color') }}</p>
                                        @endif
                                    </div>

                                    <div class="form-group">
                                        <label>Breadcrumb Overlay Opacity</label>
                                        <input type="number" class="form-control" name="faq_breadcrumb_overlay_opacity"
                                            value="{{ $bsData->faq_breadcrumb_overlay_opacity ?? 0.5 }}" step="0.01"
                                            min="0" max="1" placeholder="Enter opacity (0 to 1)">
                                        <p class="text-warning mb-0">Value must be between 0 to 1 (e.g. 0.5 for 50% opacity)
                                        </p>
                                        @if ($errors->has('faq_breadcrumb_overlay_opacity'))
                                            <p class="mb-0 text-danger">
                                                {{ $errors->first('faq_breadcrumb_overlay_opacity') }}</p>
                                        @endif
                                    </div>

                                    <hr class="my-4">
                                    <h5 class="mb-3">FAQ Page Content</h5>

                                    <div class="form-group">
                                        <label for="">Hero Illustration (top right of the FAQ page)</label>
                                        <br>
                                        <div class="thumb-preview" id="thumbPreview2">
                                            @if (!empty($bsData->faq_hero_image))
                                                <img src="{{ asset('assets/front/img/' . $bsData->faq_hero_image) }}"
                                                    alt="Hero Illustration" class="uploaded-img">
                                                <button type="button" class="btn btn-danger btn-sm remove-img-btn"
                                                    data-kind="hero">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            @else
                                                <img src="{{ asset('assets/admin/img/noimage.jpg') }}" alt="..."
                                                    class="uploaded-img">
                                            @endif
                                        </div>
                                        <br>
                                        <br>
                                        <input id="fileInput2" type="hidden" name="faq_hero_image" value="">
                                        <button id="chooseImage2" class="choose-image btn btn-primary" type="button"
                                            data-multiple="false" data-toggle="modal" data-target="#lfmModal2">Choose
                                            Image</button>
                                        <p class="text-warning mb-0">{{ allowed_image_extensions_label() }} images are allowed</p>
                                        <p class="text-info mb-0"><small>A transparent PNG/WebP works best. Leave empty to
                                                hide the illustration.</small></p>
                                        @if ($errors->has('faq_hero_image'))
                                            <p class="text-danger mb-0">{{ $errors->first('faq_hero_image') }}</p>
                                        @endif
                                    </div>

                                    <div class="form-group">
                                        <label>Introductory Text</label>
                                        <textarea class="form-control" name="faq_intro_text" rows="3" maxlength="1000"
                                            placeholder="Quickly find answers to your questions about our services, calls for tenders, and how to use the ICA platform.">{{ old('faq_intro_text', $bsData->faq_intro_text ?? '') }}</textarea>
                                        <p class="text-warning mb-0">Shown under the page title. Leave empty to use the
                                            default text.</p>
                                        @if ($errors->has('faq_intro_text'))
                                            <p class="text-danger mb-0">{{ $errors->first('faq_intro_text') }}</p>
                                        @endif
                                    </div>

                                    <div class="form-group">
                                        <label>Contact Email</label>
                                        <input type="email" class="form-control" name="faq_contact_email"
                                            value="{{ old('faq_contact_email', $bsData->faq_contact_email ?? '') }}"
                                            placeholder="contact@example.com">
                                        @if ($errors->has('faq_contact_email'))
                                            <p class="text-danger mb-0">{{ $errors->first('faq_contact_email') }}</p>
                                        @endif
                                    </div>

                                    <div class="form-group">
                                        <label>WhatsApp Number</label>
                                        <input type="text" class="form-control" name="faq_whatsapp"
                                            value="{{ old('faq_whatsapp', $bsData->faq_whatsapp ?? '') }}"
                                            placeholder="+226 25 44 44 79">
                                        <p class="text-warning mb-0">Include the country code. Used for the WhatsApp
                                            button in the "Didn't find your answer?" banner.</p>
                                        @if ($errors->has('faq_whatsapp'))
                                            <p class="text-danger mb-0">{{ $errors->first('faq_whatsapp') }}</p>
                                        @endif
                                    </div>

                                    <div class="form-group">
                                        <label>Most Viewed Questions — Maximum Displayed **</label>
                                        <input type="number" class="form-control" name="faq_frequent_max" min="1"
                                            max="20" value="{{ old('faq_frequent_max', $bsData->faq_frequent_max ?? 5) }}">
                                        <p class="text-warning mb-0">Questions clicked more than 5 times are added
                                            automatically. When the list is full, the one that has been there longest is
                                            replaced.</p>
                                        @if ($errors->has('faq_frequent_max'))
                                            <p class="text-danger mb-0">{{ $errors->first('faq_frequent_max') }}</p>
                                        @endif
                                    </div>

                                    <hr class="my-4">

                                    <div class="form-group">
                                        <label>FAQ Category**</label>
                                        <div class="selectgroup w-100">
                                            <label class="selectgroup-item">
                                                <input type="radio" name="faq_category_status" value="1"
                                                    class="selectgroup-input"
                                                    {{ $abex?->faq_category_status == 1 ? 'checked' : '' }}>
                                                <span class="selectgroup-button">Active</span>
                                            </label>
                                            <label class="selectgroup-item">
                                                <input type="radio" name="faq_category_status" value="0"
                                                    class="selectgroup-input"
                                                    {{ $abex?->faq_category_status == 0 ? 'checked' : '' }}>
                                                <span class="selectgroup-button">Deactive</span>
                                            </label>
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

        <div class="modal fade lfm-modal" id="lfmModal2" tabindex="-1" role="dialog" aria-labelledby="lfmModalTitle"
            aria-hidden="true">
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
                            deleteBtn.setAttribute('data-kind', serial === '2' ? 'hero' : 'bg');
                            deleteBtn.innerHTML = '<i class="fas fa-times"></i>';
                            thumbPreview.appendChild(deleteBtn);
                        }
                    }

                    currentModal.modal('hide');
                }
            };

            $(document).ready(function() {
                // Delete breadcrumb background image
                var deleteKinds = {
                    bg: {
                        url: '{{ route('admin.faq.delete_breadcrumb_bg', $lang_id) }}',
                        preview: '#thumbPreview1',
                        input: '#fileInput1',
                        label: 'background image'
                    },
                    hero: {
                        url: '{{ route('admin.faq.delete_hero_image', $lang_id) }}',
                        preview: '#thumbPreview2',
                        input: '#fileInput2',
                        label: 'hero illustration'
                    }
                };

                $(document).on('click', '.remove-img-btn', function(e) {
                    e.preventDefault();
                    var kind = deleteKinds[$(this).data('kind')] || deleteKinds.bg;

                    swal({
                        title: 'Are you sure?',
                        text: "You want to delete this " + kind.label + "?",
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
                                url: kind.url,
                                type: 'POST',
                                data: {
                                    _token: '{{ csrf_token() }}'
                                },
                                success: function(response) {
                                    swal.close();
                                    if (response.success) {
                                        $(kind.preview + ' img').attr('src',
                                            '{{ asset('assets/admin/img/noimage.jpg') }}'
                                        );
                                        $(kind.preview + ' .remove-img-btn').remove();
                                        $(kind.input).val('');
                                        $.notify({
                                            message: kind.label.charAt(0).toUpperCase() + kind.label.slice(1) + ' has been deleted.',
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
                                        message: 'Failed to delete the ' + kind.label + '.',
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
