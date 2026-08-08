@php
    $links = json_decode($menus, true);
@endphp
<div class="header-navbar">
    <div class="row">
        <div class="col-lg-2 col-6">
            <div class="logo-wrapper">
                <a href="{{ route('front.index') }}"><img class="lazy"
                        data-src="{{ asset('assets/front/img/' . $bs->logo) }}" alt=""></a>
            </div>
        </div>
        <div class="col-lg-10 col-6 {{ $rtl == 1 ? 'text-left' : 'text-right' }} position-static">
            <ul class="main-menu" id="mainMenu">
                @foreach ($links as $link)
                    @php
                        $href = getHref($link);
                    @endphp


                    @if (strpos($link['type'], '-megamenu') !== false)
                        @includeIf('front.default.partials.mega-menu')

                        {{-- if the link is not services OR theme version doesn't have service category --}}
                    @else
                        @if (!array_key_exists('children', $link))
                            {{-- - Level1 links which doesn't have dropdown menus - --}}
                            <li><a href="{{ $href }}" target="{{ $link['target'] }}">{{ $link['text'] }}</a>
                            </li>
                        @else
                            <li class="dropdown">
                                {{-- - Level1 links which has dropdown menus - --}}
                                <a class="dropdown-btn" href="{{ $href }}"
                                    target="{{ $link['target'] }}">{{ $link['text'] }}</a>

                                <ul class="dropdown-lists">
                                    {{-- START: 2nd level links --}}
                                    @foreach ($link['children'] as $level2)
                                        @php
                                            $l2Href = getHref($level2);
                                        @endphp

                                        <li @if (array_key_exists('children', $level2)) class="submenus" @endif>
                                            <a href="{{ $l2Href }}"
                                                target="{{ $level2['target'] }}">{{ $level2['text'] }}</a>

                                            {{-- START: 3rd Level links --}}
                                            @php
                                                if (array_key_exists('children', $level2)) {
                                                    create_menu($level2);
                                                }
                                            @endphp
                                            {{-- END: 3rd Level links --}}

                                        </li>
                                    @endforeach
                                    {{-- END: 2nd level links --}}
                                </ul>

                            </li>
                        @endif
                    @endif

                @endforeach

                @if ($bs->is_quote == 1)
                    <li><a href="{{ route('front.contact') }}" class="boxed-btn">{{ __('Request A Quote') }}</a></li>
                @endif
            </ul>
            <div id="mobileMenu"></div>
        </div>
    </div>

    @if ($be->theme_version == 'dark')
        <button type="button" class="dark-oc-toggle" id="darkOcToggle" aria-label="{{ __('Menu') }}"
            aria-expanded="false" aria-controls="darkOcPanel">
            <span></span><span></span><span></span>
        </button>

        @push('dark-offcanvas')
            {{-- Rendered at the very end of <body> (see @stack in layout.blade.php), not
                 here inside the header — the header ancestor has backdrop-filter, which
                 creates a new containing block for position:fixed descendants and would
                 otherwise shrink this panel to the header's own height instead of the
                 viewport. --}}
            <div class="dark-oc-backdrop" id="darkOcBackdrop"></div>
            <nav class="dark-oc-panel" id="darkOcPanel">
                <div class="dark-oc-head">
                    <span class="dark-oc-head-logo">{{ __('Menu') }}</span>
                    <button type="button" class="dark-oc-close" id="darkOcClose" aria-label="{{ __('Close') }}">
                        <svg viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18" /></svg>
                    </button>
                </div>

                <div class="dark-oc-nav">
                    @include('front.default.partials.dark.offcanvas-items', ['items' => $links])

                    @if ($bs->is_quote == 1)
                        <a href="{{ route('front.contact') }}" class="dark-oc-cta">{{ __('Request A Quote') }}</a>
                    @endif
                </div>

                <div class="dark-oc-foot">
                    @if (!empty($bs->support_email) || !empty($bs->support_phone))
                        <div class="dark-oc-foot-row">
                            @if (!empty($bs->support_email))
                                <a href="mailto:{{ $bs->support_email }}">
                                    <svg viewBox="0 0 24 24"><path d="M4 4h16v16H4z" /><path d="M4 6l8 7 8-7" /></svg>
                                    {{ $bs->support_email }}
                                </a>
                            @endif
                            @if (!empty($bs->support_phone))
                                <a href="tel:{{ preg_replace('/\s+/', '', $bs->support_phone) }}">
                                    <svg viewBox="0 0 24 24">
                                        <path
                                            d="M22 16.9v3a2 2 0 01-2.2 2 19.8 19.8 0 01-8.6-3.1 19.5 19.5 0 01-6-6A19.8 19.8 0 012.1 4.2 2 2 0 014.1 2h3a2 2 0 012 1.7c.1.9.3 1.8.6 2.7a2 2 0 01-.4 2.1L8.1 9.6a16 16 0 006.3 6.3l1.1-1.1a2 2 0 012.1-.4c.9.3 1.8.5 2.7.6a2 2 0 011.7 2z" />
                                    </svg>
                                    {{ $bs->support_phone }}
                                </a>
                            @endif
                        </div>
                    @endif

                    @if (count($socials) > 0)
                        <div class="dark-oc-foot-socials">
                            @foreach ($socials as $social)
                                <a target="_blank" href="{{ $social->url }}"><i class="{{ $social->icon }}"></i></a>
                            @endforeach
                        </div>
                    @endif

                    @if (!empty($currentLang) && count($langs) > 1)
                        <div class="language dark-oc-foot-lang">
                            <a class="language-btn" href="#"><i class="flaticon-worldwide"></i>
                                {{ convertUtf8($currentLang->name) }}</a>
                            <ul class="language-dropdown">
                                @foreach ($langs as $lang)
                                    <li><a href="{{ route('changeLanguage', $lang->code) }}">{{ convertUtf8($lang->name) }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </nav>
        @endpush
    @endif
</div>
