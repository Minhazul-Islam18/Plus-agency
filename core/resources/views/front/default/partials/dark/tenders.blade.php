<div class="tender-showcase-section dark-tenders-section dark-section-seam"
    style="@if (!empty($be->tender_section_bg)) background-image: url('{{ asset('assets/front/img/' . $be->tender_section_bg) }}');
        background-size: cover;
        background-position: center;
        position: relative;
        overflow: hidden; @endif">
    @if (!empty($be->tender_section_bg))
        <div class="portfolio-overlay"
            style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: #{{ $be->tender_overlay_color ?? '000000' }}; opacity: {{ $be->tender_overlay_opacity ?? '0.6' }}; z-index: 0; pointer-events: none;">
        </div>
    @endif
    <div class="container" style="position: relative; z-index: 2;">
        <div class="dark-tender-head reveal-stagger" style="--d:.05s">
            <div class="dark-tender-head-text">
                <span class="section-eyebrow">{{ convertUtf8($bs->tender_section_title) }}</span>
                <h2 class="gradient-shine-heading">{{ convertUtf8($bs->tender_section_text) }}</h2>
            </div>
            <div class="dark-tender-actions">
                @if (Route::has('tenders'))
                    <a href="{{ route('tenders') }}" class="dark-intro-cta">
                        <span>{{ __('Show all') }}</span><i>&#8594;</i>
                    </a>
                @endif
                @if ($tenders->count() > 1)
                    <div class="dark-tender-nav">
                        <button type="button" id="darkTenderPrev" aria-label="{{ __('Previous') }}"><svg viewBox="0 0 24 24"><path d="M15 6l-6 6 6 6"/></svg></button>
                        <button type="button" id="darkTenderNext" aria-label="{{ __('Next') }}"><svg viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></svg></button>
                    </div>
                @endif
            </div>
        </div>

        <div class="dark-tender-carousel dark-glass-carousel owl-carousel owl-theme reveal-stagger" style="--d:.15s">
            @foreach ($tenders as $key => $tender)
                <a href="{{ route('tender_details', [$tender->slug]) }}" class="dark-tender-card">
                    <div class="dark-tender-thumb @if (empty($tender->tender_image)) no-image @endif"
                        @if (!empty($tender->tender_image)) style="background-image: url('{{ asset('assets/front/img/tenders/' . $tender->tender_image) }}');" @endif>
                        @if (empty($tender->tender_image))
                            <svg viewBox="0 0 24 24"><path d="M7 3h8l4 4v14a1 1 0 01-1 1H7a1 1 0 01-1-1V4a1 1 0 011-1z"/><path d="M15 3v4h4"/><path d="M9 12h6M9 16h6M9 8h2"/></svg>
                        @endif
                        @if (!empty($tender->tenderCategory))
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
                            <span><svg viewBox="0 0 24 24"><path d="M12 21s7-6.5 7-12a7 7 0 10-14 0c0 5.5 7 12 7 12z"/><circle cx="12" cy="9" r="2.5"/></svg>{{ $tender->country }}</span>
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
                            <span class="dark-tender-cta"><svg viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</div>
