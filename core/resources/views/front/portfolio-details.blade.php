@extends("front.$version.layout")

@section('pagename')
    - {{ convertUtf8($portfolio->title) }}
@endsection

@section('meta-keywords', "$portfolio->meta_keywords")
@section('meta-description', "$portfolio->meta_description")

@section('breadcrumb-title', convertUtf8($bs->portfolio_details_title))
@section('breadcrumb-subtitle', \Illuminate\Support\Str::limit(convertUtf8($portfolio->title), 70))
@section('breadcrumb-link', __('Portfolio Details'))

@if (!empty($bs->portfolio_breadcrumb_bg))
    @section('breadcrumb-bg', asset('assets/front/img/' . $bs->portfolio_breadcrumb_bg))
@endif

@if (!empty($bs->portfolio_breadcrumb_overlay_color))
    @section('breadcrumb-overlay-color', $bs->portfolio_breadcrumb_overlay_color)
@endif

@if (!empty($bs->portfolio_breadcrumb_overlay_opacity))
    @section('breadcrumb-overlay-opacity', $bs->portfolio_breadcrumb_overlay_opacity)
@endif

@section('styles')
    <style>
        .project-infos {
            font-size: 15px;
        }

        .info-row {
            display: grid;
            grid-template-columns: 125px 12px 1fr;
            column-gap: 6px;
            margin-bottom: 8px;
            align-items: start;
        }

        .info-row .label {
            font-weight: 600;
            white-space: nowrap;
        }

        .info-row .colon {
            font-weight: 600;
            text-align: center;
        }

        .info-row .value {
            word-break: break-word;
        }

        @media (max-width: 576px) {
            .info-row {
                grid-template-columns: 1fr;
                row-gap: 2px;
            }

            .info-row .colon {
                display: none;
            }
        }
    </style>
@endsection
@section('content')

