(function () {
    "use strict";

    var wrappers = Array.prototype.slice.call(document.querySelectorAll('.popup-wrapper.dark-popup'));
    if (!wrappers.length) return;

    function getClosed() {
        try {
            return JSON.parse(sessionStorage.getItem('closedPopups')) || [];
        } catch (e) {
            return [];
        }
    }

    function markClosed(id) {
        var closed = getClosed();
        if (closed.indexOf(id) === -1) closed.push(id);
        sessionStorage.setItem('closedPopups', JSON.stringify(closed));
    }

    var openWrapper = null;

    function show(wrapper) {
        openWrapper = wrapper;
        wrapper.style.display = 'flex';
        // Force a reflow so the transition to .is-open actually animates
        // instead of the browser coalescing the class add into the display change.
        void wrapper.offsetWidth;
        wrapper.classList.add('is-open');
        document.body.classList.add('dark-popup-lock');
    }

    function hide(wrapper, opts) {
        opts = opts || {};
        wrapper.classList.remove('is-open');
        document.body.classList.remove('dark-popup-lock');
        if (openWrapper === wrapper) openWrapper = null;

        var settled = false;
        function finish() {
            if (settled) return;
            settled = true;
            wrapper.style.display = 'none';
            wrapper.removeEventListener('transitionend', onEnd);
            if (!opts.silent) {
                markClosed(wrapper.getAttribute('data-popup_id'));
                var idx = wrappers.indexOf(wrapper);
                var next = wrappers[idx + 1];
                if (next) schedule(next);
            }
        }
        function onEnd(e) {
            if (e.target === wrapper) finish();
        }
        wrapper.addEventListener('transitionend', onEnd);
        // Safety net in case the transition never fires (e.g. reduced-motion).
        setTimeout(finish, 500);
    }

    function schedule(wrapper) {
        var closed = getClosed();
        var id = wrapper.getAttribute('data-popup_id');
        if (closed.indexOf(id) !== -1) {
            var idx = wrappers.indexOf(wrapper);
            var next = wrappers[idx + 1];
            if (next) schedule(next);
            return;
        }
        var delay = parseInt(wrapper.getAttribute('data-popup_delay'), 10) || 0;
        setTimeout(function () { show(wrapper); }, delay);
    }

    wrappers.forEach(function (wrapper) {
        var closeBtn = wrapper.querySelector('.dark-popup-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function () { hide(wrapper); });
        }
        // Click on the backdrop itself (not the content box) closes it.
        wrapper.addEventListener('click', function (e) {
            if (e.target === wrapper) hide(wrapper);
        });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && openWrapper) hide(openWrapper);
    });

    window.addEventListener('load', function () {
        schedule(wrappers[0]);
    });
})();
