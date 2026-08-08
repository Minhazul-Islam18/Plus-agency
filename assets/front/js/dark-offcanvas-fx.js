(function () {
    "use strict";

    function init() {
        var toggle = document.getElementById('darkOcToggle');
        var backdrop = document.getElementById('darkOcBackdrop');
        var panel = document.getElementById('darkOcPanel');
        var closeBtn = document.getElementById('darkOcClose');
        // The panel/backdrop render via @stack at the end of <body> (so
        // position:fixed isn't hijacked by the header's backdrop-filter
        // containing block) — they don't exist yet when this script tag is
        // reached, so wait for DOMContentLoaded rather than querying inline.
        if (!toggle || !backdrop || !panel || !closeBtn) return;

        function open() {
            toggle.classList.add('is-active');
            toggle.setAttribute('aria-expanded', 'true');
            backdrop.classList.add('is-open');
            panel.classList.add('is-open');
            document.body.classList.add('dark-oc-lock');
        }

        function close() {
            toggle.classList.remove('is-active');
            toggle.setAttribute('aria-expanded', 'false');
            backdrop.classList.remove('is-open');
            panel.classList.remove('is-open');
            document.body.classList.remove('dark-oc-lock');
        }

        toggle.addEventListener('click', function () {
            panel.classList.contains('is-open') ? close() : open();
        });
        closeBtn.addEventListener('click', close);
        backdrop.addEventListener('click', close);
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && panel.classList.contains('is-open')) close();
        });

        // Event delegation so every nesting depth the admin configures (level 2,
        // 3, 4...) toggles independently — each .dark-oc-item only opens/closes
        // itself, parents and siblings are untouched.
        panel.addEventListener('click', function (e) {
            var link = e.target.closest('.dark-oc-link');
            if (!link) return;
            var item = link.parentElement;
            if (!item.classList.contains('dark-oc-item')) return;
            var hasChildren = item.querySelector(':scope > .dark-oc-sub-wrap');
            if (!hasChildren) return; // plain link — let it navigate
            e.preventDefault();
            item.classList.toggle('is-open');
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
