@extends('admin.layout')

@if(!empty($abs->language) && $abs->language->rtl == 1)
@section('styles')
<style>
    form:not(.modal-form) input,
    form:not(.modal-form)  textarea,
    form:not(.modal-form)  select {
        direction: rtl;
    }
    form:not(.modal-form)  .note-editor.note-frame .note-editing-area .note-editable {
        direction: rtl;
        text-align: right;
    }
</style>
@endsection
@endif

@section('content')
<div class="page-header">
    <h4 class="page-title">Approach Section</h4>
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
            <a href="#">Home</a>
        </li>
        <li class="separator">
            <i class="flaticon-right-arrow"></i>
        </li>
        <li class="nav-item">
            <a href="#">Approach Section</a>
        </li>
    </ul>
</div>
<div class="row">
    <div class="col-md-12">

        @if ($bex->home_page_pagebuilder == 0)

        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-lg-10">
                        <div class="card-title">Title & Subtitle</div>
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
            <form id="approachForm" action="{{route('admin.approach.update', $lang_id)}}" method="post">
                @csrf
                <div class="card-body">
                    {{-- Approach Section Background Image --}}
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label for="">Approach Section Background Image (Optional)</label>
                                <br>
                                <div class="thumb-preview" id="thumbPreviewApproach"
                                    style="position: relative; display: inline-block;">
                                    @if (!empty($abe->approach_section_bg))
                                        <img src="{{ asset('assets/front/img/' . $abe->approach_section_bg) }}"
                                            alt="Background Image">
                                    @else
                                        <img src="{{ asset('assets/admin/img/noimage.jpg') }}" alt="No Image">
                                    @endif
                                    <button type="button" class="btn btn-danger btn-sm delete-image" id="deleteApproachSectionBgBtn"
                                        style="position: absolute; top: 10px; right: 10px; opacity: 0; transition: opacity 0.3s;">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <br>
                                <br>

                                <input id="fileInputApproach" type="hidden" name="approach_section_bg">
                                <input id="deleteApproachSectionBg" type="hidden" name="delete_approach_section_bg" value="0">
                                <button id="chooseImageApproach" class="choose-image btn btn-primary" type="button"
                                    data-multiple="false" data-toggle="modal" data-target="#lfmModalApproach">Choose
                                    Background Image</button>

                                <p class="text-warning mb-0">{{ allowed_image_extensions_label() }} images are allowed. This will
                                    be the background for the entire approach section.</p>
                                <p class="text-info mb-0"><small><strong>Recommended size:</strong> 1920px x 350px (Width x Height)</small></p>
                                <p class="text-danger mb-0 em">
                                    @if ($errors->has('approach_section_bg'))
                                        {{$errors->first('approach_section_bg')}}
                                    @endif
                                </p>

                                <!-- Approach Section Background LFM Modal -->
                                <div class="modal fade lfm-modal" id="lfmModalApproach" tabindex="-1" role="dialog"
                                    aria-labelledby="lfmModalTitle" aria-hidden="true">
                                    <i class="fas fa-times-circle"></i>
                                    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-body p-0">
                                                <iframe src="{{ url('laravel-filemanager') }}?serial=Approach"
                                                    style="width: 100%; height: 500px; overflow: hidden; border: none;"></iframe>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>Approach Area Overlay Color Code **</label>
                                <input class="jscolor form-control ltr" name="approach_overlay_color"
                                    value="{{ $abe->approach_overlay_color ?? '000000' }}">
                                @if ($errors->has('approach_overlay_color'))
                                    <p class="mb-0 text-danger">{{$errors->first('approach_overlay_color')}}</p>
                                @endif
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>Approach Area Overlay Opacity **</label>
                                <input type="text" class="form-control ltr" name="approach_overlay_opacity"
                                    value="{{ $abe->approach_overlay_opacity ?? '0.6' }}">
                                <p class="text-warning mb-0">Opacity can be between 0 to 1.</p>
                                @if ($errors->has('approach_overlay_opacity'))
                                    <p class="mb-0 text-danger">{{$errors->first('approach_overlay_opacity')}}</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>Title **</label>
                                <input class="form-control" name="approach_section_title" value="{{$abs->approach_title}}" placeholder="Enter Title">
                                @if ($errors->has('approach_section_title'))
                                <p class="mb-0 text-danger">{{$errors->first('approach_section_title')}}</p>
                                @endif
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>Subtitle **</label>
                                <input class="form-control" name="approach_section_subtitle" value="{{$abs->approach_subtitle}}" placeholder="Enter Subtitle">
                                @if ($errors->has('approach_section_subtitle'))
                                <p class="mb-0 text-danger">{{$errors->first('approach_section_subtitle')}}</p>
                                @endif
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>Button Text</label>
                                <input class="form-control" name="approach_section_button_text" value="{{$abs->approach_button_text}}" placeholder="Enter Button Text">
                                @if ($errors->has('approach_section_button_text'))
                                <p class="mb-0 text-danger">{{$errors->first('approach_section_button_text')}}</p>
                                @endif
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>Button URL</label>
                                <input class="form-control ltr" name="approach_section_button_url" value="{{$abs->approach_button_url}}" placeholder="Enter Button URL">
                                @if ($errors->has('approach_section_button_url'))
                                <p class="mb-0 text-danger">{{$errors->first('approach_section_button_url')}}</p>
                                @endif
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
        @endif

        <div class="card">
            <div class="card-header">
                <div class="card-title d-inline-block">Points</div>
                <a href="#" class="btn btn-primary float-right" data-toggle="modal" data-target="#createPointModal"><i class="fas fa-plus"></i> Add Point</a>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-12">
                        @if (count($points) == 0)
                        <h2 class="text-center">NO POINT ADDED</h2>
                        @else
                        <div class="table-responsive">
                            <table class="table table-striped mt-3">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Icon</th>
                                        <th scope="col">Title</th>
                                        <th scope="col">Serial Number</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($points as $key => $point)
                                    <tr>
                                        <td>{{$loop->iteration}}</td>
                                        <td><i class="{{ $point->icon }}"></i></td>
                                        <td>{{convertUtf8($point->title)}}</td>
                                        <td>{{$point->serial_number}}</td>
                                        <td>
                                            <a class="btn btn-secondary btn-sm" href="{{route('admin.approach.point.edit', $point->id) . '?language=' . request()->input('language')}}">
                                                <span class="btn-label">
                                                    <i class="fas fa-edit"></i>
                                                </span>
                                                Edit
                                            </a>
                                            <form class="d-inline-block deleteform" action="{{route('admin.approach.pointdelete')}}" method="post">
                                                @csrf
                                                <input type="hidden" name="pointid" value="{{$point->id}}">
                                                <button type="submit" class="btn btn-danger btn-sm deletebtn">
                                                    <span class="btn-label">
                                                        <i class="fas fa-trash"></i>
                                                    </span>
                                                    Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Point Create Modal --}}
