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
    .thumb-preview .remove-invoice-btn,
    .thumb-preview .remove-breadcrumb-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        opacity: 0;
        transition: opacity 0.3s;
        z-index: 10;
    }
    .thumb-preview:hover .remove-invoice-btn,
    .thumb-preview:hover .remove-breadcrumb-btn {
        opacity: 1;
    }
</style>
@endsection

@section('content')
<div class="page-header">
  <h4 class="page-title">Settings</h4>
  <ul class="breadcrumbs">
    <li class="nav-home">
      <a href="{{ route('admin.dashboard') }}"><i class="flaticon-home"></i></a>
    </li>
    <li class="separator"><i class="flaticon-right-arrow"></i></li>
    <li class="nav-item"><a href="#">Tenders</a></li>
    <li class="separator"><i class="flaticon-right-arrow"></i></li>
    <li class="nav-item"><a href="#">Settings</a></li>
  </ul>
</div>

<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header">
        <div class="card-title d-inline-block">Tender Settings</div>
        <a class="btn btn-info btn-sm float-right d-inline-block"
          href="{{ route('admin.tender.index') . '?language=' . $language }}">
          <span class="btn-label"><i class="fas fa-backward" style="font-size:12px;"></i></span>
          Back
        </a>
      </div>

      <div class="card-body pt-4 pb-4">
        <div class="row">
          <div class="col-lg-8 offset-lg-2">
            <form id="settingsForm" action="{{ route('admin.tender.updateSettings') }}"
              method="POST" enctype="multipart/form-data">
              @csrf
              <input type="hidden" name="language" value="{{ $language }}">

              {{-- Tender Module Toggle --}}
              <div class="form-group">
                <label class="font-weight-bold">Tender Module</label>
                <div class="selectgroup w-100">
                  <label class="selectgroup-item">
                    <input type="radio" name="is_tender" value="1" class="selectgroup-input"
                      {{ $abex->is_tender == 1 ? 'checked' : '' }}>
                    <span class="selectgroup-button">Active</span>
                  </label>
                  <label class="selectgroup-item">
                    <input type="radio" name="is_tender" value="0" class="selectgroup-input"
                      {{ $abex->is_tender == 0 ? 'checked' : '' }}>
                    <span class="selectgroup-button">Deactive</span>
                  </label>
                </div>
                <p class="text-warning mb-0 mt-1" style="font-size:12px;">
                  Enable / disable all Tender Module pages.
                </p>
              </div>

              <hr>
              <h6 class="font-weight-bold mb-3 mt-2">Invoice Images</h6>

              {{-- Watermark --}}
              <div class="form-group">
                <label>Watermark Image</label>
                <br>
                <div class="thumb-preview" id="thumbPreview1">
                  @if (!empty($abex->invoice_watermark))
                    <img src="{{ asset('assets/admin/img/invoice/' . $abex->invoice_watermark) }}"
                      alt="Watermark" class="uploaded-img">
                    <button type="button" class="btn btn-danger btn-sm remove-invoice-btn"
                      data-serial="1" data-field="invoice_watermark"
                      data-default="{{ asset('assets/admin/img/defaults/invoice-watermark.png') }}">
                      <i class="fas fa-times"></i>
                    </button>
                  @else
                    <img src="{{ asset('assets/admin/img/defaults/invoice-watermark.png') }}"
                      alt="Default Watermark" class="uploaded-img" style="opacity:0.5;" title="Default">
                  @endif
                </div>
                <input type="hidden" name="clear_invoice_watermark" id="clearField1" value="0">
                <br><br>
                <input id="fileInput1" type="hidden" name="invoice_watermark">
                <button id="chooseImage1" class="choose-image btn btn-primary" type="button"
                  data-multiple="false" data-toggle="modal" data-target="#lfmModal1">
                  Choose Image
                </button>
                <p class="text-warning mb-0 mt-1">JPG, PNG, JPEG images are allowed</p>
                <p class="text-muted mb-0"><small>Faint watermark behind fee table. Leave empty to use default.</small></p>
              </div>

              {{-- Stamp / Signature --}}
              <div class="form-group">
                <label>Stamp / Signature</label>
                <br>
                <div class="thumb-preview" id="thumbPreview2">
                  @if (!empty($abex->invoice_sign))
                    <img src="{{ asset('assets/admin/img/invoice/' . $abex->invoice_sign) }}"
                      alt="Signature" class="uploaded-img">
                    <button type="button" class="btn btn-danger btn-sm remove-invoice-btn"
                      data-serial="2" data-field="invoice_sign"
                      data-default="{{ asset('assets/admin/img/defaults/invoice-sign.png') }}">
                      <i class="fas fa-times"></i>
                    </button>
                  @else
                    <img src="{{ asset('assets/admin/img/defaults/invoice-sign.png') }}"
                      alt="Default Signature" class="uploaded-img" style="opacity:0.5;" title="Default">
                  @endif
                </div>
                <input type="hidden" name="clear_invoice_sign" id="clearField2" value="0">
                <br><br>
                <input id="fileInput2" type="hidden" name="invoice_sign">
                <button id="chooseImage2" class="choose-image btn btn-primary" type="button"
                  data-multiple="false" data-toggle="modal" data-target="#lfmModal2">
                  Choose Image
                </button>
                <p class="text-warning mb-0 mt-1">JPG, PNG, JPEG images are allowed</p>
                <p class="text-muted mb-0"><small>Stamp/signature shown bottom-right of invoice. Leave empty to use default.</small></p>
              </div>

              {{-- Footer Wavy Background --}}
              <div class="form-group">
                <label>Footer Wavy Background</label>
                <br>
                <div class="thumb-preview" id="thumbPreview3">
                  @if (!empty($abex->invoice_footer_wavy))
                    <img src="{{ asset('assets/admin/img/invoice/' . $abex->invoice_footer_wavy) }}"
                      alt="Footer Wavy" class="uploaded-img">
                    <button type="button" class="btn btn-danger btn-sm remove-invoice-btn"
                      data-serial="3" data-field="invoice_footer_wavy"
                      data-default="{{ asset('assets/admin/img/defaults/footer-wavy.png') }}">
                      <i class="fas fa-times"></i>
                    </button>
                  @else
                    <img src="{{ asset('assets/admin/img/defaults/footer-wavy.png') }}"
                      alt="Default Wavy" class="uploaded-img" style="opacity:0.6;" title="Default">
                  @endif
                </div>
                <input type="hidden" name="clear_invoice_footer_wavy" id="clearField3" value="0">
                <br><br>
                <input id="fileInput3" type="hidden" name="invoice_footer_wavy">
                <button id="chooseImage3" class="choose-image btn btn-primary" type="button"
                  data-multiple="false" data-toggle="modal" data-target="#lfmModal3">
                  Choose Image
                </button>
                <p class="text-warning mb-0 mt-1">JPG, PNG, JPEG images are allowed</p>
                <p class="text-muted mb-0"><small>Background image behind footer address. Leave empty to use default.</small></p>
              </div>

              <hr>
              <h6 class="font-weight-bold mb-3 mt-2">Invoice Footer</h6>

              {{-- Footer Address --}}
              <div class="form-group">
                <label>Footer Address / Contact Info</label>
                <textarea name="invoice_footer_address" class="form-control summernote" rows="4">{{ old('invoice_footer_address', $abex->invoice_footer_address) }}</textarea>
                <small class="text-muted">Shown in the footer of every invoice. Leave empty to use the built-in default address.</small>
              </div>

              <hr>
              <h6 class="font-weight-bold mb-3 mt-2">Download Watermark</h6>
              <p class="text-muted mb-3" style="font-size:12px;">
                Personalised, traceable watermark stamped on every <strong>PDF</strong> a buyer downloads.
                Applies globally (all languages). Non-PDF files are not stamped.
              </p>

              {{-- Enable toggle --}}
              <div class="form-group">
                <label class="font-weight-bold">Watermark</label>
                <div class="selectgroup w-100">
                  <label class="selectgroup-item">
                    <input type="radio" name="tender_watermark_enabled" value="1" class="selectgroup-input"
                      {{ $abex->tender_watermark_enabled == 1 ? 'checked' : '' }}>
                    <span class="selectgroup-button">Active</span>
                  </label>
                  <label class="selectgroup-item">
                    <input type="radio" name="tender_watermark_enabled" value="0" class="selectgroup-input"
                      {{ $abex->tender_watermark_enabled == 0 ? 'checked' : '' }}>
                    <span class="selectgroup-button">Deactive</span>
                  </label>
                </div>
                <p class="text-warning mb-0 mt-1" style="font-size:12px;">
                  When active, a PDF that fails to stamp is blocked from download (buyer is asked to contact support).
                </p>
                <a href="{{ route('admin.tender.watermarkTest') }}" target="_blank"
                  class="btn btn-outline-info btn-sm mt-2">
                  <i class="fas fa-vial"></i> Run watermark test (no purchase needed)
                </a>
              </div>

              {{-- Template --}}
              <div class="form-group">
                <label>Watermark Text</label>
                <textarea name="tender_watermark_template" class="form-control ltr" rows="4"
                  placeholder="{company}&#10;Downloaded by: {name}&#10;Tender ID: {tender_code}&#10;{datetime}">{{ old('tender_watermark_template', $abex->tender_watermark_template) }}</textarea>
                <small class="text-muted d-block mt-1">
                  One line each. Placeholders:
                  <code>{company}</code> <code>{name}</code> <code>{first_name}</code> <code>{last_name}</code>
                  <code>{tender_code}</code> <code>{tender_title}</code> <code>{order_number}</code>
                  <code>{email}</code> <code>{datetime}</code> <code>{date}</code>.
                  <code>{datetime}</code> is the download time in UTC. Empty lines are dropped.
                </small>
              </div>

              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Text Color</label>
                    <input class="form-control jscolor ltr" name="tender_watermark_color"
                      value="{{ $abex->tender_watermark_color ?? 'FF0000' }}" placeholder="FF0000">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Opacity</label>
                    <input type="number" class="form-control ltr" name="tender_watermark_opacity"
                      value="{{ $abex->tender_watermark_opacity ?? 0.30 }}" step="0.05" min="0.05" max="1">
                    <small class="text-muted">0.05 – 1</small>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Font Size</label>
                    <input type="number" class="form-control ltr" name="tender_watermark_font_size"
                      value="{{ $abex->tender_watermark_font_size ?? 24 }}" step="1" min="6" max="96">
                    <small class="text-muted">6 – 96 pt</small>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Rotation</label>
                    <input type="number" class="form-control ltr" name="tender_watermark_rotation"
                      value="{{ $abex->tender_watermark_rotation ?? 45 }}" step="1" min="-90" max="90">
                    <small class="text-muted">degrees (45 = diagonal)</small>
                  </div>
                </div>
              </div>

              <hr>
              <h6 class="font-weight-bold mb-3 mt-2">PDF Encryption</h6>
              <p class="text-muted mb-3" style="font-size:12px;">
                Lock every downloaded <strong>PDF</strong> against editing. Buyers open the file normally (no prompt),
                but cannot modify, annotate or fill it &mdash; those actions need the owner password below, held by admins only.
                Applies globally (all languages). Non-PDF files are not encrypted.
              </p>

              {{-- Encryption toggle --}}
              <div class="form-group">
                <label class="font-weight-bold">Encryption</label>
                <div class="selectgroup w-100">
                  <label class="selectgroup-item">
                    <input type="radio" name="tender_pdf_encrypt_enabled" value="1" class="selectgroup-input"
                      {{ $abex->tender_pdf_encrypt_enabled == 1 ? 'checked' : '' }}>
                    <span class="selectgroup-button">Active</span>
                  </label>
                  <label class="selectgroup-item">
                    <input type="radio" name="tender_pdf_encrypt_enabled" value="0" class="selectgroup-input"
                      {{ $abex->tender_pdf_encrypt_enabled == 0 ? 'checked' : '' }}>
                    <span class="selectgroup-button">Deactive</span>
                  </label>
                </div>
                <p class="text-warning mb-0 mt-1" style="font-size:12px;">
                  When active, a PDF that fails to encrypt is blocked from download (buyer is asked to contact support).
                </p>
              </div>

              {{-- Password --}}
              <div class="form-group">
                <label>Owner Password</label>
                <div class="input-group">
                  <input type="password" class="form-control ltr" name="tender_pdf_password" id="tenderPdfPassword"
                    value="{{ old('tender_pdf_password', $abex->tender_pdf_password) }}"
                    placeholder="Enter owner password" autocomplete="off">
                  <div class="input-group-append">
                    <button class="btn btn-secondary" type="button" id="togglePdfPassword" tabindex="-1">
                      <i class="fas fa-eye"></i>
                    </button>
                  </div>
                </div>
                <small class="text-muted d-block mt-1">Required when encryption is active. Admin-only &mdash; never sent to buyers. Use it to unlock editing in a PDF reader.</small>
              </div>

              <hr>
              <h6 class="font-weight-bold mb-3 mt-2">Secure Download Links</h6>
              <p class="text-muted mb-3" style="font-size:12px;">
                How many times each secure download link may be opened before it expires. Applies to every
                tender download link (email &amp; on-screen). Global (all languages).
              </p>

              {{-- Max downloads per link --}}
              <div class="form-group">
                <label>Opens Allowed Per Link</label>
                <input type="number" class="form-control ltr" name="tender_max_downloads"
                  value="{{ $abex->tender_max_downloads ?? 3 }}" step="1" min="1" max="20">
                <small class="text-muted d-block mt-1">Default 3. Each selected tender gets its own link with its own counter.</small>
              </div>

              <hr>
              <h6 class="font-weight-bold mb-3 mt-2">Breadcrumb Background</h6>
              <p class="text-muted mb-3" style="font-size:12px;">Applied to the Tenders list page and Tender Details page. Settings are per-language.</p>

              {{-- Breadcrumb BG Image --}}
              <div class="form-group">
                <label>Background Image</label>
                <br>
                <div class="thumb-preview" id="thumbPreview4">
                  @if (!empty($abex->tender_breadcrumb_bg))
                    <img src="{{ asset('assets/front/img/' . $abex->tender_breadcrumb_bg) }}"
                      alt="Breadcrumb BG" class="uploaded-img">
                    <button type="button" class="btn btn-danger btn-sm remove-breadcrumb-btn">
                      <i class="fas fa-times"></i>
                    </button>
                  @else
                    <img src="{{ asset('assets/admin/img/noimage.jpg') }}" alt="No Image"
                      class="uploaded-img">
                  @endif
                </div>
                <br><br>
                <input id="fileInput4" type="hidden" name="tender_breadcrumb_bg">
                <button id="chooseImage4" class="choose-image btn btn-primary" type="button"
                  data-multiple="false" data-toggle="modal" data-target="#lfmModal4">
                  Choose Image
                </button>
                <p class="text-warning mb-0 mt-1">JPG, PNG, JPEG images are allowed</p>
                <p class="text-info mb-0"><small><strong>Recommended size:</strong> 1920px x 350px (Width x Height)</small></p>
              </div>

              {{-- Overlay Color --}}
              <div class="form-group">
                <label>Breadcrumb Overlay Color Code</label>
                <input class="form-control jscolor ltr" name="tender_breadcrumb_overlay_color"
                  value="{{ $abex->tender_breadcrumb_overlay_color ?? '000000' }}"
                  placeholder="Enter Color Code">
              </div>

              {{-- Overlay Opacity --}}
              <div class="form-group">
                <label>Breadcrumb Overlay Opacity</label>
                <input type="number" class="form-control" name="tender_breadcrumb_overlay_opacity"
                  value="{{ $abex->tender_breadcrumb_overlay_opacity ?? 0.5 }}"
                  step="0.01" min="0" max="1" placeholder="Enter opacity (0 to 1)">
                <p class="text-warning mb-0">Value must be between 0 to 1 (e.g. 0.5 for 50% opacity)</p>
              </div>

            </form>
          </div>
        </div>
      </div>

      <div class="card-footer">
        <div class="form-group text-center mb-0">
          <button type="submit" form="settingsForm" class="btn btn-success">Save Settings</button>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- LFM Modals --}}
