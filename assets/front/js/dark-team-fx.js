(function ($) {
    "use strict";
    if (!$) return;

    $(function () {
        var $carousel = $('.dark-team-carousel');
        if (!$carousel.length) return;

        $carousel.owlCarousel({
            loop: true,
            autoplay: true,
            autoplayTimeout: 4500,
            autoplaySpeed: 1800,
            autoplayHoverPause: true,
            nav: false,
            dots: false,
            margin: 24,
            smartSpeed: 600,
            responsive: {
                0: { items: 1 },
                576: { items: 2 },
                768: { items: 3 },
                1200: { items: 4 },
                2200: { items: 5 }
            }
        });

        var prevBtn = document.getElementById('darkTeamPrev');
        var nextBtn = document.getElementById('darkTeamNext');
        if (prevBtn) prevBtn.addEventListener('click', function () { $carousel.trigger('prev.owl.carousel'); });
        if (nextBtn) nextBtn.addEventListener('click', function () { $carousel.trigger('next.owl.carousel'); });
    });
})(window.jQuery);
