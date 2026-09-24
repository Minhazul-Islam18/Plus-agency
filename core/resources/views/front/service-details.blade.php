@extends("front.$version.layout")

@section('pagename')
    - {{ __('Service') }} - {{ convertUtf8($service->title) }}
@endsection

@section('meta-keywords', "$service->meta_keywords")
@section('meta-description', "$service->meta_description")

@section('breadcrumb-title', convertUtf8($bs->service_details_title))
@section('breadcrumb-subtitle', \Illuminate\Support\Str::limit(convertUtf8($service->title), 40))
@section('breadcrumb-link', __('Service Details'))

@if (!empty($bs->service_breadcrumb_bg))
    @section('breadcrumb-bg', asset('assets/front/img/' . $bs->service_breadcrumb_bg))
@endif

@if (!empty($bs->service_breadcrumb_overlay_color))
    @section('breadcrumb-overlay-color', $bs->service_breadcrumb_overlay_color)
@endif

@if (!empty($bs->service_breadcrumb_overlay_opacity))
    @section('breadcrumb-overlay-opacity', $bs->service_breadcrumb_overlay_opacity)
@endif

@if ($be->theme_version == 'dark')
    @section('styles')
        <style>
            /* Content images get wrapped in this (see the lightbox script
               below) so there's a real hint that they're clickable — not
               just a cursor:zoom-in that only mouse users would ever
               notice. The icon sits at a low resting opacity (visible on
               touch too, which has no hover state at all) and brightens
               with a gentle scale on hover. */
            .dark-svc-img-zoomable {
                position: relative;
                display: inline-block;
                cursor: zoom-in;
                max-width: 100%;
                border-radius: 8px;
                overflow: hidden;
            }
            .dark-svc-img-zoomable img {
                display: block;
                transition: transform 0.45s cubic-bezier(0.2, 0.7, 0.2, 1), filter 0.35s ease;
            }
            .dark-svc-img-zoomable:hover img {
                transform: scale(1.035);
                filter: brightness(0.82);
            }
            .dark-svc-img-zoom-icon {
                position: absolute;
                inset: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                pointer-events: none;
            }
            .dark-svc-img-zoom-icon i {
                width: 42px;
                height: 42px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                /* Confirmed live: the glyph itself wasn't rendering — the
                   circle showed, but its ::before font-family resolved to
                   this page's own body font instead of Font Awesome, so
                   the icon codepoint had no matching glyph to draw. Forcing
                   both here (not just relying on .fas's own rule/
                   inheritance) is the standard fix once something else in
                   the cascade wins that fight. */
                font-family: "Font Awesome 5 Free" !important;
                font-weight: 900 !important;
                font-size: 15px;
                color: #08130d;
                background: linear-gradient(135deg, var(--accent, #25d06f), var(--accent-2, #2dd4bf));
                box-shadow: 0 8px 20px -6px rgba(0, 0, 0, 0.55);
                opacity: 0.7;
                transform: scale(0.9);
                transition: opacity 0.3s ease, transform 0.3s cubic-bezier(0.2, 0.7, 0.2, 1);
            }
            .dark-svc-img-zoomable:hover .dark-svc-img-zoom-icon i {
                opacity: 1;
                transform: scale(1);
            }
            /* One brief pulse per image, a beat after the page settles —
               draws the eye once without nagging on every render or
               looping forever. */
            @keyframes darkSvcImgZoomPulse {
                0% { box-shadow: 0 8px 20px -6px rgba(0, 0, 0, 0.55), 0 0 0 0 rgba(var(--accent-rgb), 0.55); }
                70% { box-shadow: 0 8px 20px -6px rgba(0, 0, 0, 0.55), 0 0 0 14px rgba(var(--accent-rgb), 0); }
                100% { box-shadow: 0 8px 20px -6px rgba(0, 0, 0, 0.55), 0 0 0 0 rgba(var(--accent-rgb), 0); }
            }
            .dark-svc-img-zoom-icon i.is-pulsing {
                animation: darkSvcImgZoomPulse 1.4s ease-out;
            }
        </style>
    @endsection
@endif

@section('content')

@if ($be->theme_version == 'dark')
    <!--    dark service details section start   -->
    <div class="dark-svcp-section dark-svc-list-section">
        <div class="dark-svcp-inner @if ($service->sidebar != 1) dark-svcp-inner--full @endif">
            <div>
                <div class="dark-svcd-panel reveal-left">
                    <div class="dark-service-details">
                        {!! replaceBaseUrl(convertUtf8($service->content)) !!}
                    </div>
                </div>
            </div>
            @if ($service->sidebar == 1)
                <div>
                    <div class="dark-svcp-widget">
                        <form class="dark-svcp-search" action="{{ route('front.services') }}">
                            <input name="category" type="hidden" value="{{ request()->input('category') }}">
                            <input name="term" type="text" placeholder="{{ __('Search Services') }}"
                                value="{{ request()->input('term') }}">
                            <button type="submit"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><circle cx="11" cy="11" r="7" /><path d="M21 21l-4.3-4.3" /></svg></button>
                        </form>
                    </div>

                    @if (serviceCategory())
                        <div class="dark-svcp-widget">
                            <h4>{{ __('Categories') }}</h4>
                            <ul class="dark-svcp-cat-list">
                                <li>
                                    <a href="{{ route('front.services') }}">
                                        <span class="dark-pf-cat-icon"><i class="fas fa-th-large"></i></span>
                                        <span class="dark-pf-cat-name">{{ __('All categories') }}</span>
                                        <span class="dark-pf-cat-count">{{ $servicesCount }}</span>
                                        <span class="dark-pf-cat-chevron"><i class="fas fa-chevron-right"></i></span>
                                    </a>
                                </li>
                                @foreach ($scats as $key => $scat)
                                    <li class="{{ !empty($service->scategory) && $service->scategory->id == $scat->id ? 'is-active' : '' }}">
                                        <a href="{{ route('front.services', ['category' => $scat->id]) }}">
                                            <span class="dark-pf-cat-icon"><i class="fas fa-tag"></i></span>
                                            <span class="dark-pf-cat-name">{{ convertUtf8($scat->name) }}</span>
                                            <span class="dark-pf-cat-count">{{ $scategoryCounts->get($scat->id, 0) }}</span>
                                            <span class="dark-pf-cat-chevron"><i class="fas fa-chevron-right"></i></span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="dark-svcp-widget dark-svcp-newsletter">
                        <span class="dark-bc-eyebrow">{{ __('SUBSCRIBE') }}</span>
                        <h4 class="dark-svcp-newsletter-title">{{ __('SUBSCRIBE FOR NEWSLETTER') }}</h4>
                        <form id="subscribeForm" class="dark-svcp-newsletter-form" action="{{ route('front.subscribe') }}"
                            method="POST">
                            @csrf
                            <input name="email" type="email" placeholder="{{ __('Email') }}">
                            <button type="submit">{{ __('Subscribe') }}</button>
                        </form>
                        <p id="erremail" class="text-danger mb-0 err-email"></p>
                    </div>
                </div>
            @endif
        </div>

        @if ($relatedServices->count() > 0)
            <div class="dark-pd-similar-section dark-svcrel-section">
                <div class="dark-pd-similar-head">
                    <h3 class="dark-pd-similar-title reveal-text">
                        {{ __('Related Services') }}
                        @if (!empty($service->scategory))
                            <span class="dark-svcrel-cat">&mdash; {{ convertUtf8($service->scategory->name) }}</span>
                        @endif
                        <span class="dark-pd-similar-rule"></span>
                    </h3>
                    @if ($relatedServices->count() > 1)
                        <div class="dark-pd-similar-nav">
                            <button type="button" id="svcRelPrev" aria-label="{{ __('Previous') }}"><svg viewBox="0 0 24 24"><path d="M15 6l-6 6 6 6" /></svg></button>
                            <button type="button" id="svcRelNext" aria-label="{{ __('Next') }}"><svg viewBox="0 0 24 24"><path d="M9 6l6 6-6 6" /></svg></button>
                        </div>
                    @endif
                </div>
                <div class="owl-carousel dark-svcrel-carousel" id="svcRelCarousel">
                    @foreach ($relatedServices as $rs)
                        <div class="dark-svcp-card">
                            <div class="dark-svcp-img-wrap">
                                <span class="dark-svcp-num">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <img src="{{ asset('assets/front/img/services/' . $rs->main_image) }}" alt="{{ convertUtf8($rs->title) }}">
                            </div>
                            <div class="dark-svcp-body">
                                <h3><a @if ($rs->details_page_status == 1) href="{{ route('front.servicedetails', [$rs->slug]) }}" @endif>{{ convertUtf8($rs->title) }}</a></h3>
                                <p>{{ \Illuminate\Support\Str::limit(strip_tags($rs->summary), 150) }}</p>
                                @if ($rs->details_page_status == 1)
                                    <a href="{{ route('front.servicedetails', [$rs->slug]) }}" class="dark-svcp-link">
                                        {{ __('Read More') }}
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Image lightbox for images embedded in the service's own WYSIWYG
         content — same component (markup/CSS) as the Portfolio details
         page's gallery lightbox, own IDs/script since there's no gallery
         carousel here to stay in sync with on close, just plain content
         images. --}}
    <div class="dark-img-lightbox" id="svcImgLightbox">
        <button type="button" class="dark-img-lightbox-close" aria-label="{{ __('Close') }}">
            <svg viewBox="0 0 24 24">
                <path d="M6 6l12 12M18 6L6 18" />
            </svg>
        </button>
        <button type="button" class="dark-img-lightbox-nav dark-img-lightbox-prev" aria-label="{{ __('Previous') }}">
            <svg viewBox="0 0 24 24">
                <path d="M15 6l-6 6 6 6" />
            </svg>
        </button>
        <button type="button" class="dark-img-lightbox-nav dark-img-lightbox-next" aria-label="{{ __('Next') }}">
            <svg viewBox="0 0 24 24">
                <path d="M9 6l6 6-6 6" />
            </svg>
        </button>
        <div class="dark-img-lightbox-stage">
            <img class="dark-img-lightbox-img" id="svcLightboxImg" src="" alt="">
        </div>
        <div class="dark-img-lightbox-zoombar">
            <button type="button" class="dark-img-lightbox-zbtn" data-zoom-out aria-label="{{ __('Zoom out') }}">−</button>
            <button type="button" class="dark-img-lightbox-zbtn dark-img-lightbox-zreset" data-zoom-reset aria-label="{{ __('Reset zoom') }}">1:1</button>
            <button type="button" class="dark-img-lightbox-zbtn" data-zoom-in aria-label="{{ __('Zoom in') }}">+</button>
        </div>
        <p class="dark-img-lightbox-hint">{{ __('Scroll or pinch to zoom · drag to pan') }}</p>
    </div>
    <!--    dark service details section end   -->
