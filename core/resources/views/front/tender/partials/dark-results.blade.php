{{-- Dark-theme tender grid + pagination — shared by the normal page render
     and the AJAX filter response, so both stay byte-identical. --}}
@if ($tenders->count() == 0)
    <div class="dark-svcp-empty">
        <h3>{{ __('No Tender Found!') }}</h3>
    </div>
@else
    <div class="dark-tp-grid">
        @foreach ($tenders as $key => $tender)
            @php
                $tenderHasImage = !empty($tender->tender_image) && file_exists(base_path('../assets/front/img/tenders/' . $tender->tender_image));
            @endphp
            <a href="{{ route('tender_details', ['slug' => $tender->slug]) }}" class="dark-tender-card reveal-card" style="--d:{{ ($key % 3) * 0.1 }}s">
                <div class="dark-tender-thumb @if (!$tenderHasImage) no-image @endif"
                    @if ($tenderHasImage) style="background-image: url('{{ asset('assets/front/img/tenders/' . $tender->tender_image) }}');" @endif>
                    @if (!$tenderHasImage)
                        <svg viewBox="0 0 24 24"><path d="M7 3h8l4 4v14a1 1 0 01-1 1H7a1 1 0 01-1-1V4a1 1 0 011-1z" /><path d="M15 3v4h4" /><path d="M9 12h6M9 16h6M9 8h2" /></svg>
                    @endif
                    @if ($tender->tenderCategory)
                        <span class="dark-tender-cat">{{ convertUtf8($tender->tenderCategory->name) }}</span>
                    @endif
                    @if ($tender->submission_deadline)
                        <div class="dark-tender-countdown" data-deadline="{{ \Carbon\Carbon::parse($tender->submission_deadline)->toIso8601String() }}">
                            <span class="unit"><b data-d>00</b><span>{{ __('d') }}</span></span>
                            <span class="unit"><b data-h>00</b><span>{{ __('h') }}</span></span>
                        </div>
                    @endif
                </div>
                <div class="dark-tender-body">
                    <h3>{{ convertUtf8($tender->title) }}</h3>
                    <div class="dark-tender-meta">
                        <span><svg viewBox="0 0 24 24"><path d="M12 21s7-6.5 7-12a7 7 0 10-14 0c0 5.5 7 12 7 12z" /><circle cx="12" cy="9" r="2.5" /></svg>{{ $tender->country }}</span>
                        <span>{{ $tender->tender_code }}</span>
                    </div>
                    <div class="dark-tender-foot">
                        <div class="dark-tender-price">
                            @if (is_null($tender->current_price))
                                <span class="now">{{ __('Free') }}</span>
                            @else
                                @if (!empty($tender->previous_price))
                                    <span class="prev">{{ number_format($tender->previous_price, 0) }} {{ optional($bse)->base_currency_symbol }}</span>
                                @endif
                                <span class="now">
                                    {{ optional($bse)->base_currency_symbol_position == 'left' ? optional($bse)->base_currency_symbol . ' ' : '' }}{{ number_format($tender->current_price, 0) }}
                                    <small>{{ optional($bse)->base_currency_symbol_position == 'right' ? optional($bse)->base_currency_symbol : '' }}</small>
                                </span>
                            @endif
                        </div>
                        <span class="dark-tender-cta"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M13 6l6 6-6 6" /></svg></span>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
@endif

<nav class="dark-tp-pagination">
    {{ $tenders->appends([
        'search' => request()->input('search'),
        'category_id' => request()->input('category_id'),
        'country' => request()->input('country'),
        'checked_value' => request()->input('checked_value'),
        'minValue' => request()->input('minValue'),
        'maxValue' => request()->input('maxValue'),
        'filterValue' => request()->input('filterValue'),
    ])->links('vendor.pagination.dark-glass') }}
</nav>
