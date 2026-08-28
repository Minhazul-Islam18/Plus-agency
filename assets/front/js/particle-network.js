(function () {
    "use strict";

    function initParticleNetwork(container) {
        var canvas = container.querySelector('canvas.pn-canvas');
        if (!canvas) {
            canvas = document.createElement('canvas');
            canvas.className = 'pn-canvas';
            canvas.style.position = 'absolute';
            canvas.style.inset = '0';
            canvas.style.width = '100%';
            canvas.style.height = '100%';
            canvas.style.pointerEvents = 'none';
            canvas.style.zIndex = '0';
            container.insertBefore(canvas, container.firstChild);
        }

        var ctx = canvas.getContext('2d');
        var dpr = Math.min(window.devicePixelRatio || 1, 2);
        var w, h, particles = [];
        var mouse = { x: null, y: null };
        var running = false;
        var rafId = null;

        var isMobile = window.matchMedia('(max-width: 767px)').matches;
        // Per-instance opt-in: data-particle-density="dense" on the same
        // element as data-particle-network — a per-pixel density multiplier,
        // not a flat headcount (see COUNT below). A fixed particle count
        // regardless of container size looked fine on the full-height hero
        // it was tuned for, but crammed 500 particles into a short
        // breadcrumb-height header bar on custom pages, way too dense.
        var dense = container.getAttribute('data-particle-density') === 'dense';
        // Calibrated from the original hero-only tuning: COUNT=95 over a
        // roughly 1900x700 hero (~1.33M px^2) => ~1 particle per 14000px^2.
        // Reused as the baseline "normal" density; dense packs ~2.2x tighter.
        var AREA_PER_PARTICLE = dense ? 14000 / 2.2 : 14000;
        var COUNT_MIN = isMobile ? 14 : 20;
        var COUNT_MAX = isMobile ? 90 : 220;
        var COUNT = COUNT_MIN;
        var LINK_DIST = dense ? (isMobile ? 130 : 180) : (isMobile ? 120 : 165);
        var REPEL_DIST = isMobile ? 80 : 110;

        // Canvas dimensions have a hard browser/GPU ceiling (well under
        // 100,000px) — a data-particle-network container wrapping a long
        // WYSIWYG page (a legal doc with thousands of paragraphs can push
        // its section past 100,000px tall) blows past that, the canvas
        // fails to allocate, and Chrome paints the failed tile as plain
        // white instead of transparent — confirmed live on the CGVLU page.
        // The particle effect is only ever meant to be seen behind the
        // hero/breadcrumb near the top anyway, so capping the canvas (and
        // its own CSS height, so it isn't stretched past its real pixels)
        // avoids the failure mode entirely instead of hoping no page ever
        // gets this long again.
        var MAX_CANVAS_HEIGHT = 2600;

        function resize() {
            var containerHeight = Math.min(container.offsetHeight, MAX_CANVAS_HEIGHT);
            w = canvas.width = container.offsetWidth * dpr;
            h = canvas.height = containerHeight * dpr;
            canvas.style.width = container.offsetWidth + 'px';
            canvas.style.height = containerHeight + 'px';
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
            w = container.offsetWidth;
            h = containerHeight;
            // Recomputed on every resize, not just once at init — a short
            // custom-page header and a full-height hero using the same
            // "dense" flag now each get a count proportional to their own
            // area instead of sharing one hardcoded number.
            COUNT = Math.max(COUNT_MIN, Math.min(COUNT_MAX, Math.round((w * h) / AREA_PER_PARTICLE)));
        }

        function initParticles() {
            particles = [];
            for (var i = 0; i < COUNT; i++) {
                particles.push({
                    x: Math.random() * w,
                    y: Math.random() * h,
                    vx: (Math.random() - 0.5) * 0.35,
                    vy: (Math.random() - 0.5) * 0.35,
                    r: Math.random() * 1.6 + 0.6
                });
            }
        }

        function step() {
            if (!running) return;
            ctx.clearRect(0, 0, w, h);

            for (var i = 0; i < particles.length; i++) {
                var p = particles[i];
                p.x += p.vx;
                p.y += p.vy;
                if (p.x < 0 || p.x > w) p.vx *= -1;
                if (p.y < 0 || p.y > h) p.vy *= -1;

                if (mouse.x !== null) {
                    var mdx = p.x - mouse.x, mdy = p.y - mouse.y;
                    var mdist = Math.sqrt(mdx * mdx + mdy * mdy);
                    if (mdist < REPEL_DIST && mdist > 0.01) {
                        var force = (REPEL_DIST - mdist) / REPEL_DIST;
                        p.x += (mdx / mdist) * force * 1.4;
                        p.y += (mdy / mdist) * force * 1.4;
                    }
                }

                ctx.beginPath();
                ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
                ctx.fillStyle = 'rgba(47, 227, 127, 0.55)';
                ctx.fill();

                for (var j = i + 1; j < particles.length; j++) {
                    var q = particles[j];
                    var dx = p.x - q.x, dy = p.y - q.y;
                    var dist = Math.sqrt(dx * dx + dy * dy);
                    if (dist < LINK_DIST) {
                        ctx.beginPath();
                        ctx.moveTo(p.x, p.y);
                        ctx.lineTo(q.x, q.y);
                        ctx.strokeStyle = 'rgba(47, 227, 127, ' + (0.16 * (1 - dist / LINK_DIST)) + ')';
                        ctx.lineWidth = 1;
                        ctx.stroke();
                    }
                }
            }
            rafId = requestAnimationFrame(step);
        }

        function start() {
            if (running) return;
            running = true;
            rafId = requestAnimationFrame(step);
        }

        function stop() {
            running = false;
            if (rafId) cancelAnimationFrame(rafId);
        }

        container.addEventListener('mousemove', function (e) {
            var rect = container.getBoundingClientRect();
            mouse.x = e.clientX - rect.left;
            mouse.y = e.clientY - rect.top;
        });
        container.addEventListener('mouseleave', function () {
            mouse.x = mouse.y = null;
        });

        window.addEventListener('resize', function () {
            resize();
            initParticles();
        });

        resize();
        initParticles();

        if ('IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        start();
                    } else {
                        stop();
                    }
                });
            }, { threshold: 0.05 });
            observer.observe(container);
        } else {
            start();
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (reduceMotion) return;

        var containers = document.querySelectorAll('[data-particle-network]');
        containers.forEach(function (container) {
            if (getComputedStyle(container).position === 'static') {
                container.style.position = 'relative';
            }
            initParticleNetwork(container);
        });
    });
})();
