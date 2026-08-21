<div class="approach-section dark-approach-section dark-section-seam"
    style="@if (!empty($be->approach_section_bg)) background-image: url('{{ asset('assets/front/img/' . $be->approach_section_bg) }}');
        background-size: cover;
        background-position: center;
        position: relative;
        overflow: hidden; @endif">
    @if (!empty($be->approach_section_bg))
        <div class="approach-overlay"
            style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: #{{ $be->approach_overlay_color ?? '000000' }}; opacity: {{ $be->approach_overlay_opacity ?? '0.6' }}; z-index: 0; pointer-events: none;">
        </div>
    @endif
    <div class="container" style="position: relative; z-index: 2;">
        <div class="row align-items-center">
            <div class="col-lg-5 dark-approach-lead-col reveal-text" style="--d:.05s">
                <span class="section-eyebrow">{{ convertUtf8($bs->approach_title) }}</span>
                <h2 class="gradient-shine-heading">{{ convertUtf8($bs->approach_subtitle) }}</h2>
                @if (!empty($bs->approach_button_url) && !empty($bs->approach_button_text))
                    <a href="{{ $bs->approach_button_url }}" class="dark-intro-cta" target="_blank">
                        <span>{{ convertUtf8($bs->approach_button_text) }}</span><i>&#8594;</i>
                    </a>
                @endif
            </div>
            <div class="col-lg-7">
                <ol class="dark-approach-lists reveal-timeline">
                    @foreach ($points as $key => $point)
                        <li class="dark-approach-item reveal-timeline-item @if ($key == 0) is-active @endif" style="--d:{{ $key * 0.15 }}s" data-approach-step>
                            <div class="dark-approach-rail">
                                <span class="dark-approach-icon">
                                    <i class="{{ $point->icon }}"></i>
                                    <span class="dark-approach-num">{{ sprintf('%02d', $key + 1) }}</span>
                                </span>
                                @if (!$loop->last)
                                    <span class="dark-approach-line"></span>
                                @endif
                            </div>
                            <div class="dark-approach-text">
                                <h4>{{ convertUtf8($point->title) }}</h4>
                                <p>{{ convertUtf8($point->short_text) }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </div>
</div>
