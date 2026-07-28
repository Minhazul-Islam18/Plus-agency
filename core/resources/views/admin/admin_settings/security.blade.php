@extends('admin.layout')

@section('content')
<div class="page-header">
    <h4 class="page-title">{{__('Security Settings')}}</h4>
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
            <a href="#">{{__('Security Settings')}}</a>
        </li>
    </ul>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card-title">{{__('Login Security')}}</div>
                    </div>
                    <div class="col-lg-4 text-right">
                        <a class="btn btn-secondary btn-sm" href="{{route('admin.adminSettings.loginBranding')}}">{{__('Login Branding')}}</a>
                    </div>
                </div>
            </div>
            <div class="card-body pt-5 pb-4">
                <div class="row">
                    <div class="col-lg-6 offset-lg-3">
                        <form action="{{route('admin.adminSettings.updateSecurity')}}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="">{{__('Max Login Attempts')}} **</label>
                                <input type="number" min="1" max="20" class="form-control" name="max_login_attempts" value="{{old('max_login_attempts', $aps->max_login_attempts)}}">
                                <p class="text-warning mb-0">{{__('Number of failed login attempts before an admin account is locked. Does not apply to the super admin.')}}</p>
                                @if ($errors->has('max_login_attempts'))
                                <p class="text-danger mb-0">{{$errors->first('max_login_attempts')}}</p>
                                @endif
                            </div>
                            <div class="form-group text-center">
                                <button type="submit" class="btn btn-success">{{__('Update')}}</button>
                            </div>
                        </form>

                        <hr>

                        <div class="form-group">
                            <label>{{__('Admin Panel URL')}}</label>
                            <p class="mb-1">{{__('Current prefix:')}} <code>/{{$adminPrefix}}</code></p>
                            <p class="text-muted mb-0" style="font-size: 13px;">
                                {!! __('To change this, set :env in the server\'s :envfile file, then run :cmd. This is not editable from this page — it requires server access, by design, so it can\'t be changed by an attacker who only has admin-panel access.', [
                                    'env' => '<code>ADMIN_PANEL_PREFIX</code>',
                                    'envfile' => '<code>.env</code>',
                                    'cmd' => '<code>php artisan config:clear &amp;&amp; php artisan route:clear</code>',
                                ]) !!}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
