@extends('admin.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">Password Change Required</h4>
    <ul class="breadcrumbs">
      <li class="nav-home">
        <a href="#">
          <i class="flaticon-home"></i>
        </a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">Password Change Required</a>
      </li>
    </ul>
  </div>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <form action="{{route('admin.forcedChangePassword.update')}}" method="post" role="form">
          <div class="card-header">
            <div class="card-title">Create a New Password</div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-lg-6 offset-lg-3">
                 {{csrf_field()}}
                 <div class="alert alert-info">You're signing in with a temporary password. Please set a new password to continue.</div>
                 <div class="form-body">
                    <div class="form-group">
                       <label>New Password</label>
                       <div class="">
                          <input class="form-control" name="password" placeholder="New Password" type="password">
                          @if ($errors->has('password'))
                          <span class="text-danger">
                              {{ $errors->first('password') }}
                          </span>
                          @endif
                       </div>
                    </div>
                    <div class="form-group">
                       <label>Confirm Password</label>
                       <div class="">
                          <input class="form-control" name="password_confirmation" placeholder="Confirm Password" type="password">
                       </div>
                    </div>
                 </div>
              </div>
            </div>
          </div>
          <div class="card-footer">
            <div class="row">
               <div class="col-md-12 text-center">
                  <button type="submit" class="btn btn-success">Create Password</button>
               </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

@endsection
