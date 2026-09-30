@extends("front.$version.layout")

@section('pagename')
    - {{ convertUtf8($blog->title) }}
@endsection

@section('meta-keywords', "$blog->meta_keywords")
@section('meta-description', "$blog->meta_description")

@section('breadcrumb-title', convertUtf8($bs->blog_details_title))
@section('breadcrumb-subtitle', strlen($blog->title) > 30 ? mb_substr($blog->title, 0, 30, 'utf-8') . '...' :
    $blog->title)
@section('breadcrumb-link', __('Blog Details'))

@if (!empty($bs->blog_breadcrumb_bg))
    @section('breadcrumb-bg', asset('assets/front/img/' . $bs->blog_breadcrumb_bg))
@endif

@if (!empty($bs->blog_breadcrumb_overlay_color))
    @section('breadcrumb-overlay-color', $bs->blog_breadcrumb_overlay_color)
@endif

@if (!empty($bs->blog_breadcrumb_overlay_opacity))
    @section('breadcrumb-overlay-opacity', $bs->blog_breadcrumb_overlay_opacity)
@endif

@if ($be->theme_version == 'dark')
    {{-- Same content-image lightbox as the Service details page — identical
         markup/classes/script, just pointed at .dark-article-prose (this
         page's WYSIWYG content wrapper) instead of .dark-service-details. --}}
    @section('styles')
        <style>
            .dark-svc-img-zoomable {
                position: relative;
                display: inline-block;
                cursor: zoom-in;
                max-width: 100%;
                border-radius: 8px;
                overflow: hidden;
            }
            /* The cover image is full-bleed (width:100%, fixed height) via
               .dark-article-cover img — but that rule matches on descendant,
               not direct child, so wrapping it in the (inline-block,
               content-sized) span above would otherwise shrink the wrapper
               to the image's intrinsic size instead of stretching full width. */
            .dark-article-cover .dark-svc-img-zoomable {
                display: block;
                width: 100%;
                height: 100%;
                border-radius: 0;
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
    @php
        $blogDate = !empty($currentLang) ? \Carbon\Carbon::parse($blog->created_at)->locale($currentLang->code) : \Carbon\Carbon::parse($blog->created_at)->locale('en');
        $readMins = max(1, (int) ceil(str_word_count(strip_tags($blog->content)) / 200));
    @endphp
    <!--    dark blog details section start   -->
    <div class="dark-svcp-section dark-blogp-section">
        <div class="dark-svcp-inner @if ($blog->sidebar != 1) dark-svcp-inner--full @endif">
            <div>
                <div class="dark-article-cover">
                    <img class="lazy" data-src="{{ asset('assets/front/img/blogs/' . $blog->main_image) }}" alt="">
                    <div class="dark-cover-meta">
                        <span class="dark-cover-pill accent"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="4" width="18" height="18" rx="2" /><path d="M16 2v4M8 2v4M3 10h18" /></svg>{{ $blogDate->translatedFormat('jS F, Y') }}</span>
                        <span class="dark-cover-pill"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 3" /></svg>{{ $readMins }} {{ __('min read') }}</span>
                    </div>
                </div>

                <div class="dark-article-byline">
                    <div class="dark-byline-avatar">{{ mb_strtoupper(mb_substr(convertUtf8($bex->site_title ?? 'ICA'), 0, 2)) }}</div>
                    <div class="dark-byline-text">
                        <div class="name">{{ convertUtf8($blog->author_name) }}</div>
                        @if (!empty($blog->bcategory))
                            <div class="role">{{ convertUtf8($blog->bcategory->name) }}</div>
                        @endif
                    </div>
                    <div class="dark-byline-share">
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" aria-label="{{ __('Share') }}"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}" aria-label="{{ __('Tweet') }}"><i class="fab fa-twitter"></i></a>
                        <a href="http://www.linkedin.com/shareArticle?mini=true&amp;url={{ urlencode(url()->current()) }}&amp;title={{ convertUtf8($blog->title) }}" aria-label="{{ __('Linkedin') }}"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>

                <h1 class="dark-article-title">{{ convertUtf8($blog->title) }}</h1>

                <div class="dark-article-prose reveal-left">
                    {!! replaceBaseUrl(convertUtf8($blog->content)) !!}
                </div>

                @if (!empty($blog->tags))
                    <div class="dark-article-tags">
                        @foreach (explode(',', $blog->tags) as $tag)
                            @if (trim($tag) !== '')
                                <a href="{{ route('front.blogs', ['tag' => trim($tag)]) }}">{{ trim($tag) }}</a>
                            @endif
                        @endforeach
                    </div>
                @endif

                <div id="disqus_thread"></div>
            </div>

            @if ($blog->sidebar == 1)
                <div>
                    <div class="dark-svcp-widget">
                        <form class="dark-svcp-search" action="{{ route('front.blogs', ['category' => request()->input('category'), 'month' => request()->input('month'), 'year' => request()->input('year')]) }}" method="GET">
                            <input name="category" type="hidden" value="{{ request()->input('category') }}">
                            <input name="month" type="hidden" value="{{ request()->input('month') }}">
                            <input name="year" type="hidden" value="{{ request()->input('year') }}">
                            <input name="term" type="text" placeholder="{{ __('Search Blogs') }}" value="{{ request()->input('term') }}">
                            <button type="submit"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><circle cx="11" cy="11" r="7" /><path d="M21 21l-4.3-4.3" /></svg></button>
                        </form>
                    </div>

                    <div class="dark-svcp-widget">
                        <h4>{{ __('Categories') }}</h4>
                        <ul class="dark-svcp-cat-list">
                            <li class="{{ empty($blog->bcategory) ? 'is-active' : '' }}">
                                <a href="{{ route('front.blogs') }}">
                                    <span class="dark-pf-cat-icon"><i class="fas fa-th-large"></i></span>
                                    <span class="dark-pf-cat-name">{{ __('All categories') }}</span>
                                    <span class="dark-pf-cat-count">{{ $totalBlogsCount }}</span>
                                    <span class="dark-pf-cat-chevron"><i class="fas fa-chevron-right"></i></span>
                                </a>
                            </li>
                            @foreach ($bcats as $key => $bcat)
                                <li class="{{ !empty($blog->bcategory) && $blog->bcategory_id == $bcat->id ? 'is-active' : '' }}">
                                    <a href="{{ route('front.blogs', ['category' => $bcat->slug]) }}">
                                        <span class="dark-pf-cat-icon"><i class="fas fa-tag"></i></span>
                                        <span class="dark-pf-cat-name">{{ convertUtf8($bcat->name) }}</span>
                                        <span class="dark-pf-cat-count">{{ $bcat->blogs_count }}</span>
                                        <span class="dark-pf-cat-chevron"><i class="fas fa-chevron-right"></i></span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="dark-svcp-widget">
                        <h4>{{ __('Archives') }}</h4>
                        <ul class="dark-blogp-archive-list">
                            @foreach ($archives as $key => $archive)
                                @php
                                    $myArr = explode('-', $archive->date);
                                    $monthNum = $myArr[0];
                                    $dateObj = DateTime::createFromFormat('!m', $monthNum);
                                    $monthName = $dateObj->format('F');
                                    $monthName = !empty($currentLang) ? \Carbon\Carbon::parse($monthName)->locale($currentLang->code) : \Carbon\Carbon::parse($monthName)->locale('en');
                                    $yearLabel = !empty($currentLang) ? \Carbon\Carbon::parse($myArr[1])->locale($currentLang->code) : \Carbon\Carbon::parse($myArr[1])->locale('en');
                                @endphp
                                <li class="dark-blogp-archive-item {{ request()->input('month') == $myArr[0] && request()->input('year') == $myArr[1] ? 'is-active' : '' }}">
                                    <a href="{{ route('front.blogs', ['term' => request()->input('term'), 'category' => request()->input('category'), 'month' => $myArr[0], 'year' => $myArr[1]]) }}">{{ $monthName->translatedFormat('F') }} {{ $yearLabel->translatedFormat('Y') }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="dark-svcp-widget dark-svcp-newsletter">
                        <span class="dark-bc-eyebrow">{{ __('SUBSCRIBE') }}</span>
                        <h4 class="dark-svcp-newsletter-title">{{ __('SUBSCRIBE FOR NEWSLETTER') }}</h4>
                        <form id="subscribeForm" class="dark-svcp-newsletter-form" action="{{ route('front.subscribe') }}" method="POST">
                            @csrf
                            <input name="email" type="email" placeholder="{{ __('Email') }}">
                            <button type="submit">{{ __('Subscribe') }}</button>
                        </form>
                        <p id="erremail" class="text-danger mb-0 err-email"></p>
                    </div>
                </div>
            @endif
        </div>

        @if ($relatedBlogs->count() > 0)
            <div class="dark-pd-similar-section dark-svcrel-section">
                <div class="dark-pd-similar-head">
                    <h3 class="dark-pd-similar-title reveal-text">
                        {{ __('Related Blogs') }}
                        @if (!empty($blog->bcategory))
                            <span class="dark-svcrel-cat">&mdash; {{ convertUtf8($blog->bcategory->name) }}</span>
                        @endif
                        <span class="dark-pd-similar-rule"></span>
                    </h3>
                    @if ($relatedBlogs->count() > 1)
                        <div class="dark-pd-similar-nav">
                            <button type="button" id="blogRelPrev" aria-label="{{ __('Previous') }}"><svg viewBox="0 0 24 24"><path d="M15 6l-6 6 6 6" /></svg></button>
                            <button type="button" id="blogRelNext" aria-label="{{ __('Next') }}"><svg viewBox="0 0 24 24"><path d="M9 6l6 6-6 6" /></svg></button>
                        </div>
                    @endif
                </div>
                <div class="owl-carousel dark-svcrel-carousel" id="blogRelCarousel">
                    @foreach ($relatedBlogs as $rb)
                        @php
                            $rbDate = !empty($currentLang) ? \Carbon\Carbon::parse($rb->created_at)->locale($currentLang->code) : \Carbon\Carbon::parse($rb->created_at)->locale('en');
                        @endphp
                        <div class="dark-blogp-card">
                            <div class="dark-blogp-card-img">
                                <span class="dark-blogp-date-badge"><span class="d">{{ $rbDate->format('d') }}</span><span class="m">{{ $rbDate->translatedFormat('M') }}</span></span>
                                <img src="{{ asset('assets/front/img/blogs/' . $rb->main_image) }}" alt="{{ convertUtf8($rb->title) }}">
                            </div>
                            <div class="dark-blogp-card-body">
                                <h3><a href="{{ route('front.blogdetails', [$rb->slug]) }}">{{ strlen($rb->title) > 60 ? mb_substr($rb->title, 0, 60, 'utf-8') . '...' : $rb->title }}</a></h3>
                                <p>{!! strlen(strip_tags($rb->content)) > 90 ? mb_substr(strip_tags($rb->content), 0, 90, 'utf-8') . '...' : strip_tags($rb->content) !!}</p>
                                <a href="{{ route('front.blogdetails', [$rb->slug]) }}" class="dark-blogp-link">{{ __('Read More') }} <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12h14M13 6l6 6-6 6" /></svg></a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Image lightbox for images embedded in the post's own WYSIWYG
         content — same component (markup/CSS) as the Service details
         page's gallery lightbox, own IDs since there's no gallery carousel
         here to stay in sync with on close, just plain content images. --}}
    <div class="dark-img-lightbox" id="blogImgLightbox">
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
            <img class="dark-img-lightbox-img" id="blogLightboxImg" src="" alt="">
        </div>
        <div class="dark-img-lightbox-zoombar">
            <button type="button" class="dark-img-lightbox-zbtn" data-zoom-out aria-label="{{ __('Zoom out') }}">−</button>
            <button type="button" class="dark-img-lightbox-zbtn dark-img-lightbox-zreset" data-zoom-reset aria-label="{{ __('Reset zoom') }}">1:1</button>
            <button type="button" class="dark-img-lightbox-zbtn" data-zoom-in aria-label="{{ __('Zoom in') }}">+</button>
        </div>
        <p class="dark-img-lightbox-hint">{{ __('Scroll or pinch to zoom · drag to pan') }}</p>
    </div>
    <!--    dark blog details section end   -->
@else
    <!--    blog details section start   -->
    <div class="blog-details-section section-padding">
        <div class="container">
            <div class="row">
                <div class="{{ $blog->sidebar == 1 ? 'col-lg-7' : 'col-12' }} reveal-left">
                    <div class="blog-details">
                        <img class="blog-details-img-1 lazy"
                            data-src="{{ asset('assets/front/img/blogs/' . $blog->main_image) }}" alt="">
                        <small class="date">{{ date('F d, Y', strtotime($blog->created_at)) }} - {{ __('BY') }}
                            {{ convertUtf8($blog->author_name) }}</small>
                        <h2 class="blog-details-title">{{ convertUtf8($blog->title) }}</h2>
                        <div class="blog-details-body">
                            {!! replaceBaseUrl(convertUtf8($blog->content)) !!}
                        </div>
                    </div>
                    <div class="blog-share mb-5">
                        <ul>
                            <li><a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
                                    class="facebook-share"><i class="fab fa-facebook-f"></i> {{ __('Share') }}</a></li>
                            <li><a href="https://twitter.com/intent/tweet?text=my share text&amp;url={{ urlencode(url()->current()) }}"
                                    class="twitter-share"><i class="fab fa-twitter"></i> {{ __('Tweet') }}</a></li>
                            <li><a href="http://www.linkedin.com/shareArticle?mini=true&amp;url={{ urlencode(url()->current()) }}&amp;title={{ convertUtf8($blog->title) }}"
                                    class="linkedin-share"><i class="fab fa-linkedin-in"></i> {{ __('Linkedin') }}</a></li>
                        </ul>
                    </div>

                    <div class="comment-lists">
                        <div id="disqus_thread"></div>
                    </div>
                </div>
                <!--    blog sidebar section start   -->
                @if ($blog->sidebar == 1)
                    <div class="col-lg-4">
                        <div class="sidebar">
                            <div class="blog-sidebar-widgets">
                                <div class="searchbar-form-section">
                                    <form
                                        action="{{ route('front.blogs', ['category' => request()->input('category'), 'month' => request()->input('month'), 'year' => request()->input('year')]) }}"
                                        method="GET">
                                        <div class="searchbar">
                                            <input name="category" type="hidden"
                                                value="{{ request()->input('category') }}">
                                            <input name="month" type="hidden" value="{{ request()->input('month') }}">
                                            <input name="year" type="hidden" value="{{ request()->input('year') }}">
                                            <input name="term" type="text" placeholder="{{ __('Search Blogs') }}"
                                                value="{{ request()->input('term') }}">
                                            <button type="submit"><i class="fa fa-search"></i></button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="blog-sidebar-widgets category-widget">
                                <div class="category-lists job">
                                    <h4>{{ __('Categories') }}</h4>
                                    <ul>
                                        @foreach ($bcats as $key => $bcat)
                                            <li class="single-category @if (request()->input('category') == $bcat->slug) active @endif"><a
                                                    href="{{ route('front.blogs', ['term' => request()->input('term'), 'category' => $bcat->slug, 'month' => request()->input('month'), 'year' => request()->input('year')]) }}">{{ convertUtf8($bcat->name) }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            <div class="blog-sidebar-widgets category-widget">
                                <div class="category-lists job">
                                    <h4>{{ __('Archives') }}</h4>
                                    <ul>
                                        @foreach ($archives as $key => $archive)
                                            @php
                                                $myArr = explode('-', $archive->date);
                                                $monthNum = $myArr[0];
                                                $dateObj = DateTime::createFromFormat('!m', $monthNum);
                                                $monthName = $dateObj->format('F');
                                            @endphp
                                            <li class="single-category @if (request()->input('month') == $myArr[0] && request()->input('year') == $myArr[1]) active @endif">
                                                <a
                                                    href="{{ route('front.blogs', ['term' => request()->input('term'), 'category' => request()->input('category'), 'month' => $myArr[0], 'year' => $myArr[1]]) }}">

                                                    @php
                                                        if (!empty($currentLang)) {
                                                            $monthName = \Carbon\Carbon::parse($monthName)->locale(
                                                                "$currentLang->code",
                                                            );
                                                            $year = \Carbon\Carbon::parse($myArr[1])->locale(
                                                                "$currentLang->code",
                                                            );
                                                        } else {
                                                            $monthName = \Carbon\Carbon::parse($monthName)->locale(
                                                                'en',
                                                            );
                                                            $year = \Carbon\Carbon::parse($myArr[1])->locale('en');
                                                        }

                                                        $monthName = $monthName->translatedFormat('F');
                                                        $year = $year->translatedFormat('Y');
                                                    @endphp

                                                    {{ $monthName }} {{ $year }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
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
                    </div>
                @endif
                <!--    blog sidebar section end   -->
            </div>
        </div>
    </div>
    <!--    blog details section end   -->
@endif

@endsection

@section('scripts')
    @if ($bs->is_disqus == 1)
        {!! $bs->disqus_script !!}
    @endif

    <script>
        (function () {
            "use strict";
            var content = document.querySelector('.dark-article-prose');
            var cover = document.querySelector('.dark-article-cover img');
            var lb = document.getElementById('blogImgLightbox');
            if (!lb) return;

            // Cover image first (if present), then every image inside the
            // article body — Next/Prev cycles through both as one album.
            var imgs = [];
            if (cover) imgs.push(cover);
            if (content) imgs = imgs.concat(Array.prototype.slice.call(content.querySelectorAll('img')));
            if (!imgs.length) return;
            var album = Array.prototype.map.call(imgs, function (img) {
                return img.getAttribute('src') || img.getAttribute('data-src') || '';
            });

            var lbImg = document.getElementById('blogLightboxImg');
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
            var $track = $('#blogRelCarousel');
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
            $('#blogRelPrev').on('click', function () { $track.trigger('prev.owl.carousel'); });
            $('#blogRelNext').on('click', function () { $track.trigger('next.owl.carousel'); });
        })();
    </script>
@endsection
