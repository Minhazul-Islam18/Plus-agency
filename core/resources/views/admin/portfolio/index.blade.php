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
                    <a href="{{route('admin.portfolio.create') . '?language=' . request()->input('language')}}" class="btn btn-primary float-right btn-sm ml-2"><i class="fas fa-plus"></i> {{ __('Add Portfolio') }}</a>
                    <a href="{{route('admin.portfolio.export') . '?language=' . request()->input('language')}}" class="btn btn-outline-secondary float-right btn-sm ml-2"><i class="fas fa-download"></i> {{ __('Export') }}</a>
                    <button class="btn btn-danger float-right btn-sm ml-2 d-none bulk-delete" data-href="{{route('admin.portfolio.bulk.delete')}}"><i class="flaticon-interface-5"></i> Delete</button>
                </div>
            </div>

            {{-- Sector / Country / Status filters + search — server-side, see PortfolioController@index --}}
            <form method="get" class="row mt-3 portfolio-filter-form">
                <input type="hidden" name="language" value="{{ request()->input('language') }}">
                <div class="col-lg-3 col-md-6 mt-2">
                    <input type="text" name="search" class="form-control" value="{{ request()->input('search') }}" placeholder="{{ __('Search a project...') }}">
                </div>
                <div class="col-lg-3 col-md-6 mt-2">
                    <select name="sector_id" class="form-control" onchange="this.form.submit()">
                        <option value="">{{ __('Sector') }} — {{ __('All') }}</option>
                        @foreach ($sectors as $sector)
                            <option value="{{ $sector->id }}" {{ request()->input('sector_id') == $sector->id ? 'selected' : '' }}>{{ convertUtf8($sector->name) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-6 mt-2">
                    <select name="country" class="form-control" onchange="this.form.submit()">
                        <option value="">{{ __('Country') }} — {{ __('All') }}</option>
                        @foreach ($countries as $c)
                            <option value="{{ $c['iso'] }}" {{ request()->input('country') == $c['iso'] ? 'selected' : '' }}>{{ $c['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-4 mt-2">
                    <select name="status_id" class="form-control" onchange="this.form.submit()">
                        <option value="">{{ __('Status') }} — {{ __('All') }}</option>
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
                        <a href="{{ url()->current() . '?language=' . request()->input('language') }}" class="btn btn-link btn-sm p-0"><i class="fas fa-undo"></i> {{ __('Reset') }}</a>
                        <a href="{{ url()->current() . '?language=' . request()->input('language') . '&archived=1' }}" class="btn btn-link btn-sm p-0 ml-3">{{ request()->filled('archived') ? __('View active projects') : __('View archived projects') }}</a>
                    </div>
                @else
                    <div class="col-12 mt-2">
                        <a href="{{ url()->current() . '?language=' . request()->input('language') . '&archived=1' }}" class="btn btn-link btn-sm p-0">{{ __('View archived projects') }}</a>
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
                    .portfolios-list-table th:nth-child(2), .portfolios-list-table td:nth-child(2) { width: 8%; }
                    .portfolios-list-table th:nth-child(3), .portfolios-list-table td:nth-child(3) { width: 18%; }
                    .portfolios-list-table th:nth-child(4), .portfolios-list-table td:nth-child(4) { width: 12%; }
                    .portfolios-list-table th:nth-child(5), .portfolios-list-table td:nth-child(5) { width: 10%; }
                    .portfolios-list-table th:nth-child(6), .portfolios-list-table td:nth-child(6) { width: 8%; }
                    .portfolios-list-table th:nth-child(7), .portfolios-list-table td:nth-child(7) { width: 9%; }
                    .portfolios-list-table th:nth-child(8), .portfolios-list-table td:nth-child(8) { width: 8%; }
                    .portfolios-list-table th:nth-child(9), .portfolios-list-table td:nth-child(9) { width: 24%; }
                    .portfolio-title-cell { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: block; }
                    /* One neutral badge style for every status — the module is
                       now an open, admin-managed list (like Sector) rather
                       than 3 fixed values, so there's no fixed name to key a
                       color off of anymore. */
                    .portfolio-status-badge { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; background: #cfe2ff; color: #084298; }
                </style>
                <div class="table-responsive">
                  <table class="table table-striped mt-3 portfolios-list-table" id="basic-datatables">
                    <thead>
                      <tr>
                        <th scope="col">
                            <input type="checkbox" class="bulk-check" data-val="all">
                        </th>
                        <th scope="col">{{ __('Image') }}</th>
                        <th scope="col">{{ __('Project Title') }}</th>
                        <th scope="col">{{ __('Client') }}</th>
                        <th scope="col">{{ __('Sector') }}</th>
                        <th scope="col">{{ __('Country') }}</th>
                        <th scope="col">{{ __('Status') }}</th>
                        <th scope="col">Featured</th>
                        <th scope="col">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($portfolios as $key => $portfolio)
                        <tr>
                          <td>
                            <input type="checkbox" class="bulk-check" data-val="{{$portfolio->id}}">
                          </td>
                          <td><img src="{{asset('assets/front/img/portfolios/featured/'.$portfolio->featured_image)}}" width="60"></td>
                          <td>
                            <span class="portfolio-title-cell" title="{{ convertUtf8($portfolio->title) }}">{{ convertUtf8($portfolio->title) }}</span>
                            @if ($portfolio->is_published == 0)
                                <span class="badge badge-secondary">{{ __('Unpublished') }}</span>
                            @endif
                          </td>
                          <td>{{ convertUtf8($portfolio->client_name) }}</td>
                          <td>
                            @if (!empty($portfolio->sector))
                                {{ convertUtf8($portfolio->sector->name) }}
                            @endif
                          </td>
                          <td>
                            @if (!empty($portfolio->country))
                                <span class="fi fi-{{ strtolower($portfolio->country) }}" style="border-radius:2px;margin-right:4px;"></span>{{ $portfolio->country }}
                            @endif
                          </td>
                          <td>
                            @if (!empty($portfolio->statusInfo))
                                <span class="portfolio-status-badge">{{ convertUtf8($portfolio->statusInfo->name) }}</span>
                            @endif
                          </td>
                          <td>
                            <form id="featureForm{{$portfolio->id}}" class="d-inline-block" action="{{route('admin.portfolio.feature')}}" method="post">
                            @csrf
                            <input type="hidden" name="portfolio_id" value="{{$portfolio->id}}">
                            <select class="form-control {{$portfolio->feature == 1 ? 'bg-success' : 'bg-danger'}}" name="feature" onchange="document.getElementById('featureForm{{$portfolio->id}}').submit();">
                                <option value="1" {{$portfolio->feature == 1 ? 'selected' : ''}}>Yes</option>
                                <option value="0" {{$portfolio->feature == 0 ? 'selected' : ''}}>No</option>
                            </select>
                            </form>
                          </td>
                          <td>
                            <button type="button" class="btn btn-info btn-sm portfolio-view-btn" data-id="{{ $portfolio->id }}">
                                <span class="btn-label">
                                    <i class="fas fa-eye"></i>
                                </span>
                                {{ __('Preview') }}
                            </button>
                            <a class="btn btn-secondary btn-sm" href="{{route('admin.portfolio.edit', $portfolio->id) . '?language=' . request()->input('language')}}">
                                <span class="btn-label">
                                    <i class="fas fa-edit"></i>
                                </span>
                                Edit
                            </a>
                            <form class="deleteform d-inline-block" action="{{route('admin.portfolio.delete')}}" method="post">
                              @csrf
                              <input type="hidden" name="portfolio_id" value="{{$portfolio->id}}">
                              <button type="submit" class="btn btn-danger btn-sm deletebtn">
                                <span class="btn-label">
                                  <i class="fas fa-trash"></i>
                                </span>
                                Delete
                              </button>
                            </form>
                            <div class="btn-group">
                                <button type="button" class="btn btn-light btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                    {{ __('More') }}
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="{{ route('admin.portfolio.duplicate', $portfolio->id) }}"><i class="fas fa-copy mr-1"></i> {{ __('Duplicate project') }}</a>
                                    <a class="dropdown-item portfolio-toggle-publish" href="#" data-id="{{ $portfolio->id }}" data-published="{{ $portfolio->is_published }}">
                                        @if ($portfolio->is_published)
                                            <i class="fas fa-times-circle mr-1"></i> {{ __('Unpublish') }}
                                        @else
                                            <i class="fas fa-check-circle mr-1"></i> {{ __('Publish') }}
                                        @endif
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <h6 class="dropdown-header">{{ __('Change status') }}</h6>
                                    @foreach ($statuses as $s)
                                        <a class="dropdown-item portfolio-set-status" href="#" data-id="{{ $portfolio->id }}" data-status-id="{{ $s->id }}">{{ convertUtf8($s->name) }}</a>
                                    @endforeach
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item portfolio-copy-link" href="#" data-url="{{ route('front.portfoliodetails', $portfolio->slug) }}"><i class="fas fa-link mr-1"></i> {{ __('Copy public link') }}</a>
                                    <a class="dropdown-item portfolio-toggle-archive" href="#" data-id="{{ $portfolio->id }}" data-archived="{{ $portfolio->is_archived }}">
                                        <i class="fas fa-box-archive mr-1"></i> {{ $portfolio->is_archived ? __('Unarchive') : __('Archive') }}
                                    </a>
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
                <h5 class="modal-title">{{ __('Project Details (read-only)') }}</h5>
                <button type="button" class="modal-close-btn" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" id="portfolioViewBody" style="max-height:75vh;overflow-y:auto;padding:26px;"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
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
        $('.portfolio-view-btn').on('click', function () {
            var id = $(this).data('id');
            $('#portfolioViewBody').html('<p class="text-center text-white">Loading…</p>');
            $('#portfolioViewModal').modal('show');
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
                $.notify({ message: {!! json_encode(__('Link copied to clipboard!')) !!} }, { type: 'success', placement: { from: 'top', align: 'right' }, time: 1000 });
            });
        });
    });
</script>
@endsection
