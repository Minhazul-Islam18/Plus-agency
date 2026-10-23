{{--
    Reusable "optimize this image" switch + notice. Two call shapes:

    1) Dropped next to an LFM picker inside an admin form whose controller
       calls copy_resized_image() on save — the hidden input posts with that
       form natively.
         @include('admin.partials._image-optimize-toggle', [
             'field' => 'image', 'maxWidth' => 400, 'maxHeight' => 150,
         ])

    2) Used standalone (e.g. the LFM upload modal), where JS reads the
       hidden input's value directly (getElementById) instead of relying on
       a native form submit — same markup, same contract either way.

    Params:
    - $field      request field name ($request->boolean('optimize_' . $field))
    - $label      row label (default: "Resize & compress")
    - $maxWidth / $maxHeight  bounds actually passed to copy_resized_image()
                  by the field's controller — shown so the notice can never
                  drift out of sync with what the code does. Omit both and
                  pass $notice instead for a custom message (e.g. the format
                  conversion toggle, which has nothing to do with pixels).
    - $notice     raw notice text, overrides the auto-built one
    - $icon       Font Awesome class (default: fa-compress-arrows-alt)
    - $checked    initial state (default: true)
--}}
@php
    $label = $label ?? 'Resize & compress';
    $icon = $icon ?? 'fa-compress-arrows-alt';
    $checked = $checked ?? true;
    $notice = $notice ?? (isset($maxWidth, $maxHeight)
        ? "Shrinks it to fit {$maxWidth}&times;{$maxHeight}px if it's bigger (aspect ratio kept). Smaller images are left as-is."
        : null);
    $toggleId = 'optimize_' . $field;
@endphp
<div class="opt-toggle-row">
    <span class="opt-toggle-icon"><i class="fas {{ $icon }}"></i></span>
    <div class="opt-toggle-body">
        <div class="opt-toggle-head">
            <span class="opt-toggle-label">{{ $label }}</span>
            <label class="opt-switch">
                <input type="checkbox" id="{{ $toggleId }}_checkbox" {{ $checked ? 'checked' : '' }}
                    onchange="document.getElementById('{{ $toggleId }}').value = this.checked ? '1' : '0';">
                <span class="opt-switch-track"><span class="opt-switch-thumb"></span></span>
            </label>
            <input type="hidden" name="{{ $toggleId }}" id="{{ $toggleId }}" value="{{ $checked ? '1' : '0' }}">
        </div>
        @if ($notice)
            <p class="opt-toggle-notice">{!! $notice !!}</p>
        @endif
    </div>
</div>

@once
    <style>
        .opt-toggle-row {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            background: #f7f8fa;
            border: 1px solid #eaeaea;
            border-radius: 8px;
            padding: 10px 12px;
            margin-top: 8px;
        }
        .opt-toggle-icon {
            flex: 0 0 auto;
            width: 28px;
            height: 28px;
            border-radius: 6px;
            background: rgba(21, 114, 232, 0.1);
            color: #1572E8;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
        }
        .opt-toggle-body {
            flex: 1 1 auto;
            min-width: 0;
        }
        .opt-toggle-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }
        .opt-toggle-label {
            font-size: 13.5px;
            font-weight: 600;
            color: #1a2035;
        }
        .opt-toggle-notice {
            margin: 4px 0 0;
            font-size: 12px;
            line-height: 1.4;
            color: #767a81;
        }

        /* Switch control — no library, just enough CSS for a real on/off
           control instead of a bare checkbox. One state transition on
           toggle, nothing looping. */
        .opt-switch {
            position: relative;
            display: inline-block;
            width: 36px;
            height: 20px;
            flex: 0 0 auto;
            margin: 0;
            cursor: pointer;
        }
        .opt-switch input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            margin: 0;
            cursor: pointer;
        }
        .opt-switch-track {
            position: absolute;
            inset: 0;
            background: #dcdbdb;
            border-radius: 20px;
            transition: background-color .15s ease;
        }
        .opt-switch-thumb {
            position: absolute;
            top: 2px;
            left: 2px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.25);
            transition: transform .15s ease;
        }
        .opt-switch input:checked ~ .opt-switch-track {
            background: #1572E8;
        }
        .opt-switch input:checked ~ .opt-switch-track .opt-switch-thumb {
            transform: translateX(16px);
        }
        .opt-switch input:focus-visible ~ .opt-switch-track {
            box-shadow: 0 0 0 2px rgba(21, 114, 232, 0.35);
        }
    </style>
@endonce
