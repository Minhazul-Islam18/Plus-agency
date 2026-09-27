@extends("front.$version.layout")

@section('pagename')
    - {{ __('FAQ') }}
@endsection

@section('meta-keywords', "$be->faq_meta_keywords")
@section('meta-description', "$be->faq_meta_description")

@section('breadcrumb-title', convertUtf8($bs->faq_title))
@section('breadcrumb-subtitle', convertUtf8($bs->faq_subtitle))
@section('breadcrumb-link', __('FAQS'))

@if (!empty($bs->faq_breadcrumb_bg))
    @section('breadcrumb-bg', asset('assets/front/img/' . $bs->faq_breadcrumb_bg))
@endif

@if (!empty($bs->faq_breadcrumb_overlay_color))
    @section('breadcrumb-overlay-color', $bs->faq_breadcrumb_overlay_color)
@endif

@if (!empty($bs->faq_breadcrumb_overlay_opacity))
    @section('breadcrumb-overlay-opacity', $bs->faq_breadcrumb_overlay_opacity)
@endif

@if ($be->theme_version == 'dark')
    @php
        $faqxIntro =
            trim((string) ($bs->faq_intro_text ?? '')) !== ''
                ? $bs->faq_intro_text
                : __(
                    'Quickly find answers to your questions about our services, calls for tenders, and how to use the ICA platform.',
                );
    @endphp
    @section('breadcrumb-hero-class', 'dark-bc-faq')
    @section('breadcrumb-intro', convertUtf8($faqxIntro))
    @if (!empty($bs->faq_hero_image))
        @section('breadcrumb-art')
            <img src="{{ asset('assets/front/img/' . $bs->faq_hero_image) }}" alt="" loading="eager" decoding="async">
        @endsection
    @endif
@endif

