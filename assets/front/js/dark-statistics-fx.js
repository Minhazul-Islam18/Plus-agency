(function () {
    "use strict";

    document.addEventListener('DOMContentLoaded', function () {
        var cards = document.querySelectorAll('[data-stat]');
        if (!cards.length) return;

        var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        function easeOutExpo(t) {
            return t === 1 ? 1 : 1 - Math.pow(2, -10 * t);
        }

        function animateCount(card) {
            var target = parseInt(card.getAttribute('data-target'), 10) || 0;
            var el = card.querySelector('[data-count]');
            card.classList.add('is-counting');
            if (!el) return;
            if (reduceMotion) { el.textContent = target.toLocaleString('fr-FR'); return; }

            var duration = 1700;
            var start = null;
            function step(ts) {
                if (start === null) start = ts;
                var progress = Math.min((ts - start) / duration, 1);
                var value = Math.round(easeOutExpo(progress) * target);
                el.textContent = value.toLocaleString('fr-FR');
                if (progress < 1) requestAnimationFrame(step);
            }
            requestAnimationFrame(step);
        }

        if (!('IntersectionObserver' in window)) {
            cards.forEach(animateCount);
            return;
        }

        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    animateCount(entry.target);
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.4 });

        cards.forEach(function (c) { io.observe(c); });
    });
})();
