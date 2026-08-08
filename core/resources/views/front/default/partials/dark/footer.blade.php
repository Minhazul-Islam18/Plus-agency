<div class="dark-footer-top">
    <span class="dark-footer-mesh dark-footer-mesh--a"></span>
    <span class="dark-footer-mesh dark-footer-mesh--b"></span>

    <div class="dark-footer-grid reveal-stagger" style="--d:.05s">

        <div class="dark-footer-about">
            <div class="footer-logo-wrapper">
                <a href="{{ route('front.index') }}">
                    <img class="dark-footer-logo lazy" data-src="{{ asset('assets/front/img/' . $bs->footer_logo) }}" alt="">
                </a>
            </div>
            <p class="footer-txt">
                @if (mb_strlen($bs->footer_text, 'UTF-8') > 194)
                    <span class="footer-text-short">{{ mb_substr($bs->footer_text, 0, 194, 'UTF-8') }}…</span><span
                        class="footer-text-full" style="display:none;">{{ $bs->footer_text }}</span>
                    <span class="footer-text-toggle">{{ __('Show more') }}</span>
                @else
                    {{ $bs->footer_text }}
                @endif
            </p>
            @if (!empty($socials) && $socials->count())
                <div class="dark-footer-social">
                    @foreach ($socials as $key => $social)
                        <a href="{{ $social->url }}" target="_blank"><i class="{{ $social->icon }}"></i></a>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="dark-footer-links-col">
            <span class="footer-label">{{ __('Useful Links') }}</span>
            <ul class="footer-links">
                @foreach ($ulinks as $key => $ulink)
                    @php
                        $ulinkName = convertUtf8($ulink->name);
                        $ulinkShort = mb_strlen($ulinkName, 'UTF-8') > 36
                            ? mb_substr($ulinkName, 0, 36, 'UTF-8') . '…'
                            : null;
                    @endphp
                    @if ($ulinkShort)
                        <li>
                            <a href="{{ $ulink->url }}" style="display:inline;">
                                <span class="footer-link-short">{{ $ulinkShort }}</span><span class="footer-link-full" style="display:none;">{{ $ulinkName }}</span>
                            </a>
                            <span class="footer-link-toggle">{{ __('Show more') }}</span>
                        </li>
                    @else
                        <li>
                            <a href="{{ $ulink->url }}">{{ $ulinkName }}</a>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>

        <div class="dark-footer-newsletter">
            <span class="footer-label">{{ __('Newsletter') }}</span>
            <p>{{ convertUtf8($bs->newsletter_text) }}</p>
            <form class="dark-footer-pill-form" id="footerSubscribeForm" action="{{ route('front.subscribe') }}" method="post">
                @csrf
                <input type="email" name="email" value="" placeholder="{{ __('Enter Email Address') }}">
                <button type="submit" aria-label="{{ __('Subscribe') }}"><svg viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg></button>
            </form>
            <p id="erremail" class="text-danger mb-0 err-email"></p>
        </div>

        <div class="dark-footer-contact">
            <span class="footer-label">{{ __('Contact Us') }}</span>
            <ul class="dark-footer-contact-list">
                @php
                    $addresses = explode(PHP_EOL, $bex?->contact_addresses);
                    $phones = explode(',', $bex?->contact_numbers);
                    $mails = explode(',', $bex?->contact_mails);
                @endphp
                <li>
                    <span class="dark-footer-contact-icon"><i class="fa fa-home"></i></span>
                    <span>
                        @foreach ($addresses as $address)
                            {{ $address }}@if (!$loop->last) | @endif
                        @endforeach
                    </span>
                </li>
                <li>
                    <span class="dark-footer-contact-icon"><i class="fa fa-phone"></i></span>
                    <span>
                        @foreach ($phones as $phone)
                            {{ $phone }}@if (!$loop->last), @endif
                        @endforeach
                    </span>
                </li>
                <li>
                    <span class="dark-footer-contact-icon"><i class="far fa-envelope"></i></span>
                    <span>
                        @foreach ($mails as $mail)
                            <a href="mailto:{{ trim($mail) }}">{{ $mail }}</a>@if (!$loop->last), @endif
                        @endforeach
                    </span>
                </li>
            </ul>
        </div>

    </div>
</div>
