@extends('admin.auth-layout')

@section('auth-content')
  <div class="text-center mb-4">
    <img class="login-v2-card-logo" src="{{asset('assets/front/img/' . ($aps->login_logo ?: $bs->logo))}}" alt="">
    <h2>{{__('Create a New Password')}}</h2>
    <p class="login-v2-subtext">{{__("You're signing in with a temporary password. Please set a new password to continue.")}}</p>
  </div>

  <form action="{{route('admin.forcedChangePassword.update')}}" method="POST">
    @csrf

    <div class="form-group">
      <label>{{__('New Password')}}</label>
      <div class="login-v2-input">
        <i class="fas fa-lock"></i>
        <input type="password" name="password" id="newPassword" placeholder="{{__('Enter your new password')}}" autocomplete="new-password">
        <button type="button" class="login-v2-eye-toggle" id="newPasswordToggle" tabindex="-1">
          <i class="far fa-eye"></i>
        </button>
      </div>
      @if ($errors->has('password'))
        <p class="text-danger mb-0">{{$errors->first('password')}}</p>
      @endif
    </div>

    <div class="form-group">
      <label>{{__('Confirm Password')}}</label>
      <div class="login-v2-input">
        <i class="fas fa-lock"></i>
        <input type="password" name="password_confirmation" id="confirmPassword" placeholder="{{__('Re-enter your new password')}}" autocomplete="new-password">
        <button type="button" class="login-v2-eye-toggle" id="confirmPasswordToggle" tabindex="-1">
          <i class="far fa-eye"></i>
        </button>
      </div>
    </div>

    <button type="submit" class="login-v2-submit"><i class="fas fa-key"></i> {{__('CREATE PASSWORD')}}</button>
  </form>

  <div class="login-v2-ssl"><i class="fas fa-shield-alt"></i> <span>{{__('Protected by 256-bit SSL encryption')}}<br>{{__('Your data is safe and secure')}}</span></div>
@endsection

@section('auth-footer')
  <div class="login-v2-support"><i class="fas fa-shield-alt"></i> {{__('Need help?')}} <a href="{{route('front.contact')}}">{{__('Contact Support')}}</a></div>
@endsection

@section('scripts')
<script>
  (function () {
    function wireToggle(toggleId, inputId) {
      var toggle = document.getElementById(toggleId);
      var input = document.getElementById(inputId);
      if (!toggle || !input) return;
      toggle.addEventListener('click', function () {
        var isHidden = input.getAttribute('type') === 'password';
        input.setAttribute('type', isHidden ? 'text' : 'password');
        toggle.querySelector('i').className = isHidden ? 'far fa-eye-slash' : 'far fa-eye';
      });
    }
    wireToggle('newPasswordToggle', 'newPassword');
    wireToggle('confirmPasswordToggle', 'confirmPassword');
  })();
</script>
@endsection
