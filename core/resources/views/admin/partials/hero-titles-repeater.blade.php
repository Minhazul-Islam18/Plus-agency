@php
    // Shared by the Static and Video dark-hero admin pages — one repeater
    // row per flip-cycling title line, replacing the old separate
    // "Title" input + "Rotating Titles" textarea (two differently-shaped
    // fields for what is really one list).
    //
    // Expected params:
    //   $fieldName  (string)  POST field name, e.g. "titles"
    //   $titles     (array)   existing rows, each ['text' => ..., 'accent' => ...]
    //   $withAccent (bool)    render the per-row accent-phrase input (video hero only)
    $titlesRepeaterRows = !empty($titles) ? array_values($titles) : [['text' => '', 'accent' => '']];
@endphp

<style>
    .hero-titles-repeater-row {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 10px;
    }
    .hero-titles-repeater-row-main {
        flex: 1;
        display: flex;
        gap: 10px;
    }
    .hero-titles-repeater-row-main input {
        flex: 1;
        min-width: 0;
    }
    .hero-titles-repeater-remove {
        flex-shrink: 0;
        border: 1px solid #ffffff2a;
        background: transparent;
        color: #ff8a8a;
        border-radius: 6px;
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }
    .hero-titles-repeater-remove:hover {
        background: rgba(255, 90, 90, 0.12);
    }
    .hero-titles-repeater-remove:disabled {
        opacity: 0.35;
        cursor: not-allowed;
    }
</style>

<div class="hero-titles-repeater" data-titles-repeater data-with-accent="{{ $withAccent ? '1' : '0' }}" data-field-name="{{ $fieldName }}">
    <div data-repeater-list>
        @foreach ($titlesRepeaterRows as $i => $row)
            <div class="hero-titles-repeater-row" data-repeater-row>
                <div class="hero-titles-repeater-row-main">
                    <input type="text" class="form-control" name="{{ $fieldName }}[{{ $i }}][text]"
                        value="{{ $row['text'] ?? '' }}"
                        placeholder="{{ $i == 0 ? 'Main title' : 'Rotating title' }}">
                    @if ($withAccent)
                        <input type="text" class="form-control" name="{{ $fieldName }}[{{ $i }}][accent]"
                            value="{{ $row['accent'] ?? '' }}"
                            placeholder="Accent phrase (optional)">
                    @endif
                </div>
                <button type="button" class="hero-titles-repeater-remove" data-repeater-remove title="Remove this title">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        @endforeach
    </div>

    <button type="button" class="btn btn-sm btn-primary" data-repeater-add>+ Add title</button>

    <p class="text-muted mb-0 mt-2" style="font-size: 12.5px;">
        First one shows on load — add more and they cycle in automatically with a soft fade, a few seconds apart.
        @if ($withAccent)
            Accent phrase must match a piece of its own title exactly (word-for-word) to highlight it in color — leave it empty for a plain white title.
        @endif
    </p>
</div>

<script>
    (function () {
        function initTitlesRepeater(root) {
            if (root.dataset.repeaterInit) return;
            root.dataset.repeaterInit = '1';

            var list = root.querySelector('[data-repeater-list]');
            var addBtn = root.querySelector('[data-repeater-add]');
            var withAccent = root.getAttribute('data-with-accent') === '1';
            var fieldName = root.getAttribute('data-field-name') || 'titles';
            // Monotonic — never reuses an index, even after a row is
            // removed, so two rows can never collide on submit.
            var nextIndex = list.querySelectorAll('[data-repeater-row]').length;

            function updateRemoveButtons() {
                var rows = list.querySelectorAll('[data-repeater-row]');
                var onlyOne = rows.length <= 1;
                rows.forEach(function (row) {
                    var btn = row.querySelector('[data-repeater-remove]');
                    if (btn) btn.disabled = onlyOne;
                });
            }

            function addRow() {
                var row = document.createElement('div');
                row.className = 'hero-titles-repeater-row';
                row.setAttribute('data-repeater-row', '');

                var mainHtml = '<input type="text" class="form-control" name="' + fieldName + '[' + nextIndex + '][text]" placeholder="Rotating title">';
                if (withAccent) {
                    mainHtml += '<input type="text" class="form-control" name="' + fieldName + '[' + nextIndex + '][accent]" placeholder="Accent phrase (optional)">';
                }
                row.innerHTML =
                    '<div class="hero-titles-repeater-row-main">' + mainHtml + '</div>' +
                    '<button type="button" class="hero-titles-repeater-remove" data-repeater-remove title="Remove this title"><i class="fas fa-trash"></i></button>';

                list.appendChild(row);
                nextIndex++;
                updateRemoveButtons();
            }

            addBtn.addEventListener('click', addRow);

            list.addEventListener('click', function (e) {
                var btn = e.target.closest('[data-repeater-remove]');
                if (!btn || btn.disabled) return;
                btn.closest('[data-repeater-row]').remove();
                updateRemoveButtons();
            });

            updateRemoveButtons();
        }

        document.querySelectorAll('[data-titles-repeater]').forEach(initTitlesRepeater);
    })();
</script>
