{{--
    Shared "SEO & Social Preview" box — Yoast/RankMath-style SEO title +
    social share image fields, with a live Google SERP + social card
    preview that updates as the admin types. Included on Blog/Portfolio/
    Service create & edit forms.

    Required:
      $seoSerial          int    — unique LFM modal serial on this page (don't reuse one already in use)
    Optional:
      $seoMetaTitle        string|null  — current meta_title value
      $seoOgImageUrl       string|null  — current og_image absolute URL, if already set
      $seoFallbackImageUrl string|null  — the content's own main/featured image URL, used as the preview's fallback when no override is set
      $seoPreviewUrl       string|null  — the real front-end URL this content will live at (SERP green URL line); null on "create" (not saved yet)
      $seoSiteName         string|null  — site name, shown in the SERP/social preview
      $seoTitleSelector    string       — CSS selector for this page's own "title" input (default '[name="title"]')
      $seoDescSelector     string       — CSS selector for this page's own "meta_description" textarea (default '[name="meta_description"]')
--}}
@php
    $seoMetaTitle = $seoMetaTitle ?? '';
    $seoOgImageUrl = $seoOgImageUrl ?? null;
    $seoFallbackImageUrl = $seoFallbackImageUrl ?? null;
    $seoPreviewUrl = $seoPreviewUrl ?? url('/');
    $seoSiteName = $seoSiteName ?? config('app.name');
    $seoTitleSelector = $seoTitleSelector ?? '[name="title"]';
    $seoDescSelector = $seoDescSelector ?? '[name="meta_description"]';
    $seoBoxId = 'seoMetaBox' . $seoSerial;
@endphp

<div class="portfolio-form-col seo-meta-box" id="{{ $seoBoxId }}" data-serial="{{ $seoSerial }}"
    data-site-name="{{ $seoSiteName }}" data-preview-url="{{ $seoPreviewUrl }}"
    data-title-selector="{{ $seoTitleSelector }}" data-desc-selector="{{ $seoDescSelector }}">
    <h5><i class="fas fa-search"></i> SEO &amp; Social Preview</h5>

    <div class="form-group">
        <label>SEO Title</label>
        <input type="text" class="form-control seo-meta-title-input" name="meta_title" value="{{ $seoMetaTitle }}"
            maxlength="70" placeholder="Leave blank to use the title above">
        <p class="seo-char-counter mb-0"><span class="seo-char-count">0</span>/60 characters</p>
    </div>

    <div class="form-group">
        <label>Social Share Image</label>
        <br>
        <div class="thumb-preview seo-og-thumb" id="thumbPreview{{ $seoSerial }}">
            <img src="{{ $seoOgImageUrl ?: ($seoFallbackImageUrl ?: asset('assets/admin/img/noimage.jpg')) }}" alt="Social share image">
        </div>
        <br>
        <input id="fileInput{{ $seoSerial }}" type="hidden" name="og_image">
        <button id="chooseImage{{ $seoSerial }}" class="choose-image btn btn-primary" type="button"
            data-multiple="false" data-toggle="modal" data-target="#lfmModal{{ $seoSerial }}">Choose Image</button>
        <p class="text-warning mb-0"><small>Recommended size: 1200x630px. Falls back to the main image above when left unset.</small></p>

        <div class="modal fade lfm-modal" id="lfmModal{{ $seoSerial }}" tabindex="-1" role="dialog" aria-hidden="true">
            <i class="fas fa-times-circle"></i>
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-body p-0">
                        <iframe src="{{ url('laravel-filemanager') }}?serial={{ $seoSerial }}"
                            style="width: 100%; height: 500px; overflow: hidden; border: none;"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="seo-preview-tabs">
        <div class="seo-preview-block">
            <p class="seo-preview-label">Google Search Preview</p>
            <div class="seo-serp-card">
                <div class="seo-serp-site">
                    <span class="seo-serp-favicon"></span>
                    <span class="seo-serp-sitename"></span>
                </div>
                <div class="seo-serp-title"></div>
                <div class="seo-serp-url"></div>
                <div class="seo-serp-desc"></div>
            </div>
        </div>
        <div class="seo-preview-block">
            <p class="seo-preview-label">Social Share Preview</p>
            <div class="seo-social-card">
                <div class="seo-social-image" style="background-image:url('{{ $seoOgImageUrl ?: $seoFallbackImageUrl }}')"></div>
                <div class="seo-social-body">
                    <div class="seo-social-domain"></div>
                    <div class="seo-social-title"></div>
                    <div class="seo-social-desc"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .seo-meta-box .seo-char-counter { font-size: 12px; color: #8a94a6; }
    .seo-meta-box .seo-char-counter.is-warn { color: #e2a03f; }
    .seo-meta-box .seo-char-counter.is-over { color: #e34361; }

    .seo-preview-tabs { display: flex; flex-wrap: wrap; gap: 20px; margin-top: 10px; }
    .seo-preview-block { flex: 1 1 320px; min-width: 0; }
    .seo-preview-label { font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: #8a94a6; margin-bottom: 6px; }

    .seo-serp-card { font-family: arial, sans-serif; background: #fff; border: 1px solid #e2e5ec; border-radius: 8px; padding: 14px 16px; }
    .seo-serp-site { display: flex; align-items: center; gap: 8px; margin-bottom: 4px; }
    .seo-serp-favicon { width: 18px; height: 18px; border-radius: 50%; background: #e2e5ec; flex: 0 0 auto; }
    .seo-serp-sitename { font-size: 13px; color: #202124; }
    .seo-serp-title { font-size: 18px; line-height: 1.3; color: #1a0dab; margin-bottom: 2px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .seo-serp-url { font-size: 12px; color: #006621; margin-bottom: 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .seo-serp-desc { font-size: 13px; line-height: 1.4; color: #4d5156; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }

    .seo-social-card { border: 1px solid #dadde1; border-radius: 8px; overflow: hidden; background: #fff; }
    .seo-social-image { width: 100%; height: 150px; background-color: #e2e5ec; background-size: cover; background-position: center; }
    .seo-social-body { padding: 10px 12px; }
    .seo-social-domain { font-size: 11px; text-transform: uppercase; color: #8a8d91; margin-bottom: 2px; }
    .seo-social-title { font-size: 14px; font-weight: 600; color: #1d2129; line-height: 1.3; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .seo-social-desc { font-size: 12.5px; color: #606770; line-height: 1.35; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
</style>

<script>
    (function () {
        document.addEventListener('DOMContentLoaded', function () {
            var box = document.getElementById('{{ $seoBoxId }}');
            if (!box) return;

            var serial = box.dataset.serial;
            var siteName = box.dataset.siteName || '';
            var previewUrl = box.dataset.previewUrl || '';
            var titleInput = document.querySelector(box.dataset.titleSelector);
            var descInput = document.querySelector(box.dataset.descSelector);
            var metaTitleInput = box.querySelector('.seo-meta-title-input');
            var charCount = box.querySelector('.seo-char-count');
            var charCounter = box.querySelector('.seo-char-counter');
            var ogThumb = box.querySelector('#thumbPreview' + serial + ' img');

            var serpSitename = box.querySelector('.seo-serp-sitename');
            var serpTitle = box.querySelector('.seo-serp-title');
            var serpUrl = box.querySelector('.seo-serp-url');
            var serpDesc = box.querySelector('.seo-serp-desc');
            var socialDomain = box.querySelector('.seo-social-domain');
            var socialTitle = box.querySelector('.seo-social-title');
            var socialDesc = box.querySelector('.seo-social-desc');
            var socialImage = box.querySelector('.seo-social-image');

            var hostname = (function () {
                try { return new URL(previewUrl).hostname; } catch (e) { return previewUrl; }
            })();

            function refresh() {
                var fallbackTitle = titleInput ? titleInput.value.trim() : '';
                var seoTitle = (metaTitleInput.value.trim() || fallbackTitle || 'Untitled');
                var desc = descInput ? descInput.value.trim() : '';

                charCount.textContent = metaTitleInput.value.length;
                charCounter.classList.remove('is-warn', 'is-over');
                if (metaTitleInput.value.length > 60) charCounter.classList.add('is-over');
                else if (metaTitleInput.value.length > 50) charCounter.classList.add('is-warn');

                serpSitename.textContent = siteName;
                serpTitle.textContent = seoTitle;
                serpUrl.textContent = hostname + (previewUrl.replace(/^https?:\/\/[^\/]+/, '') || '');
                serpDesc.textContent = desc || 'No meta description set — search engines may generate one automatically.';

                socialDomain.textContent = hostname;
                socialTitle.textContent = seoTitle;
                socialDesc.textContent = desc;
            }

            // Exposed so this page's own LFM SetUrl handler (see the generic
            // one defined once per page, keyed off the active modal's
            // serial) can refresh the social-card image the instant a new
            // one is picked, without this box needing its own SetUrl copy.
            window.__seoPreviewRefreshImage = window.__seoPreviewRefreshImage || function (changedSerial, url) {
                if (String(changedSerial) !== String(serial)) return;
                if (ogThumb) ogThumb.src = url;
                if (socialImage) socialImage.style.backgroundImage = "url('" + url + "')";
            };

            if (metaTitleInput) metaTitleInput.addEventListener('input', refresh);
            if (titleInput) titleInput.addEventListener('input', refresh);
            if (descInput) descInput.addEventListener('input', refresh);
            refresh();

            // Generic LFM SetUrl — only defined if some other mechanism on
            // this page hasn't already provided one. Reads whichever
            // .lfm-modal is currently open to know which serial's
            // fileInput/thumbPreview to fill, so one definition covers
            // every LFM picker on the page, not just this box's own.
            if (!window.SetUrl) {
                window.SetUrl = function (items) {
                    var activeModal = document.querySelector('.lfm-modal.show');
                    if (!activeModal || !items || !items.length) return;
                    var activeSerial = activeModal.id.replace('lfmModal', '');
                    var fileInput = document.getElementById('fileInput' + activeSerial);
                    var thumbPreview = document.getElementById('thumbPreview' + activeSerial);
                    var url = items[0].url;

                    if (fileInput) fileInput.value = url;
                    if (thumbPreview) {
                        var img = thumbPreview.querySelector('img');
                        if (img) img.src = url;
                    }
                    if (window.__seoPreviewRefreshImage) window.__seoPreviewRefreshImage(activeSerial, url);

                    if (window.jQuery) jQuery(activeModal).modal('hide');
                };
            }
        });
    })();
</script>
