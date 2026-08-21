(function () {
    "use strict";

    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var observer = null;

    function revealAll(items) {
        items.forEach(function (el) { el.classList.add('is-visible'); });
    }

    // All reveal-on-scroll variants (see common-style.css) share this one
    // observer/toggle mechanism — only the CSS motion differs per class.
    var REVEAL_SELECTOR = '.reveal-stagger, .reveal-text, .reveal-card, .reveal-left, ' +
        '.reveal-right, .reveal-cta, .reveal-fade, .reveal-timeline-item';

    // Elements already handed to the observer (or already revealed) are
    // marked so repeated scans — the below-fold pass, AJAX re-scans, the
    // early above-fold pass — never re-observe the same element twice.
    function pendingItems(root) {
        var scope = root || document;
        return Array.prototype.filter.call(scope.querySelectorAll(REVEAL_SELECTOR), function (el) {
            return !el.classList.contains('is-visible') && !el.classList.contains('reveal-observed');
        });
    }

    function observeItems(items) {
        items.forEach(function (el) {
            el.classList.add('reveal-observed');
            observer.observe(el);
        });
    }

    // Observes every not-yet-seen reveal element currently in the DOM. Safe
    // to call repeatedly — e.g. after AJAX swaps in new content (tender
    // filter results, etc) — since it only picks up elements that aren't
    // already visible/observed, never double-observes.
    function observeNew(root) {
        var items = pendingItems(root);
        if (!items.length) return;

        if (reduceMotion || !observer) {
            revealAll(items);
            return;
        }

        observeItems(items);
    }

    if (!reduceMotion && 'IntersectionObserver' in window) {
        observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.01, rootMargin: '0px 0px -10% 0px' });
    }

    window.observeRevealStagger = observeNew;

    // First viewport shouldn't wait for the whole page (every image, every
    // below-fold carousel) to finish loading — its own position is stable
    // regardless of what loads further down, so it's safe to check real
    // intersection for it as soon as the DOM is parsed, well before `load`.
    // A short delay lets the hero/first section finish its own initial
    // layout (slider height, etc) before we measure.
    function earlyRevealAboveFold() {
        if (reduceMotion || !observer) return;
        var vh = window.innerHeight;
        var items = pendingItems(document).filter(function (el) {
            return el.getBoundingClientRect().top < vh * 1.1;
        });
        if (items.length) observeItems(items);
    }

    document.addEventListener('DOMContentLoaded', function () {
        setTimeout(earlyRevealAboveFold, 100);
    });

    // Below-fold content keeps the safe start-on-scroll path — lazy-loaded
    // images, web-font swaps and carousel init keep reflowing the page for a
    // while after `load`, so checking real intersection any earlier than
    // this can still catch a transient short layout and mark a below-fold
    // section visible before the user ever scrolls near it.
    //
    // Starting on the user's first real scroll sidesteps that entirely — by
    // definition that only happens once the page has visually settled for
    // them. A generous fallback timer covers pages the user never scrolls
    // (or scrolls too fast to matter) so content isn't permanently stuck
    // invisible.
    var started = false;
    function start() {
        if (started) return;
        started = true;
        observeNew(document);
    }

    window.addEventListener('load', function () {
        window.addEventListener('scroll', start, { once: true, passive: true });
        setTimeout(start, 2500);
    });
})();