@includeif('admin.home.approach.create')
@endsection


@section('scripts')
<script>
    $(document).ready(function() {
        $('.icp').on('iconpickerSelected', function(event){
            $("#inputIcon").val($(".iconpicker-component").find('i').attr('class'));
        });

        // make input fields RTL
        $("select[name='language_id']").on('change', function() {
            $(".request-loader").addClass("show");
            let url = "{{url('/')}}/admin/rtlcheck/" + $(this).val();
            console.log(url);
            $.get(url, function(data) {
                $(".request-loader").removeClass("show");
                if (data == 1) {
                    $("form.modal-form input").each(function() {
                        if (!$(this).hasClass('ltr')) {
                            $(this).addClass('rtl');
                        }
                    });
                    $("form.modal-form select").each(function() {
                        if (!$(this).hasClass('ltr')) {
                            $(this).addClass('rtl');
                        }
                    });
                    $("form.modal-form textarea").each(function() {
                        if (!$(this).hasClass('ltr')) {
                            $(this).addClass('rtl');
                        }
                    });
                    $("form.modal-form .nicEdit-main").each(function() {
                        $(this).addClass('rtl text-right');
                    });

                } else {
                    $("form.modal-form input, form.modal-form select, form.modal-form textarea").removeClass('rtl');
                    $("form.modal-form .nicEdit-main").removeClass('rtl text-right');
                }
            })
        });
    });

    // Approach section background image functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Attach event listener to approach section background delete button
        var deleteApproachSectionBgBtn = document.getElementById('deleteApproachSectionBgBtn');
        if (deleteApproachSectionBgBtn) {
            deleteApproachSectionBgBtn.addEventListener('click', function(e) {
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
                        document.getElementById('deleteApproachSectionBg').value = '1';
                        document.getElementById('fileInputApproach').value = '';
                        document.getElementById('approachForm').submit();
                    }
                });
            });
        }

        // Show delete button on hover for approach section background
        var thumbPreviewApproach = document.getElementById('thumbPreviewApproach');
        if (thumbPreviewApproach) {
            thumbPreviewApproach.addEventListener('mouseenter', function() {
                var deleteBtn = this.querySelector('.delete-image');
                if (deleteBtn) deleteBtn.style.opacity = '1';
            });

            thumbPreviewApproach.addEventListener('mouseleave', function() {
                var deleteBtn = this.querySelector('.delete-image');
                if (deleteBtn) deleteBtn.style.opacity = '0';
            });
        }
    });

    // Laravel File Manager SetUrl handler for Approach section
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
</script>
@endsection