@section('content')
    @if ($be->theme_version == 'dark')
        @php
            $faqxWa = preg_replace('/\D+/', '', (string) ($bs->faq_whatsapp ?? ''));
            $faqxShowCats = $bex->faq_category_status == 1 && count($categories) > 0;
            $faqxCatIds = $categories->pluck('id')->all();
            $faqxPageSize = 7;
        @endphp
        <!--   dark FAQ page start   -->
        <div class="dark-svcp-section dark-faqx">
            <div class="dark-faqx-inner">
                @if (count($faqs) == 0)
                    <div class="dark-svcp-empty">
                        <h3 class="text-center">{{ __('No FAQ Found!') }}</h3>
                    </div>
                @else
                    <div class="faqx-grid {{ count($frequentFaqs) == 0 ? 'is-solo' : '' }}" id="faqxGrid">
                        <div class="faqx-main">
                            <div class="faqx-toolbar">
                                <div class="faqx-search">
                                    <input type="text" id="faqxSearch" placeholder="{{ __('Search FAQs') }}"
                                        autocomplete="off" aria-label="{{ __('Search FAQs') }}">
                                    <span class="faqx-search-btn" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.4">
                                            <circle cx="11" cy="11" r="7" />
                                            <path d="M21 21l-4.3-4.3" />
                                        </svg></span>
                                </div>
                                @if ($faqxShowCats)
                                    <div class="faqx-select">
                                        <select id="faqxCat" aria-label="{{ __('All categories') }}">
                                            <option value="">{{ __('All categories') }}</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}">{{ convertUtf8($category->name) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                            </div>
                            <h2 class="faqx-heading reveal-text">{{ __('All questions') }}</h2>
                            <div class="faqx-list" id="faqxList" data-page-size="{{ $faqxPageSize }}"
                                data-view-url="{{ route('front.faq.view', ['id' => '__ID__']) }}"
                                data-panel-url="{{ route('front.faq.mostViewed') }}">
                                @foreach ($faqs as $qa)
                                    <div class="faqx-item" id="faqx-item-{{ $qa->id }}"
                                        data-id="{{ $qa->id }}" data-cat="{{ $qa->category_id }}"
                                        data-search="{{ mb_strtolower(strip_tags(convertUtf8($qa->question . ' ' . $qa->answer))) }}">
                                        <button type="button" class="faqx-q" aria-expanded="false"
                                            aria-controls="faqx-a-{{ $qa->id }}">
                                            <span class="faqx-num" aria-hidden="true"></span>
                                            <span class="faqx-qt">{{ convertUtf8($qa->question) }}</span>
                                            <span class="faqx-ico" aria-hidden="true"></span>
                                        </button>
                                        <div class="faqx-a" id="faqx-a-{{ $qa->id }}" role="region">
                                            <div class="faqx-a-in">
                                                <p>{!! nl2br(e(convertUtf8($qa->answer))) !!}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <p class="faqx-none" id="faqxNone" hidden>{{ __('No FAQ Found!') }}</p>
                            <div class="dark-svc-load-more-wrap faqx-more-wrap" id="faqxMoreWrap" hidden>
                                <button type="button" class="dark-svc-load-more-btn" id="faqxMore"
                                    data-text-loading="{{ __('Loading…') }}" data-text-load-more="{{ __('Show more') }}"
                                    data-text-more-suffix="{{ __('more') }}">
                                    <span class="dark-svc-load-more-label">{{ __('Show more') }}</span>
                                    <span class="dark-svc-load-more-count"></span>
                                    <span class="dark-svc-load-more-icon">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M6 9l6 6 6-6" />
                                        </svg>
                                        <span class="dark-svc-load-more-spinner"></span>
                                    </span>
                                </button>
                            </div>
                        </div>

                        <aside class="faqx-panel" id="faqxPanel" @if (count($frequentFaqs) == 0) hidden @endif>
                            <div class="faqx-panel-head">
                                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path
                                        d="M12 2.5l2.9 6 6.6.9-4.8 4.6 1.2 6.5L12 17.4 6.1 20.5l1.2-6.5L2.5 9.4l6.6-.9z" />
                                </svg>
                                <h3>{{ __('Most viewed questions') }}</h3>
                            </div>
                            <ol class="faqx-plist" id="faqxPanelList">
                                @foreach ($frequentFaqs as $fq)
                                    <li class="faqx-pli" data-faq="{{ $fq->id }}">
                                        <button type="button" class="faqx-pitem" aria-expanded="false"
                                            aria-controls="faqx-pa-{{ $fq->id }}"><span class="faqx-num"
                                                aria-hidden="true"></span><span
                                                class="faqx-pt">{{ convertUtf8($fq->question) }}</span><span
                                                class="faqx-chev" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2.4">
                                                    <path d="M9 6l6 6-6 6" />
                                                </svg></span></button>
                                        <div class="faqx-pa" id="faqx-pa-{{ $fq->id }}" role="region">
                                            <div class="faqx-pa-in">
                                                <p>{!! nl2br(e(convertUtf8($fq->answer))) !!}</p>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ol>
                        </aside>
                    </div>
                @endif

                <div class="faqx-banner reveal-text">
                    <span class="faqx-banner-ico" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 14v-2a8 8 0 0116 0v2" />
                            <rect x="2.5" y="13" width="4.5" height="7" rx="2" />
                            <rect x="17" y="13" width="4.5" height="7" rx="2" />
                            <path d="M19.5 20c0 1.4-1.6 2-4 2h-2" />
                        </svg></span>
                    <div class="faqx-banner-txt">
                        <h3>{{ __("Didn't find the answer to your question?") }}</h3>
                        <p>{{ __('Our team is available to guide you towards the most suitable solution. Tell us about your project.') }}
                        </p>
                    </div>
                    <div class="faqx-banner-cta">
                        <a href="{{ route('front.contact') }}" class="faqx-btn faqx-btn--primary">
                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path
                                    d="M3 5h18a1 1 0 011 1v.4l-10 6.2L2 6.4V6a1 1 0 011-1zm-1 4.1l9.5 5.9a1 1 0 001 0L22 9.1V18a1 1 0 01-1 1H3a1 1 0 01-1-1V9.1z" />
                            </svg>
                            {{ __('Contact us') }}
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"
                                aria-hidden="true">
                                <path d="M5 12h14M13 6l6 6-6 6" />
                            </svg>
                        </a>
                        @if ($faqxWa !== '')
                            <a href="https://wa.me/{{ $faqxWa }}" class="faqx-btn faqx-btn--ghost" target="_blank"
                                rel="noopener">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M3.5 20.5l1.3-4.2A8.5 8.5 0 1112 20.5a8.4 8.4 0 01-4-1z" />
                                    <path d="M9 8.5c0 3.5 3 6.5 6.5 6.5l1-1.6-2-1-.9.8a4 4 0 01-2-2l.8-.9-1-2z"
                                        fill="currentColor" stroke="none" />
                                </svg>
                                WhatsApp
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <!--   dark FAQ page end   -->
    @else
        <!--   FAQ section start   -->

        @if ($bex->faq_category_status == 1)
            @if (count($categories) > 0)
                @if ($be->theme_version == 'dark')
                    {{-- dark FAQ, categorized: same dark-svcp-section/dark-svcp-widget
                   sidebar pattern as the dark Blog page, sidebar on the right --}}
                    <div class="dark-svcp-section dark-faq-section">
                        <button type="button" class="dark-pf-oc-trigger" id="pfOcToggle" aria-expanded="false">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 6h16M7 12h10M10 18h4" stroke-linecap="round" />
                            </svg>
                            {{ __('Categories') }}
                        </button>
                        <div class="dark-svcp-inner">
                            <div>
                                <div class="faq-details-wrapper">
                                    <div class="tab-content">
                                        @foreach ($categories as $category)
                                            <div class="tab-pane {{ $loop->iteration == 1 ? 'show active' : '' }} fade"
                                                id="{{ 'category' . $category->id }}">
                                                <div class="accordion" id="{{ 'accordion' . $category->id }}">
                                                    @php
                                                        $qas = \App\Faq::where('category_id', $category->id)
                                                            ->where('status', 1)
                                                            ->orderBy('serial_number', 'ASC')
                                                            ->get();
                                                    @endphp

                                                    @foreach ($qas as $qa)
                                                        <div class="card mb-30 reveal-text"
                                                            style="--d:{{ ($loop->index % 6) * 0.07 }}s">
                                                            <a class="collapsed card-header" id="heading1"
                                                                href="#" data-toggle="collapse"
                                                                data-target="{{ '#collapse' . $qa->id }}"
                                                                aria-expanded="{{ $loop->iteration == 1 ? 'true' : 'false' }}"
                                                                aria-controls="{{ 'collapse' . $qa->id }}">
                                                                {{ $qa->question }}<span class="toggle_btn"></span>
                                                            </a>
                                                            <div id="{{ 'collapse' . $qa->id }}"
                                                                class="collapse {{ $loop->iteration == 1 ? 'show' : '' }}"
                                                                aria-labelledby="heading1"
                                                                data-parent="{{ '#accordion' . $category->id }}">
                                                                <div class="card-body">
                                                                    <p>{{ $qa->answer }}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div id="pfCatSource">
                                <div class="dark-svcp-widget reveal-text">
                                    <h4>{{ __('Categories') }}</h4>
                                    <ul class="dark-svcp-cat-list faq-cat-list" id="faqCatList">
                                        @foreach ($categories as $category)
                                            <li class="{{ $loop->iteration == 1 ? 'is-active' : '' }}">
                                                <a href="{{ '#category' . $category->id }}"
                                                    data-toggle="tab">{{ $category->name }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="dark-oc-backdrop" id="pfOcBackdrop"></div>
                        <nav class="dark-oc-panel pf-oc-single" id="pfOcPanel">
                            <div class="dark-oc-head">
                                <span class="dark-oc-head-logo">{{ __('Categories') }}</span>
                                <button type="button" class="dark-oc-close" id="pfOcClose" aria-label="Close">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M6 6l12 12M18 6L6 18" stroke-linecap="round" />
                                    </svg>
                                </button>
                            </div>
                            <div class="dark-oc-nav" id="pfOcBody"></div>
                        </nav>
                    </div>
                @else
                    {{-- light FAQ, categorized: identical sidebar markup/classes to
                   the light Blog page (.sidebar > .blog-sidebar-widgets.category-widget),
                   sidebar on the right --}}
                    <div class="blog-lists faq-area-v1 section-padding">
                        <div class="container">
                            <button type="button" class="pf-oc-trigger" id="pfOcToggle" aria-expanded="false">
                                <i class="fas fa-filter"></i> {{ __('Categories') }}
                            </button>
                            <div class="row">
                                <div class="col-lg-8">
                                    <div class="faq-details-wrapper">
                                        <div class="tab-content">
                                            @foreach ($categories as $category)
                                                <div class="tab-pane {{ $loop->iteration == 1 ? 'show active' : '' }} fade"
                                                    id="{{ 'category' . $category->id }}">
                                                    <div class="accordion" id="{{ 'accordion' . $category->id }}">
                                                        @php
                                                            $qas = \App\Faq::where('category_id', $category->id)
                                                                ->where('status', 1)
                                                                ->orderBy('serial_number', 'ASC')
                                                                ->get();
                                                        @endphp

                                                        @foreach ($qas as $qa)
                                                            <div class="card mb-30 reveal-text"
                                                                style="--d:{{ ($loop->index % 6) * 0.07 }}s">
                                                                <a class="collapsed card-header" id="heading1"
                                                                    href="#" data-toggle="collapse"
                                                                    data-target="{{ '#collapse' . $qa->id }}"
                                                                    aria-expanded="{{ $loop->iteration == 1 ? 'true' : 'false' }}"
                                                                    aria-controls="{{ 'collapse' . $qa->id }}">
                                                                    {{ $qa->question }}<span class="toggle_btn"></span>
                                                                </a>
                                                                <div id="{{ 'collapse' . $qa->id }}"
                                                                    class="collapse {{ $loop->iteration == 1 ? 'show' : '' }}"
                                                                    aria-labelledby="heading1"
                                                                    data-parent="{{ '#accordion' . $category->id }}">
                                                                    <div class="card-body">
                                                                        <p>{{ $qa->answer }}</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-4 reveal-text" id="pfCatSource">
                                    <div class="sidebar">
                                        <div class="blog-sidebar-widgets category-widget">
                                            <div class="category-lists job">
                                                <h4>{{ __('Categories') }}</h4>
                                                <ul class="faq-cat-list" id="faqCatList">
                                                    @foreach ($categories as $category)
                                                        <li
                                                            class="single-category {{ $loop->iteration == 1 ? 'active' : '' }}">
                                                            <a href="{{ '#category' . $category->id }}"
                                                                data-toggle="tab">{{ $category->name }}</a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="pf-oc-backdrop" id="pfOcBackdrop"></div>
                            <nav class="pf-oc-panel pf-oc-single" id="pfOcPanel">
                                <div class="pf-oc-head">
                                    <span>{{ __('Categories') }}</span>
                                    <button type="button" class="pf-oc-close" id="pfOcClose"
                                        aria-label="Close">&times;</button>
                                </div>
                                <div class="pf-oc-nav" id="pfOcBody"></div>
                            </nav>
                        </div>
                    </div>
                @endif
            @elseif ($be->theme_version == 'dark')
                <div class="dark-svcp-section dark-faq-section">
                    <div class="dark-svcp-empty">
                        <h3 class="text-center">{{ __('No FAQ Found!') }}</h3>
                    </div>
                </div>
            @endif
        @else
            <section class="faq-area-v1 pt-120 pb-120 @if ($be->theme_version == 'dark') dark-faq-section @endif">
                <div class="container">
                    <div class="row">

                        @if (count($faqs) == 0)
                            <div
                                class="col-lg-8 offset-lg-2 py-y @if ($be->theme_version == 'dark') dark-svcp-empty @else bg-light @endif">
                                <h3 class="text-center">{{ __('No FAQ Found!') }}</h3>
                            </div>
                        @else
                            <div class="col-lg-12">
                                <div class="faq-section py-0">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="accordion" id="accordionExample1">
                                                @for ($i = 0; $i < ceil(count($faqs) / 2); $i++)
                                                    <div class="card reveal-text" style="--d:{{ ($i % 6) * 0.07 }}s">
                                                        <div class="card-header" id="heading{{ $faqs[$i]->id }}">
                                                            <h2 class="mb-0">
                                                                <button class="btn btn-link collapsed btn-block text-left"
                                                                    type="button" data-toggle="collapse"
                                                                    data-target="#collapse{{ $faqs[$i]->id }}"
                                                                    aria-expanded="false"
                                                                    aria-controls="collapse{{ $faqs[$i]->id }}">
                                                                    {{ convertUtf8($faqs[$i]->question) }}
                                                                </button>
                                                            </h2>
                                                        </div>
                                                        <div id="collapse{{ $faqs[$i]->id }}" class="collapse"
                                                            aria-labelledby="heading{{ $faqs[$i]->id }}"
                                                            data-parent="#accordionExample1">
                                                            <div class="card-body">
                                                                {{ convertUtf8($faqs[$i]->answer) }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endfor
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="accordion" id="accordionExample2">
                                                @for ($i = ceil(count($faqs) / 2); $i < count($faqs); $i++)
                                                    <div class="card reveal-text" style="--d:{{ ($i % 6) * 0.07 }}s">
                                                        <div class="card-header" id="heading{{ $faqs[$i]->id }}">
                                                            <h2 class="mb-0">
                                                                <button class="btn btn-link collapsed btn-block text-left"
                                                                    type="button" data-toggle="collapse"
                                                                    data-target="#collapse{{ $faqs[$i]->id }}"
                                                                    aria-expanded="false"
                                                                    aria-controls="collapse{{ $faqs[$i]->id }}">
                                                                    {{ convertUtf8($faqs[$i]->question) }}
                                                                </button>
                                                            </h2>
                                                        </div>
                                                        <div id="collapse{{ $faqs[$i]->id }}" class="collapse"
                                                            aria-labelledby="heading{{ $faqs[$i]->id }}"
                                                            data-parent="#accordionExample2">
                                                            <div class="card-body">
                                                                {{ convertUtf8($faqs[$i]->answer) }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endfor
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            </section>
        @endif
        <!--   FAQ section end   -->
    @endif
@endsection

@section('scripts')
    @if ($be->theme_version == 'dark')
        <script src="{{ asset_v('assets/front/js/dark-faq.js') }}"></script>
    @else
        <script src="{{ asset_v('assets/front/js/category-offcanvas.js') }}"></script>
        <script>
            // Blog's category sidebar links navigate to a filtered page reload; FAQ's
            // categories are Bootstrap tab panes on the same page instead, so the
            // active-state highlight has to be toggled on tab switch rather than
            // read from the current URL. Both themes' <li> markup is targeted at
            // once (unused class names are simply never matched by either theme's CSS).
            // Class selector (not the #faqCatList id) so it also matches the cloned
            // copy inside the mobile Categories offcanvas — the clone's id is
            // stripped on clone (see category-offcanvas.js) to avoid a duplicate id,
            // but its classes (incl. faq-cat-list) are preserved.
            $(document).on('shown.bs.tab', '.faq-cat-list a[data-toggle="tab"]', function() {
                var $li = $(this).closest('li');
                $li.siblings().removeClass('is-active active');
                $li.addClass('is-active active');
            });
        </script>
    @endif
@endsection
