(function ($) {
    "use strict";

    document.addEventListener('DOMContentLoaded', function () {
        // Re-queried on every tick (not cached once) so cards swapped in
        // later — e.g. the tenders page's AJAX filter results — keep ticking
        // without needing a re-init call.
        var tick = function () {
            var boxes = document.querySelectorAll('[data-deadline]');
            boxes.forEach(function (box) {
                var deadline = new Date(box.getAttribute('data-deadline')).getTime();
                var diff = Math.max(0, deadline - Date.now());
                var d = Math.floor(diff / 86400000);
                var h = Math.floor((diff % 86400000) / 3600000);
                var dEl = box.querySelector('[data-d]');
                var hEl = box.querySelector('[data-h]');
                if (dEl) dEl.textContent = String(d).padStart(2, '0');
                if (hEl) hEl.textContent = String(h).padStart(2, '0');
            });
        };
        tick();
        setInterval(tick, 60000);
    });

    if (!$) return;
    $(function () {
        var $carousel = $('.dark-tender-carousel');
        if (!$carousel.length) return;

        $carousel.owlCarousel({
            loop: true,
            autoplay: true,
            autoplayTimeout: 4500,
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

        var prevBtn = document.getElementById('darkTenderPrev');
        var nextBtn = document.getElementById('darkTenderNext');
        if (prevBtn) prevBtn.addEventListener('click', function () { $carousel.trigger('prev.owl.carousel'); });
        if (nextBtn) nextBtn.addEventListener('click', function () { $carousel.trigger('next.owl.carousel'); });
    });
})(window.jQuery);
