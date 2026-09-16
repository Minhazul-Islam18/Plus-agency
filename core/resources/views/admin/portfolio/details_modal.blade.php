@php
    $bulletize = function ($text) {
        if (empty($text)) {
            return [];
        }
        return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $text))));
    };

    $dateFmt = fn($d) => $d ? \Carbon\Carbon::parse($d)->format(app()->getLocale() == 'fr' ? 'd M Y' : 'M d, Y') : null;

    $costFormatted = null;
    if (!empty($portfolio->cost_of_service)) {
        $dec = app()->getLocale() == 'fr' ? ',' : '.';
        $th = app()->getLocale() == 'fr' ? ' ' : ',';
        $costFormatted =
            number_format($portfolio->cost_of_service, 0, $dec, $th) . ' ' . ($bex->base_currency_symbol ?? '');
    }

    $overlayHex = !empty($portfolio->overlay_color) ? ltrim($portfolio->overlay_color, '#') : '060a09';
    $overlayOpacity = !empty($portfolio->overlay_opacity) ? ((int) $portfolio->overlay_opacity) / 100 : 0.82;
    $bloomOpacity = !empty($portfolio->overlay_bloom_opacity)
        ? ((int) $portfolio->overlay_bloom_opacity) / 100
        : $overlayOpacity;

    // front.portfoliodetails sits inside Route::prefix('{locale}') — needs
    // the locale passed explicitly here (unlike a call from a real front-end
    // request already inside that group, which reuses the current locale
    // param as a default). Only offered when actually published; a
    // draft's front-end URL 404s regardless of what admin sees here.
$liveUrl =
    $portfolio->is_published &&
    !empty($portfolio->language) &&
    \Illuminate\Support\Facades\Route::has('front.portfoliodetails')
        ? route('front.portfoliodetails', ['locale' => $portfolio->language->code, 'slug' => $portfolio->slug])
        : null;

$evidenceBlocks = [
    [
        'field' => 'problematique',
        'label' => 'Problem',
        'icon' => $portfolio->problematique_icon ?? 'fas fa-bullseye',
        'list' => false,
    ],
    [
        'field' => 'mission_ica',
        'label' => 'Mission entrusted to ICA',
        'icon' => $portfolio->mission_ica_icon ?? 'fas fa-file-signature',
        'list' => false,
    ],
    [
        'field' => 'expertise_mobilisee',
        'label' => 'Expertise mobilized',
        'icon' => $portfolio->expertise_mobilisee_icon ?? 'fas fa-cogs',
        'list' => true,
    ],
    [
        'field' => 'solution_approche',
        'label' => 'Solution / Approach',
        'icon' => $portfolio->solution_approche_icon ?? 'fas fa-lightbulb',
        'list' => true,
        'accent' => true,
    ],
    [
        'field' => 'resultat_statut',
        'label' => 'Result / Status',
        'icon' => $portfolio->resultat_statut_icon ?? 'fas fa-chart-bar',
        'list' => true,
    ],
    [
        'field' => 'impact',
        'label' => 'Impact',
        'icon' => $portfolio->impact_icon ?? 'fas fa-users',
        'list' => false,
        'wide' => true,
        ],
    ];
