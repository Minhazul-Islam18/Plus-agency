<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
  	<meta content='width=device-width, initial-scale=1.0, shrink-to-fit=no' name='viewport' />
    <title>{{$bs->website_title}}</title>
  	<link rel="icon" href="{{asset('assets/front/img/'.$bs->favicon)}}">
    <link rel="stylesheet" href="{{asset('assets/admin/css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/admin/css/login.css')}}">
  </head>
  <body>
    <div class="login-page">
      <div class="text-center mb-4">
        <img class="login-logo" src="{{asset('assets/front/img/'.$bs->logo)}}" alt="">
      </div>
      <div class="form">
        @if (session()->has('success'))
          <div class="alert alert-success fade show" role="alert" style="font-size: 14px;">
            {{session('success')}}
          </div>
        @endif

        @if ($valid)
          <form class="login-form" action="{{route('admin.activate.store', $token)}}" method="POST">
            @csrf
            <input type="password" name="password" placeholder="New Password"/>
            @if ($errors->has('password'))
              <p class="text-danger text-left">{{$errors->first('password')}}</p>
            @endif
            <input type="password" name="password_confirmation" placeholder="Confirm Password"/>
            <button type="submit">Create Password</button>
          </form>
        @else
          <div class="alert alert-danger fade show" role="alert" style="font-size: 14px;">
            This activation link is invalid or has expired. Please contact the super admin for a new invitation.
          </div>
          <a class="forget-link" href="{{route('admin.login')}}">&laquo; Back to login</a>
        @endif
      </div>
    </div>

    <!-- jquery js -->
    <script src="{{asset('assets/front/js/jquery-3.3.1.min.js')}}"></script>
    <!-- popper js -->
    <script src="{{asset('assets/front/js/popper.min.js')}}"></script>
    <!-- bootstrap js -->
    <script src="{{asset('assets/front/js/bootstrap.min.js')}}"></script>
  </body>
</html>
