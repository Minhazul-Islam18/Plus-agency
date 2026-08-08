@extends("front.$version.layout")

@section('pagename')
 - {{__('Unsubscribed')}}
@endsection

@section('no-breadcrumb', 'no-breadcrumb')

@section('content')

<div class="unsub-hero" id="unsubHero">
    <canvas id="unsubCanvas"></canvas>
    <span class="unsub-mesh unsub-mesh--1"></span>
    <span class="unsub-mesh unsub-mesh--2"></span>

    <div class="unsub-hero-inner">
        <div class="unsub-glass" id="unsubCard">
            <div class="unsub-icon">
                <span class="unsub-icon__ring"></span>
                <span class="unsub-icon__ring unsub-icon__ring--2"></span>
                <svg viewBox="0 0 52 52">
                    <circle class="unsub-icon__circle" cx="26" cy="26" r="24" fill="none"/>
                    <path class="unsub-icon__check" fill="none" d="M14 27l7 7 17-17"/>
                </svg>
            </div>

            <span class="unsub-eyebrow unsub-reveal" style="--d:0.05s">{{__('Newsletter Preferences')}}</span>
            <h2 class="unsub-title unsub-reveal" style="--d:0.1s">{{__('You have been unsubscribed')}}</h2>
            <p class="unsub-text unsub-reveal" style="--d:0.2s">{{__("You won't receive any more newsletter emails from us. Changed your mind? You can resubscribe below at any time.")}}</p>

            <form class="unsub-resubscribe unsub-reveal" id="unsubResubscribeForm" style="--d:0.3s" action="{{route('front.subscribe')}}" method="post">
                @csrf
                <span class="unsub-input-wrap">
                    <input type="email" name="email" placeholder="{{__('Enter Email Address')}}" required>
                </span>
                <button type="submit" id="unsubResubscribeBtn">
                    <span class="unsub-btn-label">{{__('Resubscribe')}}</span>
                    <span class="unsub-btn-spinner"></span>
                </button>
            </form>
            <p id="unsubResubscribeMsg" class="unsub-msg"></p>

            <a href="{{route('front.index')}}" class="unsub-home unsub-reveal" style="--d:0.4s">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M19 12H5M11 18l-6-6 6-6"/></svg> {{__('Back to Homepage')}}
            </a>
        </div>
    </div>
</div>

<svg width="0" height="0" style="position:absolute">
    <defs>
        <linearGradient id="unsubGrad" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0%" stop-color="#3e6fff"/>
            <stop offset="100%" stop-color="#17c3a2"/>
        </linearGradient>
    </defs>
</svg>

