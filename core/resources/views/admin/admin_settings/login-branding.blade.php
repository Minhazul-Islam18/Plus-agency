@extends('admin.layout')

@section('content')
<div class="page-header">
    <h4 class="page-title">{{__('Login Branding')}}</h4>
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
            <a href="#">{{__('Admins Management')}}</a>
        </li>
        <li class="separator">
            <i class="flaticon-right-arrow"></i>
        </li>
        <li class="nav-item">
            <a href="#">{{__('Login Branding')}}</a>
        </li>
    </ul>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-lg-5">
                        <div class="card-title d-inline-block">{{__('Login Page Branding')}}</div>
                    </div>
                    <div class="col-lg-3">
                        @if (!empty($langs))
                            <select name="language" class="form-control"
                                onchange="window.location='{{ url()->current() . '?language=' }}'+this.value">
                                @foreach ($langs as $lang)
                                    <option value="{{ $lang->code }}"
                                        {{ $lang->code == $selLang->code ? 'selected' : '' }}>
                                        {{ $lang->name }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                    <div class="col-lg-4 text-right">
                        <a class="btn btn-secondary btn-sm" href="{{route('admin.adminSettings.security')}}">{{__('Security Settings')}}</a>
                    </div>
                </div>
            </div>
            <div class="card-body pt-5 pb-4">
                <div class="row">
                    <div class="col-lg-6 offset-lg-3">
                        <form id="imageForm" action="{{route('admin.adminSettings.updateLoginBranding')}}" method="POST">
                            @csrf
                            <input type="hidden" name="language_id" value="{{$aps->language_id}}">

                            {{-- Logo --}}
                            <div class="form-group">
                                <label for="">{{__('Logo')}}</label>
                                <br>
                                <div class="thumb-preview" id="thumbPreview1">
                                    <img src="{{asset('assets/front/img/' . ($aps->login_logo ?: $bs->logo))}}" alt="{{__('Login Logo')}}" onerror="this.src='{{asset('assets/admin/img/noimage.jpg')}}'">
                                </div>
                                <br><br>
                                <input id="fileInput1" type="hidden" name="login_logo">
                                <button id="chooseImage1" class="choose-image btn btn-primary" type="button" data-multiple="false" data-toggle="modal" data-target="#lfmModal1">{{__('Choose Image')}}</button>
                                <p class="text-warning mb-0">{{__('JPG, PNG, JPEG, SVG images are allowed. Leave empty to use the site logo (:logo).', ['logo' => $bs->logo])}}</p>
                                @if ($errors->has('login_logo'))
                                <p class="text-danger mb-0">{{$errors->first('login_logo')}}</p>
                                @endif

                                @if ($aps->login_logo)
                                <div class="mt-2">
                                    <label class="mb-0">
                                        <input type="checkbox" name="clear_login_logo" value="1"> {{__('Remove custom logo and use the site logo instead')}}
                                    </label>
                                </div>
                                @endif

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

                            {{-- Left panel background image --}}
                            <div class="form-group">
                                <label for="">{{__('Left Panel Background Image')}}</label>
                                <br>
                                <div class="thumb-preview" id="thumbPreview2">
                                    <img src="{{asset('assets/front/img/' . ($aps->login_bg_image ?: 'admin-login-bg.png'))}}" alt="{{__('Login Background')}}" onerror="this.src='{{asset('assets/admin/img/noimage.jpg')}}'">
                                </div>
                                <br><br>
                                <input id="fileInput2" type="hidden" name="login_bg_image">
                                <button id="chooseImage2" class="choose-image btn btn-primary" type="button" data-multiple="false" data-toggle="modal" data-target="#lfmModal2">{{__('Choose Image')}}</button>
                                <p class="text-warning mb-0">{{__('JPG, PNG, JPEG, SVG images are allowed. Leave empty to use the default background.')}}</p>
                                @if ($errors->has('login_bg_image'))
                                <p class="text-danger mb-0">{{$errors->first('login_bg_image')}}</p>
                                @endif

                                @if ($aps->login_bg_image)
                                <div class="mt-2">
                                    <label class="mb-0">
                                        <input type="checkbox" name="clear_login_bg_image" value="1"> {{__('Remove custom background and use the default instead')}}
                                    </label>
                                </div>
                                @endif

                                <div class="modal fade lfm-modal" id="lfmModal2" tabindex="-1" role="dialog" aria-labelledby="lfmModalTitle" aria-hidden="true">
                                    <i class="fas fa-times-circle"></i>
                                    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-body p-0">
                                                <iframe src="{{url('laravel-filemanager')}}?serial=2" style="width: 100%; height: 500px; overflow: hidden; border: none;"></iframe>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="">{{__('Platform Name')}}</label>
                                <input type="text" class="form-control" name="platform_name" value="{{old('platform_name', $aps->platform_name)}}" placeholder="{{$bs->website_title}}">
                                <p class="text-warning mb-0">{{__('Leave empty to use the site title (:title).', ['title' => $bs->website_title])}}</p>
                                @if ($errors->has('platform_name'))
                                <p class="text-danger mb-0">{{$errors->first('platform_name')}}</p>
                                @endif
                            </div>

                            <div class="form-group">
                                <label for="">{{__('Tagline')}} **</label>
                                <input type="text" class="form-control" name="tagline" value="{{old('tagline', $aps->tagline)}}">
                                @if ($errors->has('tagline'))
                                <p class="text-danger mb-0">{{$errors->first('tagline')}}</p>
                                @endif
                            </div>

                            <div class="form-group">
                                <label for="">{{__('Copyright Text')}}</label>
                                <input type="text" class="form-control" name="copyright_text" value="{{old('copyright_text', $aps->copyright_text)}}" placeholder="{{html_entity_decode(strip_tags($bs->copyright_text)) ?: __('Leave empty to use the site copyright text')}}">
                            </div>

                            @php
                                $features = old('features', $aps->features ?: []);
                            @endphp

                            <label>{{__('Feature Highlights')}}</label>
                            <p class="text-warning" style="font-size: 12px;">{{__('This language can have a different number of features than others — add or remove rows as needed.')}}</p>

                            <div id="featuresContainer">
                                @foreach ($features as $i => $feature)
                                    @php $iconVal = $feature['icon'] ?? 'fas fa-star'; @endphp
                                    <div class="row mb-2 feature-row">
                                        <div class="col-lg-3">
                                            <div class="btn-group d-block">
                                                <button type="button" class="btn btn-outline-secondary iconpicker-component" tabindex="-1"><i class="{{$iconVal}}"></i></button>
                                                <button type="button" class="icp icp-dd btn btn-outline-secondary dropdown-toggle" data-selected="{{$iconVal}}" data-toggle="dropdown"></button>
                                                <div class="dropdown-menu"></div>
                                            </div>
                                            <input type="text" class="form-control feature-icon-input mt-1" name="features[{{$i}}][icon]" value="{{$iconVal}}" placeholder="{{__('Icon class')}}">
                                        </div>
                                        <div class="col-lg-3">
                                            <input type="text" class="form-control" name="features[{{$i}}][title]" value="{{$feature['title'] ?? ''}}" placeholder="{{__('Feature title')}}">
                                        </div>
                                        <div class="col-lg-4">
                                            <input type="text" class="form-control" name="features[{{$i}}][desc]" value="{{$feature['desc'] ?? ''}}" placeholder="{{__('Short description')}}">
                                        </div>
                                        <div class="col-lg-2">
                                            <button type="button" class="btn btn-danger btn-sm remove-feature-row"><i class="fas fa-trash"></i> {{__('Remove')}}</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <button type="button" id="addFeatureBtn" class="btn btn-info btn-sm mb-3"><i class="fas fa-plus"></i> {{__('Add Feature')}}</button>

                            @if ($errors->has('features'))
                            <p class="text-danger mb-0">{{$errors->first('features')}}</p>
                            @endif

                        </form>
                    </div>
                </div>
            </div>
            <div class="card-footer text-center">
                <button type="submit" class="btn btn-success" form="imageForm">{{__('Update')}}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    (function () {
        var container = document.getElementById('featuresContainer');
        var addBtn = document.getElementById('addFeatureBtn');
        var featureIndex = {{ count($features) }};
        var MAX_FEATURES = 8;

        var iconPlaceholder = @json(__('Icon class'));
        var titlePlaceholder = @json(__('Feature title'));
        var descPlaceholder = @json(__('Short description'));
        var removeLabel = @json(__('Remove'));

        function refreshAddButton() {
            var count = container.querySelectorAll('.feature-row').length;
            addBtn.disabled = count >= MAX_FEATURES;
        }

        var DEFAULT_ICON = 'fas fa-star';

        addBtn.addEventListener('click', function () {
            if (container.querySelectorAll('.feature-row').length >= MAX_FEATURES) return;

            var row = document.createElement('div');
            row.className = 'row mb-2 feature-row';
            row.innerHTML =
                '<div class="col-lg-3">' +
                    '<div class="btn-group d-block">' +
                        '<button type="button" class="btn btn-outline-secondary iconpicker-component" tabindex="-1"><i class="' + DEFAULT_ICON + '"></i></button>' +
                        '<button type="button" class="icp icp-dd btn btn-outline-secondary dropdown-toggle" data-selected="' + DEFAULT_ICON + '" data-toggle="dropdown"></button>' +
                        '<div class="dropdown-menu"></div>' +
                    '</div>' +
                    '<input type="text" class="form-control feature-icon-input mt-1" name="features[' + featureIndex + '][icon]" value="' + DEFAULT_ICON + '" placeholder="' + iconPlaceholder + '">' +
                '</div>' +
                '<div class="col-lg-3"><input type="text" class="form-control" name="features[' + featureIndex + '][title]" placeholder="' + titlePlaceholder + '"></div>' +
                '<div class="col-lg-4"><input type="text" class="form-control" name="features[' + featureIndex + '][desc]" placeholder="' + descPlaceholder + '"></div>' +
                '<div class="col-lg-2"><button type="button" class="btn btn-danger btn-sm remove-feature-row"><i class="fas fa-trash"></i> ' + removeLabel + '</button></div>';
            container.appendChild(row);
            // The plugin only auto-binds elements present at page load
            // ($('.icp-dd').iconpicker() in custom.js) — new rows need their
            // own instance initialized explicitly.
            $(row).find('.icp-dd').iconpicker();
            featureIndex++;
            refreshAddButton();
        });

        // Keep the visible text input (still directly editable as a
        // fallback) and the picker's own preview icon in sync whenever a
        // selection is made, in any row — scoped via closest/siblings so
        // each row's picker only ever touches its own input.
        $(document).on('iconpickerSelected', '.icp-dd', function (event, ui) {
            var iconClass = (ui && ui.iconpickerValue) ? ui.iconpickerValue : null;
            var $group = $(this).closest('.btn-group');
            if (!iconClass) {
                iconClass = $group.find('.iconpicker-component i').attr('class');
            }
            $group.find('.iconpicker-component i').attr('class', iconClass);
            $group.siblings('.feature-icon-input').val(iconClass);
        });

        container.addEventListener('click', function (e) {
            var btn = e.target.closest('.remove-feature-row');
            if (!btn) return;

            // Keep at least one row — the server requires a non-empty list.
            if (container.querySelectorAll('.feature-row').length <= 1) return;

            btn.closest('.feature-row').remove();
            refreshAddButton();
        });

        refreshAddButton();
    })();
</script>
@endsection
