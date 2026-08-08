<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $rtl == 1 ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta content='width=device-width, initial-scale=1.0, shrink-to-fit=no' name='viewport' />
    <title>@yield('title', $bs->website_title)</title>
    <link rel="icon" href="{{ asset('assets/front/img/' . $bs->favicon) }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/bootstrap.min.css') }}">
    <link href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" rel="stylesheet" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/login.css') }}">
    @yield('styles')
</head>

<body class="login-v2-body">
    <div class="login-v2">
        <div class="login-v2-left"
            style="background-image: url('{{ asset('assets/front/img/' . ($aps->login_bg_image ?: 'admin-login-bg.png')) }}');">
            <div class="login-v2-left-inner">
                <img class="login-v2-logo" src="{{ asset('assets/front/img/' . ($aps->login_logo ?: $bs->logo)) }}"
                    alt="{{ $aps->platform_name ?: $bs->website_title }}">
                <h1>{{ $aps->platform_name ?: $bs->website_title }}</h1>
                <div class="login-v2-tagline">{{ $aps->tagline }}</div>
                <div class="login-v2-divider"></div>

                @php $features = $aps->features ?: []; @endphp
                <div class="login-v2-features-viewport">
                    <ul class="login-v2-features login-v2-features-scroll"
                        style="animation-duration: {{ max(count($features), 1) * 3.5 }}s;">
                        {{-- render twice back-to-back so the loop can wrap seamlessly at -50% --}}
                        @for ($pass = 0; $pass < 2; $pass++)
                            @foreach ($features as $feature)
                                <li>
                                    <span class="login-v2-feature-icon"><i
                                            class="{{ $feature['icon'] ?? 'fas fa-check' }}"></i></span>
                                    <span class="login-v2-feature-text">
                                        <span class="login-v2-feature-title">{{ $feature['title'] ?? '' }}</span>
                                        @if (!empty($feature['desc']))
                                            <span class="login-v2-feature-desc">{{ $feature['desc'] }}</span>
                                        @endif
                                    </span>
                                </li>
                            @endforeach
                        @endfor
                    </ul>
                </div>

                <div class="login-v2-copyright">
                    {{ $aps->copyright_text ?: html_entity_decode(strip_tags($bs->copyright_text)) ?: '© ' . date('Y') . ' ' . ($aps->platform_name ?: $bs->website_title) . '. ' . __('All rights reserved.') }}
                </div>
            </div>
        </div>

        <div class="login-v2-right">
            <div class="login-v2-card">
                @yield('auth-content')
            </div>

            @hasSection('auth-footer')
                @yield('auth-footer')
            @endif
        </div>
    </div>

    <!-- jquery js -->
    <script src="{{ asset('assets/front/js/jquery-3.3.1.min.js') }}"></script>
    <!-- popper js -->
    <script src="{{ asset('assets/front/js/popper.min.js') }}"></script>
    <!-- bootstrap js -->
    <script src="{{ asset('assets/front/js/bootstrap.min.js') }}"></script>
    <!-- Bootstrap Notify -->
    <script src="{{ asset('assets/admin/js/plugin/bootstrap-notify/bootstrap-notify.min.js') }}"></script>

    @yield('scripts')

    @if (session()->has('warning'))
        <script>
            var content = {};

            content.message = '{{ session('warning') }}';
            content.title = '{{ __('Sorry!') }}';
            content.icon = 'fa fa-bell';

            $.notify(content, {
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