<style>
    /* Self-contained font-faces — this page's box design is independent of
       the site theme (renders the same on light or dark sites), so it
       can't rely on dark-glass.css's @font-face rules, which only load
       when theme_version == 'dark'. */
    @font-face {
        font-family: 'Unsub Fraunces';
        font-weight: 900;
        font-display: swap;
        src: url('{{ asset('assets/front/fonts/fraunces-900.woff2') }}') format('woff2');
    }
    @font-face {
        font-family: 'Unsub Plex Sans';
        font-weight: 100 700;
        font-display: swap;
        src: url('{{ asset('assets/front/fonts/plex-sans-variable.woff2') }}') format('woff2');
    }
    @font-face {
        font-family: 'Unsub Plex Mono';
        font-weight: 500;
        font-display: swap;
        src: url('{{ asset('assets/front/fonts/plex-mono-500.woff2') }}') format('woff2');
    }

    .unsub-hero {
        position: relative;
        overflow: hidden;
        min-height: 100vh;
        padding: 260px 20px 100px;
        background: radial-gradient(circle at 20% 15%, #0c1e2b 0%, #071016 42%, #04070d 100%);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    @media (max-width: 991px) { .unsub-hero { padding-top: 200px; } }
    @media (max-width: 575px) { .unsub-hero { padding-top: 160px; } }

    #unsubCanvas {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        display: block;
    }

    .unsub-mesh {
        position: absolute;
        border-radius: 42% 58% 65% 35% / 45% 40% 60% 55%;
        filter: blur(6px);
        opacity: 0.55;
        pointer-events: none;
        animation: unsubMorph 14s ease-in-out infinite;
    }
    .unsub-mesh--1 {
        width: 420px; height: 420px;
        background: radial-gradient(circle, rgba(62,111,255,0.32), transparent 70%);
        top: -8%; left: -6%;
    }
    .unsub-mesh--2 {
        width: 360px; height: 360px;
        background: radial-gradient(circle, rgba(23,195,162,0.24), transparent 70%);
        bottom: -10%; right: -8%;
        animation-duration: 18s;
        animation-direction: reverse;
    }
    @keyframes unsubMorph {
        0%, 100% { border-radius: 42% 58% 65% 35% / 45% 40% 60% 55%; transform: translate(0,0) scale(1); }
        33% { border-radius: 60% 40% 35% 65% / 55% 65% 35% 45%; transform: translate(24px,-18px) scale(1.08); }
        66% { border-radius: 35% 65% 55% 45% / 40% 45% 55% 60%; transform: translate(-18px,20px) scale(0.96); }
    }

    .unsub-hero-inner { position: relative; z-index: 2; width: 100%; display: flex; justify-content: center; }

    .unsub-glass {
        position: relative;
        width: 100%;
        max-width: 480px;
        text-align: center;
        padding: 56px 44px;
        border-radius: 24px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.13);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        box-shadow: 0 30px 80px rgba(0, 0, 0, 0.45), inset 0 1px 0 rgba(255,255,255,0.06);
        animation: unsubFadeUp 0.6s ease both;
        transition: transform 0.25s ease;
        will-change: transform;
        font-family: 'Unsub Plex Sans', 'Segoe UI', system-ui, sans-serif;
    }
    @keyframes unsubFadeUp {
        from { opacity: 0; transform: translateY(24px) scale(0.98); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    .unsub-reveal {
        opacity: 0;
        animation: unsubReveal 0.55s ease forwards;
        animation-delay: var(--d, 0s);
    }
    @keyframes unsubReveal {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .unsub-icon { position: relative; margin: 0 auto 28px; width: 82px; height: 82px; }
    .unsub-icon svg { position: relative; z-index: 2; width: 100%; height: 100%; }
    .unsub-icon__ring {
        position: absolute; inset: 0; border-radius: 50%;
        border: 1.5px solid rgba(23,195,162,0.5);
        animation: unsubPulse 2.4s ease-out 0.9s infinite;
    }
    .unsub-icon__ring--2 { animation-delay: 1.6s; }
    @keyframes unsubPulse {
        0% { transform: scale(0.75); opacity: 0.7; }
        80% { transform: scale(1.7); opacity: 0; }
        100% { transform: scale(1.7); opacity: 0; }
    }
    .unsub-icon__circle {
        stroke: url(#unsubGrad);
        stroke-width: 2.5;
        stroke-dasharray: 151;
        stroke-dashoffset: 151;
        filter: drop-shadow(0 0 6px rgba(23,195,162,0.55));
        animation: unsubDrawCircle 0.6s ease forwards;
    }
    .unsub-icon__check {
        stroke: #17c3a2;
        stroke-width: 3.5;
        stroke-linecap: round;
        stroke-linejoin: round;
        stroke-dasharray: 36;
        stroke-dashoffset: 36;
        animation: unsubDrawCheck 0.4s 0.55s ease forwards;
    }
    @keyframes unsubDrawCircle { to { stroke-dashoffset: 0; } }
    @keyframes unsubDrawCheck { to { stroke-dashoffset: 0; } }

    .unsub-eyebrow {
        display: inline-flex; align-items: center; gap: 9px;
        font-family: 'Unsub Plex Mono', monospace; font-size: 11.5px; letter-spacing: 0.14em; text-transform: uppercase;
        color: #17c3a2; margin-bottom: 14px;
    }
    .unsub-eyebrow::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: #17c3a2; box-shadow: 0 0 10px rgba(23,195,162,0.8); }

    .unsub-title {
        font-family: 'Unsub Fraunces', Georgia, serif;
        font-weight: 900;
        font-size: 27px;
        line-height: 1.25;
        margin: 0 0 14px;
        color: #fff;
    }
    .unsub-text { font-family: 'Unsub Plex Sans', 'Segoe UI', system-ui, sans-serif; color: rgba(255,255,255,0.65); font-size: 14.5px; line-height: 1.75; margin-bottom: 32px; }

    .unsub-resubscribe { display: flex; gap: 10px; margin-bottom: 12px; }
    .unsub-input-wrap { position: relative; flex: 1; }

    .unsub-resubscribe input {
        position: relative;
        z-index: 1;
        width: 100%;
        border: 1px solid rgba(255,255,255,0.14);
        border-radius: 100px;
        padding: 13px 20px;
        font-family: 'Unsub Plex Sans', 'Segoe UI', system-ui, sans-serif;
        font-size: 14px;
        outline: none;
        background: rgba(255,255,255,0.04);
        color: #fff;
        transition: border-color 0.2s ease, background 0.2s ease;
    }
    .unsub-resubscribe input::placeholder { color: rgba(255,255,255,0.35); }
    .unsub-resubscribe input:focus { border-color: rgba(23,195,162,0.55); background: rgba(255,255,255,0.06); }

    .unsub-resubscribe button {
        position: relative;
        overflow: hidden;
        border: none;
        background: linear-gradient(135deg, #3e6fff, #17c3a2);
        color: #04101f;
        font-family: 'Unsub Plex Mono', monospace;
        font-weight: 600;
        font-size: 12.5px;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        padding: 13px 26px;
        border-radius: 100px;
        cursor: pointer;
        transition: transform 0.2s cubic-bezier(.22,1.2,.36,1), box-shadow 0.25s ease;
        white-space: nowrap;
    }
    .unsub-resubscribe button:hover { transform: translateY(-2px); box-shadow: 0 14px 30px -8px rgba(23,195,162,0.45); }
    .unsub-resubscribe button:active { transform: translateY(0) scale(0.97); }
    .unsub-resubscribe button .unsub-btn-spinner {
        display: none;
        width: 13px; height: 13px;
        border: 2px solid rgba(4,16,15,0.35);
        border-top-color: #04101f;
        border-radius: 50%;
        margin-left: 8px;
        vertical-align: -2px;
        animation: unsubSpin 0.6s linear infinite;
    }
    .unsub-resubscribe button.loading .unsub-btn-spinner { display: inline-block; }
    .unsub-resubscribe button.loading { pointer-events: none; opacity: 0.85; }
    @keyframes unsubSpin { to { transform: rotate(360deg); } }

    .unsub-ripple {
        position: absolute;
        border-radius: 50%;
        background: rgba(4,16,15,0.35);
        transform: scale(0);
        animation: unsubRipple 0.6s ease-out;
        pointer-events: none;
    }
    @keyframes unsubRipple { to { transform: scale(2.6); opacity: 0; } }

    .unsub-msg {
        font-family: 'Unsub Plex Mono', monospace;
        font-size: 12px;
        margin-bottom: 22px;
        min-height: 16px;
        opacity: 0;
        transform: translateY(4px);
        transition: opacity 0.3s ease, transform 0.3s ease;
    }
    .unsub-msg.show { opacity: 1; transform: translateY(0); }
    .unsub-msg.success { color: #17c3a2; }
    .unsub-msg.error { color: #ff7a70; }

    .unsub-home {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: rgba(255,255,255,0.6);
        font-family: 'Unsub Plex Mono', monospace;
        font-size: 12px;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        text-decoration: none;
        transition: color 0.2s ease, gap 0.2s ease;
    }
    .unsub-home svg { width: 14px; height: 14px; }
    .unsub-home:hover { color: #17c3a2; gap: 12px; }

    @media (max-width: 480px) {
        .unsub-glass { padding: 40px 26px; }
        .unsub-resubscribe { flex-direction: column; }
    }
    @media (prefers-reduced-motion: reduce) {
        .unsub-mesh, .unsub-icon__ring, .unsub-glass, .unsub-reveal, .unsub-title { animation: none !important; }
        .unsub-glass { transform: none !important; opacity: 1; }
        #unsubCanvas { display: none; }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var hero = document.getElementById('unsubHero');
        var card = document.getElementById('unsubCard');
        var form = document.getElementById('unsubResubscribeForm');
        var btn = document.getElementById('unsubResubscribeBtn');
        var msg = document.getElementById('unsubResubscribeMsg');
        var canvas = document.getElementById('unsubCanvas');
        var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        // Card tilt on mouse move
        if (card && !reduceMotion) {
            hero.addEventListener('mousemove', function (e) {
                var rect = card.getBoundingClientRect();
                var cx = rect.left + rect.width / 2;
                var cy = rect.top + rect.height / 2;
                var dx = (e.clientX - cx) / rect.width;
                var dy = (e.clientY - cy) / rect.height;
                var maxTilt = 5;
                card.style.transform = 'perspective(900px) rotateX(' + (-dy * maxTilt) + 'deg) rotateY(' + (dx * maxTilt) + 'deg)';
            });
            hero.addEventListener('mouseleave', function () { card.style.transform = ''; });
        }

        // Ripple + spinner + fetch submit
        if (btn) {
            btn.addEventListener('click', function (e) {
                if (reduceMotion) return;
                var rect = btn.getBoundingClientRect();
                var ripple = document.createElement('span');
                var size = Math.max(rect.width, rect.height);
                ripple.className = 'unsub-ripple';
                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = (e.clientX - rect.left - size / 2) + 'px';
                ripple.style.top = (e.clientY - rect.top - size / 2) + 'px';
                btn.appendChild(ripple);
                setTimeout(function () { ripple.remove(); }, 650);
            });
        }

        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                btn.classList.add('loading');
                msg.classList.remove('show');

                var formData = new FormData(form);
                fetch(form.action, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                }).then(function (res) { return res.text(); })
                  .then(function (text) {
                    var success = text.trim() === 'success';
                    msg.className = 'unsub-msg ' + (success ? 'success' : 'error');
                    msg.textContent = success
                        ? "{{__('You have been resubscribed successfully!')}}"
                        : "{{__('This email is already subscribed or invalid.')}}";
                    requestAnimationFrame(function () { msg.classList.add('show'); });
                    if (success) form.reset();
                  })
                  .catch(function () {
                    msg.className = 'unsub-msg error';
                    msg.textContent = "{{__('Something went wrong. Please try again.')}}";
                    requestAnimationFrame(function () { msg.classList.add('show'); });
                  })
                  .finally(function () { btn.classList.remove('loading'); });
            });
        }

        // Interactive particle network background
        if (canvas && !reduceMotion) {
            var ctx = canvas.getContext('2d');
            var w, h, dpr = Math.min(window.devicePixelRatio || 1, 2);
            var particles = [];
            var mouse = { x: null, y: null };
            var COUNT = 95;
            var LINK_DIST = 165;

            function resize() {
                w = canvas.clientWidth = hero.offsetWidth;
                h = canvas.clientHeight = hero.offsetHeight;
                canvas.width = w * dpr;
                canvas.height = h * dpr;
                ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
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
                        if (mdist < 110) {
                            var force = (110 - mdist) / 110;
                            p.x += (mdx / mdist) * force * 1.4;
                            p.y += (mdy / mdist) * force * 1.4;
                        }
                    }

                    ctx.beginPath();
                    ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
                    ctx.fillStyle = 'rgba(23, 195, 162, 0.55)';
                    ctx.fill();

                    for (var j = i + 1; j < particles.length; j++) {
                        var q = particles[j];
                        var dx = p.x - q.x, dy = p.y - q.y;
                        var dist = Math.sqrt(dx * dx + dy * dy);
                        if (dist < LINK_DIST) {
                            ctx.beginPath();
                            ctx.moveTo(p.x, p.y);
                            ctx.lineTo(q.x, q.y);
                            ctx.strokeStyle = 'rgba(23, 195, 162, ' + (0.16 * (1 - dist / LINK_DIST)) + ')';
                            ctx.lineWidth = 1;
                            ctx.stroke();
                        }
                    }
                }
                requestAnimationFrame(step);
            }

            hero.addEventListener('mousemove', function (e) {
                var rect = hero.getBoundingClientRect();
                mouse.x = e.clientX - rect.left;
                mouse.y = e.clientY - rect.top;
            });
            hero.addEventListener('mouseleave', function () { mouse.x = mouse.y = null; });
            window.addEventListener('resize', function () { resize(); initParticles(); });

            resize();
            initParticles();
            requestAnimationFrame(step);
        }
    });
</script>
@endsection
