(function () {
    "use strict";

    var hero = document.getElementById("darkHeroSlider");
    if (!hero) return;

    var slides = Array.prototype.slice.call(hero.querySelectorAll(".dark-hslider-slide"));
    if (slides.length === 0) return;

    var railItems = Array.prototype.slice.call(hero.querySelectorAll(".dark-hslider-rail-item"));
    var progressFill = document.getElementById("darkHeroSliderProgressFill");
    var currentLabel = document.getElementById("darkHeroSliderCurrent");
    var prevBtn = document.getElementById("darkHeroSliderPrev");
    var nextBtn = document.getElementById("darkHeroSliderNext");

    var reduceMotion = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    var idx = 0;
    var AUTOPLAY_MS = 5500;

    // setTimeout + performance.now() tracking (rather than a fixed-tick
    // setInterval) so a hover-pause can resume from exactly where it left
    // off instead of restarting the whole slide duration.
    var autoplayTimer = null;
    var remaining = AUTOPLAY_MS;
    var segmentStart = 0;
    var hasMultiple = slides.length > 1;

    function render() {
        slides.forEach(function (s, i) {
            s.classList.toggle("is-active", i === idx);
        });

        if (!hasMultiple) return;

        railItems.forEach(function (r, i) {
            r.classList.toggle("is-active", i === idx);
            var fill = r.querySelector(".dark-hslider-rail-bar-fill");
            fill.classList.remove("is-filling");
            fill.style.width = i === idx ? "0%" : i < idx ? "100%" : "0%";
        });
        if (progressFill) progressFill.classList.remove("is-filling");
        if (currentLabel) currentLabel.textContent = String(idx + 1).padStart(2, "0");

        if (reduceMotion) return;

        // Force a reflow before re-adding the class. The progress bar (and
        // each rail bar) is one reused DOM node, not recreated per slide —
        // once the loop wraps back around, remove-then-add of the same
        // class name on the same node can get coalesced by the browser
        // into a no-op, so the animation never restarts without this.
        void hero.offsetWidth;

        railItems[idx].querySelector(".dark-hslider-rail-bar-fill").classList.add("is-filling");
        if (progressFill) progressFill.classList.add("is-filling");
    }

    function scheduleAutoplay(ms) {
        if (!hasMultiple || reduceMotion) return;
        clearTimeout(autoplayTimer);
        remaining = ms;
        segmentStart = performance.now();
        autoplayTimer = setTimeout(next, ms);
    }

    function pauseAutoplay() {
        if (!hasMultiple) return;
        clearTimeout(autoplayTimer);
        remaining -= performance.now() - segmentStart;
        if (remaining < 250) remaining = AUTOPLAY_MS;
        hero.classList.add("is-paused");
    }

    function resumeAutoplay() {
        hero.classList.remove("is-paused");
        scheduleAutoplay(remaining);
    }

    function goto(i) {
        idx = (i + slides.length) % slides.length;
        render();
        scheduleAutoplay(AUTOPLAY_MS);
    }

    function next() {
        goto(idx + 1);
    }
    function prev() {
        goto(idx - 1);
    }

    render();

    if (!hasMultiple) return;

    railItems.forEach(function (r) {
        r.addEventListener("click", function () {
            goto(parseInt(r.getAttribute("data-goto"), 10));
        });
    });
    if (nextBtn) nextBtn.addEventListener("click", next);
    if (prevBtn) prevBtn.addEventListener("click", prev);

    hero.addEventListener("mouseenter", pauseAutoplay);
    hero.addEventListener("mouseleave", function () {
        if (!dragging) resumeAutoplay();
    });

    hero.setAttribute("tabindex", "0");
    hero.addEventListener("keydown", function (e) {
        if (e.key === "ArrowRight") {
            e.preventDefault();
            next();
        } else if (e.key === "ArrowLeft") {
            e.preventDefault();
            prev();
        }
    });

    // Grab-to-swipe: pointer events cover mouse + touch + pen in one
    // listener set. A small drag-follow on the active slide gives real
    // grab feedback; crossing the threshold on release commits to the
    // next/previous slide, otherwise it springs back via the CSS transition.
    var dragging = false;
    var startX = 0;
    var deltaX = 0;
    var DRAG_THRESHOLD = 70;

    hero.addEventListener("pointerdown", function (e) {
        // Skip drag entirely when the press starts on a real control (arrow
        // buttons, rail items) — calling setPointerCapture unconditionally
        // would redirect the pointerup that follows away from the button,
        // so the button's native click never fires.
        if (e.target.closest(".dark-hslider-arrow, .dark-hslider-rail-item")) return;

        dragging = true;
        startX = e.clientX;
        deltaX = 0;
        hero.classList.add("is-dragging");
        hero.setPointerCapture(e.pointerId);
        pauseAutoplay();
    });

    hero.addEventListener("pointermove", function (e) {
        if (!dragging) return;
        deltaX = e.clientX - startX;
        var damped = Math.max(-160, Math.min(160, deltaX * 0.4));
        slides[idx].style.transform = "translateX(" + damped + "px)";
    });

    function endDrag() {
        if (!dragging) return;
        dragging = false;
        hero.classList.remove("is-dragging");
        slides[idx].style.transform = "";

        if (deltaX <= -DRAG_THRESHOLD) {
            next();
        } else if (deltaX >= DRAG_THRESHOLD) {
            prev();
        } else {
            resumeAutoplay();
        }
        deltaX = 0;
    }

    hero.addEventListener("pointerup", endDrag);
    hero.addEventListener("pointercancel", endDrag);
    hero.addEventListener("pointerleave", function () {
        if (dragging) endDrag();
    });

    scheduleAutoplay(AUTOPLAY_MS);
})();
