(function ($) {
    "use strict";
    if (!$) return;

    $(function () {
        var $carousel = $('.dark-partner-carousel');
        if (!$carousel.length) return;

        $carousel.owlCarousel({
            loop: true,
            autoplay: true,
            autoplayTimeout: (window.darkCarouselSpeeds && window.darkCarouselSpeeds.partner) || 4500,
            autoplaySpeed: 1800,
            autoplayHoverPause: true,
            smartSpeed: 1800,
            nav: false,
            dots: false,
            margin: 30,
            responsive: {
                0: { items: 2 },
                576: { items: 3 },
                992: { items: 5 }
            }
        });
    });
})(window.jQuery);
