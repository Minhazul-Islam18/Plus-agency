<section class="services-area dark-services-section dark-section-seam pb-130"
    style="@if (!empty($be->service_section_bg)) background-image: url('{{ asset('assets/front/img/' . $be->service_section_bg) }}');
        background-size: cover;
        background-position: center;
        position: relative;
        overflow: hidden; @endif">
    @if (!empty($be->service_section_bg))
        <div class="service-overlay"
            style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: #{{ $be->service_overlay_color ?? '000000' }}; opacity: {{ $be->service_overlay_opacity ?? '0.6' }}; z-index: 0; pointer-events: none;">
        </div>
    @endif
    <div class="container" style="position: relative; z-index: 2;">
        <div class="row text-center">
            <div class="col-lg-6 offset-lg-3 reveal-text">
                <span class="section-eyebrow">{{ convertUtf8($bs->service_section_title) }}</span>
                <h2 class="gradient-shine-heading">{{ convertUtf8($bs->service_section_subtitle) }}</h2>
            </div>
        </div>
    </div>
    <div class="container" style="position: relative; z-index: 2;">
        <div class="row justify-content-center">
            @foreach ($services as $key => $service)
                <div class="col-lg-4 col-md-6 col-sm-8">
                    <div class="glass-panel dark-services-item services-item mt-30 reveal-card" style="--d:{{ ($key % 3) * 0.1 }}s">
                        <div class="services-thumb">
                            <img class="lazy" data-src="{{ asset('assets/front/img/services/' . $service->main_image) }}" alt="service" />
                        </div>
                        <div class="services-content">
                            <a class="title"
                                @if ($service->details_page_status == 1) href="{{ route('front.servicedetails', $service->slug) }}" @endif>
                                <h4>{{ $service->title }}</h4>
                            </a>

                            <p>
                                @if (strlen($service->summary) > 120)
                                    {{ mb_substr($service->summary, 0, 120, 'utf-8') }}<span
                                        style="display: none;">{{ mb_substr($service->summary, 120, null, 'utf-8') }}</span>
                                    <a href="#" class="see-more">{{ __('see more') }}...</a>
                                @else
                                    {{ $service->summary }}
                                @endif
                            </p>

                            @if ($service->details_page_status == 1)
                                <a class="glass-panel-link" href="{{ route('front.servicedetails', $service->slug) }}">{{ __('Read More') }}
                                    <i class="fas fa-plus"></i></a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
