(function () {
    "use strict";

    function extractYouTubeId(url) {
        var m = url.match(/(?:youtube(?:-nocookie)?\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([\w-]{11})/);
        return m ? m[1] : null;
    }

    document.addEventListener('DOMContentLoaded', function () {
        var playBtn = document.getElementById('darkIntroPlayBtn');
        var lightbox = document.getElementById('darkIntroVideoLightbox');
        if (!playBtn || !lightbox) return;

        var frameHolder = document.getElementById('darkIntroVideoLightboxFrame');
        var closeBtn = document.getElementById('darkIntroVideoLightboxClose');

        function openLightbox() {
            var id = extractYouTubeId(playBtn.getAttribute('data-youtube-url') || '');
            if (!id) return;
            var iframe = document.createElement('iframe');
            iframe.src = 'https://www.youtube.com/embed/' + id + '?autoplay=1&rel=0';
            iframe.setAttribute('allow', 'autoplay; encrypted-media; picture-in-picture');
            iframe.setAttribute('allowfullscreen', '');
            frameHolder.innerHTML = '';
            frameHolder.appendChild(iframe);
            lightbox.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            lightbox.classList.remove('is-open');
            document.body.style.overflow = '';
            setTimeout(function () { frameHolder.innerHTML = ''; }, 300);
        }

        playBtn.addEventListener('click', openLightbox);
        playBtn.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openLightbox(); }
        });
        closeBtn.addEventListener('click', closeLightbox);
        lightbox.addEventListener('click', function (e) {
            if (e.target === lightbox) closeLightbox();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && lightbox.classList.contains('is-open')) closeLightbox();
        });
    });
})();
