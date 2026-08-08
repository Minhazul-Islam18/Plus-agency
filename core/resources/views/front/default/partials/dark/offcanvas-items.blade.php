{{-- Recursive accordion items for the dark-theme mobile offcanvas menu.
     Mirrors create_menu()'s recursion so any nesting depth the admin
     configures keeps working, not just 2 levels. --}}
@foreach ($items as $item)
    @php $itemHref = getHref($item); @endphp
    @if (array_key_exists('children', $item) && count($item['children']) > 0)
        <div class="dark-oc-item">
            <div class="dark-oc-link">
                <span>{{ $item['text'] }}</span>
                <span class="dark-oc-caret"><svg viewBox="0 0 24 24"><path d="M6 9l6 6 6-6" /></svg></span>
            </div>
            <div class="dark-oc-sub-wrap">
                <div class="dark-oc-sub-inner">
                    <div class="dark-oc-sub">
                        @include('front.default.partials.dark.offcanvas-items', ['items' => $item['children']])
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="dark-oc-item">
            <a href="{{ $itemHref }}" target="{{ $item['target'] }}" class="dark-oc-link">{{ $item['text'] }}</a>
        </div>
    @endif
@endforeach
