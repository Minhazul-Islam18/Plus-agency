(function () {
    "use strict";

    function initBlob(zone) {
        var blob = zone.querySelector('[data-hero-blob]');
        if (!blob) return;

        var hasHover = window.matchMedia && window.matchMedia('(hover: hover)').matches;
        if (!hasHover) return;

        var raf = null;
        var pendingX = null, pendingY = null;

        function apply() {
            raf = null;
            blob.style.setProperty('--bx', pendingX + 'px');
            blob.style.setProperty('--by', pendingY + 'px');
        }

        zone.addEventListener('mousemove', function (e) {
            var rect = zone.getBoundingClientRect();
            pendingX = e.clientX - rect.left;
            pendingY = e.clientY - rect.top;
            if (!raf) raf = requestAnimationFrame(apply);
        });
        zone.addEventListener('mouseenter', function () {
            zone.classList.add('is-blob-active');
        });
        zone.addEventListener('mouseleave', function () {
            zone.classList.remove('is-blob-active');
        });
    }

    function initFlip(flip) {
        var titles = flip.querySelectorAll('.dark-hero-flip-line');
        if (titles.length < 2) return;

        var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var index = 0;
        titles.forEach(function (t, i) { if (i !== 0) t.classList.remove('is-active'); });

        if (reduceMotion) return;

        setInterval(function () {
            var current = titles[index];
            var nextIndex = (index + 1) % titles.length;
            var next = titles[nextIndex];

            // Sequential, never simultaneous: current fully leaves (opacity
            // 0) BEFORE next gets .is-active, so there's no crossfade
            // window where two titles are both partially visible/garbled.
            current.classList.remove('is-active');
            current.classList.add('is-leaving');

            setTimeout(function () {
                current.classList.remove('is-leaving');
                next.classList.add('is-active');
            }, 420);

            index = nextIndex;
        }, 3400);
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-hero-blob-zone]').forEach(initBlob);
        document.querySelectorAll('[data-hero-flip]').forEach(initFlip);
    });
})();

// Partners trust-marquee — pure CSS transform loop (see dark-glass.css,
// .dark-hero-marquee-track). Both Owl and Swiper were tried for continuous
// motion here and both stuttered — they're discrete-slide libraries with a
// loop-reset boundary that a long continuous transition inevitably crosses
// mid-flight. CSS transform animation has no such boundary; it's the
// correct primitive for this, not a library job. No JS needed.
