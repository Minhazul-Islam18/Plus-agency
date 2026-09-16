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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icons@7.2.3/css/flag-icons.min.css" integrity="sha384-aQuvIWWIbpu/mSqULLDiUveyYiPoJzPKAWjUmGJ+Elm+N/LJhzfZqsutsfw870JS" crossorigin="anonymous">
<style>
  .wa-code-select + .select2-container { flex-shrink: 0; }
  .wa-code-select + .select2-container .select2-selection--single {
    height: 38px; border-radius: 4px 0 0 4px; border-right: 0; border-color: #ebedf2;
  }
  .wa-code-select + .select2-container .select2-selection--single .select2-selection__rendered {
    line-height: 36px; padding-right: 24px;
  }
  .wa-code-select + .select2-container .select2-selection__arrow { height: 36px; }
  .wa-flag-option { display:flex; align-items:center; gap:8px; }
  .wa-flag-option .fi { font-size:16px; flex-shrink:0; }
</style>
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
                                    <p class="text-warning mb-0">{{ allowed_image_extensions_label() }} images are allowed</p>
                                    <p class="text-warning mb-0"><small>Recommended size: 800x400px (2:1 landscape). The image is cropped to fit this ratio.</small></p>
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
                                            <label>Language **</label>
                                            <select id="language" name="language_id" class="form-control">
                                                @foreach ($langs as $lang)
                                                    <option value="{{ $lang->id }}"
                                                        {{ $tender->language_id == $lang->id ? 'selected' : '' }}>
                                                        {{ $lang->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <p id="errlanguage_id" class="mb-0 text-danger em"></p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Category **</label>
                                            <select id="tender_category_id" name="tender_category_id" class="form-control">
                                                <option value="" disabled>Select Category</option>
                                                @foreach ($tender_categories as $cat)
                                                    <option value="{{ $cat->id }}"
                                                        {{ $tender->tender_category_id == $cat->id ? 'selected' : '' }}>
                                                        {{ $cat->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <p id="errtender_category_id" class="mb-0 text-danger em"></p>
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

                                {{-- URL Slug --}}
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>URL Slug</label>
                                            <div class="slug-input-group">
                                                <input id="slugInput" type="text" class="form-control ltr" name="slug" value="{{ $tender->slug }}" placeholder="url-slug">
                                                <button type="button" id="regenerateSlugBtn" class="slug-regen-btn" title="Rebuild a short, meaningful slug from the current title">
                                                    <i class="fas fa-sync-alt"></i> Regenerate
                                                </button>
                                            </div>
                                            <p id="errslug" class="mb-0 text-danger em"></p>
                                            <p class="text-warning mb-0"><small>Changing the title above will NOT change this. Edit it here manually, or click Regenerate to rebuild it from the title above — the old URL redirects (301) automatically either way.</small></p>
                                            <style>
                                                .slug-input-group { display: flex; gap: 8px; }
                                                .slug-input-group input { flex: 1; min-width: 0; }
                                                .slug-regen-btn {
                                                    flex: 0 0 auto; display: inline-flex; align-items: center; gap: 7px;
                                                    padding: 0 14px; border-radius: 6px; border: 1px solid rgba(21,114,232,.35);
                                                    background: rgba(21,114,232,.12); color: #6ea8f7; font-size: 12.5px; font-weight: 600;
                                                    white-space: nowrap; cursor: pointer; transition: background .15s ease, color .15s ease, border-color .15s ease;
                                                }
                                                .slug-regen-btn:hover:not(:disabled) { background: rgba(21,114,232,.24); border-color: rgba(21,114,232,.55); color: #fff; }
                                                .slug-regen-btn:disabled { opacity: .6; cursor: wait; }
                                                .slug-regen-btn i { font-size: 11.5px; }
                                                .slug-regen-btn.is-loading i { animation: slugRegenSpin .6s linear infinite; }
                                                @keyframes slugRegenSpin { to { transform: rotate(360deg); } }
                                                #slugInput.slug-just-regenerated { animation: slugRegenFlash 1s ease; }
                                                @keyframes slugRegenFlash {
                                                    0% { box-shadow: 0 0 0 3px rgba(21,114,232,.45); }
                                                    100% { box-shadow: 0 0 0 0 rgba(21,114,232,0); }
                                                }
                                            </style>
                                            <script>
                                                document.addEventListener('DOMContentLoaded', function () {
                                                    var btn = document.getElementById('regenerateSlugBtn');
                                                    var input = document.getElementById('slugInput');
                                                    if (!btn || !input) return;
                                                    btn.addEventListener('click', function () {
                                                        var title = (document.querySelector('[name="title"]').value || '').trim();
                                                        if (!title) { alert('Enter a title first.'); return; }
                                                        btn.disabled = true;
                                                        btn.classList.add('is-loading');
                                                        fetch("{{ route('admin.slug.preview') }}", {
                                                            method: 'POST',
                                                            headers: {
                                                                'Content-Type': 'application/json',
                                                                'Accept': 'application/json',
                                                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                                            },
                                                            body: JSON.stringify({ title: title, module: 'tender', id: {{ $tender->id }} }),
                                                        })
                                                            .then(function (r) { return r.json(); })
                                                            .then(function (data) {
                                                                if (!data.slug) return;
                                                                input.value = data.slug;
                                                                input.classList.remove('slug-just-regenerated');
                                                                void input.offsetWidth;
                                                                input.classList.add('slug-just-regenerated');
                                                            })
                                                            .catch(function () { alert('Could not regenerate the URL — try again.'); })
                                                            .finally(function () {
                                                                btn.disabled = false;
                                                                btn.classList.remove('is-loading');
                                                            });
                                                    });
                                                });
                                            </script>
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

                                {{-- Expert Source --}}
                                <div class="form-group">
                                    <label>Public Procurement Expert Source **</label>
                                    <div>
                                        <label class="radio-inline mr-4">
                                            <input type="radio" name="expert_source" id="expertSourceMember" value="member"
                                                {{ !empty($tender->expert_member_id) ? 'checked' : '' }}>
                                            Select from Team Members
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" name="expert_source" id="expertSourceCustom" value="custom"
                                                {{ empty($tender->expert_member_id) ? 'checked' : '' }}>
                                            Custom Entry
                                        </label>
                                    </div>
                                    <p id="errexpert_source" class="mb-0 text-danger em"></p>
                                </div>

                                <div class="form-group" id="expertMemberWrap">
                                    <label>Team Member **</label>
                                    <select id="expertMemberSelect" name="expert_member_id" class="form-control">
                                        <option value="" selected disabled>Select Team Member</option>
                                        @foreach ($members as $m)
                                            <option value="{{ $m->id }}"
                                                data-position="{{ e($m->rank) }}"
                                                data-details="{{ $m->details }}"
                                                data-whatsapp="{{ e(preg_replace('/\D/', '', $m->whatsapp ?? '')) }}"
                                                data-email="{{ e($m->email) }}"
                                                data-image="{{ !empty($m->image) ? asset('assets/front/img/members/' . $m->image) : '' }}"
                                                {{ (int) $tender->expert_member_id === (int) $m->id ? 'selected' : '' }}>
                                                {{ $m->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="mb-0 text-warning" style="font-size:11px;"><i class="fas fa-info-circle"></i> Fields below are pre-filled from the selected member — you can still edit them for this tender.</p>
                                    <p id="errexpert_member_id" class="mb-0 text-danger em"></p>
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
                                    <textarea class="form-control summernote" name="expert_details" rows="5" placeholder="Enter Expert Details">{{ $tender->expert_details }}</textarea>
                                    <p id="errexpert_details" class="mb-0 text-danger em"></p>
                                </div>

                                {{-- Expert WhatsApp & Email --}}
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Expert WhatsApp **</label>
                                            <div style="display:flex;">
                                                <select id="waCode" class="wa-code-select ltr">
                                                    @include('admin.tender.tender._wa_codes')
                                                </select>
                                                <input type="text" id="waNumber" class="form-control ltr" style="border-radius:0 4px 4px 0;" placeholder="e.g. 1712345678" inputmode="numeric">
                                            </div>
                                            <input type="hidden" name="expert_whatsapp" id="waFull">
                                            <p class="mb-0 text-warning" style="font-size:11px;"><i class="fas fa-info-circle"></i> Enter number without country code</p>
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
                                    <p class="text-warning mb-0">{{ allowed_image_extensions_label() }} images are allowed</p>
                                    <p class="text-warning mb-0"><small>Recommended size: 700x800px (7:8 portrait). The image is cropped to fit this ratio.</small></p>
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
        var langCodeMap = @json($langs->pluck('code', 'id'));

        // WhatsApp combiner — handles: "01630968359" / "+8801630968359" / "8801630968359"
        var waCodes = $('#waCode option').map(function() { return $(this).val().replace(/\D/g,''); }).get()
                          .sort(function(a,b){ return b.length - a.length; }); // longest first

        function syncWa() {
            var code = $('#waCode').val().replace(/\D/g, '');
            var raw  = $('#waNumber').val().replace(/\D/g, '');

            var num;
            if (!raw) { $('#waFull').val(''); return; }

            if (raw.indexOf(code) === 0) {
                num = raw;                   // already has country code
            } else if (raw.charAt(0) === '0') {
                num = code + raw.slice(1);   // local 0-prefix format
            } else {
                num = code + raw;            // bare number
            }
            $('#waFull').val(num);
        }

        // Pre-fill on edit: parse stored value → select correct code + fill number field
        (function() {
            var raw = '{{ preg_replace('/[^0-9]/', '', $tender->expert_whatsapp ?? '') }}';
            if (!raw) return;
            // Deduplicate doubled country code (e.g. 8808801630... → 8801630...)
            for (var i = 0; i < waCodes.length; i++) {
                if (raw.indexOf(waCodes[i] + waCodes[i]) === 0) {
                    raw = raw.slice(waCodes[i].length);
                    break;
                }
            }
            // Match country code
            var matched = '';
            for (var i = 0; i < waCodes.length; i++) {
                if (raw.indexOf(waCodes[i]) === 0) { matched = waCodes[i]; break; }
            }
            if (matched) {
                $('#waCode').val('+' + matched);
                $('#waNumber').val(raw.slice(matched.length));
            } else if (raw.charAt(0) === '0') {
                $('#waNumber').val(raw.slice(1)); // strip leading 0
            } else {
                $('#waNumber').val(raw);
            }
            syncWa();
        })();

        $('#waCode, #waNumber').on('input change', syncWa);

        $(document).ready(function() {

            // Country code searchable dropdown with SVG flags (cross-platform incl. Windows)
            function flagEmojiToIso(emoji) {
                var chars = [...emoji];
                if (chars.length < 2) return null;
                var a = chars[0].codePointAt(0) - 0x1F1E6;
                var b = chars[1].codePointAt(0) - 0x1F1E6;
                if (a < 0 || a > 25 || b < 0 || b > 25) return null;
                return String.fromCharCode(97 + a) + String.fromCharCode(97 + b);
            }
            function waFlagTemplate(option) {
                if (!option.id) return option.text;
                var allChars = [...option.text];
                var iso = flagEmojiToIso(allChars[0] + allChars[1]);
                var label = option.text.replace(/^(\S+\s)/, '').trim();
                if (!iso) return label;
                var flagHtml = '<span class="fi fi-' + iso + '"></span>';
                return $('<span class="wa-flag-option">' + flagHtml + '<span>' + label + '</span></span>');
            }
            $('#waCode').select2({
                width: '145px',
                dropdownAutoWidth: true,
                templateResult: waFlagTemplate,
                templateSelection: waFlagTemplate,
            });

            // Expert source toggle
            function toggleExpertSource() {
                $('#expertMemberWrap').toggle($('#expertSourceMember').is(':checked'));
            }
            $('input[name=expert_source]').on('change', toggleExpertSource);
            toggleExpertSource();

            // Autofill expert fields from the selected team member (still editable after)
            $('#expertMemberSelect').on('change', function() {
                var opt = $(this).find('option:selected');
                $('input[name=expert_name]').val(opt.text().trim());
                $('input[name=expert_position]').val(opt.data('position') || '');

                var details = opt.data('details') || '';
                var $details = $('textarea[name=expert_details]');
                $details.val(details);
                if ($details.next('.note-editor').length) {
                    $details.summernote('code', details);
                }

                var wa = opt.data('whatsapp') || '';
                if (wa) { $('#waFull').val(wa); }

                $('input[name=expert_email]').val(opt.data('email') || '');

                var img = opt.data('image');
                if (img) {
                    $('#thumbPreview2').html('<img src="' + img + '" alt="Expert Image">');
                    $('#fileInput2').val(img);
                }
            });

            // Load categories + team members when language changes — moves
            // this tender to the selected language's tender list on save.
            $("#language").on('change', function () {
                var langId = $(this).val();
                window.ajaxSuccessRedirect = "{{ route('admin.tender.index') }}?language=" + langCodeMap[langId];

                $.get("{{ url('/') }}/admin/tender/" + langId + "/get_categories", function (data) {
                    var options = '<option value="" disabled selected>Select Category</option>';
                    if (data.length === 0) {
                        options += '<option value="" disabled>No Category Exists</option>';
                    } else {
                        $.each(data, function (i, cat) {
                            options += '<option value="' + cat.id + '">' + cat.name + '</option>';
                        });
                    }
                    $("#tender_category_id").html(options);
                });

                $.get("{{ url('/') }}/admin/tender/" + langId + "/get_members", function (data) {
                    var $select = $("#expertMemberSelect").empty();
                    $select.append($('<option>', { value: '', selected: true, disabled: true, text: 'Select Team Member' }));
                    if (data.length === 0) {
                        $select.append($('<option>', { value: '', disabled: true, text: 'No Team Member Exists' }));
                    } else {
                        $.each(data, function (i, m) {
                            var img = m.image ? "{{ asset('assets/front/img/members') }}/" + m.image : '';
                            $select.append($('<option>', {
                                value: m.id,
                                text: m.name || '',
                                'data-position': m.rank || '',
                                'data-details': m.details || '',
                                'data-whatsapp': (m.whatsapp || '').replace(/\D/g, ''),
                                'data-email': m.email || '',
                                'data-image': img
                            }));
                        });
                    }
                });

                $(".request-loader").addClass("show");
                $.get("{{ url('/') }}/admin/rtlcheck/" + langId, function (data) {
                    $(".request-loader").removeClass("show");
                    if (data == 1) {
                        $("form input:not(.ltr), form select:not(.ltr), form textarea:not(.ltr)").addClass('rtl');
                        $("form .summernote").siblings('.note-editor').find('.note-editable').addClass('rtl text-right');
                    } else {
                        $("form input, form select, form textarea").removeClass('rtl');
                        $("form .summernote").siblings('.note-editor').find('.note-editable').removeClass('rtl text-right');
                    }
                });
            });

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

<script>
    // Same client-side required-field check as create.blade.php — see
    // that file's comment for the full "why" (custom.js's #submitBtn
    // handler is a plain click listener with no native form validation
    // step at all). tender_image is NOT in this list — TenderController@
    // update() never force-requires it (the existing image just persists
    // if the admin doesn't pick a new one), unlike store().
    document.addEventListener('click', function (e) {
        if (!e.target || e.target.id !== 'submitBtn' || !window.jQuery) return;
        var $ = window.jQuery;

        var required = [
            { name: 'country' },
            { name: 'tender_code' },
            { name: 'language_id' },
            { name: 'tender_category_id' },
            { name: 'title' },
            { name: 'submission_deadline' },
            { name: 'overview', summernote: true },
            { name: 'expert_name' },
            { name: 'expert_position' },
            { name: 'expert_details', summernote: true },
            { name: 'expert_whatsapp' },
            { name: 'expert_email' },
        ];

        // expert_member_id is only required when "Existing team member" is
        // picked — mirrors TenderController@update's own required_if rule.
        var expertSource = $('input[name="expert_source"]:checked').val();
        if (expertSource === 'member') {
            required.push({ name: 'expert_member_id' });
        }

        $('.em').each(function () { $(this).html(''); });

        var firstInvalid = null;
        required.forEach(function (f) {
            var value;
            if (f.summernote) {
                var $sn = $('.summernote[name="' + f.name + '"]');
                value = $sn.length ? $sn.summernote('code').replace(/<[^>]*>/g, '').trim() : '';
            } else {
                value = ($('[name="' + f.name + '"]').first().val() || '').toString().trim();
            }
            if (!value) {
                var $err = $('#err' + f.name);
                if ($err.length) $err.html('This field is required.');
                if (!firstInvalid) firstInvalid = f.name;
            }
        });

        if (firstInvalid) {
            e.preventDefault();
            e.stopPropagation();
            $.notify({
                message: 'Please fill in all required fields.',
                title: 'Validation Error!',
                icon: 'fa fa-bell',
            }, {
                type: 'danger',
                placement: { from: 'top', align: 'right' },
                showProgressbar: true,
                time: 1000,
                allow_dismiss: true,
                delay: 4000,
            });
        }
    }, true);
</script>
@endsection
