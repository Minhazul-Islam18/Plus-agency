@extends("front.$version.layout")

@section('pagename')
    - {{ __('Link Invalid or Expired') }}
@endsection

@section('breadcrumb-title', __('Secure File Recovery'))
@section('breadcrumb-subtitle', __('Download link error'))
@section('breadcrumb-link', __('Download'))

@section('content')
<section style="padding:70px 0 90px; background:#f4f6f9; min-height:65vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">

                <div style="background:#fff; border-radius:12px; box-shadow:0 4px 24px rgba(0,0,0,.08); padding:44px 40px; text-align:center;">

                    {{-- Red X icon --}}
                    <div style="width:60px; height:60px; background:#ef4444; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 22px;">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"/>
                            <line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </div>

                    <h2 style="font-size:22px; font-weight:700; color:#1a2a4a; margin-bottom:10px;">
                        {{ $errorTitle ?? __('This download link is no longer valid.') }}
                    </h2>

                    <p style="font-size:14px; color:#6b7280; line-height:1.7; margin-bottom:28px;">
                        {{ $errorMessage ?? __('The link may have expired (valid for 24 hours), already reached the download limit (:count attempts), or been revoked.', ['count' => $maxDownloads ?? 3]) }}
                    </p>

                    @unless (!empty($suspended))
                        <a href="{{ route('find_my_files') }}#method=expired_link"
                           style="display:inline-block; padding:12px 28px; background:#3b6cf8; color:#fff; border-radius:7px; font-size:14px; font-weight:600; text-decoration:none; margin-right:10px;">
                            {{ __('Regenerate My Link') }}
                        </a>
                    @endunless

                    <a href="{{ route('front.contact') }}"
                       style="display:inline-block; padding:12px 28px; border:1px solid #d1d5db; color:#374151; border-radius:7px; font-size:14px; font-weight:500; text-decoration:none;">
                        {{ __('Contact Support') }}
                    </a>

                </div>

            </div>
        </div>
    </div>
</section>
@endsection
