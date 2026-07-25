{{--
    Reusable dial-code + number picker. Same searchable-dropdown component
    (.ss / .ss-dial / .phone-group) used on the tender checkout page
    (resources/views/front/tender/tender_details.blade.php), ported as-is
    since its CSS is already light-themed and fits the admin panel's
    Bootstrap4 forms without changes.

    Expects:
      $codeInputId    - id for the hidden dial-code input, e.g. "memberWhatsappCode"
      $numberInputId  - id for the visible number input
      $numberInputName - name attribute for the number input
      $countries      - \App\Http\Helpers\Countries::forCheckout()
      $preCode        - prefill dial code, e.g. "+226" (optional)
      $preNumber      - prefill national number digits (optional)
      $preFlag        - prefill flag emoji matching $preCode (optional)
--}}
@php
    $preCode  = $preCode ?? '';
    $preNumber = $preNumber ?? '';
    $preFlag  = $preFlag ?? '';
@endphp

<div class="phone-group">
    <div class="ss ss-dial" data-target="#{{ $codeInputId }}">
        <button type="button" class="ss-toggle">
            <span class="ss-flag">{{ $preFlag }}</span>
            <span class="ss-label {{ $preCode ? '' : 'ss-placeholder' }}">{{ $preCode ?: __('Code') }}</span>
            <i class="ss-caret"></i>
        </button>
        <div class="ss-panel">
            <input type="text" class="ss-search" placeholder="{{ __('Search…') }}" autocomplete="off">
            <ul class="ss-list">
                @foreach ($countries as $c)
                    <li class="ss-opt {{ $preCode === $c['dial'] ? 'selected' : '' }}" data-value="{{ $c['dial'] }}"
                        data-label="{{ $c['dial'] }}" data-flag="{{ $c['flag'] }}"
                        data-search="{{ $c['name'] }} {{ $c['dial'] }}">
                        <span class="ss-flag">{{ $c['flag'] }}</span>
                        <span class="ss-cname">{{ $c['name'] }}</span>
                        <span class="ss-dialcode">{{ $c['dial'] }}</span>
                    </li>
                @endforeach
            </ul>
            <div class="ss-empty">{{ __('No match') }}</div>
        </div>
    </div>
    <input type="text" name="{{ $numberInputName }}" id="{{ $numberInputId }}" class="phone-input"
        inputmode="numeric" maxlength="15" placeholder="{{ __('Phone Number') }}" value="{{ $preNumber }}">
</div>
<input type="hidden" name="{{ $numberInputName }}_code" id="{{ $codeInputId }}" value="{{ $preCode }}">

