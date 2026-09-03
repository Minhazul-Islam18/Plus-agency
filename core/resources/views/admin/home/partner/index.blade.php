@extends('admin.layout')

@php
$selLang = \App\Language::where('code', request()->input('language'))->first();
@endphp
@section('styles')
<style>
    .switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 24px;
    }
    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
    }
    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
    }
    input:checked+.slider {
        background-color: #1572E8;
    }
    input:focus+.slider {
        box-shadow: 0 0 1px #1572E8;
    }
    input:checked+.slider:before {
        transform: translateX(26px);
    }
    .slider.round {
        border-radius: 24px;
    }
    .slider.round:before {
        border-radius: 50%;
    }
    @if(!empty($selLang) && $selLang->rtl == 1)
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
    @endif
</style>
@endsection

@section('content')
<div class="page-header">
    <h4 class="page-title">Partners</h4>
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
            <a href="#">Partners</a>
        </li>
    </ul>
</div>
<div class="row">
    <div class="col-md-12">

        <div class="card">
            <div class="card-header">
                <div class="card-title">Partner Section Settings</div>
            </div>
            <div class="card-body">
                <form id="partnerSectionForm" action="{{route('admin.partner.section.update', $lang_id)}}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-lg-8 offset-lg-2">
                            {{-- Background Image Part --}}
                            <div class="form-group">
                                <label for="">Background Image (Optional)</label>
                                <br>
                                <div class="thumb-preview" id="thumbPreview2" style="position: relative; display: inline-block;">
                                    @if (!empty($abe->partner_bg))
                                        <img src="{{asset('assets/front/img/'.$abe->partner_bg)}}" alt="Background Image">
                                    @else
                                        <img src="{{asset('assets/admin/img/noimage.jpg')}}" alt="No Image">
                                    @endif
                                    <button type="button" class="btn btn-danger btn-sm delete-image" id="deletePartnerBgBtn"
                                        style="position: absolute; top: 10px; right: 10px; opacity: 0; transition: opacity 0.3s;">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <br>
                                <br>

                                <input id="fileInput2" type="hidden" name="background">
                                <input id="deleteBackground" type="hidden" name="delete_background" value="0">
                                <button id="chooseImage2" class="choose-image btn btn-primary" type="button" data-multiple="false" data-toggle="modal" data-target="#lfmModal2">Choose Background Image</button>

                                <p class="text-warning mb-0">{{ allowed_image_extensions_label() }} images are allowed</p>
                                <p class="text-info mb-0"><small><strong>Recommended size:</strong> 1920px x 350px (Width x Height)</small></p>
                                @if ($errors->has('background'))
                                <p class="text-danger mb-0">{{$errors->first('background')}}</p>
                                @endif
                            </div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Partner Area Overlay Color Code **</label>
                                        <input class="jscolor form-control ltr" name="partner_overlay_color" value="{{$abe->partner_overlay_color ?? '000000'}}">
                                        @if ($errors->has('partner_overlay_color'))
                                        <p class="mb-0 text-danger">{{$errors->first('partner_overlay_color')}}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Partner Area Overlay Opacity **</label>
                                        <input type="text" class="form-control ltr" name="partner_overlay_opacity" value="{{$abe->partner_overlay_opacity ?? '0.6'}}">
                                        <p class="text-warning mb-0">Opacity can be between 0 to 1.</p>
                                        @if ($errors->has('partner_overlay_opacity'))
                                        <p class="mb-0 text-danger">{{$errors->first('partner_overlay_opacity')}}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center">
                <button form="partnerSectionForm" class="btn btn-success" type="submit">Update Settings</button>
            </div>
        </div>

        <!-- Background Image LFM Modal -->
        <div class="modal fade lfm-modal" id="lfmModal2" tabindex="-1" role="dialog" aria-labelledby="lfmModalTitle" aria-hidden="true">
            <i class="fas fa-times-circle"></i>
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-body p-0">
                        <iframe src="{{url('laravel-filemanager')}}?serial=2" style="width: 100%; height: 500px; overflow: hidden; border: none;"></iframe>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="card-title d-inline-block">Partners</div>
                    </div>
                    <div class="col-lg-3">
                        @if (!empty($langs))
                        <select name="language" class="form-control" onchange="window.location='{{url()->current() . '?language='}}'+this.value">
                            <option value="" selected disabled>Select a Language</option>
                            @foreach ($langs as $lang)
                            <option value="{{$lang->code}}" {{$lang->code == request()->input('language') ? 'selected' : ''}}>{{$lang->name}}</option>
                            @endforeach
                        </select>
                        @endif
                    </div>
                    <div class="col-lg-4 offset-lg-1 mt-2 mt-lg-0">
                        <a href="#" class="btn btn-primary float-lg-right float-left" data-toggle="modal" data-target="#createModal"><i class="fas fa-plus"></i> Add Partner</a>
                        <button class="btn btn-danger float-lg-right float-left btn-sm mr-2 d-none bulk-delete"
                            data-href="{{ route('admin.partner.bulk.delete') }}"><i class="flaticon-interface-5"></i> Delete</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        @if (count($partners) == 0)
                        <h3 class="text-center">NO PARTNER FOUND</h3>
                        @else
                        <div class="table-responsive">
                            <table class="table table-striped mt-3" id="basic-datatables">
                                <thead>
                                    <tr>
                                        <th scope="col">
                                            <input type="checkbox" class="bulk-check" data-val="all">
                                        </th>
                                        <th scope="col">Partner</th>
                                        <th scope="col">Logo</th>
                                        <th scope="col">Link</th>
                                        <th scope="col">Serial Number</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($partners as $key => $partner)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="bulk-check" data-val="{{$partner->id}}">
                                        </td>
                                        <td>{{$partner->name}}</td>
                                        <td>
                                            <img src="{{asset('assets/front/img/partners/'.$partner->image)}}" alt="{{$partner->name}}" width="80">
                                        </td>
                                        <td>
                                            <a href="{{$partner->url}}" target="_blank" rel="noopener">{{$partner->url}}</a>
                                        </td>
                                        <td>{{$partner->serial_number}}</td>
                                        <td>
                                            <label class="switch">
                                                <input type="checkbox" class="status-toggle" data-id="{{$partner->id}}" {{$partner->status == 1 ? 'checked' : ''}}>
                                                <span class="slider round"></span>
                                            </label>
                                        </td>
                                        <td>
                                            <a class="btn btn-secondary btn-sm" href="{{route('admin.partner.edit', $partner->id) . '?language=' . request()->input('language')}}">
                                                <span class="btn-label">
                                                    <i class="fas fa-edit"></i>
                                                </span>
                                                Edit
                                            </a>
                                            <form class="deleteform d-inline-block" action="{{route('admin.partner.delete')}}" method="post">
                                                @csrf
                                                <input type="hidden" name="partner_id" value="{{$partner->id}}">
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