@if ($be->theme_version == 'dark')
    @php
        $pdCostFormatted = null;
        if (!empty($portfolio->cost_of_service)) {
            $decimalSeparator = app()->getLocale() == 'fr' ? ',' : '.';
            $thousandSeparator = app()->getLocale() == 'fr' ? ' ' : ',';
            $pdCostFormatted =
                floor($portfolio->cost_of_service) == $portfolio->cost_of_service
                    ? number_format($portfolio->cost_of_service, 0, $decimalSeparator, $thousandSeparator)
                    : number_format($portfolio->cost_of_service, 2, $decimalSeparator, $thousandSeparator);
        }
    @endphp
    <!--    dark portfolio details section start   -->
    <div class="dark-svcp-section">
        <div class="dark-svcp-inner">
            <div>
                @if ($portfolio->portfolio_images->count() > 0)
                    <div class="dark-pd-gallery">
                        <div class="dark-pd-gallery-main">
                            <img id="pdMainImg" class="lazy" data-src="{{ asset('assets/front/img/portfolios/sliders/' . $portfolio->portfolio_images->first()->image) }}" alt="">
                        </div>
                        @if ($portfolio->portfolio_images->count() > 1)
                            <div class="dark-pd-gallery-thumbs">
                                @foreach ($portfolio->portfolio_images as $key => $pi)
                                    <button type="button" class="{{ $key == 0 ? 'is-active' : '' }}" data-full="{{ asset('assets/front/img/portfolios/sliders/' . $pi->image) }}">
                                        <img class="lazy" data-src="{{ asset('assets/front/img/portfolios/sliders/' . $pi->image) }}" alt="">
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @elseif (!empty($portfolio->featured_image))
                    <div class="dark-pd-gallery">
                        <div class="dark-pd-gallery-main">
                            <img class="lazy" data-src="{{ asset('assets/front/img/portfolios/featured/' . $portfolio->featured_image) }}" alt="">
                        </div>
                    </div>
                @endif

                <div class="dark-pd-prose dark-service-details">
                    {!! replaceBaseUrl(convertUtf8($portfolio->content)) !!}
                </div>
            </div>

            <div>
                <h2 class="dark-pd-title">{{ convertUtf8($portfolio->title) }}</h2>

                <div class="dark-pd-spec">
                    @if (!empty($portfolio->client_name))
                        <div class="dark-pd-spec-row">
                            <span class="dark-pd-spec-label">{{ __('Client Name') }}</span>
                            <span class="dark-pd-spec-value">{{ convertUtf8($portfolio->client_name) }}</span>
                        </div>
                    @endif

                    @if (!empty($portfolio->service->title))
                        <div class="dark-pd-spec-row">
                            <span class="dark-pd-spec-label">{{ __('Service') }}</span>
                            <span class="dark-pd-spec-value">{{ convertUtf8($portfolio->service->title) }}</span>
                        </div>
                    @endif

                    @if ($portfolio->start_date)
                        @php $startDate = Carbon\Carbon::parse($portfolio->start_date); @endphp
                        <div class="dark-pd-spec-row">
                            <span class="dark-pd-spec-label">{{ __('Start Date') }}</span>
                            <span class="dark-pd-spec-value">{{ date_format($startDate, app()->getLocale() == 'fr' ? 'd-m-Y' : 'M d, Y') }}</span>
                        </div>
                    @endif

                    @if ($portfolio->submission_date)
                        @php $submissionDate = Carbon\Carbon::parse($portfolio->submission_date); @endphp
                        <div class="dark-pd-spec-row">
                            <span class="dark-pd-spec-label">{{ __('End Date') }}</span>
                            <span class="dark-pd-spec-value">{{ date_format($submissionDate, app()->getLocale() == 'fr' ? 'd-m-Y' : 'M d, Y') }}</span>
                        </div>
                    @endif

                    @if ($pdCostFormatted)
                        <div class="dark-pd-spec-row">
                            <span class="dark-pd-spec-label">{{ __('Cost of Service') }}</span>
                            <span class="dark-pd-spec-value">
                                @if ($bex?->base_currency_symbol_position == 'left')
                                    {{ $bex?->base_currency_symbol }} {{ $pdCostFormatted }}
                                @else
                                    {{ $pdCostFormatted }} {{ $bex?->base_currency_symbol }}
                                @endif
                            </span>
                        </div>
                    @endif

                    <div class="dark-pd-spec-row">
                        <span class="dark-pd-spec-label">{{ __('Status') }}</span>
                        <span class="dark-pd-spec-value dark-pd-status">{{ __(convertUtf8($portfolio->status)) }}</span>
                    </div>
                </div>

                @if ($portfolio->website_link)
                    <a href="{{ $portfolio->website_link }}" class="dark-pd-demo-btn" target="_blank">
                        {{ __('Live Demo') }}
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M7 17L17 7M7 7h10v10" /></svg>
                    </a>
                @endif

                <div class="dark-svcp-widget dark-svcp-newsletter">
                    <span class="dark-bc-eyebrow">{{ __('SUBSCRIBE') }}</span>
                    <h4 class="dark-svcp-newsletter-title">{{ __('SUBSCRIBE FOR NEWSLETTER') }}</h4>
                    <form id="subscribeForm" class="dark-svcp-newsletter-form" action="{{ route('front.subscribe') }}" method="POST">
                        @csrf
                        <input name="email" type="email" placeholder="{{ __('Email') }}">
                        <button type="submit">{{ __('Subscribe') }}</button>
                    </form>
                    <p id="erremail" class="text-danger mb-0 err-email"></p>
                </div>
            </div>
        </div>
    </div>
    <!--    dark portfolio details section end   -->
