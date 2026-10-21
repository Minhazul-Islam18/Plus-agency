@extends('admin.layout')

@if(!empty($abe->language) && $abe->language->rtl == 1)
@section('styles')
<style>
    form input,
    form textarea,
    form select {
        direction: rtl;
    }
    form .note-editor.note-frame .note-editing-area .note-editable {
        direction: rtl;
        text-align: right;
    }
</style>
@endsection
@endif

@section('content')
  <div class="page-header">
    <h4 class="page-title">SEO Informations</h4>
    <ul class="breadcrumbs">
      <li class="nav-home">
        <a href="{{route('admin.dashboard')}}">
          <i class="flaticon-home"></i>
        </a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">Basic Settings</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">SEO Informations</a>
      </li>
    </ul>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="card seo-admin-card">
        <div class="card-header">
          <div class="row">
            <div class="col-lg-9">
              <div class="card-title">Update SEO Informations</div>
            </div>
            <div class="col-lg-3">
              @if (!empty($langs))
                <select name="language" class="form-control" onchange="window.location='{{url()->current() . '?language='}}'+this.value">
                  <option value="" selected disabled>Select a Language</option>
                  @foreach ($langs as $lang)
                    <option value="{{$lang->code}}" {{$lang->code == request()->input('language') ? 'selected' : ''}}>{{$lang->name}}</option>
                  @endforeach
                </select>
              @endif
            </div>
          </div>
        </div>

        <form action="{{route('admin.seo.update', $lang_id)}}" method="post" id="seoForm">
          @csrf

          <div class="seo-shell">

            {{-- Tab rail --}}
            <div class="seo-rail">
              <div class="seo-rail-label">Pages</div>

              <button type="button" class="seo-tab-btn is-active" data-tab-btn="global">
                <span class="seo-tab-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.6 4 6 4 9s-1.5 6.4-4 9c-2.5-2.6-4-6-4-9s1.5-6.4 4-9Z"/></svg></span>
                <span class="seo-tab-label">Global Fallback</span>
                <span class="seo-tab-dot" data-dot="global"></span>
              </button>
              <button type="button" class="seo-tab-btn" data-tab-btn="home">
                <span class="seo-tab-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.6 4 6 4 9s-1.5 6.4-4 9c-2.5-2.6-4-6-4-9s1.5-6.4 4-9Z"/></svg></span>
                <span class="seo-tab-label">Home Page</span>
                <span class="seo-tab-dot" data-dot="home"></span>
              </button>
              <button type="button" class="seo-tab-btn" data-tab-btn="blogs">
                <span class="seo-tab-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.6 4 6 4 9s-1.5 6.4-4 9c-2.5-2.6-4-6-4-9s1.5-6.4 4-9Z"/></svg></span>
                <span class="seo-tab-label">Blog Listing</span>
                <span class="seo-tab-dot" data-dot="blogs"></span>
              </button>
              <button type="button" class="seo-tab-btn" data-tab-btn="portfolios">
                <span class="seo-tab-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.6 4 6 4 9s-1.5 6.4-4 9c-2.5-2.6-4-6-4-9s1.5-6.4 4-9Z"/></svg></span>
                <span class="seo-tab-label">Portfolio Listing</span>
                <span class="seo-tab-dot" data-dot="portfolios"></span>
              </button>
              <button type="button" class="seo-tab-btn" data-tab-btn="services">
                <span class="seo-tab-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.6 4 6 4 9s-1.5 6.4-4 9c-2.5-2.6-4-6-4-9s1.5-6.4 4-9Z"/></svg></span>
                <span class="seo-tab-label">Services Listing</span>
                <span class="seo-tab-dot" data-dot="services"></span>
              </button>
              <button type="button" class="seo-tab-btn" data-tab-btn="tenders">
                <span class="seo-tab-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.6 4 6 4 9s-1.5 6.4-4 9c-2.5-2.6-4-6-4-9s1.5-6.4 4-9Z"/></svg></span>
                <span class="seo-tab-label">Tenders Listing</span>
                <span class="seo-tab-dot" data-dot="tenders"></span>
              </button>
              <button type="button" class="seo-tab-btn" data-tab-btn="other">
                <span class="seo-tab-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="7" height="7" rx="1.5"/><rect x="13" y="4" width="7" height="7" rx="1.5"/><rect x="4" y="13" width="7" height="7" rx="1.5"/><rect x="13" y="13" width="7" height="7" rx="1.5"/></svg></span>
                <span class="seo-tab-label">Other Pages</span>
              </button>

              <div class="seo-rail-note">
                <div class="seo-rail-note-title">Fallback order</div>
                <div class="seo-rail-note-body">A page's own meta (Blog/Portfolio/Service/Tender/Page editors) → that page type's meta here → Global Fallback. First one filled in wins.</div>
              </div>
            </div>

            {{-- Panels --}}
            <div class="seo-main">

              {{-- Global --}}
              <div class="seo-panel is-active" data-tab-panel="global">
                <div class="seo-panel-head">
                  <h2>Global Fallback</h2>
                  <p>The last resort — used only when a page below (or an individual Blog/Portfolio/Service/Tender/custom Page) has nothing of its own set.</p>
                </div>
                <div class="seo-panel-grid seo-panel-grid--no-image">
                  <div class="seo-form-card">
                    <div class="seo-field">
                      <div class="seo-field-row">
                        <label>Global Meta Title</label>
                        <span class="seo-counter" data-counter-for="global-title">0 / 60</span>
                      </div>
                      <input type="text" class="form-control seo-title-input" data-field="title" name="meta_title" value="{{ $seoBs->meta_title }}" placeholder="Appended after the site name in <title>">
                    </div>
                    <div class="seo-field">
                      <label>Global Meta Keywords</label>
                      <input type="text" class="form-control" name="meta_keywords" value="{{ $seoBs->meta_keywords }}" placeholder="Enter meta keywords" data-role="tagsinput">
                    </div>
                    <div class="seo-field">
                      <div class="seo-field-row">
                        <label>Global Meta Description</label>
                        <span class="seo-counter" data-counter-for="global-desc">0 / 160</span>
                      </div>
                      <textarea class="form-control seo-desc-input" data-field="description" name="meta_description" rows="3" placeholder="Enter meta description">{{ $seoBs->meta_description }}</textarea>
                    </div>
                    @include('admin.partials.og-image-picker', ['ogSerial' => 15, 'ogFieldName' => 'default_social_image', 'ogCurrentUrl' => !empty($seoBs->default_social_image) ? asset('assets/front/img/' . $seoBs->default_social_image) : null, 'ogHelperText' => 'Used for Open Graph/Twitter previews on any page with no image of its own (e.g. the homepage) and no page-type image override below. Shared across every language.'])
                  </div>
                  @include('admin.partials._seo-preview-card', ['tab' => 'global', 'path' => '/', 'ogSerial' => 15, 'ogCurrentUrl' => !empty($seoBs->default_social_image) ? asset('assets/front/img/' . $seoBs->default_social_image) : null])
                </div>
              </div>

              {{-- Home --}}
              <div class="seo-panel" data-tab-panel="home">
                <div class="seo-panel-head">
                  <h2>Home Page</h2>
                  <p>Meta for the homepage. Leave blank to fall through to the Global Fallback.</p>
                </div>
                <div class="seo-panel-grid">
                  <div class="seo-form-card">
                    <div class="seo-field">
                      <div class="seo-field-row">
                        <label>Meta Title for Home Page</label>
                        <span class="seo-counter" data-counter-for="home-title">0 / 60</span>
                      </div>
                      <input type="text" class="form-control seo-title-input" data-field="title" name="home_meta_title" value="{{$abe->home_meta_title}}" placeholder="Leave blank to use the Global Fallback">
                    </div>
                    <div class="seo-field">
                      <label>Meta Keywords for Home Page</label>
                      <input type="text" class="form-control" name="home_meta_keywords" value="{{$abe->home_meta_keywords}}" placeholder="Enter meta keywords" data-role="tagsinput">
                    </div>
                    <div class="seo-field">
                      <div class="seo-field-row">
                        <label>Meta Description for Home Page</label>
                        <span class="seo-counter" data-counter-for="home-desc">0 / 160</span>
                      </div>
                      <textarea class="form-control seo-desc-input" data-field="description" name="home_meta_description" rows="3" placeholder="Enter meta description">{{$abe->home_meta_description}}</textarea>
                    </div>
                    @include('admin.partials.og-image-picker', ['ogSerial' => 10, 'ogFieldName' => 'home_og_image', 'ogCurrentUrl' => !empty($abe->home_og_image) ? asset('assets/front/img/' . $abe->home_og_image) : null])
                  </div>
                  @include('admin.partials._seo-preview-card', ['tab' => 'home', 'path' => '/', 'ogSerial' => 10, 'ogCurrentUrl' => !empty($abe->home_og_image) ? asset('assets/front/img/' . $abe->home_og_image) : null])
                </div>
              </div>

              {{-- Blogs --}}
              <div class="seo-panel" data-tab-panel="blogs">
                <div class="seo-panel-head">
                  <h2>Blog Listing</h2>
                  <p>Meta for the "All Blogs" listing page. A category filter keeps its own dynamic title.</p>
                </div>
                <div class="seo-panel-grid">
                  <div class="seo-form-card">
                    <div class="seo-field">
                      <div class="seo-field-row">
                        <label>Meta Title for Blogs Page</label>
                        <span class="seo-counter" data-counter-for="blogs-title">0 / 60</span>
                      </div>
                      <input type="text" class="form-control seo-title-input" data-field="title" name="blogs_meta_title" value="{{$abe->blogs_meta_title}}" placeholder="Leave blank to use the Global Fallback">
                    </div>
                    <div class="seo-field">
                      <label>Meta Keywords for Blogs Page</label>
                      <input type="text" class="form-control" name="blogs_meta_keywords" value="{{$abe->blogs_meta_keywords}}" placeholder="Enter meta keywords" data-role="tagsinput">
                    </div>
                    <div class="seo-field">
                      <div class="seo-field-row">
                        <label>Meta Description for Blogs Page</label>
                        <span class="seo-counter" data-counter-for="blogs-desc">0 / 160</span>
                      </div>
                      <textarea class="form-control seo-desc-input" data-field="description" name="blogs_meta_description" rows="3" placeholder="Enter meta description">{{$abe->blogs_meta_description}}</textarea>
                    </div>
                    @include('admin.partials.og-image-picker', ['ogSerial' => 13, 'ogFieldName' => 'blogs_og_image', 'ogCurrentUrl' => !empty($abe->blogs_og_image) ? asset('assets/front/img/' . $abe->blogs_og_image) : null])
                  </div>
                  @include('admin.partials._seo-preview-card', ['tab' => 'blogs', 'path' => '/blogs', 'ogSerial' => 13, 'ogCurrentUrl' => !empty($abe->blogs_og_image) ? asset('assets/front/img/' . $abe->blogs_og_image) : null])
                </div>
              </div>

              {{-- Portfolios --}}
              <div class="seo-panel" data-tab-panel="portfolios">
                <div class="seo-panel-head">
                  <h2>Portfolio Listing</h2>
                  <p>Meta for the "All Portfolios" listing page. A sector filter keeps its own dynamic title.</p>
                </div>
                <div class="seo-panel-grid">
                  <div class="seo-form-card">
                    <div class="seo-field">
                      <div class="seo-field-row">
                        <label>Meta Title for Portfolios</label>
                        <span class="seo-counter" data-counter-for="portfolios-title">0 / 60</span>
                      </div>
                      <input type="text" class="form-control seo-title-input" data-field="title" name="portfolios_meta_title" value="{{$abe->portfolios_meta_title}}" placeholder="Leave blank to use the Global Fallback">
                    </div>
                    <div class="seo-field">
                      <label>Meta Keywords for Portfolios</label>
                      <input type="text" class="form-control" name="portfolios_meta_keywords" value="{{$abe->portfolios_meta_keywords}}" placeholder="Enter meta keywords" data-role="tagsinput">
                    </div>
                    <div class="seo-field">
                      <div class="seo-field-row">
                        <label>Meta Description for Portfolios</label>
                        <span class="seo-counter" data-counter-for="portfolios-desc">0 / 160</span>
                      </div>
                      <textarea class="form-control seo-desc-input" data-field="description" name="portfolios_meta_description" rows="3" placeholder="Enter meta description">{{$abe->portfolios_meta_description}}</textarea>
                    </div>
                    @include('admin.partials.og-image-picker', ['ogSerial' => 12, 'ogFieldName' => 'portfolios_og_image', 'ogCurrentUrl' => !empty($abe->portfolios_og_image) ? asset('assets/front/img/' . $abe->portfolios_og_image) : null])
                  </div>
                  @include('admin.partials._seo-preview-card', ['tab' => 'portfolios', 'path' => '/portfolios', 'ogSerial' => 12, 'ogCurrentUrl' => !empty($abe->portfolios_og_image) ? asset('assets/front/img/' . $abe->portfolios_og_image) : null])
                </div>
              </div>

              {{-- Services --}}
              <div class="seo-panel" data-tab-panel="services">
                <div class="seo-panel-head">
                  <h2>Services Listing</h2>
                  <p>Meta for the "All Services" listing page. A category filter keeps its own dynamic title.</p>
                </div>
                <div class="seo-panel-grid">
                  <div class="seo-form-card">
                    <div class="seo-field">
                      <div class="seo-field-row">
                        <label>Meta Title for Services Page</label>
                        <span class="seo-counter" data-counter-for="services-title">0 / 60</span>
                      </div>
                      <input type="text" class="form-control seo-title-input" data-field="title" name="services_meta_title" value="{{$abe->services_meta_title}}" placeholder="Leave blank to use the Global Fallback">
                    </div>
                    <div class="seo-field">
                      <label>Meta Keywords for Services Page</label>
                      <input type="text" class="form-control" name="services_meta_keywords" value="{{$abe->services_meta_keywords}}" placeholder="Enter meta keywords" data-role="tagsinput">
                    </div>
                    <div class="seo-field">
                      <div class="seo-field-row">
                        <label>Meta Description for Services Page</label>
                        <span class="seo-counter" data-counter-for="services-desc">0 / 160</span>
                      </div>
                      <textarea class="form-control seo-desc-input" data-field="description" name="services_meta_description" rows="3" placeholder="Enter meta description">{{$abe->services_meta_description}}</textarea>
                    </div>
                    @include('admin.partials.og-image-picker', ['ogSerial' => 11, 'ogFieldName' => 'services_og_image', 'ogCurrentUrl' => !empty($abe->services_og_image) ? asset('assets/front/img/' . $abe->services_og_image) : null])
                  </div>
                  @include('admin.partials._seo-preview-card', ['tab' => 'services', 'path' => '/services', 'ogSerial' => 11, 'ogCurrentUrl' => !empty($abe->services_og_image) ? asset('assets/front/img/' . $abe->services_og_image) : null])
                </div>
              </div>

              {{-- Tenders --}}
              <div class="seo-panel" data-tab-panel="tenders">
                <div class="seo-panel-head">
                  <h2>Tenders Listing</h2>
                  <p>Meta for the public tenders listing page.</p>
                </div>
                <div class="seo-panel-grid">
                  <div class="seo-form-card">
                    <div class="seo-field">
                      <div class="seo-field-row">
                        <label>Meta Title for Tenders Page</label>
                        <span class="seo-counter" data-counter-for="tenders-title">0 / 60</span>
                      </div>
                      <input type="text" class="form-control seo-title-input" data-field="title" name="tender_meta_title" value="{{$seoBex->tender_meta_title}}" placeholder="Leave blank to use the Global Fallback">
                    </div>
                    <div class="seo-field">
                      <label>Meta Keywords for Tenders Page</label>
                      <input type="text" class="form-control" name="tender_meta_keywords" value="{{$seoBex->tender_meta_keywords}}" placeholder="Enter meta keywords" data-role="tagsinput">
                    </div>
                    <div class="seo-field">
                      <div class="seo-field-row">
                        <label>Meta Description for Tenders Page</label>
                        <span class="seo-counter" data-counter-for="tenders-desc">0 / 160</span>
                      </div>
                      <textarea class="form-control seo-desc-input" data-field="description" name="tender_meta_description" rows="3" placeholder="Enter meta description">{{$seoBex->tender_meta_description}}</textarea>
                    </div>
                    @include('admin.partials.og-image-picker', ['ogSerial' => 14, 'ogFieldName' => 'tender_og_image', 'ogCurrentUrl' => !empty($seoBex->tender_og_image) ? asset('assets/front/img/' . $seoBex->tender_og_image) : null])
                  </div>
                  @include('admin.partials._seo-preview-card', ['tab' => 'tenders', 'path' => '/tenders', 'ogSerial' => 14, 'ogCurrentUrl' => !empty($seoBex->tender_og_image) ? asset('assets/front/img/' . $seoBex->tender_og_image) : null])
                </div>
              </div>

              {{-- Other pages — keywords/description only, no meta_title/og_image column on these yet --}}
              <div class="seo-panel" data-tab-panel="other">
                <div class="seo-panel-head">
                  <h2>Other Pages</h2>
                  <p>These page types don't have an SEO title or social image override yet — just the meta keywords/description they've always had.</p>
                </div>
                <div class="seo-other-grid">
                  @foreach ([
                      'team' => 'Team Page',
                      'calendar' => 'Event Calendar Page',
                      'gallery' => 'Gallery Page',
                      'faq' => 'FAQ Page',
                      'contact' => 'Contact Page',
                      'quote' => 'Quote Page',
                      'cart' => 'Cart Page',
                      'checkout' => 'Checkout Page',
                      'login' => 'Login Page',
                      'register' => 'Register Page',
                      'forgot' => 'Forgot Password Page',
                  ] as $key => $label)
                    <div class="seo-other-card">
                      <div class="seo-other-card-title">{{ $label }}</div>
                      <div class="seo-field">
                        <label>Meta Keywords</label>
                        <input type="text" class="form-control" name="{{ $key }}_meta_keywords" value="{{ $abe->{$key.'_meta_keywords'} }}" placeholder="Enter meta keywords" data-role="tagsinput">
                      </div>
                      <div class="seo-field">
                        <label>Meta Description</label>
                        <textarea class="form-control" name="{{ $key }}_meta_description" rows="3" placeholder="Enter meta description">{{ $abe->{$key.'_meta_description'} }}</textarea>
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>

            </div>
          </div>

          <div class="card-footer">
            <div class="form">
              <div class="form-group from-show-notify row">
                <div class="col-12 text-center">
                  <button type="submit" id="displayNotif" class="btn btn-success seo-save-btn">Update</button>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

