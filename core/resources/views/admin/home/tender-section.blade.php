@extends('admin.layout')

@if(!empty($abs->language) && $abs->language->rtl == 1)
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
    <h4 class="page-title">Tender Section</h4>
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
        <a href="#">Home Page</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">Tender Section</a>
      </li>
    </ul>
  </div>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-lg-10">
                    <div class="card-title">Update Tender Section</div>
                </div>
                <div class="col-lg-2">
                    @if (!empty($langs))
                        <select name="language" class="form-control" onchange="window.location='{{url()->current() . '?language='}}'+this.value">
                            <option value="" selected disabled>Select a Language</option>
                            @foreach ($langs as $lang)
                                <option value="{{$lang->code}}" {{$lang->code == request()->input('language') ? 'selected' : ''}}>{{$lang->name}}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
            </div>
        </div>
        <div class="card-body pt-5 pb-4">
          <div class="row">
            <div class="col-lg-8 offset-lg-2">

              <form id="tenderSectionForm" action="{{route('admin.tendersection.update', $lang_id)}}" method="post">
                @csrf

                {{-- Tender Section Background Image --}}
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label for="">Tender Section Background Image (Optional)</label>
                            <br>
                            <div class="thumb-preview" id="thumbPreviewTender"
                                style="position: relative; display: inline-block;">
                                @if (!empty($abe->tender_section_bg))
                                    <img src="{{ asset('assets/front/img/' . $abe->tender_section_bg) }}"
                                        alt="Background Image">
                                @else
                                    <img src="{{ asset('assets/admin/img/noimage.jpg') }}" alt="No Image">
                                @endif
                                <button type="button" class="btn btn-danger btn-sm delete-image" id="deleteTenderSectionBgBtn"
                                    style="position: absolute; top: 10px; right: 10px; opacity: 0; transition: opacity 0.3s;">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <br>
                            <br>

                            <input id="fileInputTender" type="hidden" name="tender_section_bg">
                            <input id="deleteTenderSectionBg" type="hidden" name="delete_tender_section_bg" value="0">
                            <button id="chooseImageTender" class="choose-image btn btn-primary" type="button"
                                data-multiple="false" data-toggle="modal" data-target="#lfmModalTender">Choose
                                Background Image</button>

                            <p class="text-warning mb-0">{{ allowed_image_extensions_label() }} images are allowed. This will
                                be the background for the entire tender section.</p>
                            <p class="text-info mb-0"><small><strong>Recommended size:</strong> 1920px x 350px (Width x Height)</small></p>
                            <p class="text-danger mb-0 em" id="errtender_section_bg"></p>

                            <!-- Tender Section Background LFM Modal -->
                            <div class="modal fade lfm-modal" id="lfmModalTender" tabindex="-1" role="dialog"
                                aria-labelledby="lfmModalTitle" aria-hidden="true">
                                <i class="fas fa-times-circle"></i>
                                <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                    <div class="modal-content">
                                        <div class="modal-body p-0">
                                            <iframe src="{{ url('laravel-filemanager') }}?serial=Tender"
                                                style="width: 100%; height: 500px; overflow: hidden; border: none;"></iframe>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                  <label for="">Title **</label>
                  <input name="tender_section_title" class="form-control" value="{{$abs->tender_section_title}}">
                  <p id="errtender_section_title" class="em text-danger mb-0"></p>
                </div>
                <div class="form-group">
                  <label for="">Text **</label>
                  <input name="tender_section_text" class="form-control" value="{{$abs->tender_section_text}}">
                  <p id="errtender_section_text" class="em text-danger mb-0"></p>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>Tender Area Overlay Color Code **</label>
                            <input class="jscolor form-control ltr" name="tender_overlay_color"
                                value="{{ $abe->tender_overlay_color ?? '000000' }}">
                            <p id="errtender_overlay_color" class="em text-danger mb-0"></p>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>Tender Area Overlay Opacity **</label>
                            <input type="text" class="form-control ltr" name="tender_overlay_opacity"
                                value="{{ $abe->tender_overlay_opacity ?? '0.6' }}">
                            <p class="text-warning mb-0">Opacity can be between 0 to 1.</p>
                            <p id="errtender_overlay_opacity" class="em text-danger mb-0"></p>
                        </div>
                    </div>
                </div>
              </form>

            </div>
          </div>
        </div>

        <div class="card-footer">
          <div class="form">
            <div class="form-group from-show-notify row">
              <div class="col-12 text-center">
                <button type="submit" id="tenderSectionSubmitBtn" class="btn btn-success">Update</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Inline scripts for tender section --}}
  <script type="text/javascript">
      document.addEventListener('DOMContentLoaded', function() {
          var deleteTenderSectionBgBtn = document.getElementById('deleteTenderSectionBgBtn');
          if (deleteTenderSectionBgBtn) {
              deleteTenderSectionBgBtn.addEventListener('click', function(e) {
                  e.preventDefault();
                  swal({
                      title: 'Are you sure?',
                      text: 'You want to delete this background image?',
                      icon: 'warning',
                      buttons: {
                          cancel: { text: "Cancel", visible: true, closeModal: true },
                          confirm: { text: "Yes, delete it!", closeModal: true }
                      },
                      dangerMode: true,
                  }).then(function(willDelete) {
                      if (willDelete) {
                          document.getElementById('deleteTenderSectionBg').value = '1';
                          document.getElementById('fileInputTender').value = '';
                          document.getElementById('tenderSectionForm').submit();
                      }
                  });
              });
          }

          var tenderSectionSubmitBtn = document.getElementById('tenderSectionSubmitBtn');
          if (tenderSectionSubmitBtn) {
              tenderSectionSubmitBtn.addEventListener('click', function(e) {
                  e.preventDefault();
                  document.getElementById('tenderSectionForm').submit();
              });
          }

          var thumbPreviewTender = document.getElementById('thumbPreviewTender');
          if (thumbPreviewTender) {
              thumbPreviewTender.addEventListener('mouseenter', function() {
                  var deleteBtn = this.querySelector('.delete-image');
                  if (deleteBtn) deleteBtn.style.opacity = '1';
              });

              thumbPreviewTender.addEventListener('mouseleave', function() {
                  var deleteBtn = this.querySelector('.delete-image');
                  if (deleteBtn) deleteBtn.style.opacity = '0';
              });
          }
      });
  </script>

@endsection

@section('scripts')
    <script type="text/javascript">
        // Laravel File Manager SetUrl handler for Tender section
        window.SetUrl = function(items) {
            var activeModal = $('.lfm-modal.show');
            var modalId = activeModal.attr('id');
            var serial = modalId ? modalId.replace('lfmModal', '') : '';

            if (items && items.length > 0) {
                var fileUrl = items[0].url;

                if (serial) {
                    var fileInput = document.getElementById('fileInput' + serial);
                    var thumbPreview = document.getElementById('thumbPreview' + serial);

                    if (fileInput) {
                        fileInput.value = fileUrl;
                    }

                    if (thumbPreview) {
                        var img = thumbPreview.querySelector('img');
                        if (img) {
                            img.src = fileUrl;
                        }
                    }

                    if (typeof window.closeLfmModal === 'function') {
                        window.closeLfmModal(serial);
                    }
                }
            }
        };
    </script>
@endsection
