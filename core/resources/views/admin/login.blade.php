@extends('admin.auth-layout')

@section('auth-content')
  <div class="text-center mb-4">
    <img class="login-v2-card-logo" src="{{asset('assets/front/img/' . ($aps->login_logo ?: $bs->logo))}}" alt="">
    <h2>{{__('Welcome Back!')}}</h2>
    <p class="login-v2-subtext">{{__('Sign in to your administrator account')}}</p>
  </div>

  @if (session()->has('alert'))
    <div class="alert alert-danger fade show" role="alert" style="font-size: 14px;">
      <strong>{{__('Oops!')}}</strong> {{session('alert')}}
    </div>
  @endif

  <form action="{{route('admin.auth')}}" method="POST">
    @csrf

    <div class="form-group">
      <label>{{__('Username or Email')}}</label>
      <div class="login-v2-input">
        <i class="far fa-user"></i>
        <input type="text" name="username" placeholder="{{__('Enter your username or email')}}" autocomplete="username">
      </div>
      @if ($errors->has('username'))
        <p class="text-danger mb-0">{{$errors->first('username')}}</p>
      @endif
    </div>

    <div class="form-group">
      <label>{{__('Password')}}</label>
      <div class="login-v2-input">
        <i class="fas fa-lock"></i>
        <input type="password" name="password" id="loginPassword" placeholder="{{__('Enter your password')}}" autocomplete="current-password">
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
        <input type="checkbox" name="remember" value="1"> {{__('Remember me')}}
      </label>
      <a class="login-v2-forgot" href="{{route('admin.forget.form')}}">{{__('Forgot password?')}}</a>
    </div>

    <button type="submit" class="login-v2-submit"><i class="fas fa-lock"></i> {{__('SIGN IN')}}</button>
  </form>

  <div class="login-v2-or"><span>{{__('or')}}</span></div>

  <div class="login-v2-ssl"><i class="fas fa-shield-alt"></i> <span>{{__('Protected by 256-bit SSL encryption')}}<br>{{__('Your data is safe and secure')}}</span></div>
@endsection

@section('auth-footer')
  <div class="login-v2-support"><i class="fas fa-shield-alt"></i> {{__('Need help?')}} <a href="{{route('front.contact')}}">{{__('Contact Support')}}</a></div>
@endsection

@section('scripts')
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
@endsection
