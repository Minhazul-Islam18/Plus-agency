<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
  	<meta content='width=device-width, initial-scale=1.0, shrink-to-fit=no' name='viewport' />
    <title>{{$bs->website_title}}</title>
  	<link rel="icon" href="{{asset('assets/front/img/'.$bs->favicon)}}">
    <link rel="stylesheet" href="{{asset('assets/admin/css/bootstrap.min.css')}}">
    <link href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('assets/admin/css/login.css')}}">
  </head>
  <body class="login-v2-body">
    <div class="login-v2">
      <div class="login-v2-left" style="background-image: url('{{asset('assets/front/img/' . ($aps->login_bg_image ?: 'admin-login-bg.png'))}}');">
        <div class="login-v2-left-inner">
          <img class="login-v2-logo" src="{{asset('assets/front/img/' . ($aps->login_logo ?: $bs->logo))}}" alt="{{$aps->platform_name ?: $bs->website_title}}">
          <h1>{{$aps->platform_name ?: $bs->website_title}}</h1>
          <div class="login-v2-tagline">{{$aps->tagline}}</div>
          <div class="login-v2-divider"></div>

          <ul class="login-v2-features">
            @foreach ($aps->features ?: [] as $feature)
              <li>
                <span class="login-v2-feature-icon"><i class="{{$feature['icon'] ?? 'fas fa-check'}}"></i></span>
                <span class="login-v2-feature-text">
                  <span class="login-v2-feature-title">{{$feature['title'] ?? ''}}</span>
                  @if (!empty($feature['desc']))
                    <span class="login-v2-feature-desc">{{$feature['desc']}}</span>
                  @endif
                </span>
              </li>
            @endforeach
          </ul>

          <div class="login-v2-copyright">
            {{$aps->copyright_text ?: html_entity_decode(strip_tags($bs->copyright_text)) ?: '© ' . date('Y') . ' ' . ($aps->platform_name ?: $bs->website_title) . '. All rights reserved.'}}
          </div>
        </div>
      </div>

      <div class="login-v2-right">
        <div class="login-v2-card">
          <div class="text-center mb-4">
            <img class="login-v2-card-logo" src="{{asset('assets/front/img/' . ($aps->login_logo ?: $bs->logo))}}" alt="">
            <h2>Welcome Back!</h2>
            <p class="login-v2-subtext">Sign in to your administrator account</p>
          </div>

          @if (session()->has('alert'))
            <div class="alert alert-danger fade show" role="alert" style="font-size: 14px;">
              <strong>Oops!</strong> {{session('alert')}}
            </div>
          @endif

          <form action="{{route('admin.auth')}}" method="POST">
            @csrf

            <div class="form-group">
              <label>Username</label>
              <div class="login-v2-input">
                <i class="far fa-user"></i>
                <input type="text" name="username" placeholder="Enter your username" autocomplete="username">
              </div>
              @if ($errors->has('username'))
                <p class="text-danger mb-0">{{$errors->first('username')}}</p>
              @endif
            </div>

            <div class="form-group">
              <label>Password</label>
              <div class="login-v2-input">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" id="loginPassword" placeholder="Enter your password" autocomplete="current-password">
                <button type="button" class="login-v2-eye-toggle" id="loginPasswordToggle" tabindex="-1">
                  <i class="far fa-eye"></i>
                </button>
              </div>
              @if ($errors->has('password'))
                <p class="text-danger mb-0">{{$errors->first('password')}}</p>
              @endif
            </div>

            <div class="login-v2-row">
              <label class="login-v2-remember">
                <input type="checkbox" name="remember" value="1"> Remember me
              </label>
              <a class="login-v2-forgot" href="{{route('admin.forget.form')}}">Forgot password?</a>
            </div>

            <button type="submit" class="login-v2-submit"><i class="fas fa-lock"></i> SIGN IN</button>
          </form>

          <div class="login-v2-or"><span>or</span></div>

          <div class="login-v2-ssl"><i class="fas fa-shield-alt"></i> <span>Protected by 256-bit SSL encryption<br>Your data is safe and secure</span></div>
        </div>

        <div class="login-v2-support"><i class="fas fa-shield-alt"></i> Need help? <a href="{{route('front.contact')}}">Contact Support</a></div>
      </div>
    </div>

    <!-- jquery js -->
    <script src="{{asset('assets/front/js/jquery-3.3.1.min.js')}}"></script>
    <!-- popper js -->
    <script src="{{asset('assets/front/js/popper.min.js')}}"></script>
    <!-- bootstrap js -->
    <script src="{{asset('assets/front/js/bootstrap.min.js')}}"></script>
    <!-- Bootstrap Notify -->
    <script src="{{asset('assets/admin/js/plugin/bootstrap-notify/bootstrap-notify.min.js')}}"></script>

    <script>
      (function () {
        var toggle = document.getElementById('loginPasswordToggle');
        var input = document.getElementById('loginPassword');
        if (!toggle || !input) return;
        toggle.addEventListener('click', function () {
          var isHidden = input.getAttribute('type') === 'password';
          input.setAttribute('type', isHidden ? 'text' : 'password');
          toggle.querySelector('i').className = isHidden ? 'far fa-eye-slash' : 'far fa-eye';
        });
      })();
    </script>

    @if (session()->has('warning'))
    <script>
      var content = {};

      content.message = '{{session('warning')}}';
      content.title = 'Sorry!';
      content.icon = 'fa fa-bell';

      $.notify(content,{
        type: 'warning',
        placement: {
          from: 'top',
          align: 'right'
        },
        showProgressbar: true,
        time: 1000,
        delay: 4000,
      });
    </script>
    @endif
  </body>
</html>
