/*
 * Dark theme FAQ page: accordion, live search + category filter, "show more",
 * and the "most viewed questions" panel (view tracking + live refresh).
 * The page HTML is HTTP-cached, so the panel is re-fetched as JSON on load
 * (see FrontendController@faqMostViewed) and a first-open of a question is
 * reported once per visitor per 24h (FrontendController@faqView).
 */
(function () {
  'use strict';

  var list = document.getElementById('faqxList');
  if (!list) return;

  var items = Array.prototype.slice.call(list.querySelectorAll('.faqx-item'));
  var searchEl = document.getElementById('faqxSearch');
  var catEl = document.getElementById('faqxCat');
  var moreBtn = document.getElementById('faqxMore');
  var moreWrap = document.getElementById('faqxMoreWrap');
  var moreLabel = moreBtn.querySelector('.dark-svc-load-more-label');
  var moreCount = moreBtn.querySelector('.dark-svc-load-more-count');
  var loading = false;
  var LOAD_DELAY = 650; // short, visible "loading" beat — all questions are already on the page
  var noneEl = document.getElementById('faqxNone');
  var grid = document.getElementById('faqxGrid');
  var panel = document.getElementById('faqxPanel');
  var panelList = document.getElementById('faqxPanelList');

  var pageSize = parseInt(list.getAttribute('data-page-size'), 10) || 7;
  var limit = pageSize;
  var viewUrl = list.getAttribute('data-view-url');
  var panelUrl = list.getAttribute('data-panel-url');
  var SEEN_KEY = 'faqx_seen';
  var VID_KEY = 'faqx_vid';
  var DAY = 24 * 60 * 60 * 1000;

  /* Random per-browser id, sent with the view ping so the server can tell
     distinct visitors apart even when they share one IP (NAT, office wifi,
     mobile carriers) — see FrontendController@faqView. Persisted in
     localStorage: a fresh incognito window has none, so it correctly reads
     as a new visitor there, same as clearing site data. */
  function visitorId() {
    try {
      var v = localStorage.getItem(VID_KEY);
      if (!v) {
        v = (window.crypto && crypto.randomUUID) ? crypto.randomUUID().replace(/-/g, '') :
          (Date.now().toString(36) + Math.random().toString(36).slice(2)).padEnd(20, '0');
        localStorage.setItem(VID_KEY, v);
      }
      return v;
    } catch (e) {
      return null; // private mode / storage blocked — server falls back to IP
    }
  }

  /* ---------- filtering + "load more" ---------- */
  function applyFilters(animate) {
    var q = (searchEl ? searchEl.value : '').trim().toLowerCase();
    var cat = catEl ? catEl.value : '';
    var filtering = q !== '' || cat !== '';
    var shown = 0, matches = 0, capped = 0, entering = 0;

    items.forEach(function (it) {
      var ok = (q === '' || it.getAttribute('data-search').indexOf(q) !== -1) &&
               (cat === '' || it.getAttribute('data-cat') === cat);
      if (ok) matches++;
      var visible = ok && (filtering || shown < limit);
      if (visible) shown++; else if (ok) capped++;

      var wasHidden = it.hidden;
      it.hidden = !visible;
      if (!visible) setOpen(it, false);

      if (animate && visible && wasHidden) {
        it.style.setProperty('--i', entering++);
        it.classList.add('is-entering');
      }
    });

    noneEl.hidden = matches !== 0;
    moreWrap.hidden = filtering || capped === 0;
    moreCount.textContent = '(' + capped + ' ' + moreBtn.getAttribute('data-text-more-suffix') + ')';
  }

  list.addEventListener('animationend', function (e) {
    if (e.target.classList.contains('faqx-item')) e.target.classList.remove('is-entering');
  });

  if (searchEl) searchEl.addEventListener('input', function () { limit = pageSize; applyFilters(); });
  if (catEl) catEl.addEventListener('change', function () { limit = pageSize; applyFilters(); });

  moreBtn.addEventListener('click', function () {
    if (loading) return;
    loading = true;
    moreBtn.disabled = true;
    moreWrap.classList.add('is-loading');
    moreLabel.textContent = moreBtn.getAttribute('data-text-loading');

    setTimeout(function () {
      limit += pageSize;
      applyFilters(true);
      loading = false;
      moreBtn.disabled = false;
      moreWrap.classList.remove('is-loading');
      moreLabel.textContent = moreBtn.getAttribute('data-text-load-more');
    }, LOAD_DELAY);
  });

  /* ---------- accordion ---------- */
  function setOpen(item, open) {
    item.classList.toggle('is-open', open);
    item.querySelector('.faqx-q').setAttribute('aria-expanded', open ? 'true' : 'false');
  }

  list.addEventListener('click', function (e) {
    var btn = e.target.closest('.faqx-q');
    if (!btn) return;
    var item = btn.closest('.faqx-item');
    var willOpen = !item.classList.contains('is-open');
    items.forEach(function (it) { if (it !== item) setOpen(it, false); });
    setOpen(item, willOpen);
    if (willOpen) track(item.getAttribute('data-id'));
  });

  /* ---------- view tracking ---------- */
  function readSeen() {
    try { return JSON.parse(localStorage.getItem(SEEN_KEY)) || {}; } catch (e) { return {}; }
  }
  function writeSeen(map) {
    try { localStorage.setItem(SEEN_KEY, JSON.stringify(map)); } catch (e) { /* private mode */ }
  }

  function track(id) {
    var seen = readSeen(), now = Date.now();
    if (seen[id] && now - seen[id] < DAY) return; // already counted for this visitor today
    seen[id] = now;
    writeSeen(seen);

    var vid = visitorId();
    var body = new URLSearchParams();
    if (vid) body.set('vid', vid);

    fetch(viewUrl.replace('__ID__', encodeURIComponent(id)), {
      method: 'POST',
      keepalive: true,
      credentials: 'same-origin',
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
      body: body.toString()
    }).then(function () {
      setTimeout(refreshPanel, 400);
    }).catch(function () { /* counting is best-effort */ });
  }

  /* ---------- most viewed panel ---------- */
  // Clicking a panel question expands its answer right there (see faqx-pa
  // below), instead of jumping into the main list — with a long question
  // list, scrolling down to find where that question actually lives (it
  // could be item #39) was the whole problem this panel exists to avoid.
  function setPanelOpen(li, open) {
    li.classList.toggle('is-open', open);
    var btn = li.querySelector('.faqx-pitem');
    if (btn) btn.setAttribute('aria-expanded', open ? 'true' : 'false');
  }

  function renderPanel(entries) {
    // Only show questions that exist in the rendered list.
    entries = entries.filter(function (e) { return document.getElementById('faqx-item-' + e.id); });

    panelList.textContent = '';
    entries.forEach(function (e) {
      var li = document.createElement('li');
      li.className = 'faqx-pli';
      li.setAttribute('data-faq', e.id);
      li.innerHTML =
        '<button type="button" class="faqx-pitem" aria-expanded="false" aria-controls="faqx-pa-' + e.id + '">' +
        '<span class="faqx-num" aria-hidden="true"></span><span class="faqx-pt"></span>' +
        '<span class="faqx-chev" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M9 6l6 6-6 6"/></svg></span>' +
        '</button>' +
        '<div class="faqx-pa" id="faqx-pa-' + e.id + '" role="region"><div class="faqx-pa-in"><p></p></div></div>';
      li.querySelector('.faqx-pt').textContent = e.question;
      // e.answer is server-escaped + nl2br'd (FrontendController@faqMostViewed,
      // same treatment as the main list's own answer markup) — safe to insert.
      li.querySelector('.faqx-pa-in p').innerHTML = e.answer;
      panelList.appendChild(li);
    });

    var has = entries.length > 0;
    panel.hidden = !has;
    grid.classList.toggle('is-solo', !has);
  }

  function refreshPanel() {
    fetch(panelUrl, { credentials: 'same-origin', cache: 'no-store', headers: { 'Accept': 'application/json' } })
      .then(function (r) { return r.ok ? r.json() : null; })
      .then(function (data) { if (data && Array.isArray(data.items)) renderPanel(data.items); })
      .catch(function () { /* keep the server-rendered panel */ });
  }

  panelList.addEventListener('click', function (e) {
    var btn = e.target.closest('.faqx-pitem');
    if (!btn) return;
    var li = btn.closest('.faqx-pli');
    var willOpen = !li.classList.contains('is-open');
    panelList.querySelectorAll('.faqx-pli').forEach(function (o) { if (o !== li) setPanelOpen(o, false); });
    setPanelOpen(li, willOpen);
    if (willOpen) track(li.getAttribute('data-faq'));
  });

  applyFilters();
  refreshPanel();
})();
