@extends("front.$version.layout")

@section('pagename')
- {{__('FAQ')}}
@endsection

@section('meta-keywords', "$be->faq_meta_keywords")
@section('meta-description', "$be->faq_meta_description")

@section('breadcrumb-title', convertUtf8($bs->faq_title))
@section('breadcrumb-subtitle', convertUtf8($bs->faq_subtitle))
@section('breadcrumb-link', __('FAQS'))

@if(!empty($bs->faq_breadcrumb_bg))
@section('breadcrumb-bg', asset('assets/front/img/'.$bs->faq_breadcrumb_bg))
@endif

@if(!empty($bs->faq_breadcrumb_overlay_color))
@section('breadcrumb-overlay-color', $bs->faq_breadcrumb_overlay_color)
@endif

@if(!empty($bs->faq_breadcrumb_overlay_opacity))
@section('breadcrumb-overlay-opacity', $bs->faq_breadcrumb_overlay_opacity)
@endif

@section('content')
  <!--   FAQ section start   -->

  @if ($bex->faq_category_status == 1)
      @if (count($categories) > 0)
          @if ($be->theme_version == 'dark')
              {{-- dark FAQ, categorized: same dark-svcp-section/dark-svcp-widget
                   sidebar pattern as the dark Blog page, sidebar on the right --}}
              <div class="dark-svcp-section dark-faq-section">
                  <button type="button" class="dark-pf-oc-trigger" id="pfOcToggle" aria-expanded="false">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M7 12h10M10 18h4" stroke-linecap="round" /></svg>
                      {{ __('Categories') }}
                  </button>
                  <div class="dark-svcp-inner">
                      <div>
                          <div class="faq-details-wrapper">
                              <div class="tab-content">
                                  @foreach ($categories as $category)
                                      <div class="tab-pane {{ $loop->iteration == 1 ? 'show active' : '' }} fade" id="{{ 'category' . $category->id }}">
                                          <div class="accordion" id="{{ 'accordion' . $category->id }}">
                                              @php
                                                  $qas = \App\Faq::where('category_id', $category->id)->where('status', 1)->orderBy('serial_number', 'ASC')->get();
                                              @endphp

                                              @foreach ($qas as $qa)
                                                  <div class="card mb-30 reveal-text" style="--d:{{ ($loop->index % 6) * 0.07 }}s">
                                                      <a class="collapsed card-header" id="heading1" href="#" data-toggle="collapse" data-target="{{ '#collapse' . $qa->id }}" aria-expanded="{{ $loop->iteration == 1 ? 'true' : 'false' }}" aria-controls="{{ 'collapse' . $qa->id }}">
                                                          {{ $qa->question }}<span class="toggle_btn"></span>
                                                      </a>
                                                      <div id="{{ 'collapse' . $qa->id }}" class="collapse {{ $loop->iteration == 1 ? 'show' : '' }}" aria-labelledby="heading1" data-parent="{{ '#accordion' . $category->id }}">
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
                                          <a href="{{ '#category' . $category->id }}" data-toggle="tab">{{ $category->name }}</a>
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
                              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18" stroke-linecap="round" /></svg>
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
                                          <div class="tab-pane {{ $loop->iteration == 1 ? 'show active' : '' }} fade" id="{{ 'category' . $category->id }}">
                                              <div class="accordion" id="{{ 'accordion' . $category->id }}">
                                                  @php
                                                      $qas = \App\Faq::where('category_id', $category->id)->where('status', 1)->orderBy('serial_number', 'ASC')->get();
                                                  @endphp

                                                  @foreach ($qas as $qa)
                                                      <div class="card mb-30 reveal-text" style="--d:{{ ($loop->index % 6) * 0.07 }}s">
                                                          <a class="collapsed card-header" id="heading1" href="#" data-toggle="collapse" data-target="{{ '#collapse' . $qa->id }}" aria-expanded="{{ $loop->iteration == 1 ? 'true' : 'false' }}" aria-controls="{{ 'collapse' . $qa->id }}">
                                                              {{ $qa->question }}<span class="toggle_btn"></span>
                                                          </a>
                                                          <div id="{{ 'collapse' . $qa->id }}" class="collapse {{ $loop->iteration == 1 ? 'show' : '' }}" aria-labelledby="heading1" data-parent="{{ '#accordion' . $category->id }}">
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
                                                  <li class="single-category {{ $loop->iteration == 1 ? 'active' : '' }}">
                                                      <a href="{{ '#category' . $category->id }}" data-toggle="tab">{{ $category->name }}</a>
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
                              <button type="button" class="pf-oc-close" id="pfOcClose" aria-label="Close">&times;</button>
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
            <div class="col-lg-8 offset-lg-2 py-y @if ($be->theme_version == 'dark') dark-svcp-empty @else bg-light @endif">
                <h3 class="text-center">{{ __('No FAQ Found!') }}</h3>
            </div>
            @else
            <div class="col-lg-12">
                <div class="faq-section py-0">
                    <div class="row">
                        <div class="col-lg-6">
                           <div class="accordion" id="accordionExample1">
                              @for ($i=0; $i < ceil(count($faqs)/2); $i++)
                              <div class="card reveal-text" style="--d:{{ ($i % 6) * 0.07 }}s">
                                 <div class="card-header" id="heading{{$faqs[$i]->id}}">
                                    <h2 class="mb-0">
                                       <button class="btn btn-link collapsed btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse{{$faqs[$i]->id}}" aria-expanded="false" aria-controls="collapse{{$faqs[$i]->id}}">
                                       {{convertUtf8($faqs[$i]->question)}}
                                       </button>
                                    </h2>
                                 </div>
                                 <div id="collapse{{$faqs[$i]->id}}" class="collapse" aria-labelledby="heading{{$faqs[$i]->id}}" data-parent="#accordionExample1">
                                    <div class="card-body">
                                       {{convertUtf8($faqs[$i]->answer)}}
                                    </div>
                                 </div>
                              </div>
                              @endfor
                           </div>
                        </div>
                        <div class="col-lg-6">
                           <div class="accordion" id="accordionExample2">
                              @for ($i=ceil(count($faqs)/2); $i < count($faqs); $i++)
                              <div class="card reveal-text" style="--d:{{ ($i % 6) * 0.07 }}s">
                                 <div class="card-header" id="heading{{$faqs[$i]->id}}">
                                    <h2 class="mb-0">
                                       <button class="btn btn-link collapsed btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse{{$faqs[$i]->id}}" aria-expanded="false" aria-controls="collapse{{$faqs[$i]->id}}">
                                       {{convertUtf8($faqs[$i]->question)}}
                                       </button>
                                    </h2>
                                 </div>
                                 <div id="collapse{{$faqs[$i]->id}}" class="collapse" aria-labelledby="heading{{$faqs[$i]->id}}" data-parent="#accordionExample2">
                                    <div class="card-body">
                                       {{convertUtf8($faqs[$i]->answer)}}
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
@endsection

@section('scripts')
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
    $(document).on('shown.bs.tab', '.faq-cat-list a[data-toggle="tab"]', function () {
        var $li = $(this).closest('li');
        $li.siblings().removeClass('is-active active');
        $li.addClass('is-active active');
    });
</script>
@endsection
