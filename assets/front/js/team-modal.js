(function () {
    "use strict";

    // Delegated so it works for the homepage carousel, the full team page,
    // and both themes without per-page wiring. Ignores clicks on an <a>
    // inside the card (social icons) so those keep navigating normally.
    $(document).on("click", ".team-clickable", function (e) {
        if ($(e.target).closest("a").length) return;
        openTeamModal($(this).data("member-id"));
    });

    $(document).on("keydown", ".team-clickable", function (e) {
        if (e.key === "Enter" || e.key === " ") {
            e.preventDefault();
            openTeamModal($(this).data("member-id"));
        }
    });

    function openTeamModal(id) {
        var src = $('.team-bio-source[data-member-id="' + id + '"]');
        if (!src.length) return;

        $("#teamModalImg").attr("src", src.data("image"));
        $("#teamModalName").text(src.data("name"));
        $("#teamModalRank").text(src.data("rank"));
        $("#teamModalBannerName").text(src.data("name"));
        $("#teamModalBannerRank").text(src.data("rank"));

        var socials = "";
        var links = {
            facebook: "fa-facebook-f",
            twitter: "fa-twitter",
            linkedin: "fa-linkedin-in",
            whatsapp: "fa-whatsapp",
        };
        $.each(links, function (key, icon) {
            var url = src.data(key);
            if (url) {
                socials += '<a href="' + url + '" target="_blank"><i class="fab ' + icon + '"></i></a>';
            }
        });
        $("#teamModalSocials").html(socials);

        var contact = "";
        var phone = src.attr("data-phone"); // attr(), not data() — avoids jQuery coercing a leading-zero/all-digit number
        var email = src.attr("data-email");
        if (phone) {
            contact += '<a href="tel:' + phone + '" class="team-modal-contact-item"><i class="fas fa-phone-alt"></i><span>' + phone + '</span></a>';
        }
        if (email) {
            contact += '<a href="mailto:' + email + '" class="team-modal-contact-item"><i class="fas fa-envelope"></i><span>' + email + '</span></a>';
        }
        $("#teamModalContact").html(contact);

        $("#teamModalDetails").html(src.html());

        var $scroll = $(".team-modal-scroll");
        $scroll.scrollTop(0);
        $("#teamMemberModal").removeClass("is-avatar-collapsed");

        $("#teamMemberModal").modal("show");
    }

    // The resting avatar position is centered (left:50%, responsive); the
    // collapsed target is a fixed 20px from the banner's left edge. Those two
    // anchor types can't be reconciled with CSS alone, so the horizontal
    // translate distance is measured from the real rendered banner width and
    // written as a CSS var the transform reads (--collapse-x).
    function syncCollapseOffset() {
        var banner = document.querySelector(".team-modal-banner");
        if (!banner) return;
        var dx = 92.5 - banner.getBoundingClientRect().width / 2;
        document.getElementById("teamMemberModal").style.setProperty("--collapse-x", dx + "px");
    }

    // Only measurable once the modal is actually visible (banner has real
    // width) — measuring before .modal("show") reads a 0-width display:none
    // element and produces a wrong (positive) offset.
    $("#teamMemberModal").on("shown.bs.modal", syncCollapseOffset);

    $(window).on("resize", function () {
        if ($("#teamMemberModal").hasClass("show")) {
            syncCollapseOffset();
        }
    });

    // Collapses the avatar to a small top-left badge once the bio has been
    // scrolled past the banner, and restores it when scrolled back to top —
    // same idea as an iOS collapsing profile header. Bound directly (not
    // delegated) since the modal markup is static, already in the DOM on
    // page load — and scroll events on non-window elements don't reliably
    // bubble to document across browsers, so delegation silently never fires.
    var SCROLL_COLLAPSE_AT = 20;
    document.addEventListener(
        "scroll",
        function (e) {
            if (!e.target || !e.target.classList || !e.target.classList.contains("team-modal-scroll")) {
                return;
            }
            var collapsed = e.target.scrollTop > SCROLL_COLLAPSE_AT;
            $("#teamMemberModal").toggleClass("is-avatar-collapsed", collapsed);
        },
        true
    );
})();
