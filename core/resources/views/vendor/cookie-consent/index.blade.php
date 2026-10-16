@if($cookieConsentConfig['enabled'] && ! $alreadyConsentedWithCookies)

    @include('cookie-consent::dialogContents')

    <script>

        window.laravelCookieConsent = (function () {

            const COOKIE_VALUE = 1;
            const COOKIE_VALUE_DENIED = 0;
            const COOKIE_DOMAIN = '{{ config('session.domain') ?? request()->getHost() }}';

            function consentWithCookies() {
                setCookie('{{ $cookieConsentConfig['cookie_name'] }}', COOKIE_VALUE, {{ $cookieConsentConfig['cookie_lifetime'] }});
                hideCookieDialog();
                // Starts GA4/GTM/Pixel this same page load (see
                // layout.blade.php's __activateTracking) rather than
                // waiting for the visitor's next navigation.
                if (window.__activateTracking) {
                    window.__activateTracking();
                }
            }

            // Persists an explicit decline (value 0, same cookie — the
            // server-side Cookie::has() check that decides whether to show
            // this dialog again only checks presence, not value, so this
            // also stops it reappearing on the next visit). Tracking scripts
            // gated on this cookie (see layout.blade.php) check for the
            // value 1 specifically, so a decline never activates them.
            function denyCookies() {
                setCookie('{{ $cookieConsentConfig['cookie_name'] }}', COOKIE_VALUE_DENIED, {{ $cookieConsentConfig['cookie_lifetime'] }});
                hideCookieDialog();
            }

            function cookieExists(name) {
                return (document.cookie.split('; ').indexOf(name + '=' + COOKIE_VALUE) !== -1);
            }

            function hideCookieDialog() {
                const dialogs = document.getElementsByClassName('js-cookie-consent');

                for (let i = 0; i < dialogs.length; ++i) {
                    dialogs[i].style.display = 'none';
                }
            }

            function setCookie(name, value, expirationInDays) {
                const date = new Date();
                date.setTime(date.getTime() + (expirationInDays * 24 * 60 * 60 * 1000));
                document.cookie = name + '=' + value
                    + ';expires=' + date.toUTCString()
                    + ';domain=' + COOKIE_DOMAIN
                    + ';path=/{{ config('session.secure') ? ';secure' : null }}'
                    + '{{ config('session.same_site') ? ';samesite='.config('session.same_site') : null }}';
            }

            if (cookieExists('{{ $cookieConsentConfig['cookie_name'] }}')) {
                hideCookieDialog();
            }

            const buttons = document.getElementsByClassName('js-cookie-consent-agree');

            for (let i = 0; i < buttons.length; ++i) {
                buttons[i].addEventListener('click', consentWithCookies);
            }

            const denyButtons = document.getElementsByClassName('js-cookie-consent-deny');

            for (let i = 0; i < denyButtons.length; ++i) {
                denyButtons[i].addEventListener('click', denyCookies);
            }

            return {
                consentWithCookies: consentWithCookies,
                denyCookies: denyCookies,
                hideCookieDialog: hideCookieDialog
            };
        })();
    </script>

@endif
