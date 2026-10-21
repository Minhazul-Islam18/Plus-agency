{{--
    Live Google SERP + social-card preview used inside each tab panel on
    admin/basic/seo.blade.php. Pure markup — the one <script> on that page
    fills every .seo-serp-*/.seo-social-*/.seo-tier-* element scoped to
    the panel it's inside, on every keystroke in that panel's own title/
    description inputs (and the Global tab's, for the fallback chain).

    Required: $tab (string, matches the panel's data-tab-panel), $path (string, front-end path shown in the SERP url line)
    Optional: $ogSerial (int, matches the og-image-picker above it on the same panel — lets the script mirror that picker's live LFM selection into this card), $ogCurrentUrl (string|null, image shown on first load before any picking happens)
--}}
@php
    $ogSerial = $ogSerial ?? null;
    $ogCurrentUrl = $ogCurrentUrl ?? null;
@endphp
<div class="seo-preview-card">
    <div class="seo-preview-label">Google search preview</div>
    <div class="seo-serp-mock">
        <div class="seo-serp-site">
            <span class="seo-serp-favicon"></span>
            <span class="seo-serp-sitename"></span>
        </div>
        <div class="seo-serp-title"></div>
        <div class="seo-serp-url">icagroupe.com{{ $path === '/' ? '' : $path }}</div>
        <div class="seo-serp-desc"></div>
    </div>
    <div class="seo-tier-row">
        <span class="seo-tier-dot"></span>
        <span class="seo-tier-text"></span>
    </div>

    <div class="seo-preview-label">Social share preview</div>
    <div class="seo-social-mock">
        <div class="seo-social-image" data-og-serial="{{ $ogSerial }}">
            <img src="{{ $ogCurrentUrl ?: asset('assets/admin/img/noimage.jpg') }}" alt="" style="width:100%;height:100%;object-fit:cover;">
        </div>
        <div class="seo-social-body">
            <div class="seo-social-domain">icagroupe.com</div>
            <div class="seo-social-title"></div>
            <div class="seo-social-desc"></div>
        </div>
    </div>
</div>