<style>
  /* Matches this admin's own dark theme tokens (body[data-background-color="dark"]
     in atlantis.min.css) instead of inventing a separate, near-black
     palette: body/rail tone #1a2035, card tone #202940 — never pure black. */
  .seo-admin-card { background: #202940; border: 1px solid rgba(181,181,181,0.1); }
  .seo-admin-card .card-header { border-bottom: 1px solid rgba(181,181,181,0.1); }

  .seo-shell { display: flex; min-height: 0; }

  .seo-rail {
    width: 264px; flex: 0 0 auto; padding: 20px 14px; display: flex; flex-direction: column; gap: 3px;
    border-right: 1px solid rgba(181,181,181,0.1); background: #1a2035;
  }
  .seo-rail-label { padding: 0 10px 10px; font-size: 11px; font-weight: 700; letter-spacing: .08em; color: #6b7280; text-transform: uppercase; }
  .seo-tab-btn {
    display: flex; align-items: center; gap: 11px; width: 100%; box-sizing: border-box; padding: 10px 12px;
    border-radius: 9px; border: none; cursor: pointer; text-align: left; font-size: 13.5px; font-weight: 500;
    background: transparent; color: #8b92a9;
  }
  .seo-tab-btn:hover { background: rgba(255,255,255,0.04); }
  .seo-tab-btn.is-active { background: rgba(91,141,239,0.16); color: #fff; font-weight: 700; }
  .seo-tab-icon { display: flex; width: 18px; height: 18px; flex: 0 0 auto; color: #6b7280; }
  .seo-tab-btn.is-active .seo-tab-icon { color: #5B8DEF; }
  .seo-tab-label { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .seo-tab-dot { width: 6px; height: 6px; border-radius: 50%; flex: 0 0 auto; background: rgba(255,255,255,0.14); }
  .seo-tab-dot.is-filled { background: #3FB950; }
  .seo-rail-note {
    margin-top: 18px; padding: 14px; border-radius: 10px; background: rgba(91,141,239,0.08); border: 1px solid rgba(91,141,239,0.2);
  }
  .seo-rail-note-title { font-size: 12px; font-weight: 700; color: #8EB3F5; margin-bottom: 5px; }
  .seo-rail-note-body { font-size: 11.5px; line-height: 1.5; color: #9aa1b4; }

  .seo-main { flex: 1; min-width: 0; padding: 28px 36px 48px; }
  .seo-panel { display: none; }
  .seo-panel.is-active { display: block; }
  .seo-panel-head { margin-bottom: 20px; }
  .seo-panel-head h2 { margin: 0 0 6px; font-size: 21px; font-weight: 700; color: #fff; }
  .seo-panel-head p { margin: 0; font-size: 13.5px; color: #9aa1b4; }

  .seo-panel-grid { display: grid; grid-template-columns: minmax(0,1fr) 360px; gap: 26px; align-items: start; }
  .seo-panel-grid--no-image { grid-template-columns: minmax(0,1fr) 360px; }
  @media (max-width: 1100px) { .seo-panel-grid { grid-template-columns: 1fr; } }

  .seo-form-card {
    background: #1a2035; border: 1px solid rgba(181,181,181,0.1); border-radius: 14px; padding: 24px;
    display: flex; flex-direction: column; gap: 18px; min-width: 0;
  }
  .seo-field { display: flex; flex-direction: column; gap: 7px; }
  .seo-field label { font-size: 13px; font-weight: 600; margin: 0; }
  .seo-field-row { display: flex; align-items: baseline; justify-content: space-between; }
  .seo-counter { font-family: 'JetBrains Mono', 'SFMono-Regular', monospace; font-size: 11px; color: #6b7280; }
  .seo-counter.is-warn { color: #D29922; }
  .seo-counter.is-over { color: #F85149; }

  .seo-admin-card .form-control:focus { border-color: #5B8DEF; box-shadow: 0 0 0 3px rgba(91,141,239,0.15); }
  .seo-admin-card textarea.form-control { resize: vertical; }

  .seo-preview-card {
    background: #1a2035; border: 1px solid rgba(181,181,181,0.1); border-radius: 14px; padding: 20px;
    display: flex; flex-direction: column; gap: 14px; position: sticky; top: 20px;
  }
  .seo-preview-label { font-size: 11px; font-weight: 700; letter-spacing: .08em; color: #6b7280; text-transform: uppercase; }
  .seo-serp-mock { background: #fff; border-radius: 10px; padding: 14px 16px; }
  .seo-serp-site { display: flex; align-items: center; gap: 8px; margin-bottom: 4px; }
  .seo-serp-favicon { width: 18px; height: 18px; border-radius: 50%; background: #5B8DEF; flex: 0 0 auto; }
  .seo-serp-sitename { font-family: arial, sans-serif; font-size: 13px; color: #202124; }
  .seo-serp-title { font-family: arial, sans-serif; font-size: 18px; line-height: 1.3; color: #1a0dab; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .seo-serp-url { font-family: arial, sans-serif; font-size: 12.5px; color: #006621; margin: 2px 0 4px; }
  .seo-serp-desc { font-family: arial, sans-serif; font-size: 13px; line-height: 1.4; color: #4d5156; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
  .seo-tier-row { display: flex; align-items: center; gap: 7px; }
  .seo-tier-dot { width: 7px; height: 7px; border-radius: 50%; background: #6b7280; }
  .seo-tier-dot.is-own { background: #3FB950; }
  .seo-tier-text { font-size: 11.5px; color: #9aa1b4; }
  .seo-tier-text strong { color: #fff; }

  .seo-social-mock { background: #fff; border-radius: 10px; overflow: hidden; }
  .seo-social-image { width: 100%; height: 130px; background: #F0EEE6; display: flex; align-items: center; justify-content: center; }
  .seo-social-body { padding: 11px 13px; }
  .seo-social-domain { font-family: arial, sans-serif; font-size: 10.5px; text-transform: uppercase; color: #8a8d91; margin-bottom: 2px; }
  .seo-social-title { font-family: arial, sans-serif; font-size: 13.5px; font-weight: 700; color: #1d2129; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .seo-social-desc { font-family: arial, sans-serif; font-size: 12px; color: #606770; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

  .seo-other-grid { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 20px; }
  @media (max-width: 900px) { .seo-other-grid { grid-template-columns: 1fr; } }
  .seo-other-card { background: #1a2035; border: 1px solid rgba(181,181,181,0.1); border-radius: 14px; padding: 20px; display: flex; flex-direction: column; gap: 14px; }
  .seo-other-card-title { font-size: 13.5px; font-weight: 700; color: #fff; }

  .seo-save-btn { padding: 10px 28px; }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    var siteName = @json($seoBs->website_title ?? config('app.name'));

    // ---- Tab switching ----
    var btns = document.querySelectorAll('[data-tab-btn]');
    var panels = document.querySelectorAll('[data-tab-panel]');
    btns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var id = btn.getAttribute('data-tab-btn');
        btns.forEach(function (b) { b.classList.toggle('is-active', b === btn); });
        panels.forEach(function (p) { p.classList.toggle('is-active', p.getAttribute('data-tab-panel') === id); });
      });
    });

    // ---- Char counters + live preview ----
    function counterClass(len, soft, hard) {
      if (len > hard) return 'is-over';
      if (len > soft) return 'is-warn';
      return '';
    }

    var richTabs = ['global', 'home', 'blogs', 'portfolios', 'services', 'tenders'];

    function refreshAll() {
      var globalPanel = document.querySelector('[data-tab-panel="global"]');
      var globalTitle = globalPanel.querySelector('.seo-title-input').value.trim();
      var globalDesc = globalPanel.querySelector('.seo-desc-input').value.trim();

      richTabs.forEach(function (tab) {
        var panel = document.querySelector('[data-tab-panel="' + tab + '"]');
        if (!panel) return;
        var titleInput = panel.querySelector('.seo-title-input');
        var descInput = panel.querySelector('.seo-desc-input');
        var title = titleInput.value.trim();
        var desc = descInput.value.trim();

        var titleCounter = document.querySelector('[data-counter-for="' + tab + '-title"]');
        var descCounter = document.querySelector('[data-counter-for="' + tab + '-desc"]');
        if (titleCounter) {
          titleCounter.textContent = title.length + ' / 60';
          titleCounter.className = 'seo-counter ' + counterClass(title.length, 50, 60);
        }
        if (descCounter) {
          descCounter.textContent = desc.length + ' / 160';
          descCounter.className = 'seo-counter ' + counterClass(desc.length, 140, 160);
        }

        var dot = document.querySelector('[data-dot="' + tab + '"]');
        if (dot) dot.classList.toggle('is-filled', !!title);

        var effectiveTitle, tierLabel, tierOn;
        if (title) {
          effectiveTitle = title;
          tierLabel = tab === 'global' ? 'Your global fallback title' : 'Your own title';
          tierOn = true;
        } else if (tab !== 'global' && globalTitle) {
          effectiveTitle = globalTitle;
          tierLabel = 'Global fallback title';
          tierOn = false;
        } else {
          effectiveTitle = siteName;
          tierLabel = 'Site name only — nothing set';
          tierOn = false;
        }

        var effectiveDesc = desc || (tab !== 'global' && globalDesc) || 'No description set — search engines may generate one automatically.';

        var serpTitle = panel.querySelector('.seo-serp-title');
        var serpDesc = panel.querySelector('.seo-serp-desc');
        var socialTitle = panel.querySelector('.seo-social-title');
        var socialDesc = panel.querySelector('.seo-social-desc');
        var tierText = panel.querySelector('.seo-tier-text');
        var tierDot = panel.querySelector('.seo-tier-dot');
        var serpSitename = panel.querySelector('.seo-serp-sitename');

        if (serpSitename) serpSitename.textContent = siteName;
        if (serpTitle) serpTitle.textContent = effectiveTitle;
        if (serpDesc) serpDesc.textContent = effectiveDesc;
        if (socialTitle) socialTitle.textContent = effectiveTitle;
        if (socialDesc) socialDesc.textContent = effectiveDesc;
        if (tierText) tierText.innerHTML = 'Showing: <strong>' + tierLabel + '</strong>';
        if (tierDot) tierDot.classList.toggle('is-own', tierOn);
      });
    }

    richTabs.forEach(function (tab) {
      var panel = document.querySelector('[data-tab-panel="' + tab + '"]');
      if (!panel) return;
      var titleInput = panel.querySelector('.seo-title-input');
      var descInput = panel.querySelector('.seo-desc-input');
      if (titleInput) titleInput.addEventListener('input', refreshAll);
      if (descInput) descInput.addEventListener('input', refreshAll);
    });

    refreshAll();

    // ---- Mirror the LFM picker's thumbnail into this tab's Social Share
    // preview card. LFM only ever updates #thumbPreview{serial} img's src
    // (resources/views/vendor/laravel-filemanager/index.blade.php) — it has
    // no idea the preview card's image exists, so watch for that directly.
    document.querySelectorAll('.seo-social-image[data-og-serial]').forEach(function (box) {
      var serial = box.getAttribute('data-og-serial');
      var thumbImg = document.querySelector('#thumbPreview' + serial + ' img');
      var boxImg = box.querySelector('img');
      if (!thumbImg || !boxImg) return;
      new MutationObserver(function () {
        boxImg.src = thumbImg.src;
      }).observe(thumbImg, { attributes: true, attributeFilter: ['src'] });
    });
  });
</script>

@endsection
