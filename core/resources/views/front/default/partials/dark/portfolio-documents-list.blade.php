{{--
    Portfolio Documents list — extracted from portfolio-identity-card.blade.php
    so the real frontend detail page can place it independently (next to the
    Description, matching the mockup) instead of only inside the identity
    card's own stacked block order. Included by the identity-card partial
    itself (keeps admin preview/eye-modal unchanged) and directly by
    portfolio-details.blade.php.

    Expects $documentList — array of ['url' => .., 'name' => .., 'size' => null|int].
--}}
@if (!empty($documentList))
    <div class="dark-pic-documents">
        <div class="dark-pd-section-heading">
            <span class="dark-pd-section-icon"><i class="fas fa-paperclip"></i></span>
            {{ __('Documents') }}
        </div>
        <ul>
            @foreach ($documentList as $doc)
                <li>
                    <span class="dark-pic-doc-icon"><i class="fas fa-file-pdf"></i></span>
                    <span class="dark-pic-doc-info">
                        <span class="dark-pic-doc-name">{{ $doc['name'] }}</span>
                        @if (!empty($doc['size']))
                            <span class="dark-pic-doc-size">{{ number_format($doc['size'] / 1048576, 1) }} {{ __('MB') }}</span>
                        @endif
                    </span>
                    @if (!empty($doc['url']))
                        <a href="{{ $doc['url'] }}" download="{{ $doc['name'] }}" class="dark-pic-doc-download"><i class="fas fa-download"></i></a>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
@endif
