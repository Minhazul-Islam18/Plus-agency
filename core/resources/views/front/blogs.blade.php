@extends("front.$version.layout")

@section('pagename')
 -
 @if (empty($category))
 {{__('All')}}
 @else
 {{convertUtf8($category->name)}}
 @endif
 {{__('Blogs')}}
@endsection

@section('meta-keywords', "$be->blogs_meta_keywords")
@section('meta-description', "$be->blogs_meta_description")

@section('breadcrumb-title', convertUtf8($bs->blog_title))
@section('breadcrumb-subtitle', convertUtf8($bs->blog_subtitle))
@section('breadcrumb-link', __('Latest Blogs'))

@if(!empty($bs->blog_breadcrumb_bg))
@section('breadcrumb-bg', asset('assets/front/img/'.$bs->blog_breadcrumb_bg))
@endif

@if(!empty($bs->blog_breadcrumb_overlay_color))
@section('breadcrumb-overlay-color', $bs->blog_breadcrumb_overlay_color)
@endif

@if(!empty($bs->blog_breadcrumb_overlay_opacity))
@section('breadcrumb-overlay-opacity', $bs->blog_breadcrumb_overlay_opacity)
@endif

@if ($be->theme_version == 'dark')
  @section('breadcrumb-ledger')
    <div class="dark-bc-ledger-item">
      <span class="dark-bc-ledger-num">{{ str_pad($blogs->total(), 2, '0', STR_PAD_LEFT) }}</span>
      <span class="dark-bc-ledger-label">{{ __('Articles Published') }}</span>
    </div>
    <div class="dark-bc-ledger-item">
      <span class="dark-bc-ledger-num">{{ str_pad(count($bcats), 2, '0', STR_PAD_LEFT) }}</span>
      <span class="dark-bc-ledger-label">{{ __('Categories Covered') }}</span>
    </div>
  @endsection
@endif

@section('content')

