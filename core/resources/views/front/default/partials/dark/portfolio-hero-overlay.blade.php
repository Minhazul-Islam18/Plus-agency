{{--
    Sector badge + "on behalf of {client}" line, overlaid on the hero image
    with a bottom gradient scrim. Title/subtitle used to render here too but
    were dropped per feedback (busy over the photo, and the real page title
    already renders as the page's own <h1> in the breadcrumb header above —
    no info lost). Included inside .dark-pd-gallery-main (position:relative)
    by portfolio-details.blade.php, once per branch (multi-image gallery /
    single featured-image fallback).
--}}
<div class="dark-pd-hero-overlay">
    @if (!empty($portfolio->sector))
        <span class="dark-pd-hero-badge">{{ convertUtf8($portfolio->sector->name) }}</span>
    @endif
    @if (!empty($portfolio->client_name))
        <div class="dark-pd-hero-text">
            <p class="dark-pd-hero-client">{{ __('On behalf of') }} {{ convertUtf8($portfolio->client_name) }}</p>
        </div>
    @endif
</div>
