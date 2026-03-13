@extends('admin.layout')

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
    </style>
@endsection

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ $tender->title }}</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('admin.dashboard') }}"><i class="flaticon-home"></i></a>
            </li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item">
                <a href="{{ route('admin.tender.index') . '?language=' . request()->input('language') }}">Tenders</a>
            </li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="#">Modules</a></li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card-title d-inline-block">Modules</div>
                        </div>
                        <div class="col-lg-3"></div>
                        <div class="col-lg-4 offset-lg-1 mt-2 mt-lg-0">
                            <a href="#" class="btn btn-primary float-right btn-sm" data-toggle="modal"
                                data-target="#createModal">
                                <i class="fas fa-plus"></i> Add Module
                            </a>
                            <button class="btn btn-danger float-right btn-sm mr-2 d-none bulk-delete"
                                data-href="{{ route('admin.tender.module.bulk_delete') }}">
                                <i class="flaticon-interface-5"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">
                            @if (count($modules) == 0)
                                <h3 class="text-center">NO TENDER MODULE FOUND</h3>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-striped mt-3" id="basic-datatables">
                                        <thead>
                                            <tr>
                                                <th scope="col">
                                                    <input type="checkbox" class="bulk-check" data-val="all">
                                                </th>
                                                <th scope="col">Module Name</th>
                                                <th scope="col">Module Cost ({{ $bex->base_currency_text }})</th>
                                                <th scope="col">Status</th>
                                                <th scope="col">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($modules as $module)
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" class="bulk-check"
                                                            data-val="{{ $module->id }}">
                                                    </td>
                                                    <td>{{ strlen(convertUtf8($module->name)) > 40 ? substr(convertUtf8($module->name), 0, 40) . '...' : convertUtf8($module->name) }}
                                                    </td>
                                                    <td>{{ !is_null($module->cost) ? number_format($module->cost, 2) . ' ' . $bex->base_currency_text : 'Free' }}
                                                    </td>
                                                    <td>
                                                        <label class="switch">
                                                            <input type="checkbox" class="status-toggle"
                                                                data-id="{{ $module->id }}"
                                                                {{ $module->status == 1 ? 'checked' : '' }}>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </td>
                                                    <td>
                                                        <a class="btn btn-secondary btn-sm editbtn" href="#editModal"
                                                            data-toggle="modal" data-module_id="{{ $module->id }}"
                                                            data-name="{{ $module->name }}"
                                                            data-cost="{{ $module->cost }}"
                                                            data-summary="{{ $module->summary }}"
                                                            data-file="{{ $module->tender_file }}">
                                                            <span class="btn-label"><i class="fas fa-edit"></i></span> Edit
                                                        </a>

                                                        <form class="deleteform d-inline-block"
                                                            action="{{ route('admin.tender.module.delete') }}"
                                                            method="post">
                                                            @csrf
                                                            <input type="hidden" name="module_id"
                                                                value="{{ $module->id }}">
                                                            <button type="submit" class="btn btn-danger btn-sm deletebtn">
                                                                <span class="btn-label"><i class="fas fa-trash"></i></span>
                                                                Delete
                                                            </button>
                                                        </form>

                                                        <a class="btn btn-success btn-sm"
                                                            href="{{ route('admin.tender.module.section.index', $module->id) . '?language=' . request()->input('language') }}">
                                                            <span class="btn-label"><i class="fas fa-list-alt"></i></span>
                                                            Sections
                                                        </a>
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


    <!-- Create Tender Module Modal -->
    <div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add a Tender Module</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="ajaxForm" class="modal-form create" action="{{ route('admin.tender.module.store') }}"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="tender_id" value="{{ $tender->id }}">

                        <div class="form-group">
                            <label>Tender File **</label>
                            <br>
                            <div class="thumb-preview" id="thumbPreview1">
                                <img src="{{ asset('assets/admin/img/noimage.jpg') }}" alt="Tender Image">
                            </div>
                            <br><br>
                            <input id="fileInput1" type="hidden" name="tender_file">
                            <button id="chooseImage1" class="choose-image btn btn-primary" type="button"
                                data-multiple="false" data-toggle="modal" data-target="#lfmModal1">
                                Choose File
                            </button>
                            <p class="text-warning mb-0">ZIP, PDF, Word, Excel are allowed</p>
                            <p class="em text-danger mb-0" id="errtender_file"></p>
                        </div>

                        <div class="form-group">
                            <label>Module name **</label>
                            <input type="text" class="form-control" name="name"
                                placeholder="Enter Tender Module Name">
                            <p id="errname" class="mb-0 text-danger em"></p>
                        </div>

                        <div class="form-group">
                            <label>Module cost ({{ $bex->base_currency_text }})</label>
                            <input type="number" step="0.01" class="form-control ltr" name="cost"
                                placeholder="Enter Tender Module Cost">
                            <p class="text-warning mb-0">Leave it blank if it's a free module</p>
                            <p id="errcost" class="mb-0 text-danger em"></p>
                        </div>

                        <div class="form-group">
                            <label>Module Summary **</label>
                            <textarea class="form-control" name="summary" rows="4" placeholder="Enter Module Summary"></textarea>
                            <p id="errsummary" class="mb-0 text-danger em"></p>
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


    <!-- Edit Tender Module Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Tender Module</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="ajaxEditForm" action="{{ route('admin.tender.module.update') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <input id="inmodule_id" type="hidden" name="module_id" value="">

                        <div class="form-group">
                            <label>Tender File <span class="text-muted">(Choose new to replace)</span></label>
                            <br>
                            <div class="thumb-preview" id="thumbPreview2">
                                <img src="{{ asset('assets/admin/img/noimage.jpg') }}" alt="Tender File">
                            </div>
                            <br><br>
                            <input id="fileInput2" type="hidden" name="tender_file">
                            <button id="chooseImage2" class="choose-image btn btn-primary" type="button"
                                data-multiple="false" data-toggle="modal" data-target="#lfmModal2">
                                Choose File
                            </button>
                            <p class="text-warning mb-0">ZIP, PDF, Word, Excel are allowed</p>
                        </div>

                        <div class="form-group">
                            <label>Module name **</label>
                            <input id="inname" type="text" class="form-control" name="name"
                                placeholder="Enter Tender Module Name">
                            <p id="eerrname" class="mb-0 text-danger em"></p>
                        </div>

                        <div class="form-group">
                            <label>Module cost ({{ $bex->base_currency_text }})</label>
                            <input id="incost" type="number" step="0.01" class="form-control ltr" name="cost"
                                placeholder="Enter Tender Module Cost">
                            <p class="text-warning mb-0">Leave it blank if it's a free module</p>
                        </div>

                        <div class="form-group">
                            <label>Module Summary **</label>
                            <textarea id="insummary" class="form-control" name="summary" rows="4" placeholder="Enter Module Summary"></textarea>
                            <p id="eerrsummary" class="mb-0 text-danger em"></p>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button id="updateBtn" type="button" class="btn btn-primary">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    {{-- LFM Modal 1 (Create) --}}
    <div class="modal fade lfm-modal" id="lfmModal1" tabindex="-1" role="dialog" aria-hidden="true">
        <i class="fas fa-times-circle"></i>
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <iframe src="{{ url('laravel-filemanager') }}?serial=1"
                        style="width:100%;height:500px;overflow:hidden;border:none;"></iframe>
                </div>
            </div>
        </div>
    </div>

    {{-- LFM Modal 2 (Edit) --}}
    <div class="modal fade lfm-modal" id="lfmModal2" tabindex="-1" role="dialog" aria-hidden="true">
        <i class="fas fa-times-circle"></i>
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <iframe src="{{ url('laravel-filemanager') }}?serial=2"
                        style="width:100%;height:500px;overflow:hidden;border:none;"></iframe>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        $(document).ready(function() {

            // Populate edit modal
            $(document).on('click', '.editbtn', function() {
                var id = $(this).data('module_id');
                var name = $(this).data('name');
                var cost = $(this).data('cost');
                var summary = $(this).data('summary');
                var file = $(this).data('file');

                $('#inmodule_id').val(id);
                $('#inname').val(name);
                $('#incost').val(cost);
                $('#insummary').val(summary);
                $('#fileInput2').val('');
                if (file) {
                    $('#thumbPreview2 img').attr('src', '{{ asset('assets/front/files/tender_modules') }}/' + file);
                } else {
                    $('#thumbPreview2 img').attr('src', '{{ asset('assets/admin/img/noimage.jpg') }}');
                }
            });

            // Status toggle
            $(document).on('change', '.status-toggle', function() {
                var status = $(this).is(':checked') ? 1 : 0;
                var id = $(this).data('id');

                $.post('{{ route('admin.tender.module.status') }}', {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    status: status
                }, function() {
                    $.notify({
                        message: 'Status updated successfully.',
                        icon: 'fa fa-check'
                    }, {
                        type: 'success',
                        placement: {
                            from: 'top',
                            align: 'right'
                        },
                        showProgressbar: true,
                        time: 1000,
                        delay: 2000
                    });
                });
            });

        });
    </script>
@endsection
