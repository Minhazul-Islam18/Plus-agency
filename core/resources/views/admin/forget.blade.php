@extends('admin.auth-layout')

@section('auth-content')
  @if (session()->has('reset_sent_email'))
    @php
      $sentEmail = session('reset_sent_email');
      $atPos = strpos($sentEmail, '@');
      $maskedEmail = $atPos !== false
          ? substr($sentEmail, 0, min(2, $atPos)) . str_repeat('*', max(1, $atPos - 2)) . substr($sentEmail, $atPos)
          : $sentEmail;
    @endphp

    <div class="text-center mb-4">
      <img class="login-v2-card-logo" src="{{asset('assets/front/img/' . ($aps->login_logo ?: $bs->logo))}}" alt="">
      <div class="login-v2-success-icon"><i class="fas fa-check"></i></div>
      <h2>{{__('Temporary Password Sent!')}}</h2>
      <p class="login-v2-subtext">{{__('A temporary password has been sent to')}}<br><strong>{{$maskedEmail}}</strong></p>
    </div>

    <div class="login-v2-tip">
      <i class="far fa-envelope"></i>
      <span>{{__('Please check your inbox and your Spam/Junk folder if you do not receive the email within a few minutes.')}}</span>
    </div>

    <p class="login-v2-instructions">{{__('Once you receive the temporary password, return to the login page to sign in and complete the password change process.')}}</p>

    <a href="{{route('admin.login')}}" class="login-v2-submit-outline"><i class="fas fa-arrow-left"></i> {{__('BACK TO LOGIN')}}</a>

    <div class="login-v2-resend">
      <span>{{__("Didn't receive the email?")}}</span>
      <form action="{{route('admin.forget.mail')}}" method="POST">
        @csrf
        <input type="hidden" name="email" value="{{$sentEmail}}">
        <button type="submit" id="resendBtn" class="login-v2-resend-btn" disabled>
          <i class="fas fa-redo"></i> {{__('Resend Email')}} <span id="resendCountdown">(60s)</span>
        </button>
      </form>
    </div>
  @else
    <div class="text-center mb-4">
      <img class="login-v2-card-logo" src="{{asset('assets/front/img/' . ($aps->login_logo ?: $bs->logo))}}" alt="">
      <h2>{{__('Forgot Password?')}}</h2>
      <p class="login-v2-subtext">{{__("Enter your email and we'll send you a temporary password")}}</p>
    </div>

    <form action="{{route('admin.forget.mail')}}" method="POST">
      @csrf

      <div class="form-group">
        <label>{{__('Email')}}</label>
        <div class="login-v2-input">
          <i class="far fa-envelope"></i>
          <input type="email" name="email" placeholder="{{__('Enter your email address')}}" autocomplete="email">
        </div>
        @if ($errors->has('email'))
          <p class="text-danger mb-0">{{$errors->first('email')}}</p>
        @endif
      </div>

      <button type="submit" class="login-v2-submit"><i class="fas fa-paper-plane"></i> {{__('SEND TEMPORARY PASSWORD')}}</button>
    </form>

    <div class="login-v2-row" style="justify-content: center; margin-top: 22px; margin-bottom: 0;">
      <a class="login-v2-forgot" href="{{route('admin.login')}}">&laquo; {{__('Back to login')}}</a>
    </div>
  @endif
@endsection

@section('auth-footer')
  <div class="login-v2-support"><i class="fas fa-shield-alt"></i> {{__('Need help?')}} <a href="{{route('front.contact')}}">{{__('Contact Support')}}</a></div>
@endsection

@if (session()->has('reset_sent_email'))
@section('scripts')
<script>
  (function () {
    var seconds = 60;
    var btn = document.getElementById('resendBtn');
    var countdown = document.getElementById('resendCountdown');
    if (!btn || !countdown) return;

    var timer = setInterval(function () {
      seconds--;
      if (seconds <= 0) {
        clearInterval(timer);
        btn.disabled = false;
        countdown.textContent = '';
      } else {
        countdown.textContent = '(' + seconds + 's)';
      }
    }, 1000);
  })();
</script>
@endsection
@endif
