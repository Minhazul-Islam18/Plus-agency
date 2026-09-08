<!-- Fonts and icons -->
<script src="{{asset('assets/admin/js/plugin/webfont/webfont.min.js')}}"></script>
<script>
  WebFont.load({
    google: {"families":["Lato:300,400,700,900"]},
    custom: {"families":["Flaticon", "Font Awesome 5 Solid", "Font Awesome 5 Regular", "Font Awesome 5 Brands", "simple-line-icons"], urls: ['{{asset('assets/admin/css/fonts.min.css')}}']},
    active: function() {
      sessionStorage.fonts = true;
    }
  });
</script>

<!-- CSS Files -->
<link href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" rel="stylesheet" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
<link rel="stylesheet" href="{{asset('assets/admin/css/fontawesome-iconpicker.min.css')}}">
<link rel="stylesheet" href="{{asset('assets/admin/css/dropzone.css')}}">
<link rel="stylesheet" href="{{asset('assets/admin/css/jquery.dm-uploader.min.css')}}">
<link rel="stylesheet" href="{{asset('assets/admin/css/bootstrap.min.css')}}">
<link rel="stylesheet" href="{{asset('assets/admin/css/bootstrap-tagsinput.css')}}">
<link rel="stylesheet" href="{{asset('assets/admin/css/bootstrap-datepicker.css')}}">
<link rel="stylesheet" href="{{asset('assets/admin/css/jquery.timepicker.min.css')}}">
<link rel="stylesheet" href="{{asset('assets/admin/css/select2.min.css')}}">
<link rel="stylesheet" href="{{asset('assets/admin/css/atlantis.min.css')}}">
<link rel="stylesheet" href="{{asset('assets/admin/css/custom.css')}}">
{{-- Loaded last, after the admin theme: every Summernote toolbar button is
     rendered as <button class="note-btn btn btn-light btn-sm">, so it always
     carries Bootstrap's generic .btn/.btn-light/.btn-sm classes alongside its
     own .note-btn. With this stylesheet loading before atlantis.min.css, the
     theme's generic .btn rules won every cascade tie and reskinned the
     toolbar buttons/icons out from under the editor. Loading it last (not
     touching any selector outside its own .note-* namespace, so nothing
     else in admin is affected) lets Summernote's own button/icon sizing win
     instead. --}}
<link rel="stylesheet" href="{{asset_v('assets/admin/css/summernote-bs4.css')}}">

@yield('styles')
