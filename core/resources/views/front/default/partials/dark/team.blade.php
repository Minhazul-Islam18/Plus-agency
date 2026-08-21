<div class="team-section dark-team-section dark-section-seam section-padding"
    @if (!empty($bs->team_bg)) style="background-image: url('{{ asset('assets/front/img/' . $bs->team_bg) }}'); background-size:cover; position: relative; overflow: hidden;" @endif>
    @if (!empty($bs->team_bg))
        <div class="team-overlay"
            style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: #{{ $be->team_overlay_color ?? '000000' }}; opacity: {{ $be->team_overlay_opacity ?? '0.6' }}; z-index: 0; pointer-events: none;">
        </div>
    @endif
    <div class="team-content" style="position: relative; z-index: 2;">
        <div class="container">
            <div class="dark-team-head reveal-text" style="--d:.05s">
                <div class="dark-team-head-text">
                    <span class="section-eyebrow">{{ convertUtf8($bs->team_section_title) }}</span>
                    <h2 class="gradient-shine-heading">{{ convertUtf8($bs->team_section_subtitle) }}</h2>
                </div>
                <div class="dark-team-actions">
                    @if (Route::has('front.team'))
                        <a href="{{ route('front.team') }}" class="dark-intro-cta">
                            <span>{{ __('Show all') }}</span><i>&#8594;</i>
                        </a>
                    @endif
                    @if ($members->count() > 1)
                        <div class="dark-team-nav">
                            <button type="button" id="darkTeamPrev" aria-label="{{ __('Previous') }}"><svg viewBox="0 0 24 24"><path d="M15 6l-6 6 6 6"/></svg></button>
                            <button type="button" id="darkTeamNext" aria-label="{{ __('Next') }}"><svg viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></svg></button>
                        </div>
                    @endif
                </div>
            </div>

            <div class="dark-team-carousel dark-glass-carousel owl-carousel owl-theme">
                @foreach ($members as $key => $member)
                    <div class="dark-team-member team-clickable reveal-card" style="--d:{{ ($key % 4) * 0.1 }}s" data-member-id="{{ $member->id }}" role="button" tabindex="0">
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
    </div>
</div>
@include('front.partials.team-member-modal')
