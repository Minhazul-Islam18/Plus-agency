@php
    $overlayColor = !empty($portfolio->overlay_color) ? $portfolio->overlay_color : '060a09';
    $overlayOpacity = !empty($portfolio->overlay_opacity) ? ((int) $portfolio->overlay_opacity) / 100 : 0.82;
    // Left "bloom" behind the copy has its own admin slider — falls back
    // to the main overlay opacity when unset (older rows / not touched).
    $bloomOpacity = !empty($portfolio->overlay_bloom_opacity)
        ? ((int) $portfolio->overlay_bloom_opacity) / 100
        : $overlayOpacity;
    // hex -> "r,g,b" so dark-glass.css can build rgba() with a separate,
    // independently-controllable alpha (see --pd-hero-overlay-color below).
    $ohex = ltrim($overlayColor, '#');
    $orgb =
        strlen($ohex) === 6
            ? implode(',', [hexdec(substr($ohex, 0, 2)), hexdec(substr($ohex, 2, 2)), hexdec(substr($ohex, 4, 2))])
            : '6,10,9';
@endphp
<div class="dark-pd-hero-overlay"
    style="--pd-hero-overlay-color: {{ $orgb }}; --pd-hero-overlay-strength: {{ $overlayOpacity }}; --pd-hero-bloom-strength: {{ $bloomOpacity }};">
    @if (!empty($portfolio->sector))
        <span class="dark-pd-hero-badge" data-reveal>{{ convertUtf8($portfolio->sector->name) }}@if (!empty($portfolio->subsector)) &rsaquo; {{ convertUtf8($portfolio->subsector->name) }}@endif</span>
    @endif
    <h2 class="dark-pd-hero-title" data-reveal>{{ convertUtf8($portfolio->overlayTitle()) }}</h2>
    @if (!empty($portfolio->overlay_subtitle))
        <p class="dark-pd-hero-subtitle" data-reveal>{{ convertUtf8($portfolio->overlay_subtitle) }}</p>
    @endif
    @if (!empty($portfolio->overlayDescription()))
        <p class="dark-pd-hero-desc" data-reveal>{{ convertUtf8($portfolio->overlayDescription()) }}</p>
    @endif
</div>

@if ($portfolio->highlights->count() > 0)
    <div class="dark-pd-hero-points"
        style="--pd-hero-overlay-color: {{ $orgb }}; --pd-hero-overlay-strength: {{ $overlayOpacity }};">
        @foreach ($portfolio->highlights as $highlight)
            <div class="dark-pd-hero-point" data-reveal>
                <span class="dark-pd-hero-point-icon"><i class="{{ $highlight->icon ?: 'fas fa-star' }}"></i></span>
                <span class="dark-pd-hero-point-label">{{ convertUtf8($highlight->label) }}</span>
            </div>
        @endforeach
    </div>
@endif
