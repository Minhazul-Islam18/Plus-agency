@extends('admin.layout')

@php
$selLang = \App\Language::where('code', request()->input('language'))->first();
@endphp

@if(!empty($selLang) && $selLang->rtl == 1)
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
  <h4 class="page-title">Add New Tender</h4>
  <ul class="breadcrumbs">
    <li class="nav-home">
      <a href="{{ route('admin.dashboard') }}">
        <i class="flaticon-home"></i>
      </a>
    </li>
    <li class="separator"><i class="flaticon-right-arrow"></i></li>
    <li class="nav-item"><a href="#">Tender Management</a></li>
    <li class="separator"><i class="flaticon-right-arrow"></i></li>
    <li class="nav-item"><a href="#">Add New Tender</a></li>
  </ul>
</div>

<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header">
        <div class="card-title d-inline-block">Add New Tender</div>
        <a class="btn btn-info btn-sm float-right d-inline-block"
          href="{{ route('admin.tender.index') . '?language=' . request()->input('language') }}">
          <span class="btn-label"><i class="fas fa-backward" style="font-size:12px;"></i></span>
          Back
        </a>
      </div>

      <div class="card-body pt-5 pb-5">
        <div class="row">
          <div class="col-lg-6 offset-lg-3">

            <form id="ajaxForm" action="{{ route('admin.tender.store') }}" method="POST"
              enctype="multipart/form-data">
              @csrf

              {{-- Tender Image --}}
              <div class="form-group">
                <label>Tender Image **</label>
                <br>
                <div class="thumb-preview" id="thumbPreview1">
                  <img src="{{ asset('assets/admin/img/noimage.jpg') }}" alt="Tender Image">
                </div>
                <br><br>
                <input id="fileInput1" type="hidden" name="tender_image">
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
                      <option value="" selected disabled>Select Country</option>
                      @foreach ($countries as $country)
                        <option value="{{ $country }}">{{ $country }}</option>
                      @endforeach
                    </select>
                    <p id="errcountry" class="mb-0 text-danger em"></p>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Tender ID **</label>
                    <input type="text" class="form-control" name="tender_code"
                      placeholder="Enter Tender ID">
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
                      <option value="" selected disabled>Select Language</option>
                      @foreach ($langs as $lang)
                        <option value="{{ $lang->id }}"
                          {{ !empty($language) && $language->id == $lang->id ? 'selected' : '' }}>
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
                    <select id="tender_category_id" name="tender_category_id" class="form-control"
                      {{ empty($language) ? 'disabled' : '' }}>
                      <option value="" selected disabled>Select Category</option>
                      @foreach ($tender_categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
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
                      placeholder="Enter Tender Title">
                    <p id="errtitle" class="mb-0 text-danger em"></p>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Submission Deadline **</label>
                    <input type="datetime-local" class="form-control ltr" name="submission_deadline">
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
                      placeholder="Auto-calculated from modules" style="background:#f8f9fa;cursor:not-allowed;">
                    <p class="mb-0 text-warning"><small><i class="fas fa-info-circle"></i> Auto-calculated from the sum of active module costs.</small></p>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Previous Price ({{ $bex->base_currency_text }})</label>
                    <input type="number" step="0.01" class="form-control ltr" name="previous_price"
                      placeholder="Enter Previous Price">
                    <p class="mb-0 text-danger em"></p>
                  </div>
                </div>
              </div>

              {{-- Tender Video --}}
              <div class="form-group">
                <label>Tender Video <span class="text-muted">(Optional)</span></label>
                <input type="text" class="form-control ltr" name="video_link"
                  placeholder="Enter YouTube Video Link">
                <p id="errvideo_link" class="mb-0 text-danger em"></p>
              </div>

              {{-- Tender Overview --}}
              <div class="form-group">
                <label>Tender Overview **</label>
                <textarea class="form-control summernote" name="overview" rows="8"
                  placeholder="Enter Tender Overview"></textarea>
                <p id="erroverview" class="mb-0 text-danger em"></p>
              </div>

              {{-- Expert Source --}}
              <div class="form-group">
                <label>Public Procurement Expert Source **</label>
                <div>
                  <label class="radio-inline mr-4">
                    <input type="radio" name="expert_source" id="expertSourceMember" value="member">
                    Select from Team Members
                  </label>
                  <label class="radio-inline">
                    <input type="radio" name="expert_source" id="expertSourceCustom" value="custom" checked>
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
                      data-image="{{ !empty($m->image) ? asset('assets/front/img/members/' . $m->image) : '' }}">
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
                      placeholder="Enter Expert Name">
                    <p id="errexpert_name" class="mb-0 text-danger em"></p>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Public Procurement Expert Position **</label>
                    <input type="text" class="form-control" name="expert_position"
                      placeholder="Enter Expert Position">
                    <p id="errexpert_position" class="mb-0 text-danger em"></p>
                  </div>
                </div>
              </div>

              {{-- Expert Details --}}
              <div class="form-group">
                <label>Expert Details **</label>
                <textarea class="form-control summernote" name="expert_details" rows="5"
                  placeholder="Enter Expert Details"></textarea>
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
                      placeholder="Enter Expert Email">
                    <p id="errexpert_email" class="mb-0 text-danger em"></p>
                  </div>
                </div>
              </div>

              {{-- Expert Image --}}
              <div class="form-group">
                <label>Expert Image **</label>
                <br>
                <div class="thumb-preview" id="thumbPreview2">
                  <img src="{{ asset('assets/admin/img/noimage.jpg') }}" alt="Expert Image">
                </div>
                <br><br>
                <input id="fileInput2" type="hidden" name="expert_image">
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
              <button id="submitBtn" type="button" class="btn btn-success">Submit</button>
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
  window.ajaxSuccessRedirect = "{{ route('admin.tender.index') }}?language={{ request()->input('language') }}";
  var langCodeMap = @json($langs->pluck('code', 'id'));

  // WhatsApp combiner — handles: "01630968359" / "+8801630968359" / "8801630968359"
  function syncWa() {
    var code = $('#waCode').val().replace(/\D/g, '');          // e.g. "880"
    var raw  = $('#waNumber').val().replace(/\D/g, '');        // digits only

    var num;
    if (!raw) { $('#waFull').val(''); return; }

    if (raw.indexOf(code) === 0) {
      num = raw;                   // already has country code: 8801630968359
    } else if (raw.charAt(0) === '0') {
      num = code + raw.slice(1);   // local format: 01630968359 → 8801630968359
    } else {
      num = code + raw;            // bare number: 1630968359 → 8801630968359
    }
    $('#waFull').val(num);
  }
  $('#waCode, #waNumber').on('input change', syncWa);

  $(document).ready(function () {

    // Country code searchable dropdown with SVG flags (cross-platform incl. Windows)
    function flagEmojiToIso(emoji) {
      // Flag emoji = 2 Regional Indicator Symbols (U+1F1E6–U+1F1FF = A–Z)
      var chars = [...emoji];
      if (chars.length < 2) return null;
      var a = chars[0].codePointAt(0) - 0x1F1E6;
      var b = chars[1].codePointAt(0) - 0x1F1E6;
      if (a < 0 || a > 25 || b < 0 || b > 25) return null;
      return String.fromCharCode(97 + a) + String.fromCharCode(97 + b);
    }
    function waFlagTemplate(option, forSelection) {
      if (!option.id) return option.text;
      var allChars = [...option.text];
      var iso = flagEmojiToIso(allChars[0] + allChars[1]);
      var label = option.text.replace(/^(\S+\s)/, '').trim(); // strip flag char
      if (!iso) return label;
      var flagHtml = '<span class="fi fi-' + iso + '"></span>';
      return $('<span class="wa-flag-option">' + flagHtml + '<span>' + label + '</span></span>');
    }
    $('#waCode').select2({
      width: '145px',
      dropdownAutoWidth: true,
      templateResult: function(o) { return waFlagTemplate(o, false); },
      templateSelection: function(o) { return waFlagTemplate(o, true); },
    });

    // Expert source toggle
    function toggleExpertSource() {
      $('#expertMemberWrap').toggle($('#expertSourceMember').is(':checked'));
    }
    $('input[name=expert_source]').on('change', toggleExpertSource);
    toggleExpertSource();

    // Autofill expert fields from the selected team member (still editable after)
    $('#expertMemberSelect').on('change', function () {
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

    // Load categories + team members when language changes
    $("#language").on('change', function () {
      var langId = $(this).val();
      $("#tender_category_id").removeAttr('disabled');
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
              'data-details': m.details || '',   // raw HTML preserved — set via summernote('code', ...) on select
              'data-whatsapp': (m.whatsapp || '').replace(/\D/g, ''),
              'data-email': m.email || '',
              'data-image': img
            }));
          });
        }
      });

      // RTL check
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

  });
</script>
@endsection