@else
    <!--    case details section start   -->
    <div class="case-details-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-7 col-xl-7">
                    <div class="project-ss-carousel owl-carousel owl-theme common-carousel">
                        @foreach ($portfolio->portfolio_images as $key => $pi)
                            <a href="#" class="single-ss" data-id="{{ $pi->id }}">
                                <img class="lazy"
                                    data-src="{{ asset('assets/front/img/portfolios/sliders/' . $pi->image) }}"
                                    alt="">
                            </a>
                        @endforeach
                    </div>
                    @foreach ($portfolio->portfolio_images as $key => $pi)
                        <a id="singleMagnificSs{{ $pi->id }}" class="single-magnific-ss d-none"
                            href="{{ asset('assets/front/img/portfolios/sliders/' . $pi->image) }}"></a>
                    @endforeach
                    <div class="case-details">
                        {!! replaceBaseUrl(convertUtf8($portfolio->content)) !!}
                    </div>
                </div>
                <!--    appoint section start   -->
                <div class="col-lg-5 offset-xl-1 col-xl-4">
                    <div class="right-side">
                        <div class="row">
                            <div class="col-xl-12 col-lg-12 col-md-12">
                                <div class="project-infos">
                                    <h3>{{ convertUtf8($portfolio->title) }}</h3>

                                    <div class="info-row">
                                        <div class="label">{{ __('Client Name') }}</div>
                                        <div class="colon">:</div>
                                        <div class="value">{{ convertUtf8($portfolio->client_name) }}</div>
                                    </div>

                                    @if (!empty($portfolio->service->title))
                                        <div class="info-row">
                                            <div class="label">{{ __('Service') }}</div>
                                            <div class="colon">:</div>
                                            <div class="value">{{ convertUtf8($portfolio->service->title) }}</div>
                                        </div>
                                    @endif

                                    @if ($portfolio->start_date)
                                        @php $startDate = Carbon\Carbon::parse($portfolio->start_date); @endphp
                                        <div class="info-row">
                                            <div class="label">{{ __('Start Date') }}</div>
                                            <div class="colon">:</div>
                                            <div class="value">
                                                {{ date_format($startDate, app()->getLocale() == 'fr' ? 'd-m-Y' : 'M d, Y') }}
                                            </div>
                                        </div>
                                    @endif

                                    @if ($portfolio->submission_date)
                                        @php $submissionDate = Carbon\Carbon::parse($portfolio->submission_date); @endphp
                                        <div class="info-row">
                                            <div class="label">{{ __('End Date') }}</div>
                                            <div class="colon">:</div>
                                            <div class="value">
                                                {{ date_format($submissionDate, app()->getLocale() == 'fr' ? 'd-m-Y' : 'M d, Y') }}
                                            </div>
                                        </div>
                                    @endif

                                    @if (!empty($portfolio->cost_of_service))
                                        <div class="info-row">
                                            <div class="label">{{ __('Cost of Service') }}</div>
                                            <div class="colon">:</div>
                                            <div class="value">
                                                @php
                                                    $decimalSeparator = app()->getLocale() == 'fr' ? ',' : '.';
                                                    $thousandSeparator = app()->getLocale() == 'fr' ? ' ' : ',';

                                                    // Determine if the amount has decimals
                                                    if (
                                                        floor($portfolio->cost_of_service) ==
                                                        $portfolio->cost_of_service
                                                    ) {
                                                        // Whole number, no decimals
                                                        $formattedAmount = number_format(
                                                            $portfolio->cost_of_service,
                                                            0,
                                                            $decimalSeparator,
                                                            $thousandSeparator,
                                                        );
                                                    } else {
                                                        // Has decimals
                                                        $formattedAmount = number_format(
                                                            $portfolio->cost_of_service,
                                                            2,
                                                            $decimalSeparator,
                                                            $thousandSeparator,
                                                        );
                                                    }
                                                @endphp

                                                @if ($bex?->base_currency_symbol_position == 'left')
                                                    {{ $bex?->base_currency_symbol }} {{ $formattedAmount }}
                                                @else
                                                    {{ $formattedAmount }} {{ $bex?->base_currency_symbol }}
                                                @endif
                                            </div>
                                        </div>
                                    @endif


                                    <div class="info-row">
                                        <div class="label">{{ __('Status') }}</div>
                                        <div class="colon">:</div>
                                        <div class="value">{{ __(convertUtf8($portfolio->status)) }}</div>
                                    </div>

                                    @if ($portfolio->website_link)
                                        <div class="mt-2">
                                            <a href="{{ $portfolio->website_link }}" class="btn base-bg text-white btn-sm"
                                                target="_blank">
                                                {{ __('Live Demo') }}
                                            </a>
                                        </div>
                                    @endif
                                </div>

                                <div class="subscribe-section">
                                    <span>{{ __('SUBSCRIBE') }}</span>
                                    <h3>{{ __('SUBSCRIBE FOR NEWSLETTER') }}</h3>
                                    <form id="subscribeForm" class="subscribe-form" action="{{ route('front.subscribe') }}"
                                        method="POST">
                                        @csrf
                                        <div class="form-element"><input name="email" type="email"
                                                placeholder="{{ __('Email') }}"></div>
                                        <p id="erremail" class="text-danger mb-3 err-email"></p>
                                        <div class="form-element"><input type="submit" value="{{ __('Subscribe') }}">
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--    appoint section end   -->
            </div>
        </div>
    </div>
    <!--    case details section end   -->
@endif

@endsection

@section('scripts')
    @if ($be->theme_version == 'dark')
        <script>
            document.querySelectorAll('.dark-pd-gallery-thumbs button').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    document.querySelectorAll('.dark-pd-gallery-thumbs button').forEach(function (b) { b.classList.remove('is-active'); });
                    btn.classList.add('is-active');
                    var full = btn.getAttribute('data-full');
                    var mainImg = document.getElementById('pdMainImg');
                    mainImg.classList.remove('lazy');
                    mainImg.removeAttribute('data-src');
                    mainImg.src = full;
                });
            });
        </script>
    @endif
@endsection