<style>
    .ss {
        position: relative;
    }

    .ss-toggle {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        width: 100%;
        background: #fff;
        cursor: pointer;
        border: 1px solid #ced4da;
        border-radius: .25rem;
        padding: .375rem .75rem;
        min-height: 45px;
        font-size: 1rem;
        color: #495057;
        text-align: left;
    }

    .ss-toggle:focus {
        outline: none;
        border-color: #86b7fe;
    }

    .ss-toggle .ss-label {
        flex: 1 1 auto;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ss-label.ss-placeholder {
        color: #8a94a0;
    }

    .ss-caret {
        flex: 0 0 auto;
        width: 0;
        height: 0;
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-top: 5px solid #6c757d;
    }

    .ss-panel {
        display: none;
        position: absolute;
        z-index: 60;
        top: calc(100% + 4px);
        left: 0;
        right: auto;
        min-width: 260px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        box-shadow: 0 10px 28px rgba(0, 0, 0, .12);
        overflow: hidden;
    }

    .ss.open .ss-panel {
        display: block;
    }

    .ss-search {
        width: 100%;
        border: 0;
        border-bottom: 1px solid #edf2f7;
        padding: 10px 12px;
        font-size: 14px;
        outline: none;
    }

    .ss-list {
        max-height: 240px;
        overflow-y: auto;
        margin: 0;
        padding: 4px 0;
        list-style: none;
    }

    .ss-opt {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 8px 12px;
        font-size: 14px;
        cursor: pointer;
    }

    .ss-opt:hover,
    .ss-opt.active {
        background: #f1f5f9;
    }

    .ss-opt.selected {
        background: #e2e8f0;
        font-weight: 600;
    }

    .ss-flag {
        flex: 0 0 auto;
        font-size: 18px;
        line-height: 1;
        font-family: "Apple Color Emoji", "Segoe UI Emoji", "Noto Color Emoji", "Twemoji Mozilla", sans-serif;
    }

    .ss-opt .ss-cname {
        flex: 1 1 auto;
    }

    .ss-cname {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ss-dialcode {
        color: #64748b;
        font-size: 13px;
        flex: 0 0 auto;
    }

    .ss-empty {
        display: none;
        padding: 10px 12px;
        color: #94a3b8;
        font-size: 14px;
        list-style: none;
    }

    .ss.no-match .ss-empty {
        display: block;
    }

    .phone-group {
        display: flex;
        align-items: stretch;
        border: 1px solid #ced4da;
        border-radius: .25rem;
        background: #fff;
        overflow: visible;
    }

    .phone-group:focus-within {
        border-color: #86b7fe;
    }

    .phone-group .ss-dial {
        flex: 0 0 auto;
    }

    .phone-group .ss-dial .ss-toggle {
        border: 0;
        border-right: 1px solid #e2e8f0;
        border-radius: .25rem 0 0 .25rem;
        min-width: 92px;
        background: #f8fafc;
    }

    .phone-group .phone-input {
        flex: 1 1 auto;
        min-width: 0;
        border: 0;
        outline: none;
        padding: .375rem .75rem;
        font-size: 1rem;
        color: #495057;
        border-radius: 0 .25rem .25rem 0;
        background: transparent;
    }

    .phone-group .ss-panel {
        left: 0;
        right: auto;
    }
</style>

@once
    <script>
        {{-- admin.layout loads jQuery near the end of <body>, after @yield('content')
             (this partial's position) — deferring to DOMContentLoaded guarantees $
             exists by the time this runs, since jQuery's <script src> is synchronous
             and blocks parsing before DOMContentLoaded fires. --}}
        document.addEventListener('DOMContentLoaded', function() {
        (function() {
            function ssPaintToggle($ss, flag, label) {
                var $toggle = $ss.children('.ss-toggle');
                $toggle.children('.ss-flag').text(flag || '');
                $toggle.children('.ss-label').text(label).removeClass('ss-placeholder');
            }

            function ssSelect($ss, $opt) {
                $($ss.data('target')).val($opt.data('value'));
                ssPaintToggle($ss, $opt.data('flag'), $opt.data('label'));
                $ss.find('.ss-opt').removeClass('selected');
                $opt.addClass('selected');
            }

            function ssClose($ss) {
                $ss.removeClass('open no-match');
                $ss.find('.ss-search').val('');
                $ss.find('.ss-opt').show();
            }

            $(document).on('click', '.ss-toggle', function(e) {
                e.preventDefault();
                var $ss = $(this).closest('.ss');
                var wasOpen = $ss.hasClass('open');
                $('.ss').each(function() {
                    ssClose($(this));
                });
                if (!wasOpen) {
                    $ss.addClass('open');
                    $ss.find('.ss-search').focus();
                }
            });

            $(document).on('input', '.ss-search', function() {
                var $ss = $(this).closest('.ss');
                var q = ($(this).val() || '').toLowerCase().trim();
                var hits = 0;
                $ss.find('.ss-opt').each(function() {
                    var match = !q || String($(this).data('search')).toLowerCase().indexOf(q) > -1;
                    $(this).toggle(match);
                    if (match) hits++;
                });
                $ss.toggleClass('no-match', hits === 0);
            });

            $(document).on('click', '.ss-opt', function() {
                var $ss = $(this).closest('.ss');
                ssSelect($ss, $(this));
                ssClose($ss);
            });

            $(document).on('click', function(e) {
                if (!$(e.target).closest('.ss').length) {
                    $('.ss').each(function() {
                        ssClose($(this));
                    });
                }
            });

            $(document).on('keydown', '.ss', function(e) {
                if (e.key === 'Escape') {
                    ssClose($(this));
                }
            });

            $(document).on('input', '.phone-input', function() {
                var el = this;
                var start = el.selectionStart;
                var before = el.value || '';
                var cleaned = before.replace(/\D/g, '');
                if (cleaned !== before) {
                    var removed = before.length - cleaned.length;
                    el.value = cleaned;
                    var pos = Math.max(0, (start || 0) - removed);
                    el.setSelectionRange(pos, pos);
                }
            });
        })();
        });
    </script>
@endonce