@foreach([1,2,3,4] as $n)
<div class="modal fade lfm-modal" id="lfmModal{{ $n }}" tabindex="-1" role="dialog" aria-hidden="true">
  <i class="fas fa-times-circle"></i>
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-body p-0">
        <iframe src="{{ url('laravel-filemanager') }}?serial={{ $n }}"
          style="width:100%;height:500px;overflow:hidden;border:none;"></iframe>
      </div>
    </div>
  </div>
</div>
@endforeach
@endsection

@section('scripts')
<script>
$(document).ready(function() {

    // PDF password show / hide
    $('#togglePdfPassword').on('click', function() {
        var $inp = $('#tenderPdfPassword');
        var toText = $inp.attr('type') === 'password';
        $inp.attr('type', toText ? 'text' : 'password');
        $(this).find('i').toggleClass('fa-eye fa-eye-slash');
    });

    // Invoice image remove (client-side: revert to default, flag for server clear)
    $(document).on('click', '.remove-invoice-btn', function(e) {
        e.preventDefault();
        var $btn     = $(this);
        var serial   = $btn.data('serial');
        var defSrc   = $btn.data('default');

        swal({
            title: 'Are you sure?',
            text: 'This will revert to the default image.',
            icon: 'warning',
            buttons: { cancel: { text: 'Cancel', visible: true }, confirm: { text: 'Yes, remove it!', closeModal: true } },
            dangerMode: true,
        }).then(function(willDelete) {
            if (willDelete) {
                $('#thumbPreview' + serial + ' img').attr('src', defSrc).css('opacity', 0.5);
                $btn.remove();
                $('#fileInput' + serial).val('');
                $('#clearField' + serial).val('1');
            }
        });
    });

    // Breadcrumb image remove (AJAX delete)
    $(document).on('click', '.remove-breadcrumb-btn', function(e) {
        e.preventDefault();
        swal({
            title: 'Are you sure?',
            text: 'Delete this breadcrumb background image?',
            icon: 'warning',
            buttons: { cancel: { text: 'Cancel', visible: true }, confirm: { text: 'Yes, delete it!', closeModal: false } },
            dangerMode: true,
        }).then(function(willDelete) {
            if (willDelete) {
                $.ajax({
                    url: '{{ route('admin.tender.deleteTenderBreadcrumbBg') }}',
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}', language: '{{ $language }}' },
                    success: function(res) {
                        swal.close();
                        if (res.success) {
                            $('#thumbPreview4 img').attr('src', '{{ asset('assets/admin/img/noimage.jpg') }}');
                            $('.remove-breadcrumb-btn').remove();
                            $('#fileInput4').val('');
                            $.notify({ message: 'Background image deleted.' }, { type: 'success' });
                        }
                    },
                    error: function() { swal.close(); $.notify({ message: 'Delete failed.' }, { type: 'danger' }); }
                });
            }
        });
    });

});
</script>
@endsection
