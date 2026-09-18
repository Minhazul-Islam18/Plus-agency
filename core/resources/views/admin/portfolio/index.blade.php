@extends('admin.layout')

@php
$selLang = \App\Language::where('code', request()->input('language'))->first();
@endphp
@if(!empty($selLang) && $selLang->rtl == 1)
@section('styles')
<style>
    form:not(.modal-form) input,
    form:not(.modal-form) textarea,
    form:not(.modal-form) select,
    select[name='language'] {
        direction: rtl;
    }
    form:not(.modal-form) .note-editor.note-frame .note-editing-area .note-editable {
        direction: rtl;
        text-align: right;
    }
</style>
@endsection
@endif

@section('content')
  <div class="page-header">
    <h4 class="page-title">Portfolios</h4>
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
        <a href="#">Portfolio Page</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">Portfolios</a>
      </li>
    </ul>
  </div>
  <div class="row">
    <div class="col-md-12">

      <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-lg-4">
                    <div class="card-title d-inline-block">Portfolios</div>
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
                <div class="col-lg-5 mt-2 mt-lg-0">
                    <a href="{{route('admin.portfolio.create') . '?language=' . request()->input('language')}}" class="btn btn-primary float-right btn-sm ml-2"><i class="fas fa-plus"></i> Add Portfolio</a>
                    <a href="{{route('admin.portfolio.export') . '?language=' . request()->input('language')}}" class="btn btn-outline-secondary float-right btn-sm ml-2"><i class="fas fa-download"></i> Export</a>
                </div>
            </div>

            {{-- Sector / Country / Status filters + search — server-side, see PortfolioController@index --}}
            <form method="get" class="row mt-3 portfolio-filter-form">
                <input type="hidden" name="language" value="{{ request()->input('language') }}">
                <div class="col-lg-3 col-md-6 mt-2">
                    <input type="text" name="search" class="form-control" value="{{ request()->input('search') }}" placeholder="Search a project...">
                </div>
                <div class="col-lg-3 col-md-6 mt-2">
                    <select name="sector_id" class="form-control" onchange="this.form.submit()">
                        <option value="">Sector — All</option>
                        @foreach ($sectors as $sector)
                            <option value="{{ $sector->id }}" {{ request()->input('sector_id') == $sector->id ? 'selected' : '' }}>{{ convertUtf8($sector->name) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-6 mt-2">
                    <select name="country" class="form-control" onchange="this.form.submit()">
                        <option value="">Country — All</option>
                        @foreach ($countries as $c)
                            <option value="{{ $c['iso'] }}" {{ request()->input('country') == $c['iso'] ? 'selected' : '' }}>{{ $c['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-4 mt-2">
                    <select name="status_id" class="form-control" onchange="this.form.submit()">
                        <option value="">Status — All</option>
                        @foreach ($statuses as $s)
                            <option value="{{ $s->id }}" {{ request()->input('status_id') == $s->id ? 'selected' : '' }}>{{ convertUtf8($s->name) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-1 col-md-2 mt-2">
                    <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-search"></i></button>
                </div>
                @if (request()->filled('search') || request()->filled('sector_id') || request()->filled('country') || request()->filled('status_id') || request()->filled('archived'))
                    <div class="col-12 mt-2">
                        <a href="{{ url()->current() . '?language=' . request()->input('language') }}" class="btn btn-link btn-sm p-0"><i class="fas fa-undo"></i> Reset</a>
                        <a href="{{ url()->current() . '?language=' . request()->input('language') . '&archived=1' }}" class="btn btn-link btn-sm p-0 ml-3">{{ request()->filled('archived') ? 'View active projects' : 'View archived projects' }}</a>
                    </div>
                @else
                    <div class="col-12 mt-2">
                        <a href="{{ url()->current() . '?language=' . request()->input('language') . '&archived=1' }}" class="btn btn-link btn-sm p-0">View archived projects</a>
                    </div>
                @endif
            </form>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-lg-12">
              @if (count($portfolios) == 0)
                <h3 class="text-center">NO PORTFOLIO FOUND</h3>
              @else
                {{-- #basic-datatables is shared by many other admin list
                     views (see the identical note on the Custom Pages list)
                     — scoped to .portfolios-list-table so this never leaks. --}}
                <style>
                    .portfolios-list-table { table-layout: fixed !important; }
                    .portfolios-list-table th:nth-child(1), .portfolios-list-table td:nth-child(1) { width: 3%; }
                    .portfolios-list-table th:nth-child(2), .portfolios-list-table td:nth-child(2) { width: 7%; }
                    .portfolios-list-table th:nth-child(3), .portfolios-list-table td:nth-child(3) { width: 21%; }
                    .portfolios-list-table th:nth-child(4), .portfolios-list-table td:nth-child(4) { width: 11%; }
                    .portfolios-list-table th:nth-child(5), .portfolios-list-table td:nth-child(5) { width: 9%; }
                    .portfolios-list-table th:nth-child(6), .portfolios-list-table td:nth-child(6) { width: 9%; }
                    .portfolios-list-table th:nth-child(7), .portfolios-list-table td:nth-child(7) { width: 8%; }
                    .portfolios-list-table th:nth-child(8), .portfolios-list-table td:nth-child(8) { width: 8%; }
                    .portfolios-list-table th:nth-child(9), .portfolios-list-table td:nth-child(9) { width: 8%; }
                    .portfolios-list-table th:nth-child(10), .portfolios-list-table td:nth-child(10) { width: 16%; white-space: nowrap; }
                    .portfolios-list-table td { vertical-align: middle; }
                    .portfolio-title-cell { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: block; font-weight: 600; }
                    .portfolio-subtitle-cell { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-style: italic; font-size: 12px; color: #8a94a6; }
                    .portfolio-thumb { width: 56px; height: 56px; object-fit: cover; border-radius: 8px; }
                    /* Neutral default fill/text; the .status-border-* class
                       (keyword-matched per the requested green/yellow/red
                       meaning — Completed/In Progress/Suspended — see the
                       loop below) tints the fill to match its border
                       instead of leaving the old flat blue underneath
                       every color, which read as mismatched/clashing. */
                    .portfolio-status-badge { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; background: #cfe2ff; color: #084298; border: 1.5px solid transparent; }
                    .portfolio-status-badge.status-border-green { background: #e6f7ec; color: #1e7e34; border-color: #28a745; }
                    .portfolio-status-badge.status-border-yellow { background: #fff8e1; color: #8a6d00; border-color: #ffc107; }
                    .portfolio-status-badge.status-border-red { background: #fdecea; color: #a71d2a; border-color: #dc3545; }
                    .portfolio-status-badge.status-border-neutral { border-color: rgba(8, 66, 152, 0.25); }

                    /* Icon-only action buttons — pastel square, tinted per
                       action (view=blue, edit=neutral, delete=red, more=neutral).
                       dark-glass.css (also loaded on this page, for the
                       "View in Site" preview modal) carries a blanket
                       `button[type="submit"]{background:linear-gradient(...) !important}`
                       rule for the FRONT-END site — it was leaking onto the
                       Delete button here (the only one that's type="submit").
                       Beaten with a selector specific enough to out-rank
                       that !important instead of touching the shared
                       stylesheet — see the standing "dark-glass.css leak
                       onto admin pages" pattern. */
                    .portfolio-actions { display: flex; align-items: center; gap: 6px; flex-wrap: nowrap; }
                    .portfolio-actions button.portfolio-action-btn[type="submit"] {
                        background: #ffe3e3 !important;
                        border: none !important;
                        box-shadow: none !important;
                    }
                    .portfolio-actions button.portfolio-action-btn[type="submit"]:hover {
                        transform: none !important;
                        box-shadow: none !important;
                    }
                    .portfolio-action-btn {
                        display: inline-flex; align-items: center; justify-content: center;
                        flex: 0 0 34px; width: 34px; height: 34px; border-radius: 8px; border: none;
                        font-size: 14px; line-height: 1; cursor: pointer; transition: filter 0.15s ease;
                    }
                    .portfolio-action-btn:hover { filter: brightness(0.94); }
                    .portfolio-action-view { background: #e7f1ff; color: #0d6efd; }
                    .portfolio-action-edit { background: #eef0f3; color: #495057; }
                    .portfolio-action-delete { background: #ffe3e3; color: #e03131; }
                    .portfolio-action-more { background: #eef0f3; color: #495057; }
                    /* Bootstrap's .dropdown-toggle adds its own ::after caret
                       triangle — doubled up next to the ⋮ icon already inside
                       the button. Hidden so it's just the plain icon, like
                       the other three action buttons. */
                    .portfolio-action-more.dropdown-toggle::after { display: none; }
                    /* The menu has 9 items (Duplicate/Unpublish/Feature +
                       a Change-status list + Copy link/Archive) — tall
                       enough that Bootstrap's auto-flip on a bottom row
                       pushed it up over several rows above. Capped with
                       its own scrollbar instead of letting it grow past a
                       sane height. */
                    /* Was scoped ".portfolio-actions .dropdown-menu" — dead
                       once the menu moves to <body> on open, since it's no
                       longer a descendant of .portfolio-actions. Rescoped
                       to the portal class so it actually applies, and
                       padding tightened: Bootstrap's default 1.5rem side
                       padding on .dropdown-item made the menu size itself
                       to the longest item ("Duplicate project") while short
                       ones ("Ongoing") sat in a lot of leftover whitespace. */
                    .portfolio-actions-menu-portal { max-height: 280px; overflow-y: auto; overflow-x: hidden; min-width: 180px; }
                    .portfolio-actions-menu-portal .dropdown-item,
                    .portfolio-actions-menu-portal .dropdown-header {
                        padding: 0.4rem 0.9rem;
                        font-size: 13.5px;
                    }
                    /* .table-responsive clips anything with overflow (that's
                       its whole job) — a dropdown-menu positioned inside it
                       gets cut off on the last few rows/columns and, in
                       Firefox/Safari, doesn't escape via z-index tricks at
                       all (clipping isn't a stacking issue). Real fix: on
                       open, detach the menu to <body> and position it with
                       JS from the toggle button's own viewport rect (see
                       script below) — same approach used by Bootstrap
                       itself when boundary:'viewport' isn't enough. */
                    .portfolio-actions-menu-portal {
                        position: fixed !important;
                        margin: 0 !important;
                        transform: none !important;
                        z-index: 1071;
                    }
                </style>
                @php $countryNames = collect($countries)->pluck('name', 'iso'); @endphp
                <div class="table-responsive">
                  <table class="table table-striped mt-3 portfolios-list-table" id="basic-datatables">
                    <thead>
                      <tr>
                        <th scope="col">#</th>
                        <th scope="col">Image</th>
                        <th scope="col">Project Title</th>
                        <th scope="col">Client</th>
                        <th scope="col">Sector</th>
                        <th scope="col">Country</th>
                        <th scope="col">Status</th>
                        <th scope="col">Start Date</th>
                        <th scope="col">End Date</th>
                        <th scope="col">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($portfolios as $key => $portfolio)
                        <tr>
                          <td>{{ $key + 1 }}</td>
                          <td><img class="portfolio-thumb" src="{{asset('assets/front/img/portfolios/featured/'.$portfolio->featured_image)}}"></td>
                          <td>
                            <span class="portfolio-title-cell" title="{{ convertUtf8($portfolio->title) }}">{{ convertUtf8($portfolio->title) }}</span>
                            @if (!empty($portfolio->overlay_subtitle))
                                <span class="portfolio-subtitle-cell" title="{{ convertUtf8($portfolio->overlay_subtitle) }}">{{ convertUtf8($portfolio->overlay_subtitle) }}</span>
                            @endif
                            @if ($portfolio->is_published == 0)
                                <span class="badge badge-secondary">Unpublished</span>
                            @endif
                          </td>
                          <td>{{ convertUtf8($portfolio->client_name) }}</td>
                          <td>
                            @if (!empty($portfolio->sector))
                                <span class="d-block">{{ convertUtf8($portfolio->sector->name) }}</span>
                                @if (!empty($portfolio->subsector))
                                    {{-- Same visual treatment as the Project Title
                                         column's own title+subtitle stack — a
                                         subsector is a MORE SPECIFIC pick under
                                         its sector, not a separate value, so it
                                         reads as "belongs under the line above"
                                         rather than a second unrelated fact. --}}
                                    <span class="portfolio-subtitle-cell">
                                        <i class="fas fa-level-up-alt fa-rotate-90" style="font-size:10px;"></i>
                                        {{ convertUtf8($portfolio->subsector->name) }}
                                    </span>
                                @endif
                            @endif
                          </td>
                          <td>
                            @if (!empty($portfolio->country))
                                <span class="fi fi-{{ strtolower($portfolio->country) }}" style="font-size:20px;border-radius:2px;margin-right:6px;vertical-align:middle;"></span>{{ $countryNames[$portfolio->country] ?? $portfolio->country }}
                            @endif
                          </td>
                          <td>
                            @if (!empty($portfolio->statusInfo))
                                @php
                                    // Statuses are an open, admin-managed list (any name
                                    // allowed, in whatever language the portfolio is in —
                                    // e.g. "Réalisé"/"En cours" for French rows) — there's
                                    // no fixed id to key a color off of, so this matches on
                                    // keywords instead. Unrecognized names fall back to a
                                    // neutral border rather than guessing.
                                    $statusName = mb_strtolower(convertUtf8($portfolio->statusInfo->name));
                                    $statusBorderClass = 'status-border-neutral';
                                    if (Str::contains($statusName, ['complet', 'finish', 'réalisé', 'realise', 'done'])) {
                                        $statusBorderClass = 'status-border-green';
                                    } elseif (Str::contains($statusName, ['suspend', 'cancel', 'stop', 'hold'])) {
                                        $statusBorderClass = 'status-border-red';
                                    } elseif (Str::contains($statusName, ['progress', 'cours', 'pending', 'attente', 'ongoing'])) {
                                        $statusBorderClass = 'status-border-yellow';
                                    }
                                @endphp
                                <span class="portfolio-status-badge {{ $statusBorderClass }}">{{ convertUtf8($portfolio->statusInfo->name) }}</span>
                            @endif
                          </td>
                          <td>{{ !empty($portfolio->start_date) ? \Carbon\Carbon::parse($portfolio->start_date)->format('d-m-Y') : '—' }}</td>
                          <td>{{ !empty($portfolio->end_date) ? \Carbon\Carbon::parse($portfolio->end_date)->format('d-m-Y') : '—' }}</td>
                          <td>
                            <div class="portfolio-actions">
                                <button type="button" class="portfolio-action-btn portfolio-action-view portfolio-view-btn" data-id="{{ $portfolio->id }}" title="Preview">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <a class="portfolio-action-btn portfolio-action-edit" href="{{route('admin.portfolio.edit', $portfolio->id) . '?language=' . request()->input('language')}}" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form class="deleteform d-inline-block" action="{{route('admin.portfolio.delete')}}" method="post">
                                  @csrf
                                  <input type="hidden" name="portfolio_id" value="{{$portfolio->id}}">
                                  <button type="submit" class="portfolio-action-btn portfolio-action-delete deletebtn" title="Delete">
                                    <i class="fas fa-trash"></i>
                                  </button>
                                </form>
                                <div class="btn-group">
                                    <button type="button" class="portfolio-action-btn portfolio-action-more dropdown-toggle" aria-haspopup="true" aria-expanded="false" title="More">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <a class="dropdown-item" href="{{ route('admin.portfolio.duplicate', $portfolio->id) }}"><i class="fas fa-copy mr-1"></i> Duplicate project</a>
                                        <a class="dropdown-item portfolio-toggle-publish" href="#" data-id="{{ $portfolio->id }}" data-published="{{ $portfolio->is_published }}">
                                            @if ($portfolio->is_published)
                                                <i class="fas fa-times-circle mr-1"></i> Unpublish
                                            @else
                                                <i class="fas fa-check-circle mr-1"></i> Publish
                                            @endif
                                        </a>
                                        <a class="dropdown-item portfolio-toggle-feature" href="#" data-id="{{ $portfolio->id }}" data-featured="{{ $portfolio->feature }}">
                                            @if ($portfolio->feature)
                                                <i class="fas fa-star mr-1"></i> Unfeature
                                            @else
                                                <i class="far fa-star mr-1"></i> Feature
                                            @endif
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <h6 class="dropdown-header">Change status</h6>
                                        @foreach ($statuses as $s)
                                            <a class="dropdown-item portfolio-set-status" href="#" data-id="{{ $portfolio->id }}" data-status-id="{{ $s->id }}">{{ convertUtf8($s->name) }}</a>
                                        @endforeach
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item portfolio-copy-link" href="#" data-url="{{ route('front.portfoliodetails', $portfolio->slug) }}"><i class="fas fa-link mr-1"></i> Copy public link</a>
                                        <a class="dropdown-item portfolio-toggle-archive" href="#" data-id="{{ $portfolio->id }}" data-archived="{{ $portfolio->is_archived }}">
                                            <i class="fas fa-box-archive mr-1"></i> {{ $portfolio->is_archived ? 'Unarchive' : 'Archive' }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Read-only preview modal (the "eye" view — see mockup image 2).
       Styled as its own elevated card (border + shadow + icon-chip header,
       matching .portfolio-form-col elsewhere in this admin) instead of a
       plain flat Bootstrap default — that's what read as "not
       professional" here, not just the width. --}}
  <style>
    /* Backdrop blur so the eye-modal reads as a clearly separate layer
       above the table, not just a dark overlay — scoped to THIS modal's
       own backdrop only (.portfolio-view-backdrop, added via show.bs.modal
       below) rather than a blanket .modal-backdrop rule, which would blur
       every other modal on this admin theme too. */
    .portfolio-view-backdrop {
      background-color: rgba(15, 23, 42, 0.45) !important;
      backdrop-filter: blur(4px);
      -webkit-backdrop-filter: blur(4px);
    }
    .portfolio-view-backdrop.show {
      opacity: 1 !important;
    }
    #portfolioViewModal .modal-dialog {
      max-width: 1320px;
    }
    #portfolioViewModal .modal-content {
      background: #0b0f16;
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 14px;
      box-shadow: 0 24px 60px -20px rgba(0, 0, 0, 0.7);
      overflow: hidden;
    }
    #portfolioViewModal .modal-header {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 18px 24px;
      background: rgba(255, 255, 255, 0.03);
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }
    #portfolioViewModal .modal-title-icon {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 32px;
      height: 32px;
      border-radius: 8px;
      background: rgba(22, 185, 153, 0.16);
      color: #16b999;
      font-size: 14px;
      flex-shrink: 0;
    }
    #portfolioViewModal .modal-title {
      color: #fff;
      font-size: 15px;
      font-weight: 700;
      margin: 0;
      flex: 1;
    }
    #portfolioViewModal .modal-close-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 32px;
      height: 32px;
      border-radius: 50%;
      border: 1px solid rgba(255, 255, 255, 0.1);
      background: rgba(255, 255, 255, 0.04);
      color: rgba(255, 255, 255, 0.7);
      font-size: 14px;
      line-height: 1;
      cursor: pointer;
      transition: background 0.2s ease, color 0.2s ease;
    }
    #portfolioViewModal .modal-close-btn:hover {
      background: rgba(255, 255, 255, 0.1);
      color: #fff;
    }
    #portfolioViewModal .modal-footer {
      padding: 16px 24px;
      background: rgba(255, 255, 255, 0.03);
      border-top: 1px solid rgba(255, 255, 255, 0.08);
    }
  </style>
  <div class="modal fade" id="portfolioViewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <span class="modal-title-icon"><i class="fas fa-eye"></i></span>
                <h5 class="modal-title">Project Details (read-only)</h5>
                <button type="button" class="modal-close-btn" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" id="portfolioViewBody" style="max-height:75vh;overflow-y:auto;padding:26px;"></div>
            <div class="modal-footer">
                <a id="portfolioViewEditBtn" href="#" class="btn btn-success"><i class="fas fa-edit"></i> Edit</a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
  </div>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icons@7.2.3/css/flag-icons.min.css" integrity="sha384-aQuvIWWIbpu/mSqULLDiUveyYiPoJzPKAWjUmGJ+Elm+N/LJhzfZqsutsfw870JS" crossorigin="anonymous">
  <link rel="stylesheet" href="{{ url('/') }}/assets/front/css/dark-glass-vars.php?color={{ $bs->base_color }}&color2={{ $bs->secondary_base_color }}">
  <link rel="stylesheet" href="{{ asset_v('assets/front/css/dark-glass.css') }}">
  @include('admin.portfolio._dark_glass_button_fix')

@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        // Custom ⋮ menu, NOT Bootstrap's data-toggle="dropdown" (the button
        // has no such attribute anymore). Two independent problems ruled
        // that out:
        //  1) Kept inside the table, .table-responsive's own overflow clips
        //     the menu on the last rows/columns — no z-index fix escapes an
        //     ancestor's overflow clipping, in any browser.
        //  2) Portaling the existing dropdown-menu to <body> while still
        //     letting Bootstrap manage it fought its own Popper instance,
        //     which kept re-applying its computed `transform` over ours on
        //     every animation frame (confirmed live — inline style flips
        //     back to Popper's translate3d() right after we set top/left).
        // So this owns the whole show/hide/position/close lifecycle itself.
        (function () {
            var $openMenu = null, $openToggle = null, $scrollParent = null;

            function positionMenu() {
                if (!$openMenu || !$openToggle) return;
                var rect = $openToggle[0].getBoundingClientRect();
                var menuWidth = $openMenu.outerWidth();
                var menuHeight = $openMenu.outerHeight();
                var left = rect.right - menuWidth;
                if (left < 4) left = 4;
                var maxLeft = $(window).width() - menuWidth - 4;
                if (left > maxLeft) left = Math.max(4, maxLeft);
                var top = rect.bottom + 4;
                if (top + menuHeight > $(window).height() && rect.top - menuHeight - 4 > 0) {
                    top = rect.top - menuHeight - 4;
                }
                $openMenu.css({ top: top + 'px', left: left + 'px' });
            }

            function closeOpenMenu() {
                if (!$openMenu) return;
                var $origin = $openMenu.data('portal-origin');
                $openMenu.removeClass('show portfolio-actions-menu-portal').css({ top: '', left: '' });
                if ($origin && $origin.length) $openMenu.appendTo($origin);
                if ($openToggle) $openToggle.attr('aria-expanded', 'false');
                if ($scrollParent) $scrollParent.off('scroll.portfolioActionsMenu');
                $(window).off('resize.portfolioActionsMenu scroll.portfolioActionsMenu');
                $(document).off('click.portfolioActionsMenu keydown.portfolioActionsMenu');
                $openMenu = null; $openToggle = null; $scrollParent = null;
            }

            $('.portfolios-list-table').on('click', '.portfolio-action-more', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var $toggle = $(this);
                var $menu = $toggle.closest('.btn-group').find('.dropdown-menu');
                var reopening = $openMenu && $openMenu.is($menu);
                closeOpenMenu();
                if (reopening) return;

                $menu.data('portal-origin', $toggle.closest('.btn-group')).addClass('portfolio-actions-menu-portal show').appendTo('body');
                $openMenu = $menu;
                $openToggle = $toggle;
                $toggle.attr('aria-expanded', 'true');
                positionMenu();

                $scrollParent = $toggle.closest('.table-responsive');
                $scrollParent.on('scroll.portfolioActionsMenu', positionMenu);
                $(window).on('resize.portfolioActionsMenu scroll.portfolioActionsMenu', positionMenu);

                $(document).on('click.portfolioActionsMenu', function (ev) {
                    if (!$openMenu) return;
                    // A click on an actual item closes the menu after it
                    // does its thing; a click anywhere else inside the menu
                    // (e.g. the "Change status" header) leaves it open.
                    if ($(ev.target).closest('.dropdown-item').length && $openMenu.has(ev.target).length) {
                        closeOpenMenu();
                        return;
                    }
                    if ($openMenu.is(ev.target) || $openMenu.has(ev.target).length) return;
                    closeOpenMenu();
                });
                $(document).on('keydown.portfolioActionsMenu', function (ev) {
                    if (ev.key === 'Escape') closeOpenMenu();
                });
            });
        })();

        $('.portfolio-view-btn').on('click', function () {
            var id = $(this).data('id');
            $('#portfolioViewBody').html('<p class="text-center text-white">Loading…</p>');
            $('#portfolioViewEditBtn').attr('href',
                "{{ route('admin.portfolio.edit', '__ID__') }}?language={{ request()->input('language') }}".replace('__ID__', id)
            );
            $('#portfolioViewModal').modal('show');
            // Bootstrap appends a fresh, unscoped .modal-backdrop <div> to
            // <body> on every show — tagging THIS one right after so the
            // blur CSS above only ever applies to this modal's own backdrop.
            $('.modal-backdrop').last().addClass('portfolio-view-backdrop');
            $.get("{{ url('/') }}/admin/portfolio/" + id + "/show", function (html) {
                $('#portfolioViewBody').html(html);
            });
        });

        $('.portfolio-toggle-publish').on('click', function (e) {
            e.preventDefault();
            var id = $(this).data('id');
            var current = $(this).data('published');
            $.post("{{ route('admin.portfolio.toggle_visibility') }}", {
                _token: "{{ csrf_token() }}",
                portfolio_id: id,
                is_published: current ? 0 : 1
            }, function () { location.reload(); });
        });

        $('.portfolio-set-status').on('click', function (e) {
            e.preventDefault();
            var id = $(this).data('id');
            var statusId = $(this).data('status-id');
            $.post("{{ route('admin.portfolio.update_status') }}", {
                _token: "{{ csrf_token() }}",
                portfolio_id: id,
                status_id: statusId
            }, function () { location.reload(); });
        });

        $('.portfolio-toggle-feature').on('click', function (e) {
            e.preventDefault();
            var id = $(this).data('id');
            var current = $(this).data('featured');
            $.post("{{ route('admin.portfolio.feature') }}", {
                _token: "{{ csrf_token() }}",
                portfolio_id: id,
                feature: current ? 0 : 1
            }, function () { location.reload(); });
        });

        $('.portfolio-toggle-archive').on('click', function (e) {
            e.preventDefault();
            var id = $(this).data('id');
            var current = $(this).data('archived');
            $.post("{{ route('admin.portfolio.toggle_archive') }}", {
                _token: "{{ csrf_token() }}",
                portfolio_id: id,
                is_archived: current ? 0 : 1
            }, function () { location.reload(); });
        });

        $('.portfolio-copy-link').on('click', function (e) {
            e.preventDefault();
            var url = $(this).data('url');
            navigator.clipboard.writeText(url).then(function () {
                $.notify({ message: {!! json_encode('Link copied to clipboard!') !!} }, { type: 'success', placement: { from: 'top', align: 'right' }, time: 1000 });
            });
        });
    });
</script>
@endsection
