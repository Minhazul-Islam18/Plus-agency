@extends('admin.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">Mail From Admin</h4>
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
        <a href="#">Basic Settings</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">Email Settings</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">Mail From Admin</a>
      </li>
    </ul>
  </div>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <form action="{{route('admin.mailfromadmin.update')}}" method="post">
          @csrf
          <div class="card-header">
              <div class="row">
                  <div class="col-lg-12">
                      <div class="card-title">Mail From Admin</div>
                  </div>
              </div>
          </div>
          <div class="card-body pt-5 pb-5">
            <div class="row">
              <div class="col-lg-6 offset-lg-3">
                <div class="alert alert-warning text-center" role="alert">
                    <strong>This mail addres will be used to send all mails from this website.</strong>
                </div>
                @csrf
                <div class="form-group">
                  <label>SMTP Status **</label>
                  <div class="selectgroup w-100">
                    <label class="selectgroup-item">
                      <input type="radio" name="is_smtp" value="1" class="selectgroup-input" {{$abe->is_smtp == 1 ? 'checked' : ''}}>
                      <span class="selectgroup-button">Active</span>
                    </label>
                    <label class="selectgroup-item">
                      <input type="radio" name="is_smtp" value="0" class="selectgroup-input" {{$abe->is_smtp == 0 ? 'checked' : ''}}>
                      <span class="selectgroup-button">Deactive</span>
                    </label>
                  </div>
                  @if ($errors->has('is_smtp'))
                    <p class="mb-0 text-danger">{{$errors->first('is_smtp')}}</p>
                  @endif
                </div>
                <div class="form-group">
                    <label>SMTP Host **</label>
                    <input class="form-control" name="smtp_host" value="{{$abe->smtp_host}}">
                    @if ($errors->has('smtp_host'))
                        <p class="mb-0 text-danger">{{$errors->first('smtp_host')}}</p>
                    @endif
                </div>
                <div class="form-group">
                    <label>SMTP Port **</label>
                    <input class="form-control" name="smtp_port" value="{{$abe->smtp_port}}">
                    @if ($errors->has('smtp_port'))
                        <p class="mb-0 text-danger">{{$errors->first('smtp_port')}}</p>
                    @endif
                </div>
                <div class="form-group">
                    <label>Encryption **</label>
                    <input class="form-control" name="encryption" value="{{$abe->encryption}}">
                    @if ($errors->has('encryption'))
                        <p class="mb-0 text-danger">{{$errors->first('encryption')}}</p>
                    @endif
                </div>
                <div class="form-group">
                    <label>SMTP Username **</label>
                    <input class="form-control" name="smtp_username" value="{{$abe->smtp_username}}">
                    @if ($errors->has('smtp_username'))
                        <p class="mb-0 text-danger">{{$errors->first('smtp_username')}}</p>
                    @endif
                </div>
                <div class="form-group">
                    <label>SMTP Password **</label>
                    <input class="form-control" type="password" name="smtp_password" value="{{$abe->smtp_password}}">
                    @if ($errors->has('smtp_password'))
                        <p class="mb-0 text-danger">{{$errors->first('smtp_password')}}</p>
                    @endif
                </div>
                <div class="form-group">
                    <label>From Email **</label>
                    <input class="form-control" type="email" name="from_mail" value="{{$abe->from_mail}}">
                    @if ($errors->has('from_mail'))
                        <p class="mb-0 text-danger">{{$errors->first('from_mail')}}</p>
                    @endif
                </div>
                <div class="form-group">
                    <label>From Name **</label>
                    <input class="form-control" name="from_name" value="{{$abe->from_name}}">
                    @if ($errors->has('from_name'))
                        <p class="mb-0 text-danger">{{$errors->first('from_name')}}</p>
                    @endif
                </div>
                <hr>
                <div class="form-group">
                    <label>Send Test Email</label>
                    <div class="input-group">
                        <input type="email" class="form-control" id="smtpTestEmail" placeholder="you@example.com">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-primary" id="smtpTestBtn">Send Test</button>
                        </div>
                    </div>
                    <small class="text-muted">
                        Sends using the SMTP fields above as currently filled in (doesn't need to be
                        saved first). Using Resend? Set SMTP Host to <code>smtp.resend.com</code>,
                        Username to <code>resend</code>, Password to your Resend API key.
                    </small>
                </div>
              </div>
            </div>
          </div>
          <div class="card-footer">
            <div class="form">
              <div class="form-group from-show-notify row">
                <div class="col-12 text-center">
                  <button type="submit" class="btn btn-success">Update</button>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

@endsection

@section('scripts')
    <script>
        $(document).on('click', '#smtpTestBtn', function() {
            var $btn = $(this);
            var email = $('#smtpTestEmail').val();

            if (!email) {
                swal({
                    title: 'Enter an email',
                    text: 'Type an address to send the test email to.',
                    icon: 'warning',
                    button: 'OK'
                });
                return;
            }

            $btn.prop('disabled', true).text('Sending…');

            $.ajax({
                url: '{{ route('admin.mailfromadmin.test') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    test_email: email,
                    smtp_host: $('[name="smtp_host"]').val(),
                    smtp_port: $('[name="smtp_port"]').val(),
                    encryption: $('[name="encryption"]').val(),
                    smtp_username: $('[name="smtp_username"]').val(),
                    smtp_password: $('[name="smtp_password"]').val(),
                    from_mail: $('[name="from_mail"]').val(),
                    from_name: $('[name="from_name"]').val()
                },
                success: function(response) {
                    swal({
                        title: response.success ? 'Sent!' : 'Failed',
                        text: response.message,
                        icon: response.success ? 'success' : 'error',
                        button: 'OK'
                    });
                },
                error: function(xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) ||
                        'Test email failed to send.';
                    swal({
                        title: 'Failed',
                        text: msg,
                        icon: 'error',
                        button: 'OK'
                    });
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Send Test');
                }
            });
        });
    </script>
@endsection
