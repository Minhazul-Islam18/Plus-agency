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
