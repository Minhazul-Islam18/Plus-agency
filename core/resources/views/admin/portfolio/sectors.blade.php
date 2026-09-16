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
    </style>
  @endsection
@endif

@section('content')
  <div class="page-header">
    <h4 class="page-title">Sectors and Subsectors</h4>
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
        <a href="#">Portfolios</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">Sectors</a>
      </li>
    </ul>
  </div>

  <style>
    /* ---- Icon-picker (Sector's own icon field, both create & edit modals) —
       same plugin/reskin as the Portfolio form's own icon-pickers, copied
       locally since this is a separate page. ---- */
    .portfolio-icon-picker .btn-group { display: inline-flex; }
    .portfolio-icon-picker .iconpicker-component { width: 42px; height: 34px; padding: 0; font-size: 17px; }
    .portfolio-icon-picker .icp-dd { height: 34px; padding: 0 12px; font-size: 15px; }
    /* .portfolio-icon-picker sits in a plain flex row next to the Sector
       Name input (the .d-flex wrapper in sector_create/edit.blade.php) —
       as a default flex item it shrinks to make room for the input's own
       growth, and once it shrinks below the two buttons' combined ~76px
       width the caret button wraps onto its own line under the icon
       button. Same bug/fix as .highlight-row .portfolio-icon-picker on
       the Portfolio form itself — just missing here since this page's
       copy of the icon-picker CSS never carried it over. */
    .portfolio-icon-picker { flex: 0 0 auto; }
    .iconpicker-popover.popover { background: #1a2035 !important; border: 1px solid #2f374b !important; color: #fff; box-shadow: 0 12px 30px -10px rgba(0,0,0,.6); }
    .iconpicker-popover .iconpicker-items { max-height: 360px !important; background: #1a2035 !important; }
    .iconpicker-popover .iconpicker-item { width: 16px !important; height: 16px !important; padding: 12px !important; font-size: 15px !important; box-shadow: 0 0 0 1px #2f374b !important; color: #cfd4e4; }
    .iconpicker-popover .popover-title { background: #202940 !important; border-bottom: 1px solid #2f374b !important; color: #fff; }
    .iconpicker-popover input[type="search"].iconpicker-search { background: #131a2c !important; border: 1px solid #2f374b !important; color: #fff !important; }
    .iconpicker-popover input[type="search"].iconpicker-search::placeholder { color: rgba(255,255,255,.4); }
    .iconpicker-popover .iconpicker-item:hover:not(.iconpicker-selected) { background-color: rgba(255,255,255,.08) !important; }
    .iconpicker-popover .iconpicker-item.iconpicker-selected { background: #1572e8 !important; color: #fff; }
    .iconpicker-popover .popover-footer { background: #202940 !important; border-top: 1px solid #2f374b !important; }

    /* ---- Left panel: sector list ---- */
    .sector-nav-list {
      max-height: 640px;
      overflow-y: auto;
    }
    .sector-nav-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 14px;
      border-radius: 10px;
      color: inherit;
      text-decoration: none;
      transition: background .15s ease;
    }
    .sector-nav-item:hover {
      background: rgba(255,255,255,.05);
      color: inherit;
      text-decoration: none;
    }
    .sector-nav-item.active {
      background: rgba(21,114,232,.16);
      box-shadow: inset 0 0 0 1px rgba(21,114,232,.4);
    }
    .sector-nav-icon {
      flex: 0 0 auto;
      width: 38px;
      height: 38px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgba(21,114,232,.16);
      color: #6ea8f7;
      font-size: 16px;
    }
    .sector-nav-item.active .sector-nav-icon {
      background: #1572e8;
      color: #fff;
    }
    .sector-nav-label {
      flex: 1 1 auto;
      font-size: 13.5px;
      font-weight: 600;
      line-height: 1.35;
    }
    .sector-nav-count {
      flex: 0 0 auto;
      font-size: 11.5px;
      font-weight: 700;
      color: rgba(255,255,255,.55);
    }
    .sector-nav-chevron {
      flex: 0 0 auto;
      font-size: 11px;
      color: rgba(255,255,255,.3);
    }
    .sector-nav-item.d-none-filtered { display: none !important; }

    /* ---- Right panel: subsector table ---- */
    .subsector-icon {
      width: 34px;
      height: 34px;
      border-radius: 9px;
      background: rgba(21,114,232,.16);
      color: #6ea8f7;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 15px;
      flex: 0 0 auto;
    }
    .subsector-drag-handle {
      cursor: grab;
      color: rgba(255,255,255,.35);
      padding: 0 4px;
      /* Without this, Firefox's own native text-selection drag wins the
         mousedown before jQuery UI's sortable gets a chance to start one —
         Chrome is more forgiving and starts the custom drag anyway, which
         is why this looked fine there and did nothing in Firefox. */
      -webkit-user-select: none;
      -moz-user-select: none;
      user-select: none;
    }
    .subsector-drag-handle:active { cursor: grabbing; }
    #subsectorPanelContainer .ui-sortable-helper { box-shadow: 0 10px 24px rgba(0,0,0,.45); }
    #subsectorPanelContainer tr.subsector-row-placeholder { border: 2px dashed #3a445c; }
    .subsector-order-box {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 26px;
      height: 26px;
      border-radius: 50%;
      background: rgba(21,114,232,.16);
      color: #6ea8f7;
      font-size: 12px;
      font-weight: 700;
    }
    .subsector-id-cell {
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .subsector-id-value {
      color: rgba(255,255,255,.4);
      font-size: 12px;
    }

    /* Toggle switch — same markup/CSS every other page with a status toggle
       already uses (Gallery/Language/Home-feature/etc's own local copy;
       there's no global version of this, each page carries its own). */
    .switch { position: relative; display: inline-block; width: 44px; height: 22px; vertical-align: middle; }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; }
    .slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 3px; bottom: 3px; background-color: white; transition: .4s; }
    input:checked + .slider { background-color: #1572E8; }
    input:focus + .slider { box-shadow: 0 0 1px #1572E8; }
    input:checked + .slider:before { transform: translateX(22px); }
    .slider.round { border-radius: 22px; }
    .slider.round:before { border-radius: 50%; }

    /* Actions column — same compact icon-button pattern as the Portfolios
       list table (.portfolio-action-btn), renamed to avoid any collision
       since this is a different page. Plain stacked <a>/<button> tags
       (Bootstrap's own default sizing/display) is what was rendering as
       oversized full-width blocks before. */
    .subsector-actions { display: flex; align-items: center; gap: 6px; flex-wrap: nowrap; }
    .subsector-action-btn {
      display: inline-flex; align-items: center; justify-content: center;
      flex: 0 0 32px; width: 32px; height: 32px; border-radius: 8px; border: none;
      font-size: 13px; line-height: 1; cursor: pointer; transition: filter .15s ease;
    }
    .subsector-action-btn:hover { filter: brightness(0.94); }
    .subsector-action-edit { background: #eef0f3; color: #495057; }
    .subsector-action-delete { background: #ffe3e3; color: #e03131; }
    .subsector-actions button.subsector-action-btn[type="submit"] {
      background: #ffe3e3 !important; border: none !important; box-shadow: none !important;
    }

    /* Right-panel loading state — a scoped spinner over just this column
       instead of the sitewide full-page .request-loader, which would feel
       heavy for something as small as a single search keystroke or page
       flip. #subsectorPanelContainer itself becomes the positioning
       context; the overlay sits on top of whatever content is already
       there (old content stays visible, dimmed, until the new content is
       ready — no blank flash). */
    /* The overlay is a SIBLING of #subsectorPanelContainer, not a child of
       it — that container's own innerHTML gets fully replaced on every
       AJAX swap (loadSectorPanel's $.get success does .html(html)), which
       would wipe out an overlay living inside it. #subsectorPanelWrapper
       is the actual positioning context so the sibling overlay's
       position:absolute covers exactly the panel's area either way. */
    #subsectorPanelWrapper { position: relative; min-height: 160px; }
    .subsector-panel-loading {
      position: absolute;
      inset: 0;
      z-index: 5;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgba(11, 15, 22, 0.55);
      border-radius: 10px;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.15s ease;
    }
    .subsector-panel-loading.is-active {
      opacity: 1;
      pointer-events: auto;
    }
    .subsector-panel-spinner {
      width: 34px;
      height: 34px;
      border-radius: 50%;
      border: 3px solid rgba(110, 168, 247, 0.25);
      border-top-color: #6ea8f7;
      animation: subsectorSpin 0.7s linear infinite;
    }
    @keyframes subsectorSpin {
      to { transform: rotate(360deg); }
    }
  </style>

  <div class="row">
    <div class="col-lg-4 mb-4">
      <div class="card h-100">
        <div class="card-header">
          <div class="row align-items-center">
            <div class="col-7">
              <div class="card-title d-inline-block mb-0">List of sectors</div>
            </div>
            <div class="col-5">
              @if (!empty($langs))
                <select name="language" class="form-control form-control-sm" onchange="window.location='{{url()->current() . '?language='}}' + this.value">
                  <option selected disabled>Language</option>
                  @foreach ($langs as $lang)
                    <option value="{{$lang->code}}" {{$lang->code == request()->input('language') ? 'selected' : ''}}>
                      {{$lang->name}}
                    </option>
                  @endforeach
                </select>
              @endif
            </div>
          </div>
          <a href="#" data-toggle="modal" data-target="#createSectorModal" class="btn btn-primary btn-sm btn-block mt-3">
            <i class="fas fa-plus"></i> Add a sector
          </a>
        </div>
        <div class="card-body">
          <div class="form-group">
            <input type="text" id="sectorSearchInput" class="form-control" placeholder="Search for a sector...">
          </div>

          @if ($sectors->isEmpty())
            <p class="text-center text-muted mb-0">No sectors yet.</p>
          @else
            <div class="sector-nav-list" id="sectorNavList">
              @foreach ($sectors as $sector)
                <a href="#" class="sector-nav-item {{ $activeSector && $activeSector->id == $sector->id ? 'active' : '' }}"
                    data-id="{{ $sector->id }}" data-searchname="{{ strtolower(convertUtf8($sector->name)) }}">
                  <span class="sector-nav-icon"><i class="{{ $sector->icon ?: 'fas fa-building' }}"></i></span>
                  <span class="sector-nav-label">{{ convertUtf8($sector->name) }}</span>
                  <span class="sector-nav-count">{{ $sector->subsectors_count }}</span>
                  <i class="fas fa-chevron-right sector-nav-chevron"></i>
                </a>
              @endforeach
            </div>
          @endif
        </div>
      </div>
    </div>

    <div class="col-lg-8 mb-4">
      <div id="subsectorPanelWrapper">
        <div id="subsectorPanelLoading" class="subsector-panel-loading">
          <div class="subsector-panel-spinner"></div>
        </div>
        <div id="subsectorPanelContainer">
        @if ($activeSector)
          {!! $panelHtml !!}
        @else
          <div class="card">
            <div class="card-body text-center text-muted">
              Add a sector on the left to manage its subsectors.
            </div>
          </div>
        @endif
        </div>
      </div>
    </div>
  </div>

  <div class="how-it-works-notice">
    <div class="how-it-works-icon"><i class="fas fa-lightbulb"></i></div>
    <div>
      <div class="how-it-works-title">How it works</div>
      <ol class="how-it-works-steps mb-0">
        <li>Click on <strong>Portfolios &rarr; Sectors</strong> in the left-hand menu.</li>
        <li>The list of sectors appears on the left.</li>
        <li>Click on a sector (e.g. "Agriculture and Agri-food") to show its subsectors on the right, each with its own enable/disable switch.</li>
      </ol>
    </div>
  </div>
  <style>
    .how-it-works-notice {
      display: flex;
      align-items: flex-start;
      gap: 14px;
      margin-top: 4px;
      margin-bottom: 24px;
      padding: 16px 20px;
      background: rgba(21, 114, 232, 0.08);
      border: 1px solid rgba(21, 114, 232, 0.25);
      border-radius: 10px;
    }
    .how-it-works-icon {
      flex: 0 0 auto;
      color: #6ea8f7;
      font-size: 18px;
      line-height: 1.4;
    }
    .how-it-works-title {
      font-weight: 700;
      font-size: 13.5px;
      margin-bottom: 6px;
      color: #fff;
    }
    .how-it-works-steps {
      padding-left: 18px;
      font-size: 13px;
      color: rgba(255, 255, 255, 0.75);
    }
    .how-it-works-steps li + li { margin-top: 4px; }
  </style>

  {{-- Sector modals --}}
  @include('admin.portfolio.sector_create')
  @include('admin.portfolio.sector_edit')

  {{-- Subsector modals --}}
  @include('admin.portfolio.subsector_create')
  @include('admin.portfolio.subsector_edit')

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      if (!window.jQuery) return;
      var $ = window.jQuery;

      // ---- Sector icon-picker (create + edit modals) — mirrors
      // _form.blade.php's window.wirePortfolioIconPicker, copied locally
      // since this is a different page/script scope. ----
      function wireIconPicker($wrap) {
        var savedIcon = $wrap.find('.portfolio-icon-input').val();
        function applySavedIcon() {
          if (savedIcon) $wrap.find('.iconpicker-component i').attr('class', savedIcon);
        }
        $wrap.find('.icp').on('iconpickerCreated', applySavedIcon);
        setTimeout(applySavedIcon, 0);
        $wrap.find('.icp').on('iconpickerSelected', function () {
          var iconClass = $wrap.find('.iconpicker-component i').attr('class');
          $wrap.find('.portfolio-icon-input').val(iconClass);
        });
      }
      $('.portfolio-icon-picker').each(function () { wireIconPicker($(this)); });

      // Edit modal: custom.js's generic .editbtn handler already fills
      // #inicon (the hidden input) from data-icon — this just re-applies
      // that value to the picker's own preview <i>, and refreshes the
      // "savedIcon" closure above's reference by re-wiring on every open
      // (the picker's <i> gets reset by iconpicker() init, same reasoning
      // as the Portfolio form's own version of this).
      $('.editbtn[data-target="#editSectorModal"]').on('click', function () {
        setTimeout(function () {
          var $wrap = $('#editSectorModal .portfolio-icon-picker');
          var icon = $('#inicon').val() || 'fas fa-building';
          $wrap.find('.iconpicker-component i').attr('class', icon);
        }, 0);
      });

      // ---- Left panel: client-side search filter (no AJAX — the whole
      // list is already on the page, matches the mockup's instant-filter
      // feel better than a round trip for what's usually a short list). ----
      $('#sectorSearchInput').on('input', function () {
        var term = $(this).val().toLowerCase().trim();
        $('#sectorNavList .sector-nav-item').each(function () {
          var name = $(this).data('searchname') || '';
          $(this).toggleClass('d-none-filtered', term.length > 0 && name.indexOf(term) === -1);
        });
      });

      // ---- Left panel: click a sector -> AJAX-load its subsector panel
      // into the right column, no full page reload. ----
      function loadSectorPanel(sectorId, extraParams, opts) {
        opts = opts || {};
        var url = "{{ url('/admin/portfolio/sector') }}/" + sectorId + "/panel";
        if (extraParams) url += '?' + $.param(extraParams);
        $('#subsectorPanelLoading').addClass('is-active');
        $.get(url, function (html) {
          $('#subsectorPanelContainer').html(html);
          // Typing into the search box replaces the WHOLE panel (including
          // the box itself) on every debounced reload — the new <input> is
          // a fresh DOM node, so it loses focus/cursor by default, which is
          // what made continuous typing feel broken (each pause-and-resume
          // had to be re-clicked into). Re-focus the new instance and put
          // the cursor back at the end of what was already typed.
          if (opts.refocusSearch) {
            var $input = $('#subsectorSearchInput');
            if ($input.length) {
              $input.trigger('focus');
              var v = $input.val();
              $input[0].setSelectionRange(v.length, v.length);
            }
          }
        }).always(function () {
          $('#subsectorPanelLoading').removeClass('is-active');
        });
      }

      $(document).on('click', '.sector-nav-item', function (e) {
        e.preventDefault();
        var id = $(this).data('id');
        $('.sector-nav-item').removeClass('active');
        $(this).addClass('active');
        loadSectorPanel(id);
      });

      // ---- Right panel (AJAX-swapped) — every handler below is delegated
      // on #subsectorPanelContainer so it survives repeated .html() swaps. ----
      //
      // BUG THIS FIXES: jQuery's .data() reads a data-* attribute ONCE and
      // caches the value in its own internal store from then on — later
      // changes to the attribute made via plain setAttribute() (which is
      // exactly how the panel partial's own <script> tag updates
      // data-sector-id after every swap) are invisible to .data() forever
      // after that first read. In practice: the FIRST sector a user opened
      // got cached, and every later search/filter/reorder call on ANY
      // other sector kept silently acting on that first one instead — from
      // the user's side, typing a search felt like it randomly "switched
      // to another sector" mid-word. .attr() always re-reads the live DOM
      // attribute, no caching, so it can't go stale like this.
      function activeSectorId() {
        return $('#subsectorPanelContainer').attr('data-sector-id');
      }

      // Search / status filter -> reload the panel with those params.
      // 500ms — long enough that a normal typing cadence never fires mid-
      // word (300ms was still catching the gap between fast keystrokes,
      // reloading before the admin had actually finished typing), short
      // enough it still feels immediate once they do stop.
      var searchDebounce;
      $(document).on('input', '#subsectorSearchInput', function () {
        var val = $(this).val();
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(function () {
          loadSectorPanel(activeSectorId(), { search: val }, { refocusSearch: true });
        }, 500);
      });
      $(document).on('change', '#subsectorStatusFilter', function () {
        loadSectorPanel(activeSectorId(), { status: $(this).val(), search: $('#subsectorSearchInput').val() });
      });
      // Pagination links rendered by Laravel's paginator — same-page AJAX
      // instead of a full reload.
      $(document).on('click', '#subsectorPanelContainer .pagination a', function (e) {
        e.preventDefault();
        var href = $(this).attr('href');
        if (!href) return;
        $('#subsectorPanelLoading').addClass('is-active');
        $.get(href, function (html) {
          $('#subsectorPanelContainer').html(html);
        }).always(function () {
          $('#subsectorPanelLoading').removeClass('is-active');
        });
      });

      // Subsector status toggle — same delegated pattern AND same $.notify
      // toast this admin already uses elsewhere (Gallery/Language/etc's
      // own .status-toggle handler).
      $(document).on('change', '.subsector-status-toggle', function () {
        var $toggle = $(this);
        var id = $toggle.data('id');
        $.post("{{ route('admin.portfolio.subsector.toggle_status') }}", {
          _token: "{{ csrf_token() }}",
          subsectorId: id,
        }, function (resp) {
          if (resp.success) {
            $.notify({
              title: 'Success',
              message: 'Status updated successfully!',
              icon: 'fa fa-check',
            }, {
              type: 'success',
              placement: { from: 'top', align: 'right' },
              showProgressbar: true,
              time: 1000,
              delay: 3000,
            });
          }
        }).fail(function () {
          $toggle.prop('checked', !$toggle.is(':checked'));
          $.notify({
            title: 'Error',
            message: 'Error updating status!',
            icon: 'fa fa-times',
          }, {
            type: 'danger',
            placement: { from: 'top', align: 'right' },
            showProgressbar: true,
            time: 1000,
            delay: 3000,
          });
        });
      });

      // "Add a subsector" button (right panel header) — opens the create
      // modal pre-scoped to the currently-open sector.
      $(document).on('click', '.add-subsector-btn', function () {
        $('#subsectorCreateSectorId').val(activeSectorId());
      });

      // Edit subsector — populate the edit modal (same data-* -> #in*
      // pattern as custom.js's generic .editbtn, done locally here since
      // these rows are AJAX content custom.js's own page-ready binding
      // never saw).
      $(document).on('click', '.subsector-edit-btn', function () {
        $('#insubsectorId').val($(this).data('id'));
        $('#insubsectorName').val($(this).data('name'));
        $('#insubsectorStatus').val($(this).data('status'));
        $('#insubsectorSerial').val($(this).data('serial_number'));
        $('#editSubsectorModal').modal('show');
      });

      // Delete subsector — same sweetalert confirm UX as custom.js's
      // generic .deletebtn (title/text/Yes-cancel buttons), reimplemented
      // locally with a delegated handler since this button is AJAX content
      // that custom.js's own non-delegated page-ready binding never sees.
      $(document).on('click', '.subsector-delete-btn', function (e) {
        e.preventDefault();
        var $form = $(this).closest('form');
        swal({
          title: 'Delete this subsector?',
          text: "You won't be able to revert this!",
          type: 'warning',
          buttons: {
            confirm: { text: 'Yes, delete it!', className: 'btn btn-success' },
            cancel: { visible: true, className: 'btn btn-danger' },
          },
        }).then(function (confirmed) {
          if (confirmed) $form.trigger('submit');
        });
      });

      // Delete sector (⋮ menu, right-panel header) — same treatment. A
      // sector delete can fail server-side (still has portfolios/
      // subsectors using it) — that comes back as a normal redirect with a
      // flash message via the existing full-page form submit, same as
      // every other delete form on this admin.
      $(document).on('click', '.sector-delete-btn', function (e) {
        e.preventDefault();
        var $form = $(this).closest('form');
        swal({
          title: 'Delete this sector?',
          text: "This can't be undone. Any portfolios or subsectors still using it must be reassigned first.",
          type: 'warning',
          buttons: {
            confirm: { text: 'Yes, delete it!', className: 'btn btn-success' },
            cancel: { visible: true, className: 'btn btn-danger' },
          },
        }).then(function (confirmed) {
          if (confirmed) $form.trigger('submit');
        });
      });

      // Create/Update subsector — AJAX submit, then just reload the panel
      // (simplest way to reflect the new/changed row + correct pagination/
      // count without hand-rolling a DOM insert).
      $('#subsectorCreateForm').on('submit', function (e) {
        e.preventDefault();
        var $form = $(this);
        $.post($form.attr('action'), $form.serialize(), function (resp) {
          if (resp === 'success') {
            $('#createSubsectorModal').modal('hide');
            $form.trigger('reset');
            loadSectorPanel(activeSectorId());
          } else {
            $.each(resp, function (field, msgs) {
              $('#createSubsectorModal #err' + field).text(msgs[0]);
            });
          }
        });
      });
      $('#subsectorEditForm').on('submit', function (e) {
        e.preventDefault();
        var $form = $(this);
        $.post($form.attr('action'), $form.serialize(), function (resp) {
          if (resp === 'success') {
            $('#editSubsectorModal').modal('hide');
            loadSectorPanel(activeSectorId());
          } else {
            $.each(resp, function (field, msgs) {
              $('#editSubsectorModal #eerr' + field).text(msgs[0]);
            });
          }
        });
      });

      // Drag-drop reorder — jQuery UI sortable (already loaded site-wide,
      // same as the Portfolio form's own Highlights list), delegated re-init
      // needed every panel swap since the <tbody> itself is replaced.
      $(document).on('sectorPanelLoaded', function () {
        var $tbody = $('#subsectorSortableBody');
        if (!$tbody.length || !$.fn.sortable) return;
        $tbody.sortable({
          handle: '.subsector-drag-handle',
          axis: 'y',
          placeholder: 'subsector-row-placeholder',
          // The table sits inside a .table-responsive wrapper (overflow-x:
          // auto, needed so a narrow screen can scroll the table sideways
          // instead of breaking the page layout) — that clips/hides the
          // dragged helper the moment it moves past the wrapper's own
          // bounds, which is why dragging looked completely broken (the
          // reorder itself was saving fine; there was just no visible
          // helper following the cursor to show it was happening).
          // appendTo:'body' (tried first) escapes the clipping but breaks
          // the helper's own position math instead — a raw cloned <tr>
          // positioned absolutely outside any <table> renders in the wrong
          // place entirely in table-layout browsers. Simplest safe fix:
          // leave the helper right where jQuery UI puts it by default
          // (inside the tbody, correct positioning) and just turn OFF the
          // wrapper's clipping for the duration of the drag instead.
          start: function () {
            $tbody.closest('.table-responsive').css('overflow', 'visible');
          },
          stop: function () {
            $tbody.closest('.table-responsive').css('overflow-x', 'auto');
          },
          helper: function (e, tr) {
            // Keep column widths from collapsing while dragging — Bootstrap
            // table rows lose their <td> widths as position:absolute
            // helpers by default.
            var $originals = tr.children();
            var $helper = tr.clone();
            $helper.children().each(function (i) { $(this).width($originals.eq(i).width()); });
            return $helper;
          },
          update: function () {
            var ids = $tbody.find('tr').map(function () { return $(this).data('id'); }).get();
            // The tbody only ever holds the CURRENT PAGE's rows — without
            // this offset, reordering on page 2+ renumbers them back to
            // 1..N, colliding with page 1's own numbers instead of
            // continuing from where this page actually starts.
            var offset = parseInt($('#subsectorPanelContainer').attr('data-page-offset'), 10) || 0;
            $.post("{{ route('admin.portfolio.subsector.reorder') }}", {
              _token: "{{ csrf_token() }}",
              ids: ids,
              offset: offset,
            }).done(function () {
              // The dragged-to position is correct visually the instant you
              // drop it, but the Order column's own numbers (and the #
              // column showing each row's position) still show their PRE-
              // drag values until this reloads — without it, a reorder
              // LOOKS like it silently did nothing even though it saved.
              loadSectorPanel(activeSectorId());
            });
          },
        });
      });
      $(document).trigger('sectorPanelLoaded');

      // Re-fire the sortable-init hook after every AJAX panel swap.
      //
      // BUG THIS FIXES: this used to be a separate, immediately-invoked
      // script OUTSIDE this DOMContentLoaded callback. The admin layout
      // renders @yield('content') (this whole page, this script included)
      // BEFORE admin.partials.scripts (where jQuery/jQuery UI actually get
      // loaded) — so an immediately-run script here executes before
      // `window.jQuery` exists yet, its own `if (!window.jQuery) return;`
      // guard silently bails, and the MutationObserver never gets created
      // AT ALL. Only the very first sector shown on page load (wired via
      // the explicit trigger() two lines up, which correctly waits for
      // DOMContentLoaded) ever got sortable initialized — clicking to any
      // OTHER sector loaded a fresh <tbody> that nothing ever re-armed.
      // Being inside this callback instead guarantees jQuery UI is already
      // loaded (DOMContentLoaded only fires once every earlier synchronous
      // script — including the bottom-of-body ones — has executed).
      var $panelContainer = document.getElementById('subsectorPanelContainer');
      if ($panelContainer && window.MutationObserver) {
        var observer = new MutationObserver(function () {
          $(document).trigger('sectorPanelLoaded');
        });
        observer.observe($panelContainer, { childList: true });
      }
    });
  </script>
@endsection
