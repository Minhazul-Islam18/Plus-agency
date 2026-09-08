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

        // Vertical autoplay carousel (discrete step, DOM recycling — not a
        // marquee): shows 3 items, steps STEP_COUNT up at a time, then moves
        // the scrolled-past items to the bottom and yanks the transform back
        // by their exact height with transitions off (invisible, same content).
        var viewport = document.getElementById('darkApproachViewport');
        var list = document.getElementById('darkApproachList');
        if (!viewport || !list) return;

        var items = Array.prototype.slice.call(list.children);
        var VISIBLE = 3;
        var STEP_COUNT = 1;
        if (items.length <= VISIBLE) return;

        var SPEED_MS = (window.darkCarouselSpeeds && window.darkCarouselSpeeds.approach) || 4500;
        var offsetY = 0;
        var timer = null;
        var busy = false;

        function setViewportHeight() {
            var h = 0;
            for (var i = 0; i < VISIBLE && i < items.length; i++) {
                h += items[i].getBoundingClientRect().height;
            }
            viewport.style.height = h + 'px';
        }

        function step() {
            if (busy) return;
            busy = true;

            var leaving = items.slice(0, STEP_COUNT);
            var h = 0;
            for (var j = 0; j < leaving.length; j++) {
                h += leaving[j].getBoundingClientRect().height;
            }

            // Item heights vary with text length, so the set of 3 items
            // visible after this step needs its own height — animate the
            // viewport to it in sync with the slide, not a stale one-time value.
            var nextH = 0;
            for (var i = 0; i < VISIBLE; i++) {
                nextH += items[(STEP_COUNT + i) % items.length].getBoundingClientRect().height;
            }
            viewport.style.height = nextH + 'px';

            offsetY -= h;
            list.style.transform = 'translateY(' + offsetY + 'px)';

            var done = false;
            var fallback = setTimeout(onEnd, 800);
            function onEnd() {
                if (done) return;
                done = true;
                busy = false;
                list.removeEventListener('transitionend', onEnd);
                clearTimeout(fallback);

                for (var k = 0; k < leaving.length; k++) {
                    list.removeChild(leaving[k]);
                    list.appendChild(leaving[k]);
                    items.push(items.shift());
                }

                list.style.transition = 'none';
                offsetY += h;
                list.style.transform = 'translateY(' + offsetY + 'px)';
                void list.offsetHeight;
                list.style.transition = '';
            }
            list.addEventListener('transitionend', onEnd);
        }

        // Manual "previous" — mirror of step(): pull STEP_COUNT items back
        // from the end to the front, park the list off-screen above (instant,
        // no transition — invisible since content just got reordered to match),
        // then transition back down to reveal them.
        function stepReverse() {
            if (busy) return;
            busy = true;

            var n = items.length;
            var entering = items.slice(n - STEP_COUNT);
            var h = 0;
            for (var j = 0; j < entering.length; j++) {
                h += entering[j].getBoundingClientRect().height;
            }

            for (var k = entering.length - 1; k >= 0; k--) {
                list.insertBefore(entering[k], list.firstChild);
                items.unshift(items.pop());
            }

            var nextH = 0;
            for (var i = 0; i < VISIBLE; i++) {
                nextH += items[i].getBoundingClientRect().height;
            }

            list.style.transition = 'none';
            offsetY -= h;
            list.style.transform = 'translateY(' + offsetY + 'px)';
            viewport.style.height = nextH + 'px';
            void list.offsetHeight;
            list.style.transition = '';

            offsetY += h;
            list.style.transform = 'translateY(' + offsetY + 'px)';

            var done = false;
            var fallback = setTimeout(onEnd, 800);
            function onEnd() {
                if (done) return;
                done = true;
                busy = false;
                list.removeEventListener('transitionend', onEnd);
                clearTimeout(fallback);
            }
            list.addEventListener('transitionend', onEnd);
        }

        function start() {
            stop();
            timer = setInterval(step, SPEED_MS);
        }
        function stop() {
            if (timer) clearInterval(timer);
        }

        setViewportHeight();
        window.addEventListener('resize', setViewportHeight);

        viewport.addEventListener('mouseenter', stop);
        viewport.addEventListener('mouseleave', start);

        var upBtn = document.getElementById('darkApproachUp');
        var downBtn = document.getElementById('darkApproachDown');
        if (upBtn) {
            upBtn.addEventListener('click', function () {
                stepReverse();
                start();
            });
        }
        if (downBtn) {
            downBtn.addEventListener('click', function () {
                step();
                start();
            });
        }

        start();
    });
})();
