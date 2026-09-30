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
    <h4 class="page-title">Blogs</h4>
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
            <a href="#">Blog Page</a>
        </li>
        <li class="separator">
            <i class="flaticon-right-arrow"></i>
        </li>
        <li class="nav-item">
            <a href="#">Blogs</a>
        </li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="card-title d-inline-block">Blogs</div>
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
                        <a href="#" class="btn btn-primary float-right btn-sm" data-toggle="modal" data-target="#createModal"><i class="fas fa-plus"></i> Add Blog</a>
                        <button class="btn btn-danger float-right btn-sm mr-2 d-none bulk-delete" data-href="{{route('admin.blog.bulk.delete')}}"><i class="flaticon-interface-5"></i> Delete</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-12">
                        @if (count($blogs) == 0)
                        <h3 class="text-center">NO BLOG FOUND</h3>
                        @else
                        <style>
                            /* Status switch — same markup/CSS as the Services list's own
                               local copy; there's no global version. */
                            .switch { position: relative; display: inline-block; width: 44px; height: 22px; vertical-align: middle; }
                            .switch input { opacity: 0; width: 0; height: 0; }
                            .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; }
                            .slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 3px; bottom: 3px; background-color: white; transition: .4s; }
                            input:checked + .slider { background-color: #1572E8; }
                            input:focus + .slider { box-shadow: 0 0 1px #1572E8; }
                            input:checked + .slider:before { transform: translateX(22px); }
                            .slider.round { border-radius: 22px; }
                            .slider.round:before { border-radius: 50%; }

                            /* Icon-only action buttons, same pastel-square treatment as
                               the Services list's own Actions column. */
                            .blog-actions { display: flex; align-items: center; gap: 6px; }
                            .blog-actions button.blog-action-btn[type="submit"] {
                                background: #ffe3e3 !important;
                                border: none !important;
                                box-shadow: none !important;
                            }
                            .blog-actions button.blog-action-btn[type="submit"]:hover {
                                transform: none !important;
                                box-shadow: none !important;
                            }
                            .blog-action-btn {
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
                            .blog-action-btn:hover { filter: brightness(0.94); }
                            .blog-action-edit { background: #eef0f3; color: #495057; }
                            .blog-action-delete { background: #ffe3e3; color: #e03131; }
                        </style>
                        <div class="table-responsive">
                            <table class="table table-striped mt-3" id="basic-datatables">
                                <thead>
                                    <tr>
                                        <th scope="col">
                                            <input type="checkbox" class="bulk-check" data-val="all">
                                        </th>
                                        <th scope="col">Image</th>
                                        <th scope="col">Category</th>
                                        <th scope="col">Title</th>
                                        <th scope="col">Author</th>
                                        <th scope="col">Publish Date</th>
                                        <th scope="col">Serial Number</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($blogs as $key => $blog)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="bulk-check" data-val="{{$blog->id}}">
                                        </td>
                                        <td><img src="{{asset('assets/front/img/blogs/'.$blog->main_image)}}" alt="" width="80"></td>
                                        <td>
                                            <div class="admin-clamp-cell admin-clamp-cell--narrow">
                                                <span class="admin-clamp-text">{{ convertUtf8($blog->bcategory->name) }}</span>
                                            </div>
                                            <a href="javascript:void(0)" class="admin-seemore-btn" hidden>See more</a>
                                        </td>
                                        <td>
                                            <div class="admin-clamp-cell admin-clamp-cell--wide">
                                                <span class="admin-clamp-text">{{ convertUtf8($blog->title) }}</span>
                                            </div>
                                            <a href="javascript:void(0)" class="admin-seemore-btn" hidden>See more</a>
                                        </td>
                                        <td>{{ convertUtf8($blog->author_name) }}</td>
                                        <td>
                                            @php
                                            $date = \Carbon\Carbon::parse($blog->created_at);
                                            @endphp
                                            {{$date->translatedFormat('jS F, Y')}}
                                        </td>
                                        <td>{{$blog->serial_number}}</td>
                                        <td>
                                            <label class="switch mb-0">
                                                <input type="checkbox" class="blog-status-toggle" data-id="{{$blog->id}}" {{$blog->status == 1 ? 'checked' : ''}}>
                                                <span class="slider round"></span>
                                            </label>
                                        </td>
                                        <td>
                                            <div class="blog-actions">
                                                <a class="blog-action-btn blog-action-edit" href="{{route('admin.blog.edit', $blog->id) . '?language=' . request()->input('language')}}" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form class="deleteform d-inline-block" action="{{route('admin.blog.delete')}}" method="post">
                                                    @csrf
                                                    <input type="hidden" name="blog_id" value="{{$blog->id}}">
                                                    <button type="submit" class="blog-action-btn blog-action-delete deletebtn" title="Delete">
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
</div>
</div>
<!-- Create Blog Modal -->
<div
class="modal fade"
id="createModal"
tabindex="-1"
role="dialog"
aria-labelledby="exampleModalCenterTitle"
aria-hidden="true"
>
<div
class="modal-dialog modal-dialog-centered modal-lg"
role="document"
>
<div class="modal-content">
    <div class="modal-header">
        <h5
        class="modal-title"
        id="exampleModalLongTitle"
        >Add Blog</h5>
        <button
        type="button"
        class="close"
        data-dismiss="modal"
        aria-label="Close"
        >
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body">
<form
id="ajaxForm"
class="modal-form"
action="{{route('admin.blog.store')}}"
method="POST"
>
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
    <p class="text-warning mb-0"><small>Recommended size: 1200x750px (16:10 landscape). The image is cropped to fit this ratio.</small></p>
    <p class="em text-danger mb-0" id="errimage"></p>

</div>

<div class="form-group">
    <label for="">Language **</label>
    <select
    id="language"
    name="language_id"
    class="form-control"
    >
    <option
    value=""
    selected
    disabled
    >Select a language</option>
    @foreach ($langs as $lang)
    <option value="{{$lang->id}}">{{$lang->name}}</option>
    @endforeach
</select>
<p
id="errlanguage_id"
class="mb-0 text-danger em"
></p>
</div>
<div class="form-group">
    <label for="">Title **</label>
    <input
    type="text"
    class="form-control"
    name="title"
    placeholder="Enter title"
    value=""
    >
    <p
    id="errtitle"
    class="mb-0 text-danger em"
    ></p>
</div>
<div class="form-group">
    <label for="">URL Slug</label>
    <input type="text" class="form-control ltr" name="slug" placeholder="Leave blank to auto-generate from title">
    <p id="errslug" class="mb-0 text-danger em"></p>
    <p class="text-warning mb-0"><small>Auto-generated from the title if left blank. Editable later without breaking existing links (old URL redirects automatically).</small></p>
</div>
<div class="form-group">
    <label for="">Category **</label>
    <select
    id="bcategory"
    class="form-control"
    name="category"
    disabled
    >
    <option
    value=""
    selected
    disabled
    >Select a category</option>
</select>
<p
id="errcategory"
class="mb-0 text-danger em"
></p>
</div>
<div class="form-group">
    <label for="">Content **</label>
    <textarea id="blogContent"
    class="form-control summernote"
    name="content"
    rows="8"
    cols="80"
    placeholder="Enter content"
    ></textarea>
    <p
    id="errcontent"
    class="mb-0 text-danger em"
    ></p>
</div>
<div class="form-group">
    <label for="">Serial Number **</label>
    <input
    type="number"
    class="form-control ltr"
    name="serial_number"
    value=""
    placeholder="Enter Serial Number"
    >
    <p
    id="errserial_number"
    class="mb-0 text-danger em"
    ></p>
    <p class="text-warning mb-0"><small>The higher the serial number is, the later the blog will be shown.</small></p>
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
<div class="form-group">
    <label for="">Meta Keywords</label>
    <input
    type="text"
    class="form-control"
    name="meta_keywords"
    value=""
    data-role="tagsinput"
    >
</div>
<div class="form-group">
    <label for="">Meta Description</label>
    <textarea
    type="text"
    class="form-control"
    name="meta_description"
    rows="5"
    ></textarea>
</div>
</form>
</div>
<div class="modal-footer">
    <button
    type="button"
    class="btn btn-secondary"
    data-dismiss="modal"
    >Close</button>
    <button
    id="submitBtn"
    type="button"
    class="btn btn-primary"
    >Submit</button>
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
    // Status (activate/deactivate) toggle — delegated so it keeps working
    // across DataTables' own pagination (see faq/index.blade.php's identical
    // note on .editbtn for why a direct bind would only reach page 1's rows).
    $(document).on('change', '.blog-status-toggle', function () {
        var $toggle = $(this);
        var id = $toggle.data('id');
        var checked = $toggle.is(':checked');
        $.post("{{ route('admin.blog.status') }}", {
            _token: "{{ csrf_token() }}",
            blog_id: id,
            status: checked ? 1 : 0,
        }, function (resp) {
            if (resp.success) {
                $.notify({
                    title: 'Success',
                    message: checked ? 'Activated successfully!' : 'Deactivated successfully!',
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
                message: 'Something went wrong. Please try again.',
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

    $(document).ready(function() {
        $("select[name='language_id']").on('change', function() {

            $("#bcategory").removeAttr('disabled');

            let langid = $(this).val();
            let url = "{{url('/')}}/admin/blog/" + langid + "/getcats";
            console.log(url);
            $.get(url, function(data) {
                console.log(data);
                let options = `<option value="" disabled selected>Select a category</option>`;
                for (let i = 0; i < data.length; i++) {
                    options += `<option value="${data[i].id}">${data[i].name}</option>`;
                }
                $("#bcategory").html(options);

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
                    $("form.modal-form .summernote").siblings('.note-editor').find('.note-editable').removeClass('rtl text-right');
                }
            })
        });

        // translatable portfolios will be available if the selected language is not 'Default'
        $("#language").on('change', function() {
            let language = $(this).val();
            // console.log(language);
            if (language == 0) {
                $("#translatable").attr('disabled', true);
            } else {
                $("#translatable").removeAttr('disabled');
            }
        });
    });
    // console.log('loaded');
</script>
@endsection
