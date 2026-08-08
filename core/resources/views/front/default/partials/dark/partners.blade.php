<div class="partner-section dark-partner-section responsive-section-padding"
    @if (!empty($be->partner_bg)) style="background-image: url('{{ asset('assets/front/img/' . $be->partner_bg) }}'); background-size:cover; background-position: center; position: relative; overflow: hidden;" @endif>
    @if (!empty($be->partner_bg))
        <div class="partner-overlay"
            style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: #{{ $be->partner_overlay_color ?? '000000' }}; opacity: {{ $be->partner_overlay_opacity ?? '0.6' }}; z-index: 0; pointer-events: none;">
        </div>
    @endif
    <div class="container" style="position: relative; z-index: 2;">
        <div class="row">
            <div class="col-md-12">
                <div class="dark-partner-carousel dark-glass-carousel owl-carousel owl-theme">
                    @foreach ($partners as $key => $partner)
                        <a class="glass-panel dark-partner-item d-block" href="{{ $partner->url }}" target="_blank">
                            <img src="{{ asset('assets/front/img/partners/' . $partner->image) }}" alt="">
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
