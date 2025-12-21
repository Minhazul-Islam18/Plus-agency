@extends('admin.layout')

@if(!empty($abe->language) && $abe->language->rtl == 1)
@section('styles')
<style>
    form input,
    form textarea,
    form select,
    select {
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
    <h4 class="page-title">Pricing Section</h4>
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
            <a href="#">Pricing Section</a>
        </li>
    </ul>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-lg-10">
                        <div class="card-title">Pricing Section Settings</div>
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
                    <form id="backgroundForm" action="{{route('admin.package.background.upload', $lang_id)}}" method="POST">
                        @csrf

                        {{-- Background Image Part --}}
                        <div class="form-group">
                            <label for="">Background Image (Optional)</label>
                            <br>
                            <div class="thumb-preview" id="thumbPreview1" style="position: relative; display: inline-block;">
                                @if (!empty($abe->pricing_bg))
                                    <img src="{{asset('assets/front/img/'.$abe->pricing_bg)}}" alt="Background Image">
                                @elseif (!empty($abe->package_background))
                                    <img src="{{asset('assets/front/img/'.$abe->package_background)}}" alt="Background Image">
                                @else
                                    <img src="{{asset('assets/admin/img/noimage.jpg')}}" alt="No Image">
                                @endif
                                <button type="button" class="btn btn-danger btn-sm delete-image" id="deletePricingBgBtn"
                                    style="position: absolute; top: 10px; right: 10px; opacity: 0; transition: opacity 0.3s;">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <br>
                            <br>

                            <input id="fileInput1" type="hidden" name="background_image">
                            <input id="deleteBackground" type="hidden" name="delete_background" value="0">
                            <button id="chooseImage1" class="choose-image btn btn-primary" type="button" data-multiple="false" data-toggle="modal" data-target="#lfmModal1">Choose Background Image</button>

                            <p class="text-warning mb-0">JPG, PNG, JPEG, SVG images are allowed</p>
                            @if ($errors->has('background_image'))
                            <p class="text-danger mb-0">{{$errors->first('background_image')}}</p>
                            @endif

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
                        </div>

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="">Pricing Title **</label>
                                    <input type="text" class="form-control" name="pricing_title" value="{{$abe->pricing_title}}">
                                    @if ($errors->has('pricing_title'))
                                    <p class="mb-0 text-danger">{{$errors->first('pricing_title')}}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="">Pricing Subtitle **</label>
                                    <input type="text" class="form-control" name="pricing_subtitle" value="{{$abe->pricing_subtitle}}">
                                    @if ($errors->has('pricing_subtitle'))
                                    <p class="mb-0 text-danger">{{$errors->first('pricing_subtitle')}}</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Pricing Area Overlay Color Code **</label>
                                    <input class="jscolor form-control ltr" name="pricing_overlay_color" value="{{$abe->pricing_overlay_color ?? '000000'}}">
                                    @if ($errors->has('pricing_overlay_color'))
                                    <p class="mb-0 text-danger">{{$errors->first('pricing_overlay_color')}}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Pricing Area Overlay Opacity **</label>
                                    <input type="text" class="form-control ltr" name="pricing_overlay_opacity" value="{{$abe->pricing_overlay_opacity ?? '0.6'}}">
                                    <p class="text-warning mb-0">Opacity can be between 0 to 1.</p>
                                    @if ($errors->has('pricing_overlay_opacity'))
                                    <p class="mb-0 text-danger">{{$errors->first('pricing_overlay_opacity')}}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card-footer text-center">
            <div class="row">
                <div class="col-lg-12">
                    <button form="backgroundForm" class="btn btn-success" type="submit">Update</button>
                </div>
            </div>
        </div>

    </div>
</div>
</div>

@endsection

@section('scripts')
    <script type="text/javascript">
        // Laravel File Manager SetUrl handler
        window.SetUrl = function(items) {
            // Get the active modal's serial number
            var activeModal = $('.lfm-modal.show');
            var modalId = activeModal.attr('id');
            var serial = modalId ? modalId.replace('lfmModal', '') : '';

            if (items && items.length > 0) {
                var fileUrl = items[0].url;

                // Set value to corresponding fileInput
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

                    // Close the modal
                    if (typeof window.closeLfmModal === 'function') {
                        window.closeLfmModal(serial);
                    }
                }
            }
        };

        // Wait for DOM to be ready
        document.addEventListener('DOMContentLoaded', function() {
            // Show delete button on hover for pricing background
            var thumbPreview1 = document.getElementById('thumbPreview1');
            if (thumbPreview1) {
                thumbPreview1.addEventListener('mouseenter', function() {
                    var deleteBtn = this.querySelector('.delete-image');
                    if (deleteBtn) deleteBtn.style.opacity = '1';
                });

                thumbPreview1.addEventListener('mouseleave', function() {
                    var deleteBtn = this.querySelector('.delete-image');
                    if (deleteBtn) deleteBtn.style.opacity = '0';
                });
            }

            // Attach event listener to pricing background delete button
            var deletePricingBgBtn = document.getElementById('deletePricingBgBtn');
            if (deletePricingBgBtn) {
                deletePricingBgBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (confirm('Are you sure you want to delete this background image?')) {
                        document.getElementById('deleteBackground').value = '1';
                        document.getElementById('fileInput1').value = '';
                        // Submit the form immediately
                        document.getElementById('backgroundForm').submit();
                    }
                });
            }
        });
    </script>
@endsection
