@extends('admin.layout')

@php
$selLang = \App\Language::where('code', request()->input('language'))->first();
@endphp
@if(!empty($selLang) && $selLang->rtl == 1)
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
    <h4 class="page-title">Services</h4>
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
            <a href="#">Service Page</a>
        </li>
        <li class="separator">
            <i class="flaticon-right-arrow"></i>
        </li>
        <li class="nav-item">
            <a href="#">Services</a>
        </li>
    </ul>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-lg-3">
                        <div class="card-title d-inline-block">Services</div>
                    </div>
                    <div class="col-lg-2 mt-2 mt-lg-0">
                        @if (!empty($langs))
                        <select name="language" class="form-control" onchange="window.location='{{url()->current() . '?language='}}'+this.value">
                            <option value="" selected disabled>Select a Language</option>
                            @foreach ($langs as $lang)
                            <option value="{{$lang->code}}" {{$lang->code == request()->input('language') ? 'selected' : ''}}>{{$lang->name}}</option>
                            @endforeach
                        </select>
                        @endif
                    </div>
                    @if (serviceCategory())
                    <div class="col-lg-3 mt-2 mt-lg-0 d-flex align-items-center">
                        <form method="get" class="flex-grow-1">
                            <input type="hidden" name="language" value="{{ request()->input('language') }}">
                            <select name="category_id" class="form-control" onchange="this.form.submit()">
                                <option value="">Category — All</option>
                                @foreach ($scategories as $scategory)
                                <option value="{{ $scategory->id }}" {{ request()->input('category_id') == $scategory->id ? 'selected' : '' }}>{{ convertUtf8($scategory->name) }}</option>
                                @endforeach
                            </select>
                        </form>
                        @if (request()->filled('category_id'))
                        <a href="{{ url()->current() . '?language=' . request()->input('language') }}" class="ml-2 text-muted" title="Clear category filter"><i class="fas fa-times-circle"></i></a>
                        @endif
                    </div>
                    @endif
                    <div class="col-lg-{{ serviceCategory() ? 4 : 7 }} mt-2 mt-lg-0">
                        <a href="#" class="btn btn-primary float-right btn-sm" data-toggle="modal" data-target="#createModal"><i class="fas fa-plus"></i> Add Service</a>
                        <button class="btn btn-danger float-right btn-sm mr-2 d-none bulk-delete" data-href="{{route('admin.service.bulk.delete')}}"><i class="flaticon-interface-5"></i> Delete</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-12">
                        @if (count($services) == 0)
                        <h3 class="text-center">NO SERVICE FOUND</h3>
                        @else
                        <style>
                            /* Toggle switch — same markup/CSS every other page with a
                               status toggle already uses (Gallery/Language/Sectors/
                               etc's own local copy; there's no global version). */
                            .switch { position: relative; display: inline-block; width: 44px; height: 22px; vertical-align: middle; }
                            .switch input { opacity: 0; width: 0; height: 0; }
                            .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; }
                            .slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 3px; bottom: 3px; background-color: white; transition: .4s; }
                            input:checked + .slider { background-color: #1572E8; }
                            input:focus + .slider { box-shadow: 0 0 1px #1572E8; }
                            input:checked + .slider:before { transform: translateX(22px); }
                            .slider.round { border-radius: 22px; }
                            .slider.round:before { border-radius: 50%; }

                            /* Icon-only action buttons, same pastel-square treatment
                               as the Portfolio list's actions column. dark-glass.css
                               (also loaded admin-wide) carries a blanket
                               button[type="submit"]{background:linear-gradient(...) !important}
                               rule meant for the front-end site that leaks onto the
                               Delete button here (the only type="submit" one) —
                               beaten with a selector specific enough to out-rank
                               that !important, same fix as Portfolio's own list. */
                            .service-actions { display: flex; align-items: center; gap: 6px; }
                            .service-actions button.service-action-btn[type="submit"] {
                                background: #ffe3e3 !important;
                                border: none !important;
                                box-shadow: none !important;
                            }
                            .service-actions button.service-action-btn[type="submit"]:hover {
                                transform: none !important;
                                box-shadow: none !important;
                            }
                            .service-action-btn {
                                display: inline-flex;
                                align-items: center;
                                justify-content: center;
                                flex: 0 0 34px;
                                width: 34px;
                                height: 34px;
                                border-radius: 8px;
                                border: none;
                                font-size: 14px;
                                line-height: 1;
                                cursor: pointer;
                                transition: filter 0.15s ease;
                            }
                            .service-action-btn:hover { filter: brightness(0.94); }
                            .service-action-edit { background: #eef0f3; color: #495057; }
                            .service-action-delete { background: #ffe3e3; color: #e03131; }
                        </style>
                        <div class="table-responsive">
                            <table class="table table-striped mt-3" id="basic-datatables">
                                <thead>
                                    <tr>
                                        <th scope="col">
                                            <input type="checkbox" class="bulk-check" data-val="all">
                                        </th>
                                        <th scope="col">Image</th>
                                        <th scope="col">Title</th>
                                        @if (serviceCategory())
                                        <th scope="col">Category</th>
                                        @endif
                                        <th scope="col">Featured</th>
                                        <th scope="col">Serial Number</th>
                                        <th scope="col">Published</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>


                                <tbody>
                                    @foreach ($services as $key => $service)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="bulk-check" data-val="{{$service->id}}">
                                        </td>
                                        <td><img src="{{asset('assets/front/img/services/'.$service->main_image)}}" alt="" width="70"></td>
                                        <td>{{strlen(convertUtf8($service->title)) > 100 ? convertUtf8(substr($service->title, 0, 100)) . '...' : convertUtf8($service->title)}}</td>

                                        @if (serviceCategory())
                                        <td>
                                            @if (!empty($service->scategory))
                                            {{convertUtf8($service->scategory->name)}}
                                            @endif
                                        </td>
                                        @endif

                                        <td>
                                            <label class="switch mb-0">
                                                <input type="checkbox" class="service-feature-toggle" data-id="{{$service->id}}" {{$service->feature == 1 ? 'checked' : ''}}>
                                                <span class="slider round"></span>
                                            </label>
                                        </td>

                                        <td>{{$service->serial_number}}</td>
                                        <td>{{ !empty($service->created_at) ? $service->created_at->format('d-m-Y') : '—' }}</td>
                                    <td>
                                        <div class="service-actions">
                                            <a class="service-action-btn service-action-edit" href="{{route('admin.service.edit', $service->id) . '?language=' . request()->input('language')}}" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form class="deleteform d-inline-block" action="{{route('admin.service.delete')}}" method="post">
                                                @csrf
                                                <input type="hidden" name="service_id" value="{{$service->id}}">
                                                <button type="submit" class="service-action-btn service-action-delete deletebtn" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
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
<!-- Create Service Modal -->
<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Add Service</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
            <form id="ajaxForm" class="modal-form" action="{{route('admin.service.store')}}" method="POST">
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
                    <p class="text-warning mb-0"><small>Recommended size: 800x500px (8:5 landscape). Displayed at its natural ratio (not cropped), so keep close to this ratio for a consistent card layout.</small></p>
                    <p class="em text-danger mb-0" id="errimage"></p>

                </div>
                <div class="form-group">
                    <label for="">Language **</label>
                    <select id="language" name="language_id" class="form-control">
                        @foreach ($langs as $lang)
                        <option value="{{$lang->id}}">{{$lang->name}}</option>
                        @endforeach
                    </select>
                    <p id="errlanguage_id" class="mb-0 text-danger em"></p>
                </div>
                <div class="form-group">
                    <label for="">Title **</label>
                    <input type="text" class="form-control" name="title" placeholder="Enter title" value="">
                    <p id="errtitle" class="mb-0 text-danger em"></p>
                </div>
                @if (serviceCategory())
                <div class="form-group">
                    <label for="">Category **</label>
                    <select id="scategory" class="form-control" name="category" disabled>
                        <option value="" selected disabled>Select a category</option>
                    </select>
                    <p id="errcategory" class="mb-0 text-danger em"></p>
                </div>
                @endif

                <div class="form-group">
                    <label for="">Summary **</label>
                    <textarea class="form-control" name="summary" placeholder="Enter summary" rows="3"></textarea>
                    <p id="errsummary" class="mb-0 text-danger em"></p>
                </div>

                <div class="form-group">
                    <label>Details Page **</label>
                    <div class="selectgroup w-100">
                        <label class="selectgroup-item">
                            <input type="radio" name="details_page_status" value="1" class="selectgroup-input" checked>
                            <span class="selectgroup-button">Enable</span>
                        </label>
                        <label class="selectgroup-item">
                            <input type="radio" name="details_page_status" value="0" class="selectgroup-input">
                            <span class="selectgroup-button">Disable</span>
                        </label>
                    </div>
                    <p id="errdetails_page_status" class="mb-0 text-danger em"></p>
                </div>

                <div class="form-group">
                    <label>Sidebar **</label>
                    <div class="selectgroup w-100">
                        <label class="selectgroup-item">
                            <input type="radio" name="sidebar" value="1" class="selectgroup-input" checked>
                            <span class="selectgroup-button">Enabled</span>
                        </label>
                        <label class="selectgroup-item">
                            <input type="radio" name="sidebar" value="0" class="selectgroup-input">
                            <span class="selectgroup-button">Disabled</span>
                        </label>
                    </div>
                    <p id="errsidebar" class="mb-0 text-danger em"></p>
                </div>

                <div class="form-group" id="contentFg">
                    <label for="">Content **</label>
                    <textarea id="serviceContent" class="form-control summernote" name="content" data-height="300" placeholder="Enter content"></textarea>
                    <p id="errcontent" class="mb-0 text-danger em"></p>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label for="">Serial Number **</label>
                            <input type="number" class="form-control ltr" name="serial_number" value="" placeholder="Enter Serial Number">
                            <p id="errserial_number" class="mb-0 text-danger em"></p>
                            <p class="text-warning"><small>The higher the serial number is, the later the service will be shown everywhere.</small></p>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Meta Keywords</label>
                    <input class="form-control" name="meta_keywords" value="" placeholder="Enter meta keywords" data-role="tagsinput">
                    <p id="errmeta_keywords" class="mb-0 text-danger em"></p>
                </div>
                <div class="form-group">
                    <label>Meta Description</label>
                    <textarea class="form-control" name="meta_description" rows="5" placeholder="Enter meta description"></textarea>
                    <p id="errmeta_description" class="mb-0 text-danger em"></p>
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
    // Featured toggle — same delegated pattern + $.notify toast the
    // Sectors/Subsectors admin page's own status switch uses.
    $(document).ready(function () {
        $(document).on('change', '.service-feature-toggle', function () {
            var $toggle = $(this);
            var id = $toggle.data('id');
            var checked = $toggle.is(':checked');
            $.post("{{ route('admin.service.feature') }}", {
                _token: "{{ csrf_token() }}",
                service_id: id,
                feature: checked ? 1 : 0,
            }, function (resp) {
                if (resp.success) {
                    $.notify({
                        title: 'Success',
                        message: checked ? 'Featured successfully!' : 'Unfeatured successfully!',
                        icon: 'fa fa-check',
                    }, {
                        type: 'success',
                        placement: { from: 'top', align: 'right' },
                        showProgressbar: true,
                        time: 1000,
                        delay: 3000,
                    });
                }
            }).fail(function () {
                $toggle.prop('checked', !checked);
                $.notify({
                    title: 'Error',
                    message: 'Error updating status!',
                    icon: 'fa fa-times',
                }, {
                    type: 'danger',
                    placement: { from: 'top', align: 'right' },
                    showProgressbar: true,
                    time: 1000,
                    delay: 3000,
                });
            });
        });
    });

    function toggleDetails() {
        let val = $("input[name='details_page_status']:checked").val();

        // if 'details page' is 'enable', then show 'content' & hide 'summary'
        if (val == 1) {
            $("#contentFg").show();
        }
        // if 'details page' is 'disable', then show 'summary' & hide 'content'
        else if (val == 0) {
            $("#contentFg").hide();
        }
    }

    $("input[name='details_page_status']").on('change', function() {
        toggleDetails();
    });
</script>

@if(serviceCategory())
<script>
    function loadCategories() {
        $("#scategory").removeAttr('disabled');
        let langid = $("select[name='language_id']").val();
        let url = "{{url('/')}}/admin/service/" + langid + "/getcats";
        // console.log(url);
        $.get(url, function(data) {
            console.log(data);
            let options = `<option value="" disabled selected>Select a category</option>`;
            for (let i = 0; i < data.length; i++) {
                options += `<option value="${data[i].id}">${data[i].name}</option>`;
            }
            $("#scategory").html(options);

        });
    }

    $(document).ready(function() {

        loadCategories();

        $("select[name='language_id']").on('change', function() {
            loadCategories();
        });

    });
</script>
@endif

<script>
    $(document).ready(function() {
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
                    $("form.modal-form .summernote").each(function() {
                        $(this).siblings('.note-editor').find('.note-editable').addClass('rtl text-right');
                    });

                } else {
                    $("form.modal-form input, form.modal-form select, form.modal-form textarea").removeClass('rtl');
                    $("form.modal-form .summernote").siblings('.note-editor').find('.note-editable').removeClass('rtl text-right');
                }
            })
        });
    });
</script>
@endsection
