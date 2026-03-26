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

        .file-picker-wrap .no-file-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            background: #fafbfc;
            border: 2px dashed #d0d7de;
            border-radius: 10px;
            padding: 22px 16px;
            margin-bottom: 8px;
            text-align: center;
            cursor: default;
        }

        .file-picker-wrap .no-file-label .nf-icon {
            font-size: 28px;
            color: #c0c8d0;
        }

        .file-picker-wrap .no-file-label .nf-title {
            font-size: 13px;
            font-weight: 600;
            color: #7a8694;
            margin: 0;
        }

        .file-picker-wrap .no-file-label .nf-hint {
            font-size: 11px;
            color: #b0b8c2;
            margin: 0;
        }

        .file-selected-card {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #f0f7ff;
            border: 1.5px solid #cce0ff;
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 8px;
            position: relative;
        }

        .file-selected-card .file-icon {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #fff;
            flex-shrink: 0;
        }

        .file-selected-card .file-icon.zip {
            background: #f39c12;
        }

        .file-selected-card .file-icon.pdf {
            background: #e74c3c;
        }

        .file-selected-card .file-icon.word {
            background: #2980b9;
        }

        .file-selected-card .file-icon.excel {
            background: #27ae60;
        }

        .file-selected-card .file-icon.other {
            background: #7f8c8d;
        }

        .file-selected-card .file-meta {
            flex: 1;
            min-width: 0;
        }

        .file-selected-card .file-name {
            font-size: 13px;
            font-weight: 600;
            color: #2c3e50;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .file-selected-card .file-ext-badge {
            display: inline-block;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 1px 6px;
            border-radius: 4px;
            color: #fff;
            margin-top: 2px;
        }

        .file-selected-card .file-ext-badge.zip {
            background: #f39c12;
        }

        .file-selected-card .file-ext-badge.pdf {
            background: #e74c3c;
        }

        .file-selected-card .file-ext-badge.word {
            background: #2980b9;
        }

        .file-selected-card .file-ext-badge.excel {
            background: #27ae60;
        }

        .file-selected-card .file-ext-badge.other {
            background: #7f8c8d;
        }

        .file-selected-card .btn-clear-file {
            background: none;
            border: none;
            color: #bbb;
            font-size: 15px;
            cursor: pointer;
            padding: 2px 4px;
            line-height: 1;
            flex-shrink: 0;
        }

        .file-selected-card .btn-clear-file:hover {
            color: #e74c3c;
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

                        <div class="form-group file-picker-wrap">
                            <label>Tender File **</label>
                            <div id="fileDetail1" class="file-selected-card" style="display:none;">
                                <div id="fileIcon1" class="file-icon other"><i class="fas fa-file"></i></div>
                                <div class="file-meta">
                                    <div id="fileName1" class="file-name"></div>
                                    <span id="fileExt1" class="file-ext-badge other"></span>
                                </div>
                                <button type="button" class="btn-clear-file" onclick="clearFile(1)" title="Remove">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <div id="noFile1" class="no-file-label">
                                <i class="fas fa-cloud-upload-alt nf-icon"></i>
                                <p class="nf-title">No file selected</p>
                                <p class="nf-hint">ZIP, PDF, Word, Excel accepted</p>
                            </div>
                            <input id="fileInput1" type="hidden" name="tender_file">
                            <button id="chooseImage1" class="choose-image btn btn-primary btn-sm" type="button"
                                data-multiple="false" data-toggle="modal" data-target="#lfmModal1">
                                <i class="fas fa-paperclip mr-1"></i> Choose File
                            </button>
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

                        <div class="form-group file-picker-wrap">
                            <label>Tender File <span class="text-muted">(Choose new to replace)</span></label>
                            <div id="fileDetail2" class="file-selected-card" style="display:none;">
                                <div id="fileIcon2" class="file-icon other"><i class="fas fa-file"></i></div>
                                <div class="file-meta">
                                    <div id="fileName2" class="file-name"></div>
                                    <span id="fileExt2" class="file-ext-badge other"></span>
                                </div>
                                <button type="button" class="btn-clear-file" onclick="clearFile(2)" title="Remove">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <div id="noFile2" class="no-file-label">
                                <i class="fas fa-cloud-upload-alt nf-icon"></i>
                                <p class="nf-title">No file selected</p>
                                <p class="nf-hint">ZIP, PDF, Word, Excel accepted</p>
                            </div>
                            <input id="fileInput2" type="hidden" name="tender_file">
                            <button id="chooseImage2" class="choose-image btn btn-primary btn-sm" type="button"
                                data-multiple="false" data-toggle="modal" data-target="#lfmModal2">
                                <i class="fas fa-paperclip mr-1"></i> Choose File
                            </button>
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
                    <iframe src="{{ url('laravel-filemanager') }}?type=file&serial=1&callback=SetUrl"
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
                    <iframe src="{{ url('laravel-filemanager') }}?type=file&serial=2&callback=SetUrl"
                        style="width:100%;height:500px;overflow:hidden;border:none;"></iframe>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        var extMap = {
            zip: {
                cls: 'zip',
                icon: 'fa-file-archive',
                label: 'ZIP'
            },
            rar: {
                cls: 'zip',
                icon: 'fa-file-archive',
                label: 'RAR'
            },
            pdf: {
                cls: 'pdf',
                icon: 'fa-file-pdf',
                label: 'PDF'
            },
            doc: {
                cls: 'word',
                icon: 'fa-file-word',
                label: 'DOC'
            },
            docx: {
                cls: 'word',
                icon: 'fa-file-word',
                label: 'DOCX'
            },
            xls: {
                cls: 'excel',
                icon: 'fa-file-excel',
                label: 'XLS'
            },
            xlsx: {
                cls: 'excel',
                icon: 'fa-file-excel',
                label: 'XLSX'
            },
        };

        function renderFileCard(serial, name) {
            if (!name) {
                clearFile(serial);
                return;
            }
            var ext = name.split('.').pop().toLowerCase();
            var info = extMap[ext] || {
                cls: 'other',
                icon: 'fa-file',
                label: ext.toUpperCase()
            };

            $('#fileIcon' + serial).attr('class', 'file-icon ' + info.cls)
                .html('<i class="fas ' + info.icon + '"></i>');
            $('#fileName' + serial).text(name);
            $('#fileExt' + serial).attr('class', 'file-ext-badge ' + info.cls).text(info.label);
            $('#fileDetail' + serial).show();
            $('#noFile' + serial).hide();
        }

        function clearFile(serial) {
            $('#fileInput' + serial).val('');
            $('#fileDetail' + serial).hide();
            $('#noFile' + serial).show();
        }

        var allowedExts = ['zip', 'rar', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];

        window.SetUrl = function(items) {
            var activeModal = $('.lfm-modal.show');
            var serial = activeModal.length ? activeModal.attr('id').replace('lfmModal', '') : '';
            if (!serial || !items || !items.length) return;

            var item = items[0];
            var ext = item.name.split('.').pop().toLowerCase();

            activeModal.modal('hide');

            if (allowedExts.indexOf(ext) === -1) {
                clearFile(serial);
                $.notify({
                    message: 'Invalid file type ".' + ext + '". Allowed: ' + allowedExts.join(', '),
                    icon: 'fa fa-exclamation-triangle'
                }, {
                    type: 'danger',
                    placement: { from: 'top', align: 'right' },
                    showProgressbar: true,
                    time: 1000,
                    delay: 4000
                });
                return;
            }

            $('#fileInput' + serial).val(item.url);
            renderFileCard(serial, item.name);
        };

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
                renderFileCard(2, file || null);
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
