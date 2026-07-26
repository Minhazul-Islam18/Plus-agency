@extends('admin.layout')

@section('content')
<div class="page-header">
    <h4 class="page-title">Login Branding</h4>
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
            <a href="#">Admins Management</a>
        </li>
        <li class="separator">
            <i class="flaticon-right-arrow"></i>
        </li>
        <li class="nav-item">
            <a href="#">Login Branding</a>
        </li>
    </ul>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card-title">Login Page Branding</div>
                    </div>
                    <div class="col-lg-4 text-right">
                        <a class="btn btn-secondary btn-sm" href="{{route('admin.adminSettings.security')}}">Security Settings</a>
                    </div>
                </div>
            </div>
            <div class="card-body pt-5 pb-4">
                <div class="row">
                    <div class="col-lg-6 offset-lg-3">
                        <form id="imageForm" action="{{route('admin.adminSettings.updateLoginBranding')}}" method="POST">
                            @csrf

                            {{-- Logo --}}
                            <div class="form-group">
                                <label for="">Logo</label>
                                <br>
                                <div class="thumb-preview" id="thumbPreview1">
                                    <img src="{{asset('assets/front/img/' . ($aps->login_logo ?: $bs->logo))}}" alt="Login Logo" onerror="this.src='{{asset('assets/admin/img/noimage.jpg')}}'">
                                </div>
                                <br><br>
                                <input id="fileInput1" type="hidden" name="login_logo">
                                <button id="chooseImage1" class="choose-image btn btn-primary" type="button" data-multiple="false" data-toggle="modal" data-target="#lfmModal1">Choose Image</button>
                                <p class="text-warning mb-0">JPG, PNG, JPEG, SVG images are allowed. Leave empty to use the site logo ({{$bs->logo}}).</p>
                                @if ($errors->has('login_logo'))
                                <p class="text-danger mb-0">{{$errors->first('login_logo')}}</p>
                                @endif

                                @if ($aps->login_logo)
                                <div class="mt-2">
                                    <label class="mb-0">
                                        <input type="checkbox" name="clear_login_logo" value="1"> Remove custom logo and use the site logo instead
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
                                <label for="">Left Panel Background Image</label>
                                <br>
                                <div class="thumb-preview" id="thumbPreview2">
                                    <img src="{{asset('assets/front/img/' . ($aps->login_bg_image ?: 'admin-login-bg.png'))}}" alt="Login Background" onerror="this.src='{{asset('assets/admin/img/noimage.jpg')}}'">
                                </div>
                                <br><br>
                                <input id="fileInput2" type="hidden" name="login_bg_image">
                                <button id="chooseImage2" class="choose-image btn btn-primary" type="button" data-multiple="false" data-toggle="modal" data-target="#lfmModal2">Choose Image</button>
                                <p class="text-warning mb-0">JPG, PNG, JPEG, SVG images are allowed. Leave empty to use the default background.</p>
                                @if ($errors->has('login_bg_image'))
                                <p class="text-danger mb-0">{{$errors->first('login_bg_image')}}</p>
                                @endif

                                @if ($aps->login_bg_image)
                                <div class="mt-2">
                                    <label class="mb-0">
                                        <input type="checkbox" name="clear_login_bg_image" value="1"> Remove custom background and use the default instead
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
                                <label for="">Platform Name</label>
                                <input type="text" class="form-control" name="platform_name" value="{{old('platform_name', $aps->platform_name)}}" placeholder="{{$bs->website_title}}">
                                <p class="text-warning mb-0">Leave empty to use the site title ({{$bs->website_title}}).</p>
                                @if ($errors->has('platform_name'))
                                <p class="text-danger mb-0">{{$errors->first('platform_name')}}</p>
                                @endif
                            </div>

                            <div class="form-group">
                                <label for="">Tagline **</label>
                                <input type="text" class="form-control" name="tagline" value="{{old('tagline', $aps->tagline)}}">
                                @if ($errors->has('tagline'))
                                <p class="text-danger mb-0">{{$errors->first('tagline')}}</p>
                                @endif
                            </div>

                            <div class="form-group">
                                <label for="">Copyright Text</label>
                                <input type="text" class="form-control" name="copyright_text" value="{{old('copyright_text', $aps->copyright_text)}}" placeholder="{{html_entity_decode(strip_tags($bs->copyright_text)) ?: 'Leave empty to use the site copyright text'}}">
                            </div>

                            @php
                                $features = old('features', $aps->features ?: [
                                    ['icon' => 'fas fa-shield-alt', 'title' => 'Secure Authentication', 'desc' => 'Advanced protection for your account'],
                                    ['icon' => 'fas fa-folder-open', 'title' => 'Tender Management', 'desc' => 'Manage tenders with ease'],
                                    ['icon' => 'fas fa-credit-card', 'title' => 'Payment Tracking', 'desc' => 'Real-time payment monitoring'],
                                    ['icon' => 'fas fa-chart-bar', 'title' => 'Administrative Dashboard', 'desc' => 'Powerful tools for better decisions'],
                                ]);
                            @endphp

                            <label>Feature Highlights</label>
                            @for ($i = 0; $i < 4; $i++)
                                <div class="row mb-2">
                                    <div class="col-lg-3">
                                        <input type="text" class="form-control" name="features[{{$i}}][icon]" value="{{$features[$i]['icon'] ?? ''}}" placeholder="Icon class">
                                    </div>
                                    <div class="col-lg-4">
                                        <input type="text" class="form-control" name="features[{{$i}}][title]" value="{{$features[$i]['title'] ?? ''}}" placeholder="Feature title">
                                    </div>
                                    <div class="col-lg-5">
                                        <input type="text" class="form-control" name="features[{{$i}}][desc]" value="{{$features[$i]['desc'] ?? ''}}" placeholder="Short description">
                                    </div>
                                </div>
                            @endfor
                            @if ($errors->has('features'))
                            <p class="text-danger mb-0">{{$errors->first('features')}}</p>
                            @endif

                        </form>
                    </div>
                </div>
            </div>
            <div class="card-footer text-center">
                <button type="submit" class="btn btn-success" form="imageForm">Update</button>
            </div>
        </div>
    </div>
</div>
@endsection
