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
                                            <div class="value">{{ date_format($startDate, 'M d, Y') }}</div>
                                        </div>
                                    @endif

                                    @if ($portfolio->submission_date)
                                        @php $submissionDate = Carbon\Carbon::parse($portfolio->submission_date); @endphp
                                        <div class="info-row">
                                            <div class="label">{{ __('End Date') }}</div>
                                            <div class="colon">:</div>
                                            <div class="value">{{ date_format($submissionDate, 'M d, Y') }}</div>
                                        </div>
                                    @endif

                                    @if (!empty($portfolio->cost_of_service))
                                        <div class="info-row">
                                            <div class="label">{{ __('Cost of Service') }}</div>
                                            <div class="colon">:</div>
                                            <div class="value">
                                                @php
                                                    $decimalSeparator = app()->getLocale() == 'fr' ? ',' : '.';
                                                    $thousandSeparator = app()->getLocale() == 'fr' ? ' ' : ',';

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
                                        <div class="value">{{ convertUtf8($portfolio->status) }}</div>
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

@endsection