@if ($be->theme_version == 'dark')
  <!--    dark blog page start   -->
  <div class="dark-svcp-section">
    <button type="button" class="dark-pf-oc-trigger" id="pfOcToggle" aria-expanded="false">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M7 12h10M10 18h4" stroke-linecap="round" /></svg>
        {{ __('Sidebar') }}
    </button>
    <div class="dark-svcp-inner">
      <div>
        {{-- Search + Year filter — left column, horizontal, same
             .faqx-search/.faqx-select pattern as the FAQ page's toolbar.
             A real GET form (not FAQ's client-side JS filter): the blog
             list is server-paginated, so filtering has to round-trip. --}}
        <form method="GET" action="{{ route('front.blogs') }}" class="faqx-toolbar dark-blogp-toolbar">
          <input type="hidden" name="category" value="{{ request()->input('category') }}">
          <input type="hidden" name="month" value="{{ request()->input('month') }}">
          <div class="faqx-search">
            <input type="text" name="term" placeholder="{{ __('Search Blogs') }}" value="{{ request()->input('term') }}">
            <button type="submit" class="faqx-search-btn" style="padding:0"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><circle cx="11" cy="11" r="7" /><path d="M21 21l-4.3-4.3" /></svg></button>
          </div>
          @if (count($years) > 0)
            <div class="faqx-select">
              <select name="year" aria-label="{{ __('All years') }}" onchange="this.form.submit()">
                <option value="">{{ __('All years') }}</option>
                @foreach ($years as $y)
                  <option value="{{ $y }}" {{ (string) $year === (string) $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
              </select>
            </div>
          @endif
        </form>

        @if (count($blogs) == 0)
          <div class="dark-svcp-empty">
            <h3>{{ __('NO BLOG FOUND') }}</h3>
          </div>
        @else
          @foreach ($blogs as $key => $blog)
            @php
              $blogDate = !empty($currentLang) ? \Carbon\Carbon::parse($blog->created_at)->locale($currentLang->code) : \Carbon\Carbon::parse($blog->created_at)->locale('en');
            @endphp
            @if ($key == 0)
              <div class="dark-blogp-feat reveal-left">
                <div class="dark-blogp-feat-img">
                  <img class="lazy" data-src="{{ asset('assets/front/img/blogs/' . $blog->main_image) }}" alt="">
                </div>
                <div class="dark-blogp-feat-body">
                  <div class="dark-blogp-meta">
                    <span>{{ convertUtf8($blog->author_name) }}</span><span class="dot"></span><span class="date">{{ $blogDate->translatedFormat('jS F, Y') }}</span>
                  </div>
                  <h2><a href="{{ route('front.blogdetails', [$blog->slug]) }}">{{ strlen($blog->title) > 90 ? mb_substr($blog->title, 0, 90, 'utf-8') . '...' : $blog->title }}</a></h2>
                  <p>{!! strlen(strip_tags($blog->content)) > 150 ? mb_substr(strip_tags($blog->content), 0, 150, 'utf-8') . '...' : strip_tags($blog->content) !!}</p>
                  <a href="{{ route('front.blogdetails', [$blog->slug]) }}" class="dark-blogp-link">{{ __('Read More') }} <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12h14M13 6l6 6-6 6" /></svg></a>
                </div>
              </div>
              <div class="dark-blogp-grid">
            @endif

            @if ($key > 0)
              <div class="dark-blogp-card reveal-card" style="--d:{{ ($key % 3) * 0.1 }}s">
                <div class="dark-blogp-card-img">
                  <span class="dark-blogp-date-badge"><span class="d">{{ $blogDate->format('d') }}</span><span class="m">{{ $blogDate->translatedFormat('M') }}</span></span>
                  <img class="lazy" data-src="{{ asset('assets/front/img/blogs/' . $blog->main_image) }}" alt="">
                </div>
                <div class="dark-blogp-card-body">
                  <h3><a href="{{ route('front.blogdetails', [$blog->slug]) }}">{{ strlen($blog->title) > 60 ? mb_substr($blog->title, 0, 60, 'utf-8') . '...' : $blog->title }}</a></h3>
                  <p>{!! strlen(strip_tags($blog->content)) > 90 ? mb_substr(strip_tags($blog->content), 0, 90, 'utf-8') . '...' : strip_tags($blog->content) !!}</p>
                  <a href="{{ route('front.blogdetails', [$blog->slug]) }}" class="dark-blogp-link">{{ __('Read More') }} <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12h14M13 6l6 6-6 6" /></svg></a>
                </div>
              </div>
            @endif
          @endforeach
          </div>
        @endif

        @if ($blogs->hasPages())
          <nav class="dark-svcp-pagination">
            {{ $blogs->appends(['term' => request()->input('term'), 'month' => request()->input('month'), 'year' => request()->input('year'), 'category' => request()->input('category')])->links('vendor.pagination.dark-glass') }}
          </nav>
        @endif
      </div>

      <div id="pfCatSource">
        {{-- Search moved into the left-column toolbar above (was here) —
             Categories is now the first pinned widget in the mobile
             offcanvas instead. --}}
        <div class="dark-svcp-widget" data-pf-pin="top">
          <h4>{{ __('Categories') }}</h4>
          <ul class="dark-svcp-cat-list">
            @foreach ($bcats as $key => $bcat)
              <li class="{{ request()->input('category') == $bcat->slug ? 'is-active' : '' }}">
                <a href="{{ route('front.blogs', ['term' => request()->input('term'), 'category' => $bcat->slug, 'month' => request()->input('month'), 'year' => request()->input('year')]) }}">{{ convertUtf8($bcat->name) }}</a>
              </li>
            @endforeach
          </ul>
        </div>

        @if (count($archives) > 0)
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
        @endif

        <div class="dark-svcp-widget dark-svcp-newsletter" data-pf-pin="bottom">
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
    </div>

    <div class="dark-oc-backdrop" id="pfOcBackdrop"></div>
    <nav class="dark-oc-panel" id="pfOcPanel">
        <div class="dark-oc-head">
            <span class="dark-oc-head-logo">{{ __('Sidebar') }}</span>
            <button type="button" class="dark-oc-close" id="pfOcClose" aria-label="Close">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18" stroke-linecap="round" /></svg>
            </button>
        </div>
        <div class="dark-oc-nav" id="pfOcBody"></div>
    </nav>
  </div>
  <!--    dark blog page end   -->
@else
  <!--    blog lists start   -->
  <div class="blog-lists section-padding">
     <div class="container">
        <button type="button" class="pf-oc-trigger" id="pfOcToggle" aria-expanded="false">
            <i class="fas fa-filter"></i> {{ __('Sidebar') }}
        </button>
        <div class="row">
           <div class="col-lg-8">
              <div class="row">
                @if (count($blogs) == 0)
                  <div class="col-md-12">
                    <div class="bg-light py-5">
                      <h3 class="text-center">{{__('NO BLOG FOUND')}}</h3>
                    </div>
                  </div>
                @else
                  @foreach ($blogs as $key => $blog)
                    <div class="col-md-6">
                       <div class="single-blog reveal-card" style="--d:{{ ($key % 2) * 0.1 }}s">
                          <div class="blog-img-wrapper">
                             <img class="lazy" data-src="{{asset('assets/front/img/blogs/'.$blog->main_image)}}" alt="">
                          </div>
                          <div class="blog-txt">
                            @php
                                if (!empty($currentLang)) {
                                    $blogDate = \Carbon\Carbon::parse($blog->created_at)->locale("$currentLang->code");
                                } else {
                                    $blogDate = \Carbon\Carbon::parse($blog->created_at)->locale("en");
                                }

                                $blogDate = $blogDate->translatedFormat('jS F, Y');
                            @endphp
                             <p class="date"><small>{{__('By')}} <span class="username">{{ convertUtf8($blog->author_name) }}</span></small> | <small>{{$blogDate}}</small> </p>

                             <h4 class="blog-title"><a href="{{route('front.blogdetails', [$blog->slug])}}">{{strlen($blog->title) > 40 ? mb_substr($blog->title, 0, 40, 'utf-8') . '...' : $blog->title}}</a></h4>

                             <p class="blog-summary">{!! strlen(strip_tags($blog->content)) > 100 ? mb_substr(strip_tags($blog->content), 0, 100, 'utf-8') . '...' : strip_tags($blog->content) !!}</p>

                             <a href="{{route('front.blogdetails', [$blog->slug])}}" class="readmore-btn"><span>{{__('Read More')}}</span></a>

                          </div>
                       </div>
                    </div>
                  @endforeach
                @endif
              </div>
              @if ($blogs->total() > 6)
                <div class="row">
                   <div class="col-md-12">
                      <nav class="pagination-nav {{$blogs->total() > 6 ? 'mb-4' : ''}}">
                        {{$blogs->appends(['term'=>request()->input('term'), 'month'=>request()->input('month'), 'year'=>request()->input('year'), 'category' => request()->input('category')])->links()}}
                      </nav>
                   </div>
                </div>
              @endif
           </div>
           <!--    blog sidebar section start   -->
           <div class="col-lg-4">
              <div class="sidebar" id="pfCatSource">
                 <div class="blog-sidebar-widgets" data-pf-pin="top">
                    <div class="searchbar-form-section">
                       <form action="{{route('front.blogs', ['category' => request()->input('category'), 'month' => request()->input('month'), 'year' => request()->input('year')])}}" method="GET">
                          <div class="searchbar">
                             <input name="category" type="hidden" value="{{request()->input('category')}}">
                             <input name="month" type="hidden" value="{{request()->input('month')}}">
                             <input name="year" type="hidden" value="{{request()->input('year')}}">
                             <input name="term" type="text" placeholder="{{__('Search Blogs')}}" value="{{request()->input('term')}}">
                             <button type="submit"><i class="fa fa-search"></i></button>
                          </div>
                       </form>
                    </div>
                 </div>
                 <div class="blog-sidebar-widgets category-widget">
                    <div class="category-lists job">
                       <h4>{{__('Categories')}}</h4>
                       <ul>
                          @foreach ($bcats as $key => $bcat)
                            <li class="single-category @if(request()->input('category') == $bcat->slug) active @endif"><a href="{{route('front.blogs', ['term'=>request()->input('term'), 'category'=>$bcat->slug, 'month' => request()->input('month'), 'year' => request()->input('year')])}}">{{convertUtf8($bcat->name)}}</a></li>
                          @endforeach
                       </ul>
                    </div>
                 </div>
                 @if (count($archives) > 0)
                 <div class="blog-sidebar-widgets category-widget">
                    <div class="category-lists job">
                       <h4>{{__('Archives')}}</h4>
                       <ul>
                          @foreach ($archives as $key => $archive)
                            @php
                              $myArr = explode('-', $archive->date);
                              $monthNum  = $myArr[0];
                              $dateObj   = DateTime::createFromFormat('!m', $monthNum);
                              $monthName = $dateObj->format('F');
                            @endphp
                            <li class="single-category @if(request()->input('month') == $myArr[0] && request()->input('year') == $myArr[1]) active @endif">
                                <a href="{{route('front.blogs', ['term'=>request()->input('term'), 'category'=>request()->input('category'),'month'=>$myArr[0], 'year'=>$myArr[1]])}}">

                                    @php
                                        if (!empty($currentLang)) {
                                            $monthName = \Carbon\Carbon::parse($monthName)->locale("$currentLang->code");
                                            $year = \Carbon\Carbon::parse($myArr[1])->locale("$currentLang->code");
                                        } else {
                                            $monthName = \Carbon\Carbon::parse($monthName)->locale("en");
                                            $year = \Carbon\Carbon::parse($myArr[1])->locale("en");
                                        }

                                        $monthName = $monthName->translatedFormat('F');
                                        $year = $year->translatedFormat('Y');
                                    @endphp

                                    {{$monthName}} {{$year}}
                                </a>
                            </li>
                          @endforeach
                       </ul>
                    </div>
                 </div>
                 @endif
                 <div class="subscribe-section" data-pf-pin="bottom">
                    <span>{{__('SUBSCRIBE')}}</span>
                    <h3>{{__('SUBSCRIBE FOR NEWSLETTER')}}</h3>
                    <form id="subscribeForm" class="subscribe-form" action="{{route('front.subscribe')}}" method="POST">
                       @csrf
                       <div class="form-element"><input name="email" type="email" placeholder="{{__('Email')}}"></div>
                       <p id="erremail" class="text-danger mb-3 err-email"></p>
                       <div class="form-element"><input type="submit" value="{{__('Subscribe')}}"></div>
                    </form>
                 </div>
              </div>
           </div>
           <!--    blog sidebar section end   -->
        </div>

        <div class="pf-oc-backdrop" id="pfOcBackdrop"></div>
        <nav class="pf-oc-panel" id="pfOcPanel">
            <div class="pf-oc-head">
                <span>{{ __('Sidebar') }}</span>
                <button type="button" class="pf-oc-close" id="pfOcClose" aria-label="Close">&times;</button>
            </div>
            <div class="pf-oc-nav" id="pfOcBody"></div>
        </nav>
     </div>
  </div>
  <!--    blog lists end   -->
@endif
@endsection

@section('scripts')
    <script src="{{ asset_v('assets/front/js/category-offcanvas.js') }}"></script>
@endsection
