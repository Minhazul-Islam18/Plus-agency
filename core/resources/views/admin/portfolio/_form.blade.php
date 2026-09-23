{{--
    Shared Portfolio create/edit form fields — the "evidence of competence"
    3-column redesign (see ~/Documents/15-Portoflios/Recommandation sur
    Portofolios-*.docx). Included by create.blade.php (with $portfolio = null)
    and edit.blade.php (with the real $portfolio).

    Expects: $portfolio (nullable), $services, $sectors, $statuses, $countries, $bex.
--}}
@php
    $isEdit = !empty($portfolio);
    $old = function ($field, $default = '') use ($portfolio, $isEdit) {
        return old($field, $isEdit ? $portfolio->{$field} ?? $default : $default);
    };
@endphp

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icons@7.2.3/css/flag-icons.min.css" integrity="sha384-aQuvIWWIbpu/mSqULLDiUveyYiPoJzPKAWjUmGJ+Elm+N/LJhzfZqsutsfw870JS" crossorigin="anonymous">

<style>
    /* Matches the app's own dark admin theme (body[data-background-color="dark"]
       .card => #202940, .card-title => #fff) instead of a hardcoded light
       panel — a light bg here left labels using the theme's light-on-dark
       default color, unreadable against it. A real border + shadow (the
       flat bg-only version blended straight into the page behind it) plus
       a proper header strip is what actually reads as "separate cards"
       instead of one continuous grey field. */
    .portfolio-form-col {
        background: #202940;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 10px;
        padding: 22px;
        box-shadow: 0 12px 30px -14px rgba(0, 0, 0, 0.5);
    }

    .portfolio-form-col h5 {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 14.5px;
        font-weight: 700;
        letter-spacing: 0.01em;
        color: #fff;
        margin: -22px -22px 22px;
        padding: 16px 22px;
        background: rgba(255, 255, 255, 0.03);
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 10px 10px 0 0;
    }

    .portfolio-form-col h5 i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 26px;
        height: 26px;
        border-radius: 7px;
        background: rgba(22, 185, 153, 0.16);
        color: #16b999;
        font-size: 12px;
        flex-shrink: 0;
    }

    /* .form-group label's site-wide default (#495057, atlantis.min.css) is
       tuned for a light card background — barely readable against this
       dark one. Scoped here only, not touching the global rule other
       (light-background) admin pages already rely on. */
    .portfolio-form-col .form-group label {
        color: #ccd2e3;
    }

    .portfolio-block-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 14px;
        margin-top: 14px;
    }

    @media (max-width: 1199px) {
        .portfolio-block-grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 767px) {
        .portfolio-block-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Divides a card into sub-groups — a heading-less rule reads as a
       lighter break than another h5, appropriate one level down inside a
       column that already has its own heading. */
    .portfolio-subdivider {
        border: none;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        margin: 4px 0 18px;
    }

    .portfolio-block {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 6px;
        padding: 12px;
    }

    .portfolio-block label {
        font-weight: 600;
        font-size: 13px;
        margin-bottom: 6px;
        color: #8b92a9;
    }

    .portfolio-block-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 6px;
    }

    /* Icon-picker trigger (same fontawesome-iconpicker plugin used by
       Approach/Statistics/Features elsewhere in the admin) — the selected-
       icon preview and the dropdown-open caret, sized up so the current
       icon actually reads at a glance instead of a tiny unreadable chip. */
    .portfolio-icon-picker .btn-group {
        display: inline-flex;
    }

    .portfolio-icon-picker .iconpicker-component {
        width: 42px;
        height: 34px;
        padding: 0;
        font-size: 17px;
    }

    .portfolio-icon-picker .icp-dd {
        height: 34px;
        padding: 0 12px;
        font-size: 15px;
    }

    /* Icon Highlights — drag-drop list builder. */
    .highlights-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-top: 10px;
    }
    /* Dark theme, matching this admin skin — not a light card. Also fixes
       a genuine bug found live: dark-glass.css (loaded on this page for
       the Aperçu preview) has a blanket, unscoped `.form-control{color:
       #fff; background: rgba(255,255,255,.06); ...}` rule for the FRONT
       END's own inputs. Every OTHER input on this page already sits on
       this same dark navy admin background, so white text on a
       near-transparent white overlay still reads fine there — a light
       row background was the actual problem: same white text landed on
       a near-white row, not a genuine "missing" style. */
    .highlight-row {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 10px;
        background: #1a2035;
        border: 1px solid #2f374b;
        border-radius: 6px;
    }
    .highlight-drag-handle {
        cursor: grab;
        color: rgba(255, 255, 255, 0.45);
        padding: 0 2px;
    }
    .highlight-drag-handle:active {
        cursor: grabbing;
    }
    .highlight-label {
        flex: 1 1 auto;
    }
    /* The icon-picker's two buttons (42px + 34px) need ~78px — as a plain
       flex item it shrinks below that to make room for .highlight-label's
       own flex:1 1 auto, and the caret button wraps onto its own line
       once it doesn't fit (confirmed live via getBoundingClientRect: the
       picker was rendering at 65px instead of its natural ~78px). Fixed
       width, not shrinkable — .highlight-label is the one meant to give
       up space here. */
    .highlight-row .portfolio-icon-picker {
        flex: 0 0 auto;
    }
    .highlight-row.ui-sortable-helper {
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.45);
    }
    .highlight-row-placeholder {
        border: 2px dashed #3a445c;
        border-radius: 6px;
        background: rgba(255, 255, 255, 0.03);
        margin-bottom: 0;
    }

    /* The icon-picker popover ships with its own light-theme CSS (white/
       #f7f7f7 chrome, dark text) — normally fine on its own, but this page
       also loads the front-end's dark-glass.css (for the Aperçu preview
       elsewhere on this same form), and that stylesheet has a blanket,
       unscoped `input[type="search"], .form-control {...}` rule with
       !important that overrides the search box's background to a
       near-transparent glass tint while its *text* stays whatever this
       page's own body color is (white) — white-on-near-white, unreadable.
       Rather than patch the leak at its source (dark-glass.css needs that
       generic rule for real front-end inputs), reskin the whole popover
       dark to match the admin theme — consistent with the rest of this
       form instead of just papering over the one broken input. */
    .iconpicker-popover.popover {
        background: #1a2035 !important;
        border: 1px solid #2f374b !important;
        color: #fff;
        box-shadow: 0 12px 30px -10px rgba(0, 0, 0, 0.6);
    }
    .iconpicker-popover .iconpicker-items {
        max-height: 360px !important;
    }
    .iconpicker-popover .iconpicker-item {
        /* Sized to keep 4 per row inside the (reverted-to-default) 234px
           popover width — bigger than the plugin default 14px, but not
           so big it drops to 3 per row. */
        width: 16px !important;
        height: 16px !important;
        padding: 12px !important;
        font-size: 15px !important;
    }
    .iconpicker-popover .popover-title {
        background: #202940 !important;
        border-bottom: 1px solid #2f374b !important;
        color: #fff;
    }
    .iconpicker-popover input[type="search"].iconpicker-search {
        background: #131a2c !important;
        border: 1px solid #2f374b !important;
        color: #fff !important;
    }
    .iconpicker-popover input[type="search"].iconpicker-search::placeholder {
        color: rgba(255, 255, 255, 0.4);
    }
    .iconpicker-popover .iconpicker-items {
        background: #1a2035 !important;
    }
    .iconpicker-popover .iconpicker-item {
        box-shadow: 0 0 0 1px #2f374b !important;
        color: #cfd4e4;
    }
    .iconpicker-popover .iconpicker-item:hover:not(.iconpicker-selected) {
        background-color: rgba(255, 255, 255, 0.08) !important;
    }
    .iconpicker-popover .iconpicker-item.iconpicker-selected {
        background: #1572e8 !important;
        color: #fff;
    }
    .iconpicker-popover .popover-footer {
        background: #202940 !important;
        border-top: 1px solid #2f374b !important;
    }

    /* Country <select> upgraded to select2 — a plain native <select> with
       176 countries in raw alphabetical order (no search, no flags) is
       what "not a proper select at all" meant. select2's default theme is
       also unstyled light-on-white (the same class of bug as the
       icon-picker popover fixed earlier) since neither this page nor the
       site-wide init pass `theme:'bootstrap'` — reskinned dark here
       rather than touching the shared global init. */
    #portfolioCountrySelect + .select2-container,
    #sectors + .select2-container,
    #portfolioStatuses + .select2-container {
        width: 100% !important;
    }
    .select2-container--default .select2-selection--single {
        background: #1a2035 !important;
        border: 1px solid #2f374b !important;
        border-radius: 4px;
        height: calc(2.25rem + 2px) !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #fff;
        line-height: calc(2.25rem) !important;
        padding-left: 12px;
        /* A long service title (or country name) must not stretch the
           closed control past its own width — clip it instead. */
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: rgba(255, 255, 255, 0.4);
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: calc(2.25rem + 2px) !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow b {
        border-color: rgba(255, 255, 255, 0.5) transparent transparent transparent;
    }
    .select2-dropdown {
        background: #1a2035 !important;
        border: 1px solid #2f374b !important;
        color: #fff;
    }
    .select2-search--dropdown .select2-search__field {
        background: #131a2c !important;
        border: 1px solid #2f374b !important;
        color: #fff !important;
    }
    .select2-container--default .select2-results__option {
        color: #cfd4e4;
        /* select2's dropdown panel always matches the closed control's own
           width (unlike a native <select>'s popup, which grows to fit its
           widest option) — this is what actually fixes "dropdown grows
           wide with big text". Left to wrap/overflow by default though, so
           still needs its own clip+ellipsis for a title longer than that
           fixed width. */
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background: #1572e8 !important;
        color: #fff;
    }
    /* select2.min.css's OWN default theme has a bare
       `.select2-results__option--selected{background-color:#ddd}` rule —
       light gray, meant for its light theme. [aria-selected="true"] is
       NOT a reliable way to target "this is the picked value": select2
       repurposes that attribute for keyboard/mouse FOCUS (the "active
       descendant"), which moves to whatever option the pointer is over —
       so the instant focus lands on a different option, the actually-
       selected one drops back to aria-selected="false" and was left with
       nothing overriding that light-gray default (visible live: reopening
       a populated select and hovering a different row than the current
       value showed it washed-out light-gray with barely-readable text —
       exactly this scenario). Target the --selected CLASS directly
       instead, unconditionally, so it stays dark regardless of focus. */
    .select2-container--default .select2-results__option--selected {
        background: rgba(255, 255, 255, 0.08) !important;
        color: #cfd4e4;
    }
    .select2-results__option .fi {
        margin-right: 8px;
        border-radius: 2px;
    }

    /* Shopify/WordPress-style layout: content-heavy fields in the main
       (left) column, everything else — language, publish state, taxonomy,
       dates — in a sidebar (right) column. Sticky so it stays in view
       while the main column (Evidence of Competence, Carousel Overlay...)
       scrolls past it. Language is the FIRST sidebar card: it used to sit
       far down the page while Sector/Status/Partners (which all
       load FROM it) were scattered elsewhere, forcing a scroll-down-then-
       back-up round trip just to set up a new portfolio. */
    @media (min-width: 992px) {
        .portfolio-sidebar-col {
            position: sticky;
            top: 20px;
            align-self: flex-start;
        }
    }
    /* Below lg, columns stack (Bootstrap's own behavior) but stay in DOM
       order by default — that would put the sidebar (Language included)
       BELOW every main-column card, further down than it was before this
       reorg. .row is flex (Bootstrap 4), so `order` alone reorders the
       stack without touching markup: sidebar first, main content after,
       on phones/tablets. */
    @media (max-width: 991px) {
        .portfolio-main-col {
            order: 2;
        }
        .portfolio-sidebar-col {
            order: 1;
            margin-bottom: 24px;
        }
    }
    .portfolio-form-col.is-language {
        border-color: rgba(21, 114, 232, 0.4);
    }
    .portfolio-form-col.is-language h5 {
        background: rgba(21, 114, 232, 0.12);
        border-bottom-color: rgba(21, 114, 232, 0.3);
    }
    .portfolio-form-col.is-language h5 i {
        background: rgba(21, 114, 232, 0.2);
        color: #6ea8f7;
    }
    .portfolio-sidebar-col .portfolio-form-col + .portfolio-form-col {
        margin-top: 24px;
    }

    /* "Regenerate URL" button — Slug field's input-group companion (see
       admin.slug.preview / SlugController). Same accent as the Language
       card above for one consistent "smart/assisted field" visual language
       across the form. */
    .slug-input-group {
        display: flex;
        gap: 8px;
    }
    .slug-input-group input {
        flex: 1;
        min-width: 0;
    }
    .slug-regen-btn {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 0 14px;
        border-radius: 6px;
        border: 1px solid rgba(21, 114, 232, 0.35);
        background: rgba(21, 114, 232, 0.12);
        color: #6ea8f7;
        font-size: 12.5px;
        font-weight: 600;
        white-space: nowrap;
        cursor: pointer;
        transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
    }
    .slug-regen-btn:hover:not(:disabled) {
        background: rgba(21, 114, 232, 0.24);
        border-color: rgba(21, 114, 232, 0.55);
        color: #fff;
    }
    .slug-regen-btn:disabled {
        opacity: 0.6;
        cursor: wait;
    }
    .slug-regen-btn i {
        font-size: 11.5px;
    }
    .slug-regen-btn.is-loading i {
        animation: slugRegenSpin 0.6s linear infinite;
    }
    @keyframes slugRegenSpin {
        to {
            transform: rotate(360deg);
        }
    }
    /* Brief ring pulse on the field itself once a fresh slug lands — a
       box-shadow (not background) so it never clobbers the form-control's
       own themed background color. */
    #slugInput.slug-just-regenerated {
        animation: slugRegenFlash 1s ease;
    }
    @keyframes slugRegenFlash {
        0% {
            box-shadow: 0 0 0 3px rgba(21, 114, 232, 0.45);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(21, 114, 232, 0);
        }
    }
