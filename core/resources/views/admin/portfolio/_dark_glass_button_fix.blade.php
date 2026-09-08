{{--
    dark-glass.css (the FRONT-END theme's own stylesheet, loaded on this
    admin page only for the .dark-pic-* preview/eye-modal content) has an
    "input/button[type=submit]" rule — a bare attribute selector, higher
    specificity than a single class like .btn-success, and !important — so
    it leaks onto this page's own real admin buttons, reskinning them with
    the front-end's gradient CTA look. Adding the .btn-success/.btn-primary/
    .btn-danger class to the selector raises specificity just enough to win
    back the admin theme's real (flat, non-gradient) button colors, without
    touching the front-end file — it's correct there, just not scoped to
    stay off admin pages.
--}}
<style>
    button[type="submit"].btn-success, input[type="submit"].btn-success {
        background: #31CE36 !important;
        border: 1px solid #31CE36 !important;
        box-shadow: none !important;
    }
    button[type="submit"].btn-primary, input[type="submit"].btn-primary {
        background: #1572E8 !important;
        border: 1px solid #1572E8 !important;
        box-shadow: none !important;
    }
    button[type="submit"].btn-danger, input[type="submit"].btn-danger {
        background: #F25961 !important;
        border: 1px solid #F25961 !important;
        box-shadow: none !important;
    }
</style>
