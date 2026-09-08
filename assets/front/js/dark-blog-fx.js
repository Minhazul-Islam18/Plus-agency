(function ($) {
    "use strict";
    if (!$) return;

    $(function () {
        var $carousel = $('.dark-blog-carousel');
        if (!$carousel.length) return;

        $carousel.owlCarousel({
            loop: true,
            autoplay: true,
            autoplayTimeout: (window.darkCarouselSpeeds && window.darkCarouselSpeeds.blog) || 4500,
            autoplaySpeed: 1800,
            autoplayHoverPause: true,
            nav: false,
            dots: false,
            margin: 26,
            smartSpeed: 600,
            responsive: {
                0: { items: 1 },
                576: { items: 2 },
                992: { items: 3 }
            }
        });

        var prevBtn = document.getElementById('darkBlogPrev');
        var nextBtn = document.getElementById('darkBlogNext');
        if (prevBtn) prevBtn.addEventListener('click', function () { $carousel.trigger('prev.owl.carousel'); });
        if (nextBtn) nextBtn.addEventListener('click', function () { $carousel.trigger('next.owl.carousel'); });
    });
})(window.jQuery);