<!-- Create Partner Modal -->
<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Add Partner</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
            <form id="ajaxForm" class="modal-form" action="{{route('admin.partner.store')}}" method="post">
                @csrf
                {{-- Image Part --}}
                <div class="form-group">
                    <label for="">Image ** </label>
                    <br>
                    <div class="thumb-preview" id="thumbPreview1">
                        <img src="{{asset('assets/admin/img/noimage.jpg')}}" alt="User Image">
                    </div>
                    <br>
                    <br>


                    <input id="fileInput1" type="hidden" name="image">
                    <button id="chooseImage1" class="choose-image btn btn-primary" type="button" data-multiple="false" data-toggle="modal" data-target="#lfmModal1">Choose Image</button>


                    <p class="text-warning mb-0">{{ allowed_image_extensions_label() }} images are allowed</p>
                    <p class="text-warning mb-0"><small>Recommended size: 300x150px (landscape, transparent background). Displayed at natural size, not cropped.</small></p>
                    <p class="em text-danger mb-0" id="errimage"></p>

                </div>
                <div class="form-group">
                    <label for="">Language **</label>
                    <select name="language_id" class="form-control">
                        <option value="" selected disabled>Select a language</option>
                        @foreach ($langs as $lang)
                        <option value="{{$lang->id}}">{{$lang->name}}</option>
                        @endforeach
                    </select>
                    <p id="errlanguage_id" class="mb-0 text-danger em"></p>
                </div>
                <div class="form-group">
                    <label for="">Partner Name **</label>
                    <input type="text" class="form-control" name="name" value="" placeholder="Enter Partner Name">
                    <p id="errname" class="mb-0 text-danger em"></p>
                </div>
                <div class="form-group">
                    <label for="">URL **</label>
                    <input type="text" class="form-control ltr" name="url" value="" placeholder="Enter URL">
                    <p id="errurl" class="mb-0 text-danger em"></p>
                </div>
                <div class="form-group">
                    <label for="">Serial Number **</label>
                    <input type="number" class="form-control ltr" name="serial_number" value="" placeholder="Enter Serial Number">
                    <p id="errserial_number" class="mb-0 text-danger em"></p>
                    <p class="text-warning"><small>The higher the serial number is, the later the partner will be shown.</small></p>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button id="submitBtn" type="button" class="btn btn-primary">Submit</button>
        </div>
    </div>
</div>
</div>

<!-- Image LFM Modal -->
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
@endsection

@section('scripts')
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
                }

                // Close the modal
                if (typeof window.closeLfmModal === 'function') {
                    window.closeLfmModal(serial);
                }
            }
        }
    };

    $(document).ready(function() {

        // Use event delegation for status toggle to work with paginated rows
        $(document).on('change', '.status-toggle', function() {
            let status = $(this).is(':checked') ? 1 : 0;
            let id = $(this).data('id');

            $.ajax({
                url: '{{ route('admin.partner.status') }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    status: status
                },
                success: function(response) {
                    if (response.success) {
                        $.notify({
                            title: 'Success',
                            message: 'Status updated successfully!',
                            icon: 'fa fa-check'
                        }, {
                            type: 'success',
                            placement: { from: 'top', align: 'right' },
                            showProgressbar: true,
                            time: 1000,
                            delay: 3000
                        });
                    }
                },
                error: function(xhr) {
                    $.notify({
                        title: 'Error',
                        message: 'Error updating status!',
                        icon: 'fa fa-times'
                    }, {
                        type: 'danger',
                        placement: { from: 'top', align: 'right' },
                        showProgressbar: true,
                        time: 1000,
                        delay: 3000
                    });
                }
            });
        });

        // Show delete button on hover for partner background
        $('#thumbPreview2').hover(
            function() {
                $(this).find('.delete-image').css('opacity', '1');
            },
            function() {
                $(this).find('.delete-image').css('opacity', '0');
            }
        );

        // Handle partner background delete button
        $('#deletePartnerBgBtn').on('click', function(e) {
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
                    $('#deleteBackground').val('1');
                    $('#fileInput2').val('');
                    $('#partnerSectionForm').submit();
                }
            });
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
</script>
@endsection
