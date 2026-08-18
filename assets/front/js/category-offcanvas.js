/* Portfolio / Blog / FAQ: mobile Categories filter offcanvas. Shared by
   both themes and all three pages — only one theme's markup exists in the
   DOM per request/page, all reuse the same element IDs, so this one
   script covers every case. */
(function () {
  document.addEventListener('DOMContentLoaded', function () {
    var toggle = document.getElementById('pfOcToggle');
    var backdrop = document.getElementById('pfOcBackdrop');
    var panel = document.getElementById('pfOcPanel');
    var closeBtn = document.getElementById('pfOcClose');
    var body = document.getElementById('pfOcBody');
    var source = document.getElementById('pfCatSource');

    if (!toggle || !backdrop || !panel || !closeBtn || !body || !source) {
      return;
    }

    // #pfCatSource may wrap one widget (Portfolio/FAQ: just Categories) or
    // several sibling widgets (Blog: Search/Categories/Archives/Newsletter).
    // A widget can opt into a fixed top/bottom slot via data-pf-pin="top"/
    // "bottom" on its source element (Blog: Search pins top, Newsletter
    // pins bottom); everything else lands in the scrollable middle.
    var topWrap = document.createElement('div');
    topWrap.className = 'pf-oc-fixed-top';
    var midWrap = document.createElement('div');
    midWrap.className = 'pf-oc-scroll-mid';
    var bottomWrap = document.createElement('div');
    bottomWrap.className = 'pf-oc-fixed-bottom';

    Array.prototype.forEach.call(source.children, function (child) {
      var clone = child.cloneNode(true);
      // Strip ids from the clone (and its descendants) — the source stays
      // in the DOM (just hidden on mobile), so keeping duplicate ids around
      // would break any id-based selector/script targeting the original
      // (e.g. FAQ's tab-active-state sync, the newsletter form's submit
      // handler).
      if (clone.id) {
        clone.removeAttribute('id');
      }
      clone.querySelectorAll('[id]').forEach(function (el) {
        el.removeAttribute('id');
      });

      var pin = child.getAttribute('data-pf-pin');
      if (pin === 'top') {
        topWrap.appendChild(clone);
      } else if (pin === 'bottom') {
        bottomWrap.appendChild(clone);
      } else {
        midWrap.appendChild(clone);
      }
    });

    if (topWrap.children.length) {
      body.appendChild(topWrap);
    }
    if (midWrap.children.length) {
      body.appendChild(midWrap);
    }
    if (bottomWrap.children.length) {
      body.appendChild(bottomWrap);
    }

    function open() {
      backdrop.classList.add('is-open');
      panel.classList.add('is-open');
      document.body.classList.add('pf-oc-lock');
      toggle.setAttribute('aria-expanded', 'true');
    }

    function close() {
      backdrop.classList.remove('is-open');
      panel.classList.remove('is-open');
      document.body.classList.remove('pf-oc-lock');
      toggle.setAttribute('aria-expanded', 'false');
    }

    toggle.addEventListener('click', function () {
      panel.classList.contains('is-open') ? close() : open();
    });
    closeBtn.addEventListener('click', close);
    backdrop.addEventListener('click', close);
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && panel.classList.contains('is-open')) {
        close();
      }
    });
  });
})();
