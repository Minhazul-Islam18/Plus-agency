@foreach ($scategories as $key => $scategory)
    <div class="dark-svc-card is-entering" style="animation-delay:{{ ($key % 6) * 0.08 }}s">
        <span class="dark-svc-card-num">{{ sprintf('%02d', $startIndex + $key + 1) }}</span>
        @if (!empty($scategory->image))
            <span class="dark-svc-card-icon">
                <img src="{{ asset('assets/front/img/service_category_icons/' . $scategory->image) }}" alt="">
            </span>
        @endif
        <h3>{{ convertUtf8($scategory->name) }}</h3>
        <p>{{ convertUtf8($scategory->short_text) }}</p>
        <a href="{{ route('front.services', ['category' => $scategory->id]) }}" class="dark-svc-card-link">{{ __('View Services') }} &#8594;</a>
    </div>
@endforeach
