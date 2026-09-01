<div class="testimonial-section dark-testimonial-section dark-section-seam pb-115"
    style="@if (!empty($be->testimonial_section_bg)) background-image: url('{{ asset('assets/front/img/' . $be->testimonial_section_bg) }}');
        background-size: cover;
        background-position: center;
        position: relative;
        overflow: hidden; @endif">
    @if (!empty($be->testimonial_section_bg))
        <div class="testimonial-overlay"
            style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: #{{ $be->testimonial_overlay_color ?? '000000' }}; opacity: {{ $be->testimonial_overlay_opacity ?? '0.6' }}; z-index: 0; pointer-events: none;">
        </div>
    @endif
    <div class="container" style="position: relative; z-index: 2;">
        <div class="dark-testi-head reveal-text" style="--d:.05s">
            <div class="dark-testi-head-text">
                <span class="section-eyebrow">{{ convertUtf8($bs->testimonial_title) }}</span>
                <h2 class="gradient-shine-heading">{{ convertUtf8($bs->testimonial_subtitle) }}</h2>
            </div>
            @if ($testimonials->count() > 1)
                <div class="dark-testi-nav">
                    <button type="button" id="darkTestiPrev" aria-label="{{ __('Previous') }}"><svg viewBox="0 0 24 24"><path d="M15 6l-6 6 6 6"/></svg></button>
                    <button type="button" id="darkTestiNext" aria-label="{{ __('Next') }}"><svg viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></svg></button>
                </div>
            @endif
        </div>

        <div class="dark-testi-carousel dark-glass-carousel owl-carousel owl-theme">
            @foreach ($testimonials as $key => $testimonial)
                <div class="dark-testimonial-card reveal-card" style="--d:{{ ($key % 3) * 0.1 }}s">
                    <div class="img-wrapper">
                        <img src="{{ asset('assets/front/img/testimonials/' . $testimonial->image) }}" alt="">
                    </div>
                    <div class="client-desc">
                        @if (!empty($testimonial->company_logo))
                            @php
                                $logoImg = '<img src="' . asset('assets/front/img/testimonials/' . $testimonial->company_logo) . '" alt="' . e(convertUtf8($testimonial->name)) . '">';
                            @endphp
                            @if (!empty($testimonial->company_url))
                                <a href="{{ $testimonial->company_url }}" target="_blank" rel="noopener noreferrer nofollow"
                                    class="testi-company-logo" aria-label="{{ __('Visit company website') }}">
                                    {!! $logoImg !!}
                                </a>
                            @else
                                <div class="testi-company-logo testi-company-logo-static">
                                    {!! $logoImg !!}
                                </div>
                            @endif
                        @endif
                        <div class="dark-testimonial-quote-icon">{{ __('Testimonial') }} <b>{{ sprintf('N°%02d', $key + 1) }}</b></div>
                        <h6 class="name">{{ convertUtf8($testimonial->name) }}</h6>
                        <p class="rank">{{ convertUtf8($testimonial->rank) }}</p>
                        <p class="comment">
                            @php $testiComment = convertUtf8($testimonial->comment); @endphp
                            @if (mb_strlen($testiComment, 'UTF-8') > 150)
                                <span class="testi-comment-short">{{ mb_substr($testiComment, 0, 150, 'UTF-8') }}…</span><span
                                    class="testi-comment-full" style="display:none;">{{ $testiComment }}</span>
                                <span class="testi-comment-toggle">{{ __('Show more') }}</span>
                            @else
                                {{ $testiComment }}
                            @endif
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
