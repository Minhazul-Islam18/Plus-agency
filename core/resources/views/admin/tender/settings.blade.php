@extends('admin.layout')

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
                      alt="Watermark" style="max-height:80px;">
                  @else
                    <img src="{{ asset('assets/admin/img/defaults/invoice-watermark.png') }}"
                      alt="Default Watermark" style="max-height:80px; opacity:0.5;" title="Default">
                  @endif
                </div>
                <br>
                <input id="fileInput1" type="hidden" name="invoice_watermark">
                <button id="chooseImage1" class="btn btn-primary btn-sm" type="button"
                  data-multiple="false" data-toggle="modal" data-target="#lfmModal1">
                  Choose Image
                </button>
                <p class="text-warning mb-0 mt-1" style="font-size:12px;">Faint watermark behind fee table. Leave empty to use default.</p>
              </div>

              {{-- Stamp / Signature --}}
              <div class="form-group">
                <label>Stamp / Signature</label>
                <br>
                <div class="thumb-preview" id="thumbPreview2">
                  @if (!empty($abex->invoice_sign))
                    <img src="{{ asset('assets/admin/img/invoice/' . $abex->invoice_sign) }}"
                      alt="Signature" style="max-height:100px;">
                  @else
                    <img src="{{ asset('assets/admin/img/defaults/invoice-sign.png') }}"
                      alt="Default Signature" style="max-height:100px; opacity:0.5;" title="Default">
                  @endif
                </div>
                <br>
                <input id="fileInput2" type="hidden" name="invoice_sign">
                <button id="chooseImage2" class="btn btn-primary btn-sm" type="button"
                  data-multiple="false" data-toggle="modal" data-target="#lfmModal2">
                  Choose Image
                </button>
                <p class="text-warning mb-0 mt-1" style="font-size:12px;">Stamp/signature shown bottom-right of invoice. Leave empty to use default.</p>
              </div>

              {{-- Footer Wavy Background --}}
              <div class="form-group">
                <label>Footer Wavy Background</label>
                <br>
                <div class="thumb-preview" id="thumbPreview3">
                  @if (!empty($abex->invoice_footer_wavy))
                    <img src="{{ asset('assets/admin/img/invoice/' . $abex->invoice_footer_wavy) }}"
                      alt="Footer Wavy" style="max-height:60px; width:100%; object-fit:cover;">
                  @else
                    <img src="{{ asset('assets/admin/img/defaults/footer-wavy.png') }}"
                      alt="Default Wavy" style="max-height:60px; width:100%; object-fit:cover; opacity:0.6;" title="Default">
                  @endif
                </div>
                <br>
                <input id="fileInput3" type="hidden" name="invoice_footer_wavy">
                <button id="chooseImage3" class="btn btn-primary btn-sm" type="button"
                  data-multiple="false" data-toggle="modal" data-target="#lfmModal3">
                  Choose Image
                </button>
                <p class="text-warning mb-0 mt-1" style="font-size:12px;">Background image behind footer address. Leave empty to use default.</p>
              </div>

              <hr>
              <h6 class="font-weight-bold mb-3 mt-2">Invoice Footer</h6>

              {{-- Footer Address --}}
              <div class="form-group">
                <label>Footer Address / Contact Info</label>
                <textarea name="invoice_footer_address" class="form-control summernote" rows="4">{{ old('invoice_footer_address', $abex->invoice_footer_address) }}</textarea>
                <small class="text-muted">Shown in the footer of every invoice. Leave empty to use the built-in default address.</small>
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

<div class="modal fade lfm-modal" id="lfmModal3" tabindex="-1" role="dialog" aria-hidden="true">
  <i class="fas fa-times-circle"></i>
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-body p-0">
        <iframe src="{{ url('laravel-filemanager') }}?serial=3"
          style="width:100%;height:500px;overflow:hidden;border:none;"></iframe>
      </div>
    </div>
  </div>
</div>
@endsection
