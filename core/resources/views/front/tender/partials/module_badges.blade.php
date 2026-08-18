{{-- Module badges. Rendered inside the checkout form (step 1) when the tender is
     purchasable, otherwise in the standalone "Check the plans" section — never both,
     so the ids and the selection JS stay unique on the page. --}}
@foreach ($modules as $module)
    @if (is_null($module->cost))
        {{-- Free module → click to download --}}
        <a @if (!empty($module->tender_file)) href="{{ route('tender.module.download.free', $module->id) }}"
           @else
             href="#" @endif
            class="module-badge free-badge" title="{{ __('Free – click to download') }}">
            <i class="fas fa-download"></i>
            <span>{{ convertUtf8($module->name) }}
                <small style="font-weight:400;">({{ __('Free of charge') }})</small>
            </span>
        </a>
    @else
        {{-- Paid module → toggle selection, adds cost to total --}}
        <div class="module-badge paid-badge" data-cost="{{ $module->cost }}" data-module-id="{{ $module->id }}"
            onclick="toggleModule(this)" title="{{ __('Click to select / deselect') }}">
            <i class="fas fa-lock"></i>
            <span>{{ convertUtf8($module->name) }}
                <small
                    style="font-weight:400;">({{ $bse->base_currency_symbol_position == 'left' ? $bse->base_currency_symbol : '' }}{{ number_format($module->cost, 0) }}{{ $bse->base_currency_symbol_position == 'right' ? ' ' . $bse->base_currency_symbol : '' }})</small>
            </span>
        </div>
    @endif
@endforeach
