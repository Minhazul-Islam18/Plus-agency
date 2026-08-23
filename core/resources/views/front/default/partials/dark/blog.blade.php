<div class="blog-section dark-blog-section dark-section-seam section-padding"
    @if (!empty($be->blog_bg)) style="background-image: url('{{ asset('assets/front/img/' . $be->blog_bg) }}'); background-size:cover; position: relative; overflow: hidden;" @endif>
    @if (!empty($be->blog_bg))
        <div class="blog-overlay"
            style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: #{{ $be->blog_overlay_color ?? '000000' }}; opacity: {{ $be->blog_overlay_opacity ?? '0.6' }}; z-index: 0; pointer-events: none;">
        </div>
    @endif
    <div class="container" style="position: relative; z-index: 2;">
        <div class="dark-blog-head reveal-text" style="--d:.05s">
            <div class="dark-blog-head-text">
                <span class="section-eyebrow">{{ convertUtf8($bs->blog_section_title) }}</span>
                <h2 class="gradient-shine-heading">{{ convertUtf8($bs->blog_section_subtitle) }}</h2>
            </div>
            <div class="dark-blog-actions">
                @if (Route::has('front.blogs'))
                    <a href="{{ route('front.blogs') }}" class="dark-intro-cta">
                        <span>{{ __('Show all') }}</span><i>&#8594;</i>
                    </a>
                @endif
                @if ($blogs->count() > 1)
                    <div class="dark-blog-nav">
                        <button type="button" id="darkBlogPrev" aria-label="{{ __('Previous') }}"><svg viewBox="0 0 24 24"><path d="M15 6l-6 6 6 6"/></svg></button>
                        <button type="button" id="darkBlogNext" aria-label="{{ __('Next') }}"><svg viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></svg></button>
                    </div>
                @endif
            </div>
        </div>

        <div class="dark-blog-carousel dark-glass-carousel owl-carousel owl-theme">
            @foreach ($blogs as $key => $blog)
                <a href="{{ route('front.blogdetails', $blog->slug) }}" class="dark-blog-card reveal-card" style="--d:{{ ($key % 3) * 0.1 }}s">
                    <div class="blog-img-wrapper">
                        <img src="{{ asset('assets/front/img/blogs/' . $blog->main_image) }}" alt="">
                        @php
                            $blogDate = \Carbon\Carbon::parse($blog->created_at)->locale("$currentLang->code");
                            $blogDate = $blogDate->translatedFormat('jS F, Y');
                        @endphp
                        <span class="blog-date-badge">{{ $blogDate }}</span>
                    </div>
                    <div class="blog-txt">
                        <p class="date"><small>{{ __('By') }} <span class="username">{{ __('Admin') }}</span></small></p>

                        <h4 class="blog-title">{{ convertUtf8($blog->title) }}</h4>

                        <p class="blog-summary">{{ strip_tags($blog->content) }}</p>

                        <span class="glass-panel-link">{{ __('Read More') }} &#8594;</span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</div>