@endphp
<style>
    /* Scoped to .pdm-wrap throughout — this partial is re-injected wholesale
       on every modal open (AJAX .html() swap), so nothing here may leak
       into the surrounding admin page. Tokens measured off this actual
       admin skin (Atlantis), not invented. */
    .pdm-wrap {
        --pdm-green: #31CE36;
        --pdm-green-rgb: 49, 206, 54;
        --pdm-blue: #1572e8;
        --pdm-blue-rgb: 21, 114, 232;
        --pdm-panel: #202940;
        --pdm-line: rgba(255, 255, 255, .10);
        --pdm-line-soft: rgba(255, 255, 255, .07);
        --pdm-dim: rgba(255, 255, 255, .62);
        --pdm-dimmer: rgba(255, 255, 255, .4);
        color: #fff;
        font-family: inherit;
    }

    .pdm-head {
        display: flex;
        align-items: flex-start;
        gap: 18px;
        margin-bottom: 18px;
    }

    .pdm-head-main {
        flex: 1;
        min-width: 0;
    }

    .pdm-head-actions {
        flex: 0 0 auto;
    }

    .pdm-view-live {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-size: 12.5px;
        font-weight: 600;
        padding: 8px 14px;
        border-radius: 8px;
        background: var(--pdm-green);
        color: #06210f;
        text-decoration: none;
    }

    .pdm-view-live:hover {
        color: #06210f;
        opacity: .9;
    }

    .pdm-view-live svg {
        width: 13px;
        height: 13px;
    }

    .pdm-view-live.is-disabled {
        background: rgba(255, 255, 255, .06);
        color: var(--pdm-dimmer);
        pointer-events: none;
    }

    .pdm-hero {
        width: 84px;
        height: 84px;
        border-radius: 10px;
        overflow: hidden;
        flex: 0 0 auto;
        border: 1px solid var(--pdm-line);
        background: #0c1420;
    }

    .pdm-hero img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .pdm-hero-empty {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--pdm-dimmer);
        font-size: 22px;
    }

    .pdm-eyebrow {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
        margin-bottom: 8px;
    }

    .pdm-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 10.5px;
        font-weight: 600;
        letter-spacing: .03em;
        padding: 4px 10px;
        border-radius: 100px;
        font-family: "JetBrains Mono", monospace;
        text-transform: uppercase;
    }

    .pdm-pill svg {
        width: 9px;
        height: 9px;
    }

    .pdm-pill-sector {
        background: rgba(var(--pdm-green-rgb), .16);
        color: var(--pdm-green);
        border: 1px solid rgba(var(--pdm-green-rgb), .3);
    }

    .pdm-pill-status {
        background: rgba(var(--pdm-blue-rgb), .16);
        color: #6fa8f5;
        border: 1px solid rgba(var(--pdm-blue-rgb), .3);
    }

    .pdm-pill-live {
        background: rgba(var(--pdm-green-rgb), .16);
        color: var(--pdm-green);
        border: 1px solid rgba(var(--pdm-green-rgb), .3);
    }

    .pdm-pill-draft {
        background: rgba(255, 255, 255, .08);
        color: var(--pdm-dim);
        border: 1px solid var(--pdm-line);
    }

    .pdm-title {
        font-size: 18px;
        font-weight: 700;
        margin: 0 0 8px;
        line-height: 1.3;
    }

    .pdm-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
        font-size: 12px;
        color: var(--pdm-dim);
    }

    .pdm-meta .item {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .pdm-meta svg {
        width: 12px;
        height: 12px;
        color: var(--pdm-dimmer);
        flex: 0 0 auto;
    }

    .pdm-meta .fi {
        border-radius: 2px;
        width: 14px;
        height: 10px;
        display: inline-block;
    }

    .pdm-tabs {
        display: flex;
        gap: 4px;
        border-bottom: 1px solid var(--pdm-line);
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .pdm-tab {
        display: flex;
        align-items: center;
        gap: 7px;
        font-size: 12.5px;
        font-weight: 600;
        color: var(--pdm-dim);
        padding: 10px 14px;
        cursor: pointer;
        border-bottom: 2px solid transparent;
        background: none;
        border-top: none;
        border-left: none;
        border-right: none;
        font-family: inherit;
    }

    .pdm-tab svg {
        width: 14px;
        height: 14px;
    }

    .pdm-tab .count {
        font-family: "JetBrains Mono", monospace;
        font-size: 10px;
        background: rgba(255, 255, 255, .06);
        padding: 1px 6px;
        border-radius: 100px;
        color: var(--pdm-dimmer);
    }

    .pdm-tab.is-active {
        color: #fff;
        border-bottom-color: var(--pdm-blue);
    }

    .pdm-tab.is-active .count {
        background: rgba(var(--pdm-blue-rgb), .18);
        color: #8fb8f2;
    }

    .pdm-pane {
        display: none;
    }

    .pdm-pane.is-active {
        display: block;
    }

    .pdm-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }

    .pdm-item {
        background: var(--pdm-panel);
        border: 1px solid var(--pdm-line-soft);
        border-radius: 10px;
        padding: 12px 14px;
    }

    .pdm-item.span2 {
        grid-column: span 2;
    }

    .pdm-item.span3 {
        grid-column: 1 / -1;
    }

    .pdm-item .k {
        display: flex;
        align-items: center;
        gap: 7px;
        font-size: 10.5px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: var(--pdm-dimmer);
        margin-bottom: 6px;
    }

    .pdm-item .k svg {
        width: 11px;
        height: 11px;
    }

    .pdm-item .v {
        font-size: 13px;
        font-weight: 600;
        word-break: break-word;
    }

    .pdm-item .v.dim {
        color: var(--pdm-dim);
        font-weight: 500;
    }

    /* Partners — own full-width row, each partner a chip (logo + name)
       rather than a comma list buried in a grid cell. */
    .pdm-partners {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .pdm-partner {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        background: rgba(255, 255, 255, .06);
        border: 1px solid var(--pdm-line);
        border-radius: 100px;
        padding: 4px 12px 4px 5px;
        font-size: 12px;
        font-weight: 600;
    }

    .pdm-partner img {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        object-fit: contain;
        background: #fff;
        flex: 0 0 auto;
    }

    .pdm-empty {
        font-size: 12.5px;
        color: var(--pdm-dimmer);
        font-style: italic;
        padding: 20px 0;
        text-align: center;
    }

    .pdm-eoc-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }

    .pdm-eoc-card {
        background: var(--pdm-panel);
        border: 1px solid var(--pdm-line-soft);
        border-radius: 12px;
        padding: 15px;
    }

    .pdm-eoc-card.accent {
        border-color: rgba(var(--pdm-green-rgb), .35);
        background: rgba(var(--pdm-green-rgb), .05);
    }

    .pdm-eoc-card.wide {
        grid-column: span 3;
    }

    .pdm-eoc-head {
        display: flex;
        align-items: center;
        gap: 9px;
        margin-bottom: 9px;
    }

    .pdm-eoc-icon {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        background: rgba(var(--pdm-blue-rgb), .15);
        color: #8fb8f2;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        font-size: 13px;
    }

    .pdm-eoc-card.accent .pdm-eoc-icon {
        background: rgba(var(--pdm-green-rgb), .18);
        color: var(--pdm-green);
    }

    .pdm-eoc-head h4 {
        font-size: 12.5px;
        font-weight: 700;
        margin: 0;
    }

    .pdm-eoc-card p {
        font-size: 12px;
        line-height: 1.6;
        color: var(--pdm-dim);
        margin: 0;
    }

    .pdm-eoc-card ul {
        margin: 0;
        padding-left: 16px;
        font-size: 12px;
        line-height: 1.7;
        color: var(--pdm-dim);
    }

    .pdm-overlay-controls {
        display: flex;
        gap: 24px;
        margin-bottom: 20px;
    }

    .pdm-oc-item label {
        display: block;
        font-size: 10.5px;
        font-weight: 600;
        color: var(--pdm-dimmer);
        margin-bottom: 7px;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .pdm-oc-swatch {
        width: 30px;
        height: 30px;
        border-radius: 7px;
        border: 1px solid var(--pdm-line);
    }

    .pdm-oc-bar {
        width: 150px;
        height: 6px;
        border-radius: 100px;
        background: rgba(255, 255, 255, .06);
        position: relative;
    }

    .pdm-oc-bar-fill {
        position: absolute;
        inset: 0;
        border-radius: 100px;
        background: var(--pdm-green);
    }

    .pdm-oc-val {
        font-family: "JetBrains Mono", monospace;
        font-size: 11px;
        color: var(--pdm-dim);
        margin-top: 6px;
    }

    .pdm-section-label {
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: var(--pdm-dimmer);
        margin: 4px 0 10px;
    }

    .pdm-hl-list {
        display: flex;
        flex-direction: column;
        gap: 7px;
    }

    .pdm-hl-row {
        display: flex;
        align-items: center;
        gap: 11px;
        background: var(--pdm-panel);
        border: 1px solid var(--pdm-line-soft);
        border-radius: 9px;
        padding: 9px 13px;
    }

    .pdm-hl-row i {
        font-size: 16px;
        color: var(--pdm-green);
        flex: 0 0 auto;
        width: 18px;
        text-align: center;
    }

    .pdm-hl-row span {
        font-size: 12px;
        font-weight: 600;
    }

    .pdm-media-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 9px;
        margin-bottom: 20px;
    }

    .pdm-media-thumb {
        aspect-ratio: 4/3;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid var(--pdm-line-soft);
        background: #0c1420;
    }

    .pdm-media-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .pdm-doc-list {
        display: flex;
        flex-direction: column;
        gap: 7px;
    }

    .pdm-doc-row {
        display: flex;
        align-items: center;
        gap: 11px;
        background: var(--pdm-panel);
        border: 1px solid var(--pdm-line-soft);
        border-radius: 9px;
        padding: 10px 13px;
    }

    .pdm-doc-icon {
        width: 30px;
        height: 30px;
        border-radius: 7px;
        background: rgba(var(--pdm-green-rgb), .14);
        color: var(--pdm-green);
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        font-size: 13px;
    }

    .pdm-doc-name {
        font-size: 12px;
        font-weight: 600;
        flex: 1;
        word-break: break-word;
    }

    .pdm-doc-size {
        font-size: 10.5px;
        color: var(--pdm-dimmer);
        font-family: "JetBrains Mono", monospace;
        flex: 0 0 auto;
    }

    .pdm-doc-dl {
        width: 28px;
        height: 28px;
        border-radius: 100px;
        background: rgba(var(--pdm-blue-rgb), .15);
        color: #8fb8f2;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        text-decoration: none;
    }

    @media (max-width: 820px) {

        .pdm-grid,
        .pdm-eoc-grid,
        .pdm-media-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .pdm-eoc-card.wide {
            grid-column: span 2;
        }
    }
</style>

<div class="pdm-wrap">
    <div class="pdm-head">
        <div class="pdm-hero">
            @if (!empty($heroImageUrl))
                <img src="{{ $heroImageUrl }}" alt="">
            @else
                <div class="pdm-hero-empty"><i class="fas fa-image"></i></div>
            @endif
        </div>
        <div class="pdm-head-main">
            <div class="pdm-eyebrow">
                @if (!empty($portfolio->sector))
                    {{-- A subsector reads inline as "Sector › Subsector" here (a
                         single compact pill, not two lines) — the spec grid
                         further down repeats it stacked, where there's room. --}}
                    <span class="pdm-pill pdm-pill-sector"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5">
                            <circle cx="12" cy="12" r="9" />
                        </svg>{{ convertUtf8($portfolio->sector->name) }}@if (!empty($portfolio->subsector)) &rsaquo; {{ convertUtf8($portfolio->subsector->name) }}@endif</span>
                @endif
                @if (!empty($portfolio->statusInfo))
                    <span class="pdm-pill pdm-pill-status"><svg viewBox="0 0 24 24" fill="currentColor">
                            <circle cx="12" cy="12" r="6" />
                        </svg>{{ convertUtf8($portfolio->statusInfo->name) }}</span>
                @endif
                <span class="pdm-pill {{ $portfolio->is_published ? 'pdm-pill-live' : 'pdm-pill-draft' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M5 13l4 4L19 7" />
                    </svg>
                    {{ $portfolio->is_published ? 'Published' : 'Unpublished' }}
                </span>
            </div>
            <h3 class="pdm-title">{{ convertUtf8($portfolio->title) }}</h3>
            <div class="pdm-meta">
                @if (!empty($portfolio->client_name))
                    <span class="item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 21h18M5 21V7l8-4v18M13 21V11l6 4v6" />
                        </svg>{{ convertUtf8($portfolio->client_name) }}</span>
                @endif
                @if (!empty($portfolio->country))
                    <span class="item"><span
                            class="fi fi-{{ strtolower($portfolio->country) }}"></span>{{ $countryName ?? $portfolio->country }}</span>
                @endif
                {{-- Project's own execution span (Start -> End) — NOT the tender's
                     Submission Deadline, which is a separate date shown in the
                     detail rows below. --}}
                @if ($portfolio->start_date || $portfolio->end_date)
                    <span class="item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" />
                            <path d="M16 2v4M8 2v4M3 10h18" />
                        </svg>{{ $dateFmt($portfolio->start_date) ?? '—' }} →
                        {{ $dateFmt($portfolio->end_date) ?? '—' }}</span>
                @endif
                @if ($costFormatted)
                    <span class="item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 1v22M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" />
                        </svg>{{ $costFormatted }}</span>
                @endif
            </div>
        </div>
        <div class="pdm-head-actions">
            @if ($liveUrl)
                <a href="{{ $liveUrl }}" target="_blank" class="pdm-view-live">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6M15 3h6v6M10 14L21 3" />
                    </svg>
                    View in Site
                </a>
            @else
                <span class="pdm-view-live is-disabled" title="Unpublished — no live URL yet">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6M15 3h6v6M10 14L21 3" />
                    </svg>
                    View in Site
                </span>
            @endif
        </div>
    </div>

    <div class="pdm-tabs">
        <button type="button" class="pdm-tab is-active" data-pdm-tab="overview">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7" rx="1" />
                <rect x="14" y="3" width="7" height="7" rx="1" />
                <rect x="3" y="14" width="7" height="7" rx="1" />
                <rect x="14" y="14" width="7" height="7" rx="1" />
            </svg>
            Overview
        </button>
        <button type="button" class="pdm-tab" data-pdm-tab="eoc">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 11l3 3L22 4M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11" />
            </svg>
            Evidence of Competence <span class="count">6</span>
        </button>
        <button type="button" class="pdm-tab" data-pdm-tab="overlay">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="18" height="18" rx="2" />
                <path d="M3 16l5-5 4 4 5-6 4 5" />
            </svg>
            Carousel Overlay
        </button>
        <button type="button" class="pdm-tab" data-pdm-tab="media">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="18" height="18" rx="2" />
                <circle cx="8.5" cy="8.5" r="1.5" />
                <path d="M21 15l-5-5L5 21" />
            </svg>
            Media & Documents <span
                class="count">{{ count($galleryImages ?? []) + count($documentList ?? []) }}</span>
        </button>
    </div>

    {{-- ===== Overview ===== --}}
    <div class="pdm-pane is-active" data-pdm-pane="overview">
        <div class="pdm-grid">
            <div class="pdm-item">
                <div class="k"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 21h18M5 21V7l8-4v18M13 21V11l6 4v6" />
                    </svg>Client</div>
                <div class="v">{{ !empty($portfolio->client_name) ? convertUtf8($portfolio->client_name) : '—' }}
                </div>
            </div>
            <div class="pdm-item">
                <div class="k"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>Service</div>
                <div class="v {{ empty($portfolio->service) ? 'dim' : '' }}">
                    {{ !empty($portfolio->service) ? convertUtf8($portfolio->service->title) : '—' }}</div>
            </div>
            <div class="pdm-item">
                <div class="k"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" />
                        <path d="M16 2v4M8 2v4M3 10h18" />
                    </svg>Start Date</div>
                <div class="v {{ !$portfolio->start_date ? 'dim' : '' }}">
                    {{ $dateFmt($portfolio->start_date) ?? '—' }}</div>
            </div>
            <div class="pdm-item">
                <div class="k"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" />
                        <path d="M16 2v4M8 2v4M3 10h18M9 16l2 2 4-4" />
                    </svg>End Date</div>
                <div class="v {{ !$portfolio->end_date ? 'dim' : '' }}">
                    {{ $dateFmt($portfolio->end_date) ?? '—' }}</div>
            </div>
            <div class="pdm-item">
                <div class="k"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" />
                        <path d="M16 2v4M8 2v4M3 10h18M8 15l2-2 2 2 4-4" />
                    </svg>Submission Deadline</div>
                <div class="v {{ !$portfolio->submission_date ? 'dim' : '' }}">
                    {{ $dateFmt($portfolio->submission_date) ?? '—' }}</div>
            </div>
            <div class="pdm-item">
                <div class="k"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 1v22M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" />
                    </svg>Cost of Service</div>
                <div class="v {{ !$costFormatted ? 'dim' : '' }}">{{ $costFormatted ?? '—' }}</div>
            </div>
            <div class="pdm-item span2">
                <div class="k"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path
                            d="M10 13a5 5 0 007.07 0l1.93-1.93a5 5 0 00-7.07-7.07L10.5 5.43M14 11a5 5 0 00-7.07 0L5 12.93a5 5 0 007.07 7.07L13.5 18.57" />
                    </svg>Website Link</div>
                @if (!empty($portfolio->website_link))
                    <div class="v"><a href="{{ $portfolio->website_link }}" target="_blank"
                            style="color:#8fb8f2;">{{ $portfolio->website_link }}</a></div>
                @else
                    <div class="v dim">—</div>
                @endif
            </div>
            <div class="pdm-item">
                <div class="k"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path
                            d="M4 19.5A2.5 2.5 0 016.5 17H20M4 19.5A2.5 2.5 0 006.5 22H20V2H6.5A2.5 2.5 0 004 4.5v15z" />
                    </svg>Language / #</div>
                <div class="v dim">{{ !empty($portfolio->language) ? $portfolio->language->name : '—' }} ·
                    #{{ $portfolio->serial_number }}</div>
            </div>
            <div class="pdm-item span3">
                <div class="k"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8z" />
                    </svg>Partners</div>
                @if ($portfolio->partnerRefs->isNotEmpty())
                    <div class="pdm-partners">
                        @foreach ($portfolio->partnerRefs as $partner)
                            <span class="pdm-partner">
                                @if (!empty($partner->image))
                                    <img src="{{ asset('assets/front/img/partners/' . $partner->image) }}"
                                        alt="{{ convertUtf8($partner->name) }}"
                                        onerror="this.style.display='none'">
                                @endif
                                {{ convertUtf8($partner->name) }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <div class="v dim">—</div>
                @endif
            </div>
        </div>
    </div>

    {{-- ===== Evidence of Competence ===== --}}
    <div class="pdm-pane" data-pdm-pane="eoc">
        <div class="pdm-eoc-grid">
            @foreach ($evidenceBlocks as $blk)
                <div
                    class="pdm-eoc-card {{ $blk['accent'] ?? false ? 'accent' : '' }} {{ $blk['wide'] ?? false ? 'wide' : '' }}">
                    <div class="pdm-eoc-head">
                        <span class="pdm-eoc-icon"><i class="{{ $blk['icon'] }}"></i></span>
                        <h4>{{ $blk['label'] }}</h4>
                    </div>
                    @if ($blk['list'])
                        @php $lines = $bulletize($portfolio->{$blk['field']}); @endphp
                        @if (count($lines))
                            <ul>
                                @foreach ($lines as $line)
                                    <li>{{ convertUtf8($line) }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p style="color:var(--pdm-dimmer); font-style:italic;">Not filled in yet</p>
                        @endif
                    @else
                        <p>{{ !empty($portfolio->{$blk['field']}) ? convertUtf8($portfolio->{$blk['field']}) : 'Not filled in yet' }}
                        </p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- ===== Carousel Overlay ===== --}}
    <div class="pdm-pane" data-pdm-pane="overlay">
        <div class="pdm-grid" style="margin-bottom:20px;">
            <div class="pdm-item span2">
                <div class="k"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2" />
                        <path d="M3 16l5-5 4 4 5-6 4 5" />
                    </svg>Overlay Title</div>
                <div class="v {{ empty($portfolio->overlay_title) ? 'dim' : '' }}">
                    {{ convertUtf8($portfolio->overlayTitle()) }}
                    @if (empty($portfolio->overlay_title))
                        <span style="font-weight:400; color:var(--pdm-dimmer);">(falling back to Project Title)</span>
                    @endif
                </div>
            </div>
            <div class="pdm-item">
                <div class="k"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 6h16M4 12h10M4 18h7" />
                    </svg>Overlay Subtitle</div>
                <div class="v {{ empty($portfolio->overlay_subtitle) ? 'dim' : '' }}">
                    {{ !empty($portfolio->overlay_subtitle) ? convertUtf8($portfolio->overlay_subtitle) : '—' }}</div>
            </div>
        </div>
        <div class="pdm-overlay-controls">
            <div class="pdm-oc-item">
                <label>Overlay Color</label>
                <div class="pdm-oc-swatch" style="background:#{{ $overlayHex }};"></div>
            </div>
            <div class="pdm-oc-item">
                <label>Overlay Opacity</label>
                <div class="pdm-oc-bar">
                    <div class="pdm-oc-bar-fill" style="width:{{ $overlayOpacity * 100 }}%;"></div>
                </div>
                <div class="pdm-oc-val">{{ round($overlayOpacity * 100) }}%</div>
            </div>
            <div class="pdm-oc-item">
                <label>Left Bloom Opacity</label>
                <div class="pdm-oc-bar">
                    <div class="pdm-oc-bar-fill" style="width:{{ $bloomOpacity * 100 }}%;"></div>
                </div>
                <div class="pdm-oc-val">{{ round($bloomOpacity * 100) }}%</div>
            </div>
        </div>
        @if (!empty($portfolio->overlayDescription()))
            <div class="pdm-item" style="margin-bottom:20px;">
                <div class="k"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 6h16M4 12h16M4 18h10" />
                    </svg>Overlay Description</div>
                <div class="v">{{ convertUtf8($portfolio->overlayDescription()) }}</div>
            </div>
        @endif
        <p class="pdm-section-label">Icon Highlights ({{ $portfolio->highlights->count() }})</p>
        @if ($portfolio->highlights->count() > 0)
            <div class="pdm-hl-list">
                @foreach ($portfolio->highlights as $h)
                    <div class="pdm-hl-row"><i
                            class="{{ $h->icon ?: 'fas fa-star' }}"></i><span>{{ convertUtf8($h->label) }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <p class="pdm-empty">No highlights added yet.</p>
        @endif
    </div>

    {{-- ===== Media & Documents ===== --}}
    <div class="pdm-pane" data-pdm-pane="media">
        <p class="pdm-section-label">Gallery ({{ count($galleryImages ?? []) }})</p>
        @if (!empty($galleryImages))
            <div class="pdm-media-grid">
                @foreach ($galleryImages as $img)
                    <div class="pdm-media-thumb"><img src="{{ $img }}" alt=""></div>
                @endforeach
            </div>
        @else
            <p class="pdm-empty" style="margin-bottom:20px;">No gallery images.</p>
        @endif

        <p class="pdm-section-label">Documents ({{ count($documentList ?? []) }})</p>
        @if (!empty($documentList))
            <div class="pdm-doc-list">
                @foreach ($documentList as $doc)
                    <div class="pdm-doc-row">
                        <span class="pdm-doc-icon"><i class="fas fa-file-alt"></i></span>
                        <span class="pdm-doc-name">{{ $doc['name'] }}</span>
                        @if (!empty($doc['size']))
                            <span class="pdm-doc-size">{{ number_format($doc['size'] / 1048576, 1) }} MB</span>
                        @endif
                        <a class="pdm-doc-dl" href="{{ $doc['url'] }}" target="_blank" title="Download"><i
                                class="fas fa-download"></i></a>
                    </div>
                @endforeach
            </div>
        @else
            <p class="pdm-empty">No documents attached.</p>
        @endif
    </div>
</div>

<script>
    // Delegated on document (guarded so re-opening the modal — which
    // reloads this whole partial via AJAX .html() — never stacks up a
    // duplicate handler) since the elements themselves are destroyed and
    // recreated on every open.
    if (!window.__pdmTabsBound) {
        window.__pdmTabsBound = true;
        $(document).on('click', '.pdm-tab', function() {
            var $wrap = $(this).closest('.pdm-wrap');
            var target = $(this).data('pdm-tab');
            $wrap.find('.pdm-tab').removeClass('is-active');
            $(this).addClass('is-active');
            $wrap.find('.pdm-pane').removeClass('is-active');
            $wrap.find('.pdm-pane[data-pdm-pane="' + target + '"]').addClass('is-active');
        });
    }
</script>
