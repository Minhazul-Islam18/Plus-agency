(function ($) {
    "use strict";
    if (!$) return;

    $(function () {
        var $carousel = $('.dark-testi-carousel');
        if (!$carousel.length) return;

        $carousel.owlCarousel({
            loop: true,
            autoplay: true,
            autoplayTimeout: (window.darkCarouselSpeeds && window.darkCarouselSpeeds.testimonial) || 4500,
            autoplaySpeed: 1800,
            autoplayHoverPause: true,
            nav: false,
            dots: false,
            margin: 24,
            smartSpeed: 600,
            responsive: {
                0: { items: 1 },
                640: { items: 2 },
                2200: { items: 3 }
            }
        });

        var prevBtn = document.getElementById('darkTestiPrev');
        var nextBtn = document.getElementById('darkTestiNext');
        if (prevBtn) prevBtn.addEventListener('click', function () { $carousel.trigger('prev.owl.carousel'); });
        if (nextBtn) nextBtn.addEventListener('click', function () { $carousel.trigger('next.owl.carousel'); });
    });
})(window.jQuery);
