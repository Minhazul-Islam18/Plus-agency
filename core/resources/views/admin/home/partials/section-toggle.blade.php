{{--
    Single section card: on/off toggle + (optional) carousel autoplay speed.
    Expects: $name (DB field on BasicSetting), $icon (fa- class suffix),
    $label, $sub, $speedField (nullable, field on BasicExtended), $abs, $abe.
--}}
<div class="sec-card">
    <div class="sec-card-head">
        <div class="sec-icon"><i class="fas {{ $icon }}"></i></div>
        <div>
            <p class="sec-card-title">{{ $label }}</p>
            <p class="sec-card-sub">{{ $sub }}</p>
        </div>
    </div>
    <div class="sec-card-controls">
        <div class="selectgroup">
            <label class="selectgroup-item">
                <input type="radio" name="{{ $name }}" value="1" class="selectgroup-input" {{ $abs->{$name} == 1 ? 'checked' : '' }}>
                <span class="selectgroup-button">Active</span>
            </label>
            <label class="selectgroup-item">
                <input type="radio" name="{{ $name }}" value="0" class="selectgroup-input" {{ $abs->{$name} == 0 ? 'checked' : '' }}>
                <span class="selectgroup-button">Deactive</span>
            </label>
        </div>

        @if ($speedField)
            <div class="sec-speed">
                <i class="fas fa-tachometer-alt"></i>
                <input type="number" class="form-control form-control-sm ltr" name="{{ $speedField }}"
                    value="{{ $abe->{$speedField} ?? 4500 }}" min="2000" max="15000" step="500">
                <span>ms</span>
            </div>
        @endif
    </div>
</div>
