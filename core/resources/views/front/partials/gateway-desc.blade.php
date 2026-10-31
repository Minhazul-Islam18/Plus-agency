@php
    $gwDesc = (string) ($desc ?? '');
    $gwLimit = 150;
@endphp
<p class="gateway-desc">
    @if (mb_strlen($gwDesc, 'UTF-8') > $gwLimit)
        <span class="gateway-desc-short">{!! nl2br(e(rtrim(mb_substr($gwDesc, 0, $gwLimit, 'UTF-8')))) !!}…</span><span
            class="gateway-desc-full" style="display:none;">{!! nl2br(e($gwDesc)) !!}</span>
        <span class="gateway-desc-toggle" role="button" tabindex="0" style="white-space:nowrap; font-size:11px; text-decoration:underline; cursor:pointer;">{{ __('Show more') }}</span>
    @else
        {!! nl2br(e($gwDesc)) !!}
    @endif
</p>
