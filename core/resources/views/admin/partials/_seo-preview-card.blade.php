{{--
    Live Google SERP + social-card preview used inside each tab panel on
    admin/basic/seo.blade.php. Pure markup — the one <script> on that page
    fills every .seo-serp-*/.seo-social-*/.seo-tier-* element scoped to
    the panel it's inside, on every keystroke in that panel's own title/
    description inputs (and the Global tab's, for the fallback chain).

    Required: $tab (string, matches the panel's data-tab-panel), $path (string, front-end path shown in the SERP url line)
--}}
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
        <div class="seo-social-image">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#B0AEA5" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="9.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
        </div>
        <div class="seo-social-body">
            <div class="seo-social-domain">icagroupe.com</div>
            <div class="seo-social-title"></div>
            <div class="seo-social-desc"></div>
        </div>
    </div>
</div>
