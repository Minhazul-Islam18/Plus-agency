{{-- The admin-set CTA background image renders behind the glass panel —
     the panel's translucent tint + backdrop-blur turns it into a soft
     frosted-glass backdrop rather than hiding it outright. Overlay color/
     opacity reuse the same light-theme fields so admin only manages one
     image/overlay pair for both themes. --}}
<div class="cta-section dark-cta-section dark-section-seam"
    style="@if (!empty($bs->cta_bg)) background-image: url('{{ asset('assets/front/img/' . $bs->cta_bg) }}'); background-size: cover; background-position: center; @endif position: relative; overflow: hidden;">
    @if (!empty($bs->cta_bg))
        <div style="position: absolute; inset: 0; z-index: 0; pointer-events: none; background-color: #{{ $be->cta_overlay_color ?? '000000' }}; opacity: {{ $be->cta_overlay_opacity ?? '0.6' }};"></div>
    @endif
    <div class="container" style="position: relative; z-index: 2;">
        <div class="glass-panel dark-cta-content reveal-cta">
            <span class="dark-cta-grid-texture"></span>
            <span class="dark-cta-mesh"></span>

            <div class="dark-cta-left">
                @if (!empty($bs->cta_section_badge))
                    <span class="section-eyebrow">{{ convertUtf8($bs->cta_section_badge) }}</span>
                @endif
                <div class="dark-cta-text">
                    <h3>{{ convertUtf8($bs->cta_section_text) }}</h3>
                </div>
            </div>

            @if (!empty($bs->cta_section_button_url) && !empty($bs->cta_section_button_text))
                <span class="dark-cta-divider"></span>

                <div class="dark-cta-right">
                    <a href="{{ $bs->cta_section_button_url }}" class="dark-cta-btn" target="_blank">
                        <span>{{ convertUtf8($bs->cta_section_button_text) }}</span>
                        <i><svg viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg></i>
                    </a>
                    @php
                        $ctaPhone = !empty($bse->contact_numbers ?? null) ? trim(explode(',', $bse->contact_numbers)[0]) : null;
                        $ctaMail = !empty($bse->contact_mails ?? null) ? trim(explode(',', $bse->contact_mails)[0]) : null;
                    @endphp
                    @if ($ctaPhone || $ctaMail)
                        <div class="dark-cta-meta">
                            @if ($ctaPhone)
                                <a href="tel:{{ preg_replace('/\s+/', '', $ctaPhone) }}">{{ $ctaPhone }}</a>
                            @endif
                            @if ($ctaPhone && $ctaMail)
                                <span class="dot"></span>
                            @endif
                            @if ($ctaMail)
                                <a href="mailto:{{ $ctaMail }}">{{ $ctaMail }}</a>
                            @endif
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
