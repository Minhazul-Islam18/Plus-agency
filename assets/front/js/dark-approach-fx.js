(function () {
    "use strict";

    document.addEventListener('DOMContentLoaded', function () {
        var steps = document.querySelectorAll('[data-approach-step]');
        if (!steps.length || !('IntersectionObserver' in window)) return;

        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) entry.target.classList.add('is-active');
            });
        }, { threshold: 0.5 });

        steps.forEach(function (s) { io.observe(s); });
    });
})();
