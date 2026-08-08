<div class="intro-section dark-intro-section dark-section-seam">
    <div class="intro-section-backdrop"
        @if (!empty($be->intro_section_bg)) style="background-image: url('{{ asset('assets/front/img/' . $be->intro_section_bg) }}');" @endif>
        @if (!empty($be->intro_section_bg))
            <div class="intro-section-overlay"
                style="background-color: #{{ $be->intro_overlay_color ?? '000000' }}; opacity: {{ $be->intro_overlay_opacity ?? '0.6' }};">
            </div>
        @endif
    </div>

    <div class="container" style="position: relative; z-index: 2;">
        @if ($bs->feature_section == 1)
            <div class="hero-features dark-feature-ledger-wrap">
                @if (!empty($bs->feature_section_subtitle))
                    <div class="text-center dark-feature-head">
                        @if (!empty($bs->feature_section_title))
                            <span class="section-eyebrow">{{ convertUtf8($bs->feature_section_title) }}</span>
                        @endif
                        <h2 class="gradient-shine-heading">{{ convertUtf8($bs->feature_section_subtitle) }}</h2>
                    </div>
                @endif

                <div class="glass-panel dark-feature-ledger">
                    @foreach ($features as $key => $feature)
                        <div class="dark-feature-cell reveal-stagger" style="--cell-color:#{{ $feature->color }}; --d:{{ $key * 0.1 }}s">
                            <span class="dark-feature-cell-index">{{ sprintf('%02d', $key + 1) }}</span>
                            <span class="dark-feature-cell-icon" style="background: linear-gradient(155deg, #{{ $feature->color }}, var(--accent-2));">
                                <i class="{{ $feature->icon }}"></i>
                            </span>
                            <h3>{{ convertUtf8($feature->title) }}</h3>
                            <span class="dark-feature-cell-line"></span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($bs->intro_section == 1)
            <div class="dark-intro-wrap">
                <span class="dark-intro-mesh"></span>
                <div class="reveal-stagger" style="--d:.05s">
                    @if (!empty($bs->intro_section_title))
                        <span class="dark-intro-eyebrow">{{ convertUtf8($bs->intro_section_title) }}</span>
                    @endif
                    <p class="dark-intro-statement">{{ convertUtf8($bs->intro_section_text) }}</p>
                    <span class="dark-intro-rule"></span>
                    @if (!empty($bs->intro_section_button_url) && !empty($bs->intro_section_button_text))
                        <a href="{{ $bs->intro_section_button_url }}" class="dark-intro-cta" target="_blank">
                            <span>{{ convertUtf8($bs->intro_section_button_text) }}</span><i>&#8594;</i>
                        </a>
                    @endif
                </div>

                <div class="dark-intro-media reveal-stagger" style="--d:.2s">
                    <div class="dark-intro-media-frame">
                        @if (!empty($bs->intro_bg))
                            <img class="dark-intro-media-thumb" src="{{ asset('assets/front/img/' . $bs->intro_bg) }}" alt="">
                        @endif
                        <span class="dark-intro-media-scrim"></span>
                        <span class="dark-intro-media-glow"></span>
                        @if (!empty($bs->intro_section_video_link))
                            <div class="dark-intro-play-btn" id="darkIntroPlayBtn" data-youtube-url="{{ $bs->intro_section_video_link }}" role="button" tabindex="0" aria-label="Play video">
                                <svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            </div>
                            <div class="dark-intro-media-tag"><span class="dot"></span> {{ !empty($bs->intro_section_video_text) ? convertUtf8($bs->intro_section_video_text) : __('Watch our story') }}</div>
                        @endif
                    </div>
                </div>
            </div>

            @if (!empty($bs->intro_section_video_link))
                <div class="dark-video-lightbox" id="darkIntroVideoLightbox">
                    <div class="dark-video-lightbox-inner">
                        <span class="dark-video-lightbox-close" id="darkIntroVideoLightboxClose">&times;</span>
                        <div id="darkIntroVideoLightboxFrame"></div>
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>
