@extends("front.$version.layout")

@section('pagename')
    - {{ __('Service') }} - {{ convertUtf8($service->title) }}
@endsection

@section('meta-keywords', "$service->meta_keywords")
@section('meta-description', "$service->meta_description")

@section('breadcrumb-title', convertUtf8($bs->service_details_title))
@section('breadcrumb-subtitle', \Illuminate\Support\Str::limit(convertUtf8($service->title), 40))
@section('breadcrumb-link', __('Service Details'))

@if (!empty($bs->service_breadcrumb_bg))
    @section('breadcrumb-bg', asset('assets/front/img/' . $bs->service_breadcrumb_bg))
@endif

@if (!empty($bs->service_breadcrumb_overlay_color))
    @section('breadcrumb-overlay-color', $bs->service_breadcrumb_overlay_color)
@endif

@if (!empty($bs->service_breadcrumb_overlay_opacity))
    @section('breadcrumb-overlay-opacity', $bs->service_breadcrumb_overlay_opacity)
@endif

@section('content')

@if ($be->theme_version == 'dark')
    <!--    dark service details section start   -->
    <div class="dark-svcp-section">
        <div class="dark-svcp-inner @if ($service->sidebar != 1) dark-svcp-inner--full @endif">
            <div>
                <div class="dark-svcd-panel reveal-left">
                    <div class="dark-service-details">
                        {!! replaceBaseUrl(convertUtf8($service->content)) !!}
                    </div>
                </div>
            </div>
            @if ($service->sidebar == 1)
                <div>
                    <div class="dark-svcp-widget">
                        <form class="dark-svcp-search" action="{{ route('front.services') }}">
                            <input name="category" type="hidden" value="{{ request()->input('category') }}">
                            <input name="term" type="text" placeholder="{{ __('Search Services') }}"
                                value="{{ request()->input('term') }}">
                            <button type="submit"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><circle cx="11" cy="11" r="7" /><path d="M21 21l-4.3-4.3" /></svg></button>
                        </form>
                    </div>

                    @if (serviceCategory())
                        <div class="dark-svcp-widget">
                            <h4>{{ __('Categories') }}</h4>
                            <ul class="dark-svcp-cat-list">
                                @foreach ($scats as $key => $scat)
                                    <li class="{{ !empty($service->scategory) && $service->scategory->id == $scat->id ? 'is-active' : '' }}">
                                        <a href="{{ route('front.services', ['category' => $scat->id, 'term' => request()->input('term')]) }}">{{ convertUtf8($scat->name) }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="dark-svcp-widget dark-svcp-newsletter">
                        <span class="dark-bc-eyebrow">{{ __('SUBSCRIBE') }}</span>
                        <h4 class="dark-svcp-newsletter-title">{{ __('SUBSCRIBE FOR NEWSLETTER') }}</h4>
                        <form id="subscribeForm" class="dark-svcp-newsletter-form" action="{{ route('front.subscribe') }}"
                            method="POST">
                            @csrf
                            <input name="email" type="email" placeholder="{{ __('Email') }}">
                            <button type="submit">{{ __('Subscribe') }}</button>
                        </form>
                        <p id="erremail" class="text-danger mb-0 err-email"></p>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <!--    dark service details section end   -->
@else
    <!--    services details section start   -->
    <div class="pt-115 pb-110 service-details-section">
        <div class="container">
            <div class="row">
                <div class="{{ $service->sidebar == 1 ? 'col-lg-7' : 'col-12' }} reveal-left">
                    <div class="service-details">
                        {!! replaceBaseUrl(convertUtf8($service->content)) !!}
                    </div>
                </div>
                <!--    service sidebar start   -->
                @if ($service->sidebar == 1)
                    <div class="col-lg-4">
                        <div class="blog-sidebar-widgets">
                            <div class="searchbar-form-section">
                                <form action="{{ route('front.services') }}">
                                    <div class="searchbar">
                                        <input name="category" type="hidden" value="{{ request()->input('category') }}">
                                        <input name="term" type="text" placeholder="{{ __('Search Services') }}"
                                            value="{{ request()->input('term') }}">
                                        <button type="submit"><i class="fa fa-search"></i></button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @if (serviceCategory())
                            <div class="blog-sidebar-widgets category-widget">
                                <div class="category-lists job">
                                    <h4>{{ __('Categories') }}</h4>
                                    <ul>
                                        @foreach ($scats as $key => $scat)
                                            <li
                                                class="single-category {{ !empty($service->scategory) && $service->scategory->id == $scat->id ? 'active' : '' }}">
                                                <a
                                                    href="{{ route('front.services', ['category' => $scat->id, 'term' => request()->input('term')]) }}">{{ convertUtf8($scat->name) }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif
                        <div class="subscribe-section">
                            <span>{{ __('SUBSCRIBE') }}</span>
                            <h3>{{ __('SUBSCRIBE FOR NEWSLETTER') }}</h3>
                            <form id="subscribeForm" class="subscribe-form" action="{{ route('front.subscribe') }}"
                                method="POST">
                                @csrf
                                <div class="form-element"><input name="email" type="email"
                                        placeholder="{{ __('Email') }}"></div>
                                <p id="erremail" class="text-danger mb-3 err-email"></p>
                                <div class="form-element"><input type="submit" value="{{ __('Subscribe') }}"></div>
                            </form>
                        </div>
                    </div>
                @endif
                <!--    service sidebar end   -->
            </div>
        </div>
    </div>
    <!--    services details section end   -->
@endif

@endsection
