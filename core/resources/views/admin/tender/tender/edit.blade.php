@extends('admin.layout')

@php
    $selLang = $tender->language ?? null;
@endphp

@if (!empty($selLang) && $selLang->rtl == 1)
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
        <h4 class="page-title">Edit Tender</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="flaticon-home"></i>
                </a>
            </li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="#">Tender Management</a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="#">Edit Tender</a></li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title d-inline-block">Edit Tender</div>
                    <a class="btn btn-info btn-sm float-right d-inline-block"
                        href="{{ route('admin.tender.index') . '?language=' . request()->input('language') }}">
                        <span class="btn-label"><i class="fas fa-backward" style="font-size:12px;"></i></span>
                        Back
                    </a>
                </div>

                <div class="card-body pt-5 pb-5">
                    <div class="row">
                        <div class="col-lg-6 offset-lg-3">

                            <form id="ajaxForm" action="{{ route('admin.tender.update') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="tender_id" value="{{ $tender->id }}">

                                {{-- Tender Image --}}
                                <div class="form-group">
                                    <label>Tender Image **</label>
                                    <br>
                                    <div class="thumb-preview" id="thumbPreview1">
                                        @if (!empty($tender->tender_image))
                                            <img src="{{ asset('assets/front/img/tenders/' . $tender->tender_image) }}"
                                                alt="Tender Image">
                                        @else
                                            <img src="{{ asset('assets/admin/img/noimage.jpg') }}" alt="Tender Image">
                                        @endif
                                    </div>
                                    <br><br>
                                    <input id="fileInput1" type="hidden" name="tender_image"
                                        value="{{ !empty($tender->tender_image) ? asset('assets/front/img/tenders/' . $tender->tender_image) : '' }}">
                                    <button id="chooseImage1" class="choose-image btn btn-primary" type="button"
                                        data-multiple="false" data-toggle="modal" data-target="#lfmModal1">
                                        Choose Image
                                    </button>
                                    <p class="text-warning mb-0">JPG, PNG, JPEG, SVG images are allowed</p>
                                    <p class="em text-danger mb-0" id="errtender_image"></p>
                                </div>

                                {{-- Country & Tender ID --}}
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Country **</label>
                                            <select name="country" class="form-control">
                                                <option value="" disabled>Select Country</option>
                                                @foreach ($countries as $country)
                                                    <option value="{{ $country }}"
                                                        {{ $tender->country == $country ? 'selected' : '' }}>
                                                        {{ $country }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <p id="errcountry" class="mb-0 text-danger em"></p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Tender ID **</label>
                                            <input type="text" class="form-control" name="tender_code"
                                                placeholder="Enter Tender ID" value="{{ $tender->tender_code }}">
                                            <p id="errtender_code" class="mb-0 text-danger em"></p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Language & Category --}}
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Language</label>
                                            <input type="text" class="form-control"
                                                value="{{ $tender->language->name ?? 'N/A' }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Category</label>
                                            <select name="tender_category_id" class="form-control">
                                                <option value="" disabled>Select Category</option>
                                                @foreach ($tender_categories as $cat)
                                                    <option value="{{ $cat->id }}"
                                                        {{ $tender->tender_category_id == $cat->id ? 'selected' : '' }}>
                                                        {{ $cat->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                {{-- Title & Submission Deadline --}}
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Tender Title **</label>
                                            <input type="text" class="form-control" name="title"
                                                placeholder="Enter Tender Title" value="{{ $tender->title }}">
                                            <p id="errtitle" class="mb-0 text-danger em"></p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Submission Deadline **</label>
                                            <input type="datetime-local" class="form-control ltr" name="submission_deadline"
                                                value="{{ !empty($tender->submission_deadline) ? \Carbon\Carbon::parse($tender->submission_deadline)->format('Y-m-d\TH:i') : '' }}">
                                            <p id="errsubmission_deadline" class="mb-0 text-danger em"></p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Current Price & Previous Price --}}
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Current Price ({{ $bex->base_currency_text }})</label>
                                            <input type="text" class="form-control ltr" readonly
                                                value="{{ $tender->current_price ? number_format($tender->current_price, 2) : 'Free' }}"
                                                style="background:#f8f9fa;cursor:not-allowed;">
                                            <p class="mb-0 text-warning"><small><i class="fas fa-info-circle"></i> Auto-calculated from the sum of active module costs.</small></p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Previous Price ({{ $bex->base_currency_text }})</label>
                                            <input type="number" step="0.01" class="form-control ltr"
                                                name="previous_price" placeholder="Enter Previous Price"
                                                value="{{ $tender->previous_price }}">
                                            <p class="mb-0 text-danger em"></p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Tender Video --}}
                                <div class="form-group">
                                    <label>Tender Video <span class="text-muted">(Optional)</span></label>
                                    <input type="text" class="form-control ltr" name="video_link"
                                        placeholder="Enter YouTube Video Link" value="{{ $tender->video_link }}">
                                    <p id="errvideo_link" class="mb-0 text-danger em"></p>
                                </div>

                                {{-- Tender Overview --}}
                                <div class="form-group">
                                    <label>Tender Overview **</label>
                                    <textarea class="form-control summernote" name="overview" rows="8" placeholder="Enter Tender Overview">{{ $tender->overview }}</textarea>
                                    <p id="erroverview" class="mb-0 text-danger em"></p>
                                </div>

                                {{-- Expert Name & Position --}}
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Public Procurement Expert Name **</label>
                                            <input type="text" class="form-control" name="expert_name"
                                                placeholder="Enter Expert Name" value="{{ $tender->expert_name }}">
                                            <p id="errexpert_name" class="mb-0 text-danger em"></p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Public Procurement Expert Position **</label>
                                            <input type="text" class="form-control" name="expert_position"
                                                placeholder="Enter Expert Position"
                                                value="{{ $tender->expert_position }}">
                                            <p id="errexpert_position" class="mb-0 text-danger em"></p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Expert Details --}}
                                <div class="form-group">
                                    <label>Expert Details **</label>
                                    <textarea class="form-control" name="expert_details" rows="5" placeholder="Enter Expert Details">{{ $tender->expert_details }}</textarea>
                                    <p id="errexpert_details" class="mb-0 text-danger em"></p>
                                </div>

                                {{-- Expert WhatsApp & Phone --}}
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Expert WhatsApp **</label>
                                            <input type="text" class="form-control ltr" name="expert_whatsapp"
                                                placeholder="Enter Expert WhatsApp"
                                                value="{{ $tender->expert_whatsapp }}">
                                            <p id="errexpert_whatsapp" class="mb-0 text-danger em"></p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Expert Email **</label>
                                            <input type="email" class="form-control ltr" name="expert_email"
                                                placeholder="Enter Expert Email" value="{{ $tender->expert_email }}">
                                            <p id="errexpert_email" class="mb-0 text-danger em"></p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Expert Image --}}
                                <div class="form-group">
                                    <label>Expert Image **</label>
                                    <br>
                                    <div class="thumb-preview" id="thumbPreview2">
                                        @if (!empty($tender->expert_image))
                                            <img src="{{ asset('assets/front/img/tender_experts/' . $tender->expert_image) }}"
                                                alt="Expert Image">
                                        @else
                                            <img src="{{ asset('assets/admin/img/noimage.jpg') }}" alt="Expert Image">
                                        @endif
                                    </div>
                                    <br><br>
                                    <input id="fileInput2" type="hidden" name="expert_image"
                                        value="{{ !empty($tender->expert_image) ? asset('assets/front/img/tender_experts/' . $tender->expert_image) : '' }}">
                                    <button id="chooseImage2" class="choose-image btn btn-primary" type="button"
                                        data-multiple="false" data-toggle="modal" data-target="#lfmModal2">
                                        Choose Image
                                    </button>
                                    <p class="text-warning mb-0">JPG, PNG, JPEG, SVG images are allowed</p>
                                    <p class="em text-danger mb-0" id="errexpert_image"></p>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="form">
                        <div class="form-group from-show-notify row">
                            <div class="col-12 text-center">
                                <button id="submitBtn" type="button" class="btn btn-success">Update</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tender Image LFM Modal --}}
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

    {{-- Expert Image LFM Modal --}}
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
        window.ajaxSuccessRedirect =
            "{{ route('admin.tender.index') }}?language={{ $tender->language->code ?? request()->input('language') }}";

        $(document).ready(function() {

            // Update image preview when LFM selects an image
            window.setFileInput = function(serial, url) {
                if (serial == 1) {
                    $('#fileInput1').val(url);
                    $('#thumbPreview1 img').attr('src', url);
                } else if (serial == 2) {
                    $('#fileInput2').val(url);
                    $('#thumbPreview2 img').attr('src', url);
                }
            };

        });
    </script>
@endsection
