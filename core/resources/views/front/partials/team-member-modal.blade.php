{{-- Shared team-member profile popup: one modal + one hidden data source per
     member. Included wherever a $members collection is rendered (homepage
     carousel, full team page), in both light and dark themes. Cards opt in
     by adding class="team-clickable" data-member-id="{{ $member->id }}" —
     the click handler (assets/front/js/team-modal.js) does the rest. --}}
<div class="modal fade team-profile-modal {{ ($be->theme_version ?? '') == 'dark' ? 'dark-team-profile-modal' : '' }}"
    id="teamMemberModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <div class="team-modal-banner">
                <span class="team-modal-banner-glow"></span>
                <div class="team-modal-avatar-wrap">
                    <img id="teamModalImg" class="team-modal-avatar" src="" alt="">
                </div>
                <div class="team-modal-banner-title">
                    <span id="teamModalBannerName" class="team-modal-banner-name"></span>
                    <span id="teamModalBannerRank" class="team-modal-banner-rank"></span>
                </div>
            </div>
            <div class="team-modal-scroll">
                <div class="team-modal-heading">
                    <h3 id="teamModalName"></h3>
                    <span id="teamModalRank" class="team-modal-rank"></span>
                    <div id="teamModalSocials" class="team-modal-socials"></div>
                </div>
                <div id="teamModalContact" class="team-modal-contact"></div>
                <div class="team-modal-divider"></div>
                <div id="teamModalDetails" class="team-modal-details"></div>
            </div>
        </div>
    </div>
</div>

@foreach ($members as $member)
    <div class="team-bio-source d-none" data-member-id="{{ $member->id }}"
        data-name="{{ convertUtf8($member->name) }}"
        data-rank="{{ convertUtf8($member->rank) }}"
        data-image="{{ asset('assets/front/img/members/' . $member->image) }}"
        data-facebook="{{ $member->facebook }}"
        data-twitter="{{ $member->twitter }}"
        data-linkedin="{{ $member->linkedin }}"
        data-whatsapp="{{ $member->whatsapp_link }}"
        data-phone="{{ $member->whatsapp }}"
        data-email="{{ $member->email }}">{!! $member->details !!}</div>
@endforeach
