(function ($) {
    "use strict";
    if (!$) return;

    $(function () {
        var $carousel = $('.dark-case-carousel');
        if (!$carousel.length) return;

        // Own Owl instance (not the shared .case-carousel one main.js already
        // inits for the light theme) — that one scales up to 5 items on wide
        // screens; this stays capped at 3, matching the approved design.
        $carousel.owlCarousel({
            loop: true,
            autoplay: false,
            nav: false,
            dots: false,
            margin: 24,
            smartSpeed: 600,
            responsive: {
                0: { items: 1 },
                576: { items: 2 },
                992: { items: 3 }
            }
        });

        var $nav = $('[data-case-nav]');
        if (!$nav.length) return;

        $nav.find('.dark-case-nav-prev').on('click', function () {
            $carousel.trigger('prev.owl.carousel');
        });
        $nav.find('.dark-case-nav-next').on('click', function () {
            $carousel.trigger('next.owl.carousel');
        });
    });
})(window.jQuery);
