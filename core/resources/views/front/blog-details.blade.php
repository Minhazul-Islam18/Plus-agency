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


@section('content')

@if ($be->theme_version == 'dark')
    @php
        $blogDate = !empty($currentLang) ? \Carbon\Carbon::parse($blog->created_at)->locale($currentLang->code) : \Carbon\Carbon::parse($blog->created_at)->locale('en');
        $readMins = max(1, (int) ceil(str_word_count(strip_tags($blog->content)) / 200));
    @endphp
    <!--    dark blog details section start   -->
    <div class="dark-svcp-section">
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
                        <div class="name">{{ __('Admin') }}</div>
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

                <div class="dark-article-prose">
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
                            @foreach ($bcats as $key => $bcat)
                                <li class="{{ request()->input('category') == $bcat->slug ? 'is-active' : '' }}">
                                    <a href="{{ route('front.blogs', ['term' => request()->input('term'), 'category' => $bcat->slug, 'month' => request()->input('month'), 'year' => request()->input('year')]) }}">{{ convertUtf8($bcat->name) }}</a>
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
    </div>
    <!--    dark blog details section end   -->
@else
    <!--    blog details section start   -->
    <div class="blog-details-section section-padding">
        <div class="container">
            <div class="row">
                <div class="{{ $blog->sidebar == 1 ? 'col-lg-7' : 'col-12' }}">
                    <div class="blog-details">
                        <img class="blog-details-img-1 lazy"
                            data-src="{{ asset('assets/front/img/blogs/' . $blog->main_image) }}" alt="">
                        <small class="date">{{ date('F d, Y', strtotime($blog->created_at)) }} - {{ __('BY') }}
                            {{ __('Admin') }}</small>
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
@endsection
