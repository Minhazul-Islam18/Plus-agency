(function () {
    "use strict";

    document.addEventListener('DOMContentLoaded', function () {
        var items = document.querySelectorAll('.reveal-stagger');
        if (!items.length) return;

        var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (reduceMotion || !('IntersectionObserver' in window)) {
            items.forEach(function (el) { el.classList.add('is-visible'); });
            return;
        }

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.01, rootMargin: '0px 0px 200px 0px' });

        items.forEach(function (el) { observer.observe(el); });

        // Safety net: never leave content permanently invisible if the
        // observer misses an element for any reason (layout race, zero
        // size at observe-time, etc) — force-reveal everything left over
        // shortly after load regardless of scroll position.
        setTimeout(function () {
            items.forEach(function (el) { el.classList.add('is-visible'); });
        }, 2500);
    });
})();
