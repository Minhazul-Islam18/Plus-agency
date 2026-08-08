@if (($be->theme_version ?? null) == 'dark')
    <div class="js-cookie-consent dark-cookie-toast">
        <div class="dark-cookie-head">
            <span class="dark-cookie-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                    <path d="M21 12.5A8.5 8.5 0 1112.5 3a2 2 0 002.5 2.5 2 2 0 002.5 2.5 2 2 0 002.5 2.5 2 2 0 001 2z" />
                    <circle cx="9" cy="10" r="0.8" fill="currentColor" stroke="none" />
                    <circle cx="13" cy="14" r="0.8" fill="currentColor" stroke="none" />
                    <circle cx="9.5" cy="15.5" r="0.6" fill="currentColor" stroke="none" />
                </svg>
            </span>
            <div class="dark-cookie-headtext">
                <span class="dark-cookie-eyebrow">{{ __('Privacy') }}</span>
                <span class="dark-cookie-title">{{ __('Cookies') }}</span>
            </div>
        </div>
        <p class="dark-cookie-consent__message">
            {!! trans('cookie-consent::texts.message') !!}
        </p>
        <div class="dark-cookie-actions">
            <button class="js-cookie-consent-deny dark-cookie-consent__deny">
                {{ __('Deny') }}
            </button>
            <button class="js-cookie-consent-agree dark-cookie-consent__agree">
                {{ trans('cookie-consent::texts.agree') }}
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6L9 17l-5-5" /></svg>
            </button>
        </div>
    </div>
@else
    <div class="js-cookie-consent cookie-consent fixed bottom-0 inset-x-0 pb-2 z-50">
        <div class="max-w-7xl mx-auto px-6">
            <div class="p-4 md:p-2 rounded-lg bg-yellow-100">
                <div class="flex items-center justify-between flex-wrap">
                    <div class="max-w-full flex-1 items-center md:w-0 md:inline">
                        <p class="md:ml-3 text-black cookie-consent__message">
                            {!! trans('cookie-consent::texts.message') !!}
                        </p>
                    </div>
                    <div class="mt-2 flex-shrink-0 w-full sm:mt-0 sm:w-auto">
                        <button class="js-cookie-consent-agree cookie-consent__agree cursor-pointer flex items-center justify-center px-4 py-2 rounded-md text-sm font-medium text-yellow-800 bg-yellow-400 hover:bg-yellow-300">
                            {{ trans('cookie-consent::texts.agree') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
