@php
    // Dedicated dark-theme hero settings (admin: Content Management -> Home Page Sections -> Dark Theme Hero),
    // falling back to the shared light-theme hero fields until the admin fills the new page in.
    $dhEyebrow = $darkHero?->eyebrow ?: __('Welcome');
    $dhTitle = $darkHero?->title ?: $bs->hero_section_title;
    $dhRotating = $darkHero?->rotating_titles ?: $bs->hero_rotating_titles;
    $dhText = $darkHero?->text ?: $bs->hero_section_text;
    $dhButtonText = $darkHero?->button_text ?: $bs->hero_section_button_text;
    $dhButtonUrl = $darkHero?->button_url ?: $bs->hero_section_button_url;
    $dhMetaLeft = $darkHero?->meta_left;
    $dhMetaRight = $darkHero?->meta_right;

    $darkHeroTitles = collect([$dhTitle])
        ->merge(preg_split('/\r\n|\r|\n/', (string) $dhRotating))
        ->map(fn($t) => trim((string) $t))
        ->filter()
        ->values();
@endphp
<div class="dark-hero" data-particle-network>
    <span class="dark-hero-mesh dark-hero-mesh--1"></span>
    <span class="dark-hero-mesh dark-hero-mesh--2"></span>

    <div class="dark-hero-inner">
        <div class="dark-hero-panel-wrap" data-hero-blob-zone>
            <span class="dark-hero-blob" data-hero-blob></span>

            <div class="dark-hero-panel">
                <div class="dark-hero-grid">
                    <div class="dark-hero-col-left">
                        <span class="dark-hero-eyebrow">{{ convertUtf8($dhEyebrow) }}</span>

                        <h1 class="dark-hero-flip" data-hero-flip>
                            @foreach ($darkHeroTitles as $key => $title)
                                <span class="dark-hero-flip-line {{ $key == 0 ? 'is-active' : '' }}">{{ convertUtf8($title) }}</span>
                            @endforeach
                        </h1>
                    </div>

                    <div class="dark-hero-col-right">
                        <p class="dark-hero-text">{{ convertUtf8($dhText) }}</p>

                        @if (!empty($dhButtonUrl) && !empty($dhButtonText))
                            <a href="{{ $dhButtonUrl }}" class="dark-hero-cta" target="_blank">
                                <span>{{ convertUtf8($dhButtonText) }}</span>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        @endif

                        @if (!empty($dhMetaLeft) || !empty($dhMetaRight))
                            <div class="dark-hero-meta">
                                @if (!empty($dhMetaLeft))<span>{{ convertUtf8($dhMetaLeft) }}</span>@endif
                                @if (!empty($dhMetaRight))<span>{{ convertUtf8($dhMetaRight) }}</span>@endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if ($bs->partner_section == 1 && !empty($partners) && count($partners) > 0)
            <div class="dark-hero-marquee">
                <div class="dark-hero-marquee-label">{{ __('Trusted across sectors') }}</div>
                <div class="dark-hero-marquee-track">
                    @for ($i = 0; $i < 2; $i++)
                        @foreach ($partners as $partner)
                            @if (!empty($partner->url))
                                <a href="{{ $partner->url }}" target="_blank" rel="noopener" class="dark-hero-marquee-item">
                                    <img src="{{ asset('assets/front/img/partners/' . $partner->image) }}" alt="">
                                </a>
                            @else
                                <span class="dark-hero-marquee-item">
                                    <img src="{{ asset('assets/front/img/partners/' . $partner->image) }}" alt="">
                                </span>
                            @endif
                        @endforeach
                    @endfor
                </div>
            </div>
        @endif
    </div>
</div>