@else
    <!--    services details section start   -->
    <div class="pt-115 pb-110 service-details-section">
        <div class="container">
            <div class="row">
                <div class="{{ $service->sidebar == 1 ? 'col-lg-7' : 'col-12' }} reveal-left">
                    <div class="service-details">
                        {!! replaceBaseUrl(convertUtf8($service->content)) !!}
                    </div>
                </div>
                <!--    service sidebar start   -->
                @if ($service->sidebar == 1)
                    <div class="col-lg-4">
                        <div class="blog-sidebar-widgets">
                            <div class="searchbar-form-section">
                                <form action="{{ route('front.services') }}">
                                    <div class="searchbar">
                                        <input name="category" type="hidden" value="{{ request()->input('category') }}">
                                        <input name="term" type="text" placeholder="{{ __('Search Services') }}"
                                            value="{{ request()->input('term') }}">
                                        <button type="submit"><i class="fa fa-search"></i></button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @if (serviceCategory())
                            <div class="blog-sidebar-widgets category-widget">
                                <div class="category-lists job">
                                    <h4>{{ __('Categories') }}</h4>
                                    <ul>
                                        @foreach ($scats as $key => $scat)
                                            <li
                                                class="single-category {{ !empty($service->scategory) && $service->scategory->id == $scat->id ? 'active' : '' }}">
                                                <a
                                                    href="{{ route('front.services', ['category' => $scat->id, 'term' => request()->input('term')]) }}">{{ convertUtf8($scat->name) }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif
                        <div class="subscribe-section">
                            <span>{{ __('SUBSCRIBE') }}</span>
                            <h3>{{ __('SUBSCRIBE FOR NEWSLETTER') }}</h3>
                            <form id="subscribeForm" class="subscribe-form" action="{{ route('front.subscribe') }}"
                                method="POST">
                                @csrf
                                <div class="form-element"><input name="email" type="email"
                                        placeholder="{{ __('Email') }}"></div>
                                <p id="erremail" class="text-danger mb-3 err-email"></p>
                                <div class="form-element"><input type="submit" value="{{ __('Subscribe') }}"></div>
                            </form>
                        </div>
                    </div>
                @endif
                <!--    service sidebar end   -->
            </div>
        </div>
    </div>
    <!--    services details section end   -->
@endif

@if ($be->theme_version == 'dark')
    @section('scripts')
        <script>
            (function () {
                "use strict";
                var content = document.querySelector('.dark-service-details');
                var lb = document.getElementById('svcImgLightbox');
                if (!content || !lb) return;

                var imgs = content.querySelectorAll('img');
                if (!imgs.length) return;
                var album = Array.prototype.map.call(imgs, function (img) {
                    return img.getAttribute('src') || img.getAttribute('data-src') || '';
                });

                var lbImg = document.getElementById('svcLightboxImg');
                var lbClose = lb.querySelector('.dark-img-lightbox-close');
                var lbPrevBtn = lb.querySelector('.dark-img-lightbox-prev');
                var lbNextBtn = lb.querySelector('.dark-img-lightbox-next');
                var lbStage = lb.querySelector('.dark-img-lightbox-stage');
                var zoomInBtn = lb.querySelector('[data-zoom-in]');
                var zoomOutBtn = lb.querySelector('[data-zoom-out]');
                var zoomResetBtn = lb.querySelector('[data-zoom-reset]');

                var MIN_SCALE = 1;
                var MAX_SCALE = 4;
                var lbIndex = 0;
                var scale = 1;
                var panX = 0;
                var panY = 0;

                function applyTransform() {
                    lbImg.style.transform = 'translate(' + panX + 'px,' + panY + 'px) scale(' + scale + ')';
                    lbImg.classList.toggle('is-zoomed', scale > 1);
                }

                function clampPan() {
                    var maxX = Math.max(0, (lbImg.offsetWidth * scale - lbImg.offsetWidth) / 2);
                    var maxY = Math.max(0, (lbImg.offsetHeight * scale - lbImg.offsetHeight) / 2);
                    panX = Math.max(-maxX, Math.min(maxX, panX));
                    panY = Math.max(-maxY, Math.min(maxY, panY));
                }

                function resetZoom() {
                    scale = 1;
                    panX = 0;
                    panY = 0;
                    applyTransform();
                }

                function zoomToward(clientX, clientY, newScale) {
                    newScale = Math.max(MIN_SCALE, Math.min(MAX_SCALE, newScale));
                    if (newScale === scale) return;
                    var rect = lbImg.getBoundingClientRect();
                    var offsetX = clientX - (rect.left + rect.width / 2);
                    var offsetY = clientY - (rect.top + rect.height / 2);
                    var ratio = newScale / scale;
                    panX = offsetX - (offsetX - panX) * ratio;
                    panY = offsetY - (offsetY - panY) * ratio;
                    scale = newScale;
                    if (scale === MIN_SCALE) {
                        panX = 0;
                        panY = 0;
                    }
                    clampPan();
                    applyTransform();
                }

                function showLb(index) {
                    lbIndex = (index + album.length) % album.length;
                    resetZoom();
                    lbImg.src = album[lbIndex];
                    var multi = album.length > 1;
                    if (lbPrevBtn) lbPrevBtn.style.display = multi ? '' : 'none';
                    if (lbNextBtn) lbNextBtn.style.display = multi ? '' : 'none';
                }

                function openLb(index) {
                    showLb(index || 0);
                    lb.classList.add('is-open');
                    document.body.style.overflow = 'hidden';
                }

                function closeLb() {
                    lb.classList.remove('is-open');
                    document.body.style.overflow = '';
                }

                for (var i = 0; i < imgs.length; i++) {
                    (function (img, index) {
                        // Wrapped so a hover state (mouse) and an always-on
                        // low-opacity icon (touch — no hover event at all)
                        // both make it obvious the image opens something,
                        // instead of only the cursor style hinting at it.
                        var wrap = document.createElement('span');
                        wrap.className = 'dark-svc-img-zoomable';
                        img.parentNode.insertBefore(wrap, img);
                        wrap.appendChild(img);
                        var icon = document.createElement('span');
                        icon.className = 'dark-svc-img-zoom-icon';
                        icon.innerHTML = '<i class="fas fa-search-plus"></i>';
                        wrap.appendChild(icon);

                        wrap.addEventListener('click', function () {
                            openLb(index);
                        });

                        // One-time attention pulse, staggered slightly per
                        // image so a content block with several images
                        // doesn't flash them all in one identical beat.
                        setTimeout(function () {
                            icon.querySelector('i').classList.add('is-pulsing');
                        }, 600 + index * 220);
                    })(imgs[i], i);
                }

                if (lbClose) lbClose.addEventListener('click', closeLb);
                lb.addEventListener('click', function (e) {
                    if (e.target === lb) closeLb();
                });
                if (lbNextBtn) lbNextBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    showLb(lbIndex + 1);
                });
                if (lbPrevBtn) lbPrevBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    showLb(lbIndex - 1);
                });

                document.addEventListener('keydown', function (e) {
                    if (!lb.classList.contains('is-open')) return;
                    if (e.key === 'Escape') closeLb();
                    else if (e.key === 'ArrowRight') showLb(lbIndex + 1);
                    else if (e.key === 'ArrowLeft') showLb(lbIndex - 1);
                });

                function centerZoom(delta) {
                    var rect = lbStage.getBoundingClientRect();
                    zoomToward(rect.left + rect.width / 2, rect.top + rect.height / 2, scale + delta);
                }
                if (zoomInBtn) zoomInBtn.addEventListener('click', function () {
                    centerZoom(0.6);
                });
                if (zoomOutBtn) zoomOutBtn.addEventListener('click', function () {
                    centerZoom(-0.6);
                });
                if (zoomResetBtn) zoomResetBtn.addEventListener('click', resetZoom);

                lbImg.addEventListener('wheel', function (e) {
                    e.preventDefault();
                    zoomToward(e.clientX, e.clientY, scale + (e.deltaY < 0 ? 0.3 : -0.3));
                }, { passive: false });

                lbImg.addEventListener('dblclick', function (e) {
                    zoomToward(e.clientX, e.clientY, scale > 1 ? 1 : 2.5);
                });

                var dragging = false;
                var dragStartX = 0;
                var dragStartY = 0;
                var panStartX = 0;
                var panStartY = 0;

                lbImg.addEventListener('mousedown', function (e) {
                    if (scale <= 1) return;
                    dragging = true;
                    dragStartX = e.clientX;
                    dragStartY = e.clientY;
                    panStartX = panX;
                    panStartY = panY;
                    lbImg.classList.add('is-dragging');
                });
                window.addEventListener('mousemove', function (e) {
                    if (!dragging) return;
                    panX = panStartX + (e.clientX - dragStartX);
                    panY = panStartY + (e.clientY - dragStartY);
                    clampPan();
                    applyTransform();
                });
                window.addEventListener('mouseup', function () {
                    if (!dragging) return;
                    dragging = false;
                    lbImg.classList.remove('is-dragging');
                });

                var pinch = null;
                var pan1 = null;
                var lastTapTime = 0;

                function touchDist(t) {
                    var dx = t[0].clientX - t[1].clientX;
                    var dy = t[0].clientY - t[1].clientY;
                    return Math.sqrt(dx * dx + dy * dy);
                }

                lbImg.addEventListener('touchstart', function (e) {
                    if (e.touches.length === 2) {
                        pan1 = null;
                        pinch = {
                            startDist: touchDist(e.touches),
                            startScale: scale,
                            startPanX: panX,
                            startPanY: panY,
                            midX: (e.touches[0].clientX + e.touches[1].clientX) / 2,
                            midY: (e.touches[0].clientY + e.touches[1].clientY) / 2
                        };
                    } else if (e.touches.length === 1) {
                        var now = Date.now();
                        if (now - lastTapTime < 300) {
                            zoomToward(e.touches[0].clientX, e.touches[0].clientY, scale > 1 ? 1 : 2.5);
                            pan1 = null;
                        } else if (scale > 1) {
                            pan1 = {
                                startX: e.touches[0].clientX,
                                startY: e.touches[0].clientY,
                                startPanX: panX,
                                startPanY: panY
                            };
                        }
                        lastTapTime = now;
                    }
                }, { passive: true });

                lbImg.addEventListener('touchmove', function (e) {
                    if (pinch && e.touches.length === 2) {
                        e.preventDefault();
                        var factor = touchDist(e.touches) / pinch.startDist;
                        var newScale = Math.max(MIN_SCALE, Math.min(MAX_SCALE, pinch.startScale * factor));
                        var ratio = newScale / pinch.startScale;
                        panX = pinch.midX - (pinch.midX - pinch.startPanX) * ratio;
                        panY = pinch.midY - (pinch.midY - pinch.startPanY) * ratio;
                        scale = newScale;
                        if (scale === MIN_SCALE) {
                            panX = 0;
                            panY = 0;
                        }
                        clampPan();
                        applyTransform();
                    } else if (pan1 && e.touches.length === 1) {
                        e.preventDefault();
                        panX = pan1.startPanX + (e.touches[0].clientX - pan1.startX);
                        panY = pan1.startPanY + (e.touches[0].clientY - pan1.startY);
                        clampPan();
                        applyTransform();
                    }
                }, { passive: false });

                lbImg.addEventListener('touchend', function (e) {
                    if (e.touches.length === 0) {
                        pinch = null;
                        pan1 = null;
                    } else if (e.touches.length === 1) {
                        pinch = null;
                    }
                });
            })();
        </script>

        <script>
            (function () {
                var $ = window.jQuery;
                var $track = $('#svcRelCarousel');
                if (!$ || !$track.length || !$.fn.owlCarousel) return;

                var n = $track.children().length;
                $track.owlCarousel({
                    loop: n > 3,
                    rewind: n <= 3,
                    autoplay: n > 1,
                    autoplayTimeout: 4500,
                    autoplaySpeed: 900,
                    autoplayHoverPause: true,
                    smartSpeed: 600,
                    margin: 22,
                    nav: false,
                    dots: false,
                    responsive: {
                        0: { items: 1 },
                        640: { items: 2 },
                        992: { items: 3 }
                    }
                });
                $('#svcRelPrev').on('click', function () { $track.trigger('prev.owl.carousel'); });
                $('#svcRelNext').on('click', function () { $track.trigger('next.owl.carousel'); });
            })();
        </script>
    @endsection
@endif

@endsection
