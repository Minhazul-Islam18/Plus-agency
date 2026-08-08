(function () {
    "use strict";

    var ring = document.querySelector(".dark-btt-ring-progress");
    if (!ring) return;

    var CIRC = 2 * Math.PI * 20;
    ring.style.strokeDasharray = CIRC;
    ring.style.strokeDashoffset = CIRC;

    var reduceMotion = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    var ticking = false;

    function update() {
        var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        var docHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        var pct = docHeight > 0 ? Math.min(scrollTop / docHeight, 1) : 0;
        ring.style.strokeDashoffset = CIRC - pct * CIRC;
        ticking = false;
    }

    update();

    if (!reduceMotion) {
        window.addEventListener(
            "scroll",
            function () {
                if (!ticking) {
                    requestAnimationFrame(update);
                    ticking = true;
                }
            },
            { passive: true }
        );
        window.addEventListener("resize", update);
    }
})();