</style>

{{-- Shopify/WordPress-style layout: content-heavy fields in the main
     (left) column, everything else — language, publish state, taxonomy,
     dates — in a sticky sidebar (right) column. Language is the FIRST
     sidebar card: Sector/Status/Partners below it all cascade
     from it, so picking it first — instead of scrolling down to it after
     everything else — is what actually lets that cascade fire before you
     need those fields. --}}
<div class="row">
    <div class="col-lg-8 portfolio-main-col">

        <div class="portfolio-form-col">
            <h5><i class="fas fa-file-alt"></i> General Information</h5>

            <div class="form-group">
                <label>Project Title **</label>
                <input type="text" class="form-control" name="title" value="{{ $old('title') }}" required
                    placeholder="Ex: Call for tenders for 15 kV and 33 kV mobile substations">
                <p id="errtitle" class="mb-0 text-danger em"></p>
            </div>

            <div class="form-group">
                <label>URL Slug</label>
                <div class="slug-input-group">
                    <input id="slugInput" type="text" class="form-control ltr" name="slug" value="{{ $old('slug') }}"
                        placeholder="Leave blank to auto-generate from title">
                    @if ($isEdit)
                        <button type="button" id="regenerateSlugBtn" class="slug-regen-btn"
                            title="Rebuild a short, meaningful slug from the current title">
                            <i class="fas fa-sync-alt"></i> Regenerate
                        </button>
                    @endif
                </div>
                <p id="errslug" class="mb-0 text-danger em"></p>
                <p id="eerrslug" class="mb-0 text-danger em"></p>
                <p class="text-warning mb-0"><small>{{ $isEdit ? 'Changing the title above will NOT change this. Edit it here manually, or click Regenerate to rebuild it from the title above — the old URL redirects (301) automatically either way.' : 'Auto-generated from the title if left blank. Editable later without breaking existing links (old URL redirects automatically).' }}</small></p>
            </div>
            @if ($isEdit)
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        var btn = document.getElementById('regenerateSlugBtn');
                        var input = document.getElementById('slugInput');
                        if (!btn || !input) return;
                        btn.addEventListener('click', function () {
                            var title = (document.querySelector('[name="title"]').value || '').trim();
                            if (!title) {
                                window.jQuery && jQuery.notify
                                    ? jQuery.notify({ message: 'Enter a title first.' }, { type: 'warning' })
                                    : alert('Enter a title first.');
                                return;
                            }
                            btn.disabled = true;
                            btn.classList.add('is-loading');
                            fetch("{{ route('admin.slug.preview') }}", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                },
                                body: JSON.stringify({ title: title, module: 'portfolio', id: {{ $portfolio->id }} }),
                            })
                                .then(function (r) { return r.json(); })
                                .then(function (data) {
                                    if (!data.slug) return;
                                    input.value = data.slug;
                                    input.classList.remove('slug-just-regenerated');
                                    void input.offsetWidth;
                                    input.classList.add('slug-just-regenerated');
                                })
                                .catch(function () {
                                    alert('Could not regenerate the URL — try again.');
                                })
                                .finally(function () {
                                    btn.disabled = false;
                                    btn.classList.remove('is-loading');
                                });
                        });
                    });
                </script>
            @endif

            <div class="form-group">
                <label>Detailed Article **</label>
                <textarea id="portfolioContent" class="form-control summernote" name="content" required
                    placeholder="Describe the project in detail…" data-height="300">{{ $isEdit ? replaceBaseUrl($portfolio->content) : $old('content') }}</textarea>
                <p id="errcontent" class="mb-0 text-danger em"></p>
            </div>

            <div class="form-group">
                <label>Meta Keywords</label>
                <input class="form-control" name="meta_keywords" value="{{ $old('meta_keywords') }}"
                    placeholder="Enter meta keywords" data-role="tagsinput">
            </div>
            <div class="form-group mb-0">
                <label>Meta Description</label>
                <textarea class="form-control" name="meta_description" rows="4" placeholder="Enter meta description">{{ $old('meta_description') }}</textarea>
            </div>
        </div>

        <div class="portfolio-form-col mt-4">
            <h5><i class="fas fa-images"></i> Media</h5>

            <div class="row">
            <div class="col-lg-3">
            {{-- Featured / main image (serial=1) --}}
            <div class="form-group">
                <label>Main Image **</label>
                <br>
                <div class="thumb-preview" id="thumbPreview1">
                    <img src="{{ $isEdit && $portfolio->featured_image ? asset('assets/front/img/portfolios/featured/' . $portfolio->featured_image) : asset('assets/admin/img/noimage.jpg') }}"
                        alt="Featured Image">
                </div>
                <br><br>
                <input id="fileInput1" type="hidden" name="image">
                <button id="chooseImage1" class="choose-image btn btn-primary" type="button" data-multiple="false"
                    data-toggle="modal" data-target="#lfmModal1">Click to upload an image</button>
                <p class="text-warning mb-0">{{ allowed_image_extensions_label() }} images are allowed</p>
                <p class="text-warning mb-0"><small>Max file size: {{ max_upload_size_label('image') }}</small></p>
                <p class="text-warning mb-0"><small>Recommended size: 1200x1500px (4:5 portrait). Shown as a cropped card thumbnail across the site, and as the details-page hero image when no gallery images are added.</small></p>
                <p class="em text-danger mb-0" id="errimage"></p>

                <div class="modal fade lfm-modal" id="lfmModal1" tabindex="-1" role="dialog" aria-hidden="true">
                    <i class="fas fa-times-circle"></i>
                    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-body p-0">
                                <iframe src="{{ url('laravel-filemanager') }}?serial=1&callback=lfmNoop"
                                    style="width:100%;height:500px;overflow:hidden;border:none;"></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>

            <div class="col-lg-3">
            {{-- Client logo (serial=4) --}}
            <div class="form-group">
                <label>Client logo</label>
                <br>
                <div class="thumb-preview" id="thumbPreview4">
                    <img src="{{ $isEdit && $portfolio->client_logo ? asset('assets/front/img/portfolios/logos/' . $portfolio->client_logo) : asset('assets/admin/img/noimage.jpg') }}"
                        alt="Client Logo">
                </div>
                <br><br>
                <input id="fileInput4" type="hidden" name="client_logo">
                <button id="chooseImage4" class="choose-image btn btn-primary" type="button" data-multiple="false"
                    data-toggle="modal" data-target="#lfmModal4">Choose a logo</button>
                <p class="text-warning mb-0">{{ allowed_image_extensions_label() }} images are allowed</p>
                <p class="text-warning mb-0"><small>Max file size: {{ max_upload_size_label('image') }}</small></p>
                <p class="text-warning mb-0"><small>Recommended size: 300x100px (landscape, transparent background). Displayed small (22px tall) next to the client name.</small></p>

                <div class="modal fade lfm-modal" id="lfmModal4" tabindex="-1" role="dialog" aria-hidden="true">
                    <i class="fas fa-times-circle"></i>
                    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-body p-0">
                                <iframe src="{{ url('laravel-filemanager') }}?serial=4&callback=lfmNoop"
                                    style="width:100%;height:500px;overflow:hidden;border:none;"></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>

            <div class="col-lg-3">
            {{-- Gallery (serial=2, existing) --}}
            <div class="form-group">
                <label>Image Gallery **</label>
                <br>
                <div class="slider-thumbs" id="sliderThumbs2"></div>
                <input id="fileInput2" type="hidden" name="slider" value="" />
                <button id="chooseImage2" class="choose-image btn btn-primary" type="button" data-multiple="true"
                    data-toggle="modal" data-target="#lfmModal2">Add images</button>
                <p class="text-warning mb-0">{{ allowed_image_extensions_label() }} images are allowed</p>
                <p class="text-warning mb-0"><small>Max file size: {{ max_upload_size_label('image') }} per image</small></p>
                <p class="text-warning mb-0"><small>Recommended size: 1600x900px (16:9 landscape). Shown as the main details-page carousel image.</small></p>
                <p id="errslider" class="mb-0 text-danger em"></p>

                <div class="modal fade lfm-modal" id="lfmModal2" tabindex="-1" role="dialog" aria-hidden="true">
                    <i class="fas fa-times-circle"></i>
                    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-body p-0">
                                <iframe id="lfmIframe2"
                                    src="{{ url('laravel-filemanager') }}?serial=2&callback=lfmNoop{{ $isEdit ? '&portfolio=' . $portfolio->id : '' }}"
                                    style="width:100%;height:500px;overflow:hidden;border:none;"></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>

            <div class="col-lg-3">
            {{-- Documents (serial=3, LFM "file" category) --}}
            <div class="form-group">
                <label>Documents (optional)</label>
                <br>
                <div class="slider-thumbs" id="sliderThumbs3"></div>
                <input id="fileInput3" type="hidden" name="documents" value="" />
                <button id="chooseImage3" class="choose-image btn btn-primary" type="button" data-multiple="true"
                    data-toggle="modal" data-target="#lfmModal3">Add documents</button>
                <p class="text-warning mb-0">Formats: PDF, DOC, DOCX, XLSX</p>
                <p class="text-warning mb-0"><small>Max file size: {{ max_upload_size_label('file') }} per document</small></p>
                <p id="errdocuments" class="mb-0 text-danger em"></p>

                <div class="modal fade lfm-modal" id="lfmModal3" tabindex="-1" role="dialog" aria-hidden="true">
                    <i class="fas fa-times-circle"></i>
                    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-body p-0">
                                <iframe id="lfmIframe3"
                                    src="{{ url('laravel-filemanager') }}?type=file&serial=3&callback=lfmNoop{{ $isEdit ? '&documents=' . $portfolio->id : '' }}"
                                    style="width:100%;height:500px;overflow:hidden;border:none;"></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>
            </div>

            <input type="text" class="form-control d-none" name="tags" value="{{ $old('tags') }}">
        </div>

        {{-- Evidence of Competence — the 6 structured reference blocks (see
             doc: Problem -> ICA Mission -> Expertise -> Approach/Solution
             -> Result -> Impact). Plain textareas, one bullet per
             non-empty line on render — no rich text needed here. --}}
        <div class="portfolio-form-col mt-4">
            <h5><i class="fas fa-layer-group"></i> Evidence of Competence</h5>

            @php
                // Each block's icon is admin-chosen (picked from the same
                // icon-picker library used elsewhere in this admin — Approach
                // points, Statistics, Features, ...), stored in its own
                // <field>_icon column. Falls back to the block's original
                // hardcoded icon for rows saved before this existed.
                $blockIcons = [
                    'problematique' => 'fas fa-bullseye',
                    'mission_ica' => 'fas fa-file-signature',
                    'expertise_mobilisee' => 'fas fa-cogs',
                    'solution_approche' => 'fas fa-lightbulb',
                    'resultat_statut' => 'fas fa-chart-bar',
                    'impact' => 'fas fa-users',
                ];
            @endphp

            <div class="portfolio-block-grid">
                @foreach ([
                    ['field' => 'problematique', 'label' => 'Problem', 'rows' => 3, 'placeholder' => 'Describe the problem or the client\'s need…'],
                    ['field' => 'mission_ica', 'label' => 'Mission entrusted to ICA', 'rows' => 3, 'placeholder' => 'Describe the mission entrusted to ICA…'],
                    ['field' => 'expertise_mobilisee', 'label' => 'Expertise mobilized', 'rows' => 3, 'placeholder' => 'List the expertise mobilized by ICA… (one per line)'],
                    ['field' => 'solution_approche', 'label' => 'Solution / Approach', 'rows' => 3, 'placeholder' => 'Describe the solution or approach used… (one per line)'],
                    ['field' => 'resultat_statut', 'label' => 'Result / Status', 'rows' => 3, 'placeholder' => 'Present the results obtained or the current status… (one per line)'],
                    ['field' => 'impact', 'label' => 'Impact', 'rows' => 3, 'placeholder' => 'Specify the project\'s impact (if relevant)… (one per line)'],
                ] as $blk)
                    @php $blkIcon = $old($blk['field'] . '_icon', $blockIcons[$blk['field']]); @endphp
                    <div class="portfolio-block">
                        <div class="portfolio-block-head">
                            <label class="mb-0">{{ $blk['label'] }}</label>
                            <div class="portfolio-icon-picker">
                                <div class="btn-group d-block">
                                    <button type="button" class="btn btn-sm btn-secondary iconpicker-component" tabindex="-1"
                                        title="Choose an icon"><i class="{{ $blkIcon }}"></i></button>
                                    <button type="button" class="icp icp-dd btn btn-sm btn-secondary dropdown-toggle"
                                        data-toggle="dropdown"></button>
                                    <div class="dropdown-menu"></div>
                                </div>
                                <input type="hidden" class="portfolio-icon-input" name="{{ $blk['field'] }}_icon"
                                    value="{{ $blkIcon }}">
                            </div>
                        </div>
                        <textarea class="form-control" name="{{ $blk['field'] }}" rows="{{ $blk['rows'] }}"
                            placeholder="{{ $blk['placeholder'] }}">{{ $old($blk['field']) }}</textarea>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Carousel/hero banner overlay — left copy (badge/title/subtitle/
             desc over a controllable scrim) + right icon-highlights panel,
             both shared across every image in this portfolio's own gallery
             carousel (see front/portfolio-details.blade.php). Approved
             design: https://claude.ai/code/artifact/62a27330-ae43-4222-a702-a93fd0811d02 --}}
        <div class="portfolio-form-col mt-4">
            <h5><i class="fas fa-panorama"></i> Carousel Overlay</h5>
            <p class="text-warning"><small>Shown on top of this project's own gallery carousel. Title/subtitle/description fall back to Project Title / Summary when left blank.</small></p>

            <div class="row">
                <div class="col-lg-4">
                    <div class="form-group">
                        <label>Overlay Title</label>
                        <input type="text" class="form-control" name="overlay_title" value="{{ $old('overlay_title') }}"
                            placeholder="Ex: International Tender Notice AOI N°035/2026">
                        <p id="erroverlay_title" class="mb-0 text-danger em"></p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="form-group">
                        <label>Overlay Subtitle</label>
                        <input type="text" class="form-control" name="overlay_subtitle" value="{{ $old('overlay_subtitle') }}"
                            placeholder="Ex: Mobile substations 15 kV and 33 kV">
                        <p id="erroverlay_subtitle" class="mb-0 text-danger em"></p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="form-group">
                        <label>Overlay Description</label>
                        <input type="text" class="form-control" name="overlay_description" value="{{ $old('overlay_description') }}"
                            placeholder="Ex: On behalf of SONABEL">
                        <p id="erroverlay_description" class="mb-0 text-danger em"></p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-3">
                    <div class="form-group">
                        <label>Overlay Color</label>
                        <br>
                        <input type="color" id="overlayColorInput" class="jscolor-none" name="overlay_color"
                            value="#{{ $old('overlay_color', '060a09') }}"
                            style="width:44px;height:38px;padding:2px;border-radius:6px;border:1px solid #dee2e6;cursor:pointer;">
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="form-group">
                        <label>Overlay Opacity <small class="text-muted">(right panel + badge)</small> — <span id="overlayOpacityLabel">{{ $old('overlay_opacity', 82) }}%</span></label>
                        <input type="range" id="overlayOpacityInput" class="form-control-range" name="overlay_opacity"
                            min="30" max="95" value="{{ $old('overlay_opacity', 82) }}">
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="form-group">
                        <label>Left Bloom Opacity <small class="text-muted">(glow behind the copy)</small> — <span id="overlayBloomOpacityLabel">{{ $old('overlay_bloom_opacity') ?: $old('overlay_opacity', 82) }}%</span></label>
                        <input type="range" id="overlayBloomOpacityInput" class="form-control-range" name="overlay_bloom_opacity"
                            min="30" max="95" value="{{ $old('overlay_bloom_opacity') ?: $old('overlay_opacity', 82) }}">
                    </div>
                </div>
            </div>

            <hr class="portfolio-subdivider">

            <label>Icon Highlights <small class="text-muted">(right-side panel — drag to reorder)</small></label>
            <div id="highlightsList" class="highlights-list">
                @foreach (($isEdit ? $portfolio->highlights : collect()) as $h)
                    <div class="highlight-row">
                        <span class="highlight-drag-handle" title="Drag to reorder"><i class="fas fa-grip-vertical"></i></span>
                        <div class="portfolio-icon-picker">
                            <div class="btn-group d-block">
                                <button type="button" class="btn btn-sm btn-secondary iconpicker-component" tabindex="-1"
                                    title="Choose an icon"><i class="{{ $h->icon ?: 'fas fa-star' }}"></i></button>
                                <button type="button" class="icp icp-dd btn btn-sm btn-secondary dropdown-toggle"
                                    data-toggle="dropdown"></button>
                                <div class="dropdown-menu"></div>
                            </div>
                            <input type="hidden" class="portfolio-icon-input highlight-icon" value="{{ $h->icon ?: 'fas fa-star' }}">
                        </div>
                        <input type="text" class="form-control highlight-label" value="{{ convertUtf8($h->label) }}"
                            placeholder="Ex: Fast and deployable solution">
                        <button type="button" class="btn btn-sm btn-danger highlight-remove" title="Remove"><i class="fas fa-trash"></i></button>
                    </div>
                @endforeach
            </div>
            <button type="button" id="addHighlightBtn" class="btn btn-sm btn-secondary mt-2">
                <i class="fas fa-plus"></i> Add highlight
            </button>
            <input type="hidden" name="highlights_json" id="highlightsJson">
        </div>

    </div>

    <div class="col-lg-4 portfolio-sidebar-col">

        {{-- Language — first sidebar card, always the first thing visible.
             Sector/Status/Partners below all cascade FROM this
             (see create.blade.php's language-change AJAX handlers), so
             picking it here first — instead of scrolling down to a
             "Project Details" card further down the page — is the actual
             fix for the old back-and-forth. --}}
        <div class="portfolio-form-col is-language">
            <h5><i class="fas fa-globe"></i> Language</h5>
            <div class="form-group mb-0">
                <label>Language **</label>
                {{-- Editable on edit too now — changing it re-runs the same
                     language cascade AJAX as create (see edit.blade.php),
                     which clears+reloads Sector/Status/Partners for
                     the new language (those FK rows are language-scoped, so
                     old selections wouldn't exist under the new language). --}}
                <select id="language" name="language_id" class="form-control" required
                    data-current="{{ $isEdit ? $portfolio->language_id : '' }}">
                    <option value="" {{ $old('language_id') ? '' : 'selected' }} disabled>Select a
                        language</option>
                    @foreach ($langs as $lang)
                        <option value="{{ $lang->id }}"
                            {{ $old('language_id') == $lang->id ? 'selected' : '' }}>{{ $lang->name }}
                        </option>
                    @endforeach
                </select>
                <p id="errlanguage_id" class="mb-0 text-danger em"></p>
            </div>
        </div>

        <div class="portfolio-form-col">
            <h5><i class="fas fa-bullhorn"></i> Publishing</h5>

            <div class="form-group">
                <label>Visibility</label>
                @php $isPublishedVal = $old('is_published', 1); @endphp
                <select id="visibilitySelect" class="form-control ltr" name="is_published">
                    <option value="1" {{ $isPublishedVal == 1 ? 'selected' : '' }}>Published
                    </option>
                    <option value="0" {{ $isPublishedVal == 0 ? 'selected' : '' }}>Unpublished
                    </option>
                </select>
                <p class="text-warning mb-0"><small>The project will be visible on the website.</small>
                </p>
            </div>

            <div class="form-group">
                <label>Status **</label>
                <select id="portfolioStatuses" class="form-control ltr" name="status_id" required {{ $isEdit ? '' : 'disabled' }}>
                    <option value="" {{ $old('status_id') ? '' : 'selected' }} disabled>
                        Select a status</option>
                    @foreach ($statuses as $s)
                        <option value="{{ $s->id }}"
                            {{ $old('status_id') == $s->id ? 'selected' : '' }}>
                            {{ convertUtf8($s->name) }}</option>
                    @endforeach
                </select>
                <p id="errstatus_id" class="mb-0 text-danger em"></p>
            </div>

            <div class="form-group mb-0">
                <label>Serial Number **</label>
                <input type="number" class="form-control ltr" name="serial_number" required
                    value="{{ $old('serial_number') }}" placeholder="Enter Serial Number">
                <p id="errserial_number" class="mb-0 text-danger em"></p>
            </div>
        </div>

        <div class="portfolio-form-col">
            <h5><i class="fas fa-folder-open"></i> Organization</h5>

            <div class="form-group">
                <label>Client **</label>
                <input type="text" class="form-control" name="client_name" value="{{ $old('client_name') }}"
                    data-role="tagsinput" required placeholder="Ex: SONABEL">
                <p id="errclient_name" class="mb-0 text-danger em"></p>
            </div>

            <div class="form-group">
                <label>Sector</label>
                <select id="sectors" class="form-control" name="sector_id" {{ $isEdit ? '' : 'disabled' }}>
                    <option value="" {{ $old('sector_id') ? '' : 'selected' }} disabled>
                        Ex: Energy</option>
                    @foreach ($sectors as $sector)
                        <option value="{{ $sector->id }}"
                            {{ $old('sector_id') == $sector->id ? 'selected' : '' }}>
                            {{ convertUtf8($sector->name) }}</option>
                    @endforeach
                </select>
                <p id="errsector_id" class="mb-0 text-danger em"></p>
            </div>

            <div class="form-group">
                <label>Subsector</label>
                <select id="subsectors" class="form-control" name="subsector_id"
                    {{ $isEdit && $old('sector_id', $portfolio->sector_id ?? null) ? '' : 'disabled' }}>
                    <option value="" {{ $old('subsector_id') ? '' : 'selected' }} disabled>
                        Select a sector first</option>
                    @foreach ($subsectors as $subsector)
                        <option value="{{ $subsector->id }}"
                            {{ $old('subsector_id') == $subsector->id ? 'selected' : '' }}>
                            {{ convertUtf8($subsector->name) }}</option>
                    @endforeach
                </select>
                <p id="errsubsector_id" class="mb-0 text-danger em"></p>
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label>Country</label>
                        <select id="portfolioCountrySelect" class="form-control" name="country">
                            <option value="" {{ $old('country') ? '' : 'selected' }} disabled>Ex: Burkina Faso
                            </option>
                            @foreach ($countries as $c)
                                <option value="{{ $c['iso'] }}"
                                    {{ $old('country') == $c['iso'] ? 'selected' : '' }}>{{ $c['name'] }}</option>
                            @endforeach
                        </select>
                        <p id="errcountry" class="mb-0 text-danger em"></p>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label>Year</label>
                        <input type="text" class="form-control ltr" name="year" value="{{ $old('year') }}"
                            placeholder="Ex: 2026">
                        <p id="erryear" class="mb-0 text-danger em"></p>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Cost of Service ({{ $bex->base_currency_symbol }})</label>
                <input type="number" class="form-control" name="cost_of_service" id="cost_of_service"
                    value="{{ $old('cost_of_service') }}" placeholder="Enter cost of service" step="0.01"
                    min="0">
                <p id="errcost_of_service" class="mb-0 text-danger em"></p>
            </div>

            <div class="form-group mb-0">
                <label>Partners</label>
                {{-- Picked from the "Our Partners" module (App\Partner —
                     admin/home/partner) instead of free text, so a
                     partner's real logo can be shown against this
                     project. Cascades on language change same as
                     Sector/Status. --}}
                <select id="partnerIds" class="form-control" name="partner_ids[]" multiple
                    {{ $isEdit ? '' : 'disabled' }}>
                    @foreach ($partners as $partner)
                        <option value="{{ $partner->id }}"
                            {{ $isEdit && $portfolio->partnerRefs->contains('id', $partner->id) ? 'selected' : '' }}>
                            {{ convertUtf8($partner->name) }}</option>
                    @endforeach
                </select>
                <p id="errpartner_ids" class="mb-0 text-danger em"></p>
            </div>
        </div>

        <div class="portfolio-form-col">
            <h5><i class="fas fa-calendar-alt"></i> Timeline</h5>

            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label>Start Date</label>
                        <input id="startDate" type="text" class="form-control datepicker" name="start_date"
                            value="{{ $old('start_date') }}" placeholder="Enter start date" autocomplete="off">
                        <p id="errstart_date" class="mb-0 text-danger em"></p>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label>End Date</label>
                        @php
                            // end_date is a real `date` column (stored Y-m-d — MySQL
                            // requires that format, see PortfolioController's
                            // parseAdminDate()/endDateRule()); start_date/submission_date
                            // are legacy varchar columns storing whatever the datepicker
                            // typed verbatim (m/d/Y). Reformat back to m/d/Y here so the
                            // field DISPLAYS the same format as its siblings and as what
                            // was typed — only the display, not the DB storage, changes.
                            $endDateDisplay = $old('end_date');
                            if ($endDateDisplay && preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDateDisplay)) {
                                $endDateDisplay = \Carbon\Carbon::parse($endDateDisplay)->format('m/d/Y');
                            }
                        @endphp
                        <input id="endDate" type="text" class="form-control datepicker" name="end_date"
                            value="{{ $endDateDisplay }}" placeholder="Enter end date (leave blank if ongoing)"
                            autocomplete="off">
                        <p id="errend_date" class="mb-0 text-danger em"></p>
                        {{-- custom.js's #updateBtn (edit) success/error handlers look up
                             "eerr"+field, not "err"+field like #submitBtn (create) does —
                             a pre-existing id-prefix mismatch between the two shared
                             handlers. Harmless everywhere else since no other field had
                             a rule that could actually fail server-side on edit, but the
                             new End Date <= Submission Date rule below can — so it needs
                             a home under both prefixes to actually show on either page. --}}
                        <p id="eerrend_date" class="mb-0 text-danger em"></p>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Submission Date</label>
                <input id="submissionDate" type="text" class="form-control datepicker"
                    name="submission_date" value="{{ $old('submission_date') }}"
                    placeholder="Enter submission date" autocomplete="off">
                <p id="errsubmission_date" class="mb-0 text-danger em"></p>
                <p class="text-warning mb-0"><small>The tender's submission deadline. Once set, End Date
                        above can't be later than this.</small></p>
            </div>

            <div class="form-group mb-0">
                <label>Website Link</label>
                <input type="url" class="form-control" name="website_link" value="{{ $old('website_link') }}"
                    placeholder="Enter website link">
            </div>
        </div>

    </div>
</div>

<script>
    // Every LFM iframe on this page (main image, logo, gallery, documents)
    // already has its own working "Confirm" handler — see the app's own
    // override at resources/views/vendor/laravel-filemanager/index.blade.php
    // (a `data-action="use"` click listener that correctly fills
    // #fileInput<serial>/#sliderThumbs<serial>). The problem: that
    // override doesn't stop the STOCK LFM script (assets/lfm/js/script.js)
    // from ALSO handling the very same click via its own generic
    // `[data-action]` delegate — and the stock handler's `use()` function,
    // finding no `?callback=` param and no `window.opener` (this is an
    // iframe, not a popup), falls through to `window.open(item.url)` —
    // opening the just-picked file's raw front-end URL in a new tab right
    // after selecting it. That's the "it's again uploading in site!"
    // report: nothing actually re-uploads, but a document/image opening in
    // a fresh tab right after you picked it reads exactly like that.
    //
    // Passing `callback=lfmNoop` on each iframe's src reroutes that stock
    // fallback: `use()` finds `parent[callback]` (this function) truthy,
    // calls it (a deliberate no-op) instead of `window.open`, and marks
    // itself "succeeded" — which also skips its own `window.close()` call
    // (harmless here since `window.opener` is null anyway). The real,
    // working selection handling above is untouched either way.
    window.lfmNoop = function () {};
</script>

<script>
    // Wires up the 6 icon-pickers above. custom.js already auto-inits
    // every ".icp-dd" via `$('.icp-dd').iconpicker();` on page ready — the
    // one thing that pattern doesn't handle is more than one instance on
    // the same page (its own examples elsewhere all target a single
    // hardcoded #inputIcon). Scoping each picker to its own
    // .portfolio-icon-picker wrapper via closest() instead of a shared ID
    // is what lets all 6 coexist.
    document.addEventListener('DOMContentLoaded', function () {
        if (!window.jQuery) return;
        var $ = window.jQuery;

        // Shared by the 6 fixed Evidence-of-Competence pickers (present at
        // page load — custom.js's own blanket `$('.icp-dd').iconpicker()`
        // ready-call already covers those) AND every highlight row's own
        // picker, including ones added later by "Add highlight" — those
        // never existed when custom.js's ready-call ran, so `freshInit`
        // tells this function to call `.iconpicker()` on them itself first.
        window.wirePortfolioIconPicker = function ($wrap, freshInit) {
            var savedIcon = $wrap.find('.portfolio-icon-input').val();
            function applySavedIcon() {
                if (savedIcon) $wrap.find('.iconpicker-component i').attr('class', savedIcon);
            }
            // custom.js's own `.iconpicker()` call (no `selected` option
            // passed) resets the preview <i> to blank as its LAST init
            // step, then fires "iconpickerCreated" — listening for that
            // (attached here, before custom.js's ready-queued init call
            // actually runs) re-applies the real saved icon right after,
            // instead of guessing at a timeout that runs before or after.
            $wrap.find('.icp').on('iconpickerCreated', applySavedIcon);
            // Belt-and-braces in case "iconpickerCreated" already fired
            // by the time this attaches (init order isn't contractual).
            setTimeout(applySavedIcon, 0);

            $wrap.find('.icp').on('iconpickerSelected', function () {
                var iconClass = $wrap.find('.iconpicker-component i').attr('class');
                $wrap.find('.portfolio-icon-input').val(iconClass);
            });

            if (freshInit) $wrap.find('.icp-dd').iconpicker();
        };

        $('.portfolio-icon-picker').each(function () {
            window.wirePortfolioIconPicker($(this), false);
        });

        // Country <select> — searchable, with a flag next to each name.
        // The option's own `value` IS the ISO code already, so the flag
        // template is a direct lookup — no emoji-parsing needed (unlike
        // the #waCode dial-code picker elsewhere in the admin, which
        // decodes a flag emoji baked into its option text instead).
        function countryFlagTemplate(opt) {
            if (!opt.id) return opt.text;
            var iso = opt.id.toLowerCase();
            return $(
                '<span><span class="fi fi-' + iso + '"></span> ' + opt.text + '</span>'
            );
        }
        // Deferred to window 'load' — verified by instrumentation (logging
        // every $.fn.select2 call with a timestamp) that a plain
        // setTimeout(fn, 0) here was NOT enough: custom.js's own generic
        // `$('.select2').select2();` (its jQuery-ready handler) actually
        // fired ~66ms AFTER our setTimeout(0) callback in testing — later
        // than the very next tick, for reasons not worth chasing further
        // (jQuery's internal ready-deferred resolution isn't a plain
        // synchronous DOMContentLoaded listener). Since select2's
        // generated wrapper span carries "select2" as one of ITS OWN
        // classes, that generic pass running AFTER ours exists matches
        // the CONTAINER and re-initializes IT as if it were a select —
        // corrupting it (collapsed to 1px, hidden, unreadable — the
        // original bug report). 'load' is the one point genuinely
        // guaranteed later than any jQuery-ready handler on the page
        // (DOMContentLoaded/ready always resolve before 'load' fires, by
        // spec) — creating our container only then means there is
        // nothing left on the page that could still re-catch it.
        window.addEventListener('load', function () {
            $('#portfolioCountrySelect').select2({
                width: '100%',
                templateResult: countryFlagTemplate,
                templateSelection: countryFlagTemplate,
            });

            // Sector / Status — same treatment: same disabled-until-
            // language-picked lifecycle, same reskin, same width fix.
            $('#sectors').select2({ width: '100%' });
            $('#portfolioStatuses').select2({ width: '100%' });
            $('#subsectors').select2({ width: '100%' });

            // Sector -> Subsector cascade — same AJAX-swap-options pattern
            // as Language -> Sector/Status/Partners (create/edit
            // .blade.php), just one level deeper. Picking a DIFFERENT
            // sector always resets Subsector (a subsector from the old
            // sector wouldn't be valid under the new one).
            $('#sectors').on('change', function () {
                var sectorId = $(this).val();
                if (!sectorId) {
                    $('#subsectors').html('<option value="" selected disabled>Select a sector first</option>').prop('disabled', true).trigger('change');
                    return;
                }
                // Loading state — the fetch is usually near-instant, but on a
                // slow connection the select otherwise just sits on whatever
                // the PREVIOUS sector's subsectors were for a moment, which
                // reads as "did my click even register?". Disabling it during
                // the fetch also blocks picking a stale option mid-request.
                $('#subsectors').prop('disabled', true)
                    .html('<option value="" selected disabled>Loading subsectors…</option>')
                    .trigger('change');
                $.get("{{ url('/') }}/admin/portfolio/sector/" + sectorId + "/get_subsectors", function (data) {
                    var options;
                    if (data.length === 0) {
                        // Empty state — a real, distinct message instead of
                        // either a blank list or the generic "Select a
                        // subsector" prompt, which would look identical to
                        // "you haven't picked one yet" even though there's
                        // genuinely nothing TO pick for this sector.
                        options = '<option value="" selected disabled>No subsectors for this sector</option>';
                    } else {
                        options = '<option value="" selected disabled>Select a subsector</option>';
                        for (var i = 0; i < data.length; i++) {
                            options += '<option value="' + data[i].id + '">' + data[i].name + '</option>';
                        }
                    }
                    $('#subsectors').html(options).prop('disabled', data.length === 0).trigger('change');
                }).fail(function () {
                    // Was previously silent on a network/server error — the
                    // select would just keep showing "Loading subsectors…"
                    // forever with no indication anything went wrong.
                    $('#subsectors')
                        .html('<option value="" selected disabled>Could not load subsectors — try again</option>')
                        .prop('disabled', true)
                        .trigger('change');
                });
            });

            // Partners — multi-select, same disabled-until-language-picked
            // lifecycle. `multiple` on the underlying <select> is all
            // select2 needs to render it as a tag-chip picker.
            $('#partnerIds').select2({ width: '100%', placeholder: 'Select partners' });

            // Visibility — plain 2-option select, not language-gated, no
            // disabled-lifecycle needed. Same reskin/width fix as the rest.
            $('#visibilitySelect').select2({ width: '100%', minimumResultsForSearch: -1 });

            // Language — now editable on edit too (was disabled/hidden-input
            // locked before). Same reskin as its siblings.
            $('#language').select2({ width: '100%' });
        });
    });
</script>

<script>
    // Every field flagged ** above is `required` in the backend validator
    // (PortfolioController@store/@update) — this enforces the same list on
    // the client so a missing field is caught instantly instead of only
    // after a round trip. Plain HTML5 `required` attributes don't work
    // here: custom.js's #submitBtn handler is a plain 'click' listener that
    // builds the AJAX request directly (it doesn't go through the form's
    // native submit/constraint-validation step at all), and two of these
    // fields (image, slider) are `type="hidden"` — the spec bars hidden
    // inputs from constraint validation entirely regardless of `required`.
    //
    // Registered on `document` with capture:true so it always runs BEFORE
    // custom.js's own bubble-phase listener on #submitBtn — capturing
    // listeners on an ancestor fire before any listener on the target
    // itself, no matter which script tag ran first. Calling
    // stopPropagation() here when invalid stops the event before it ever
    // reaches that handler, so no AJAX call goes out.
    document.addEventListener('click', function (e) {
        if (!e.target || e.target.id !== 'submitBtn' || !window.jQuery) return;
        var $ = window.jQuery;

        // Highlights: serialize the list's CURRENT DOM order (drag-drop
        // reordering only ever moves the actual .highlight-row elements —
        // there's no separate "position" field to keep in sync) into JSON
        // right before every submit attempt, valid or not, so it's always
        // fresh for PortfolioController::storeHighlights().
        if (typeof window.serializePortfolioHighlights === 'function') {
            window.serializePortfolioHighlights();
        }

        var isEdit = {{ $isEdit ? 'true' : 'false' }};
        var required = [
            { name: 'title' },
            { name: 'content', summernote: true },
            { name: 'client_name' },
            { name: 'status_id' },
            { name: 'serial_number' },
            // Always resubmitted in full on save (see storeDocuments/
            // update() comments) — required on create AND edit.
            { name: 'slider' },
        ];
        if (!isEdit) {
            required.push({ name: 'image' }, { name: 'language_id' });
        }

        $('.em').each(function () { $(this).html(''); });

        var firstInvalid = null;
        required.forEach(function (f) {
            var value;
            if (f.summernote) {
                // custom.js's own submit handlers (#submitBtn/#updateBtn)
                // locate the summernote instance the same way — by its
                // `.summernote` class, not `#portfolioContent` — because
                // this build reassigns the textarea's `id` to a random
                // string on init (confirmed live: renders as e.g.
                // `_5o536vi2e`), leaving only the `name` and class stable.
                value = $('#ajaxForm .summernote').summernote('code').replace(/<[^>]*>/g, '').trim();
            } else {
                value = ($('[name="' + f.name + '"]').first().val() || '').toString().trim();
            }
            if (!value) {
                var $err = $('#err' + f.name);
                if ($err.length) $err.html('This field is required.');
                if (!firstInvalid) firstInvalid = f.name;
            }
        });

        // End Date must never be later than Submission Date (the tender's
        // submission deadline) — but ONLY once a Submission Date is
        // actually set. No Submission Date yet = no restriction at all.
        // Mirrors PortfolioController::endDateRule() server-side; this is
        // just the instant, no-round-trip version of the same rule.
        var notifyMsg = 'Please fill in all required fields.';
        var endVal = ($('#endDate').val() || '').trim();
        var subVal = ($('#submissionDate').val() || '').trim();
        if (endVal && subVal) {
            var endD = new Date(endVal);
            var subD = new Date(subVal);
            if (!isNaN(endD) && !isNaN(subD) && endD > subD) {
                var msg = 'End Date must be on or before the Submission Date.';
                $('#errend_date, #eerrend_date').html(msg);
                if (!firstInvalid) {
                    firstInvalid = 'end_date';
                    notifyMsg = msg;
                }
            }
        }

        if (firstInvalid) {
            e.preventDefault();
            e.stopPropagation();
            $.notify({
                message: notifyMsg,
                title: 'Validation Error!',
                icon: 'fa fa-bell',
            }, {
                type: 'danger',
                placement: { from: 'top', align: 'right' },
                showProgressbar: true,
                time: 1000,
                allow_dismiss: true,
                delay: 4000,
            });
        }
    }, true);
</script>

<script>
    // Icon Highlights — drag-to-reorder list (jQuery UI's sortable, already
    // loaded site-wide in admin/partials/scripts.blade.php — no new
    // dependency) + add/remove rows + serialize to JSON before submit.
    document.addEventListener('DOMContentLoaded', function () {
        if (!window.jQuery || !window.jQuery.fn.sortable) return;
        var $ = window.jQuery;
        var $list = $('#highlightsList');

        $list.sortable({
            handle: '.highlight-drag-handle',
            axis: 'y',
            placeholder: 'highlight-row-placeholder',
            forcePlaceholderSize: true,
        });

        function highlightRowTemplate() {
            return $(
                '<div class="highlight-row">' +
                    '<span class="highlight-drag-handle" title="Drag to reorder"><i class="fas fa-grip-vertical"></i></span>' +
                    '<div class="portfolio-icon-picker">' +
                        '<div class="btn-group d-block">' +
                            '<button type="button" class="btn btn-sm btn-secondary iconpicker-component" tabindex="-1" title="Choose an icon"><i class="fas fa-star"></i></button>' +
                            '<button type="button" class="icp icp-dd btn btn-sm btn-secondary dropdown-toggle" data-toggle="dropdown"></button>' +
                            '<div class="dropdown-menu"></div>' +
                        '</div>' +
                        '<input type="hidden" class="portfolio-icon-input highlight-icon" value="fas fa-star">' +
                    '</div>' +
                    '<input type="text" class="form-control highlight-label" placeholder="Ex: Fast and deployable solution">' +
                    '<button type="button" class="btn btn-sm btn-danger highlight-remove" title="Remove"><i class="fas fa-trash"></i></button>' +
                '</div>'
            );
        }

        $('#addHighlightBtn').on('click', function () {
            var $row = highlightRowTemplate();
            $list.append($row);
            $list.sortable('refresh');
            if (typeof window.wirePortfolioIconPicker === 'function') {
                window.wirePortfolioIconPicker($row.find('.portfolio-icon-picker'), true);
            }
        });

        // Delegated — covers rows already in the DOM at load AND every row
        // added afterward via the button above.
        $list.on('click', '.highlight-remove', function () {
            $(this).closest('.highlight-row').remove();
        });

        window.serializePortfolioHighlights = function () {
            var items = [];
            $list.find('.highlight-row').each(function () {
                var $row = $(this);
                var label = $row.find('.highlight-label').val();
                if (!label || !label.trim()) return;
                items.push({
                    icon: $row.find('.highlight-icon').val() || 'fas fa-star',
                    label: label.trim(),
                });
            });
            $('#highlightsJson').val(JSON.stringify(items));
        };
    });

    // Overlay opacity slider — live label, matching the approved artifact's
    // own control (percent shown next to the slider, not just the raw input).
    document.addEventListener('DOMContentLoaded', function () {
        [
            ['overlayOpacityInput', 'overlayOpacityLabel'],
            ['overlayBloomOpacityInput', 'overlayBloomOpacityLabel'],
        ].forEach(function (pair) {
            var range = document.getElementById(pair[0]);
            var label = document.getElementById(pair[1]);
            if (!range || !label) return;
            range.addEventListener('input', function () {
                label.textContent = range.value + '%';
            });
        });
    });
</script>
