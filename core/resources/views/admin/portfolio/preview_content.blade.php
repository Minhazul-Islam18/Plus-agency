{{--
    Hero (image + sector badge + title) + the shared identity-card partial —
    composed once here for the two modal contexts (Aperçu preview, read-only
    eye view) that both need this exact shape. The real frontend page has
    its own richer hero (carousel + lightbox, built separately) and includes
    the identity-card partial directly instead of this wrapper.

    Expects the same variables as portfolio-identity-card.blade.php, plus
    $heroImageUrl (nullable).
--}}
@if (!empty($heroImageUrl))
    <div class="dark-pic-preview-hero">
        <img class="dark-pic-preview-hero-img" src="{{ $heroImageUrl }}" alt="{{ convertUtf8($portfolio->title) }}">
        <div class="dark-pic-preview-hero-overlay">
            @if (!empty($portfolio->sector))
                <span class="dark-pic-preview-hero-badge">{{ convertUtf8($portfolio->sector->name) }}</span>
            @endif
            <h3 class="dark-pic-preview-hero-title">{{ convertUtf8($portfolio->title) }}</h3>
        </div>
    </div>
@endif

@include('front.default.partials.dark.portfolio-identity-card', [
    'portfolio' => $portfolio,
    'galleryImages' => $galleryImages ?? [],
    'documentList' => $documentList ?? [],
    'clientLogoUrl' => $clientLogoUrl ?? null,
    'countryName' => $countryName ?? null,
])

@if (!empty($galleryImages))
    <div class="dark-pic-preview-gallery">
        <div class="dark-pd-section-heading">
            <span class="dark-pd-section-icon"><i class="fas fa-images"></i></span>
            {{ __('Image Gallery') }}
        </div>
        <div class="dark-pic-preview-gallery-grid">
            @foreach ($galleryImages as $img)
                <img class="dark-pic-preview-gallery-thumb" src="{{ $img }}" alt="">
            @endforeach
        </div>
    </div>
@endif
