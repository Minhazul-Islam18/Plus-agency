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
        var COUNT = isMobile ? 42 : 95;
        var LINK_DIST = isMobile ? 120 : 165;
        var REPEL_DIST = isMobile ? 80 : 110;

        function resize() {
            w = canvas.width = container.offsetWidth * dpr;
            h = canvas.height = container.offsetHeight * dpr;
            canvas.style.width = container.offsetWidth + 'px';
            canvas.style.height = container.offsetHeight + 'px';
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
            w = container.offsetWidth;
            h = container.offsetHeight;
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
