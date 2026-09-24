(function () {
    "use strict";

    document.addEventListener('DOMContentLoaded', function () {
        var btn = document.getElementById('darkSvcLoadMoreBtn');
        var wrap = document.getElementById('darkSvcLoadMoreWrap');
        var grid = document.getElementById('darkSvcGrid');
        if (!btn || !wrap || !grid) return;

        var INITIAL_COUNT = grid.children.length;
        var label = btn.querySelector('.dark-svc-load-more-label');
        var countEl = btn.querySelector('.dark-svc-load-more-count');

        // Server-rendered translations — the button's own text starts
        // correctly localized (Blade __()), but every state change after
        // that was hardcoding English, overwriting it back regardless of
        // the page's actual language.
        var TXT_LOADING = btn.dataset.textLoading || 'Loading…';
        var TXT_LOAD_MORE = btn.dataset.textLoadMore || 'Load more';
        var TXT_SHOW_LESS = btn.dataset.textShowLess || 'Show less';
        var TXT_MORE_SUFFIX = btn.dataset.textMoreSuffix || 'more';

        function setLoading(isLoading) {
            btn.disabled = isLoading;
            btn.style.opacity = isLoading ? '0.6' : '';
            wrap.classList.toggle('is-loading', isLoading);
            if (isLoading) {
                label.textContent = TXT_LOADING;
                countEl.style.display = 'none';
            }
        }

        function showLoadMoreState(total, loaded) {
            wrap.classList.remove('is-done');
            label.textContent = TXT_LOAD_MORE;
            countEl.style.display = '';
            countEl.textContent = '(' + (total - loaded) + ' ' + TXT_MORE_SUFFIX + ')';
            btn.dataset.offset = loaded;
            btn.dataset.total = total;
        }

        function showDoneState() {
            wrap.classList.add('is-done');
            label.textContent = TXT_SHOW_LESS;
            countEl.style.display = 'none';
        }

        btn.addEventListener('click', function () {
            if (wrap.classList.contains('is-done')) {
                while (grid.children.length > INITIAL_COUNT) {
                    grid.removeChild(grid.lastElementChild);
                }
                showLoadMoreState(parseInt(btn.dataset.total, 10) || INITIAL_COUNT, INITIAL_COUNT);
                wrap.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            var url = btn.dataset.url
                + '?lang=' + encodeURIComponent(btn.dataset.lang)
                + '&offset=' + encodeURIComponent(btn.dataset.offset);

            setLoading(true);
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    var tmp = document.createElement('div');
                    tmp.innerHTML = data.html;
                    // Snapshot first: tmp.children is live, and appendChild moves each card
                    // out of it, which made the loop skip every other card.
                    Array.prototype.slice.call(tmp.children).forEach(function (card, i) {
                        card.style.animationDelay = (i * 0.08) + 's';
                        grid.appendChild(card);
                    });
                    if (data.has_more) {
                        showLoadMoreState(data.total, data.next_offset);
                    } else {
                        btn.dataset.offset = data.next_offset;
                        btn.dataset.total = data.total;
                        showDoneState();
                    }
                })
                .finally(function () { setLoading(false); });
        });
    });
})();
