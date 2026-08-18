@extends("front.$version.layout")

@section('pagename')
 - {{__('Team Members')}}
@endsection

@section('meta-keywords', "$be->team_meta_keywords")
@section('meta-description', "$be->team_meta_description")

@section('breadcrumb-title', $bs->team_title)
@section('breadcrumb-subtitle', $bs->team_subtitle)
@section('breadcrumb-link', __('Team Members'))

@if(!empty($bs->team_bg))
@section('breadcrumb-bg', asset('assets/front/img/'.$bs->team_bg))
@endif

@if(!empty($be->team_overlay_color))
@section('breadcrumb-overlay-color', $be->team_overlay_color)
@endif

@if(!empty($be->team_overlay_opacity))
@section('breadcrumb-overlay-opacity', $be->team_overlay_opacity)
@endif

@section('content')

@if ($be->theme_version == 'dark')
  <!--   dark team page start   -->
  <div class="dark-svcp-section">
    <div class="dark-team-grid">
      @foreach ($members as $key => $member)
        <div class="dark-team-member team-clickable" data-member-id="{{ $member->id }}" role="button" tabindex="0">
          <img class="team-img" src="{{ asset('assets/front/img/members/' . $member->image) }}" alt="">
          <span class="team-scrim"></span>
          <span class="team-view-hint" aria-label="{{ __('View Profile') }}"><i class="fas fa-plus"></i></span>
          <div class="team-social">
            @if (!empty($member->facebook))
              <a href="{{ $member->facebook }}" target="_blank"><i class="fab fa-facebook-f"></i></a>
            @endif
            @if (!empty($member->twitter))
              <a href="{{ $member->twitter }}" target="_blank"><i class="fab fa-twitter"></i></a>
            @endif
            @if (!empty($member->linkedin))
              <a href="{{ $member->linkedin }}" target="_blank"><i class="fab fa-linkedin-in"></i></a>
            @endif
            @if (!empty($member->whatsapp_link))
              <a href="{{ $member->whatsapp_link }}" target="_blank"><i class="fab fa-whatsapp"></i></a>
            @endif
          </div>
          <div class="team-info">
            <h3>{{ convertUtf8($member->name) }}</h3>
            <p>{{ convertUtf8($member->rank) }}</p>
          </div>
        </div>
      @endforeach
    </div>
  </div>
  @include('front.partials.team-member-modal')
  <!--   dark team page end   -->
@else
  <!--   team page start   -->
  <div class="team-page">
    <div class="container">
      <div class="row">
        @foreach ($members as $key => $member)
          <div class="col-lg-3 col-sm-6">
            <div class="single-team-member team-clickable" data-member-id="{{ $member->id }}" role="button" tabindex="0">
               <div class="team-img-wrapper">
                  <img class="lazy" data-src="{{asset('assets/front/img/members/'.$member->image)}}" alt="">
                  <span class="team-view-hint" aria-label="{{ __('View Profile') }}"><i class="fas fa-plus"></i></span>
                  <div class="social-accounts">
                     <ul class="social-account-lists">
                        @if (!empty($member->facebook))
                          <li class="single-social-account"><a href="{{$member->facebook}}"><i class="fab fa-facebook-f"></i></a></li>
                        @endif
                        @if (!empty($member->twitter))
                          <li class="single-social-account"><a href="{{$member->twitter}}"><i class="fab fa-twitter"></i></a></li>
                        @endif
                        @if (!empty($member->linkedin))
                          <li class="single-social-account"><a href="{{$member->linkedin}}"><i class="fab fa-linkedin-in"></i></a></li>
                        @endif
                        @if (!empty($member->whatsapp_link))
                          <li class="single-social-account"><a href="{{$member->whatsapp_link}}" target="_blank"><i class="fab fa-whatsapp"></i></a></li>
                        @endif
                     </ul>
                  </div>
               </div>
               <div class="member-info">
                  <h5 class="member-name">{{convertUtf8($member->name)}}</h5>
                  <small>{{convertUtf8($member->rank)}}</small>
               </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>
  @include('front.partials.team-member-modal')
  <!--   team page end   -->
@endif
@endsection
