<div class="statistics-section dark-statistics-section dark-section-seam"
    style="@if (!empty($be->statistics_bg)) background-image: url('{{ asset('assets/front/img/' . $be->statistics_bg) }}'); background-size:cover; @endif position: relative; overflow: hidden;"
    id="statisticsSection">
    <div class="statistics-overlay"
        style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: #{{ $be->statistics_overlay_color ?? '000000' }}; opacity: {{ $be->statistics_overlay_opacity ?? '0.6' }}; z-index: 0; pointer-events: none;">
    </div>
    <span class="dark-statistics-bg-texture"></span>
    <div class="statistics-container" style="position: relative; z-index: 2;">
        <div class="container">
            <div class="dark-stat-grid">
                @foreach ($statistics as $key => $statistic)
                    @php $statTarget = (int) preg_replace('/\D/', '', $statistic->quantity); @endphp
                    <div class="dark-stat-card reveal-card" style="--d:{{ ($key % 4) * 0.1 }}s" data-stat data-target="{{ $statTarget }}">
                        <span class="dark-stat-glow"></span>
                        <div class="dark-stat-content">
                            <span class="dark-stat-icon"><i class="{{ $statistic->icon }}"></i></span>
                            <div class="dark-stat-number-row">
                                {{-- Real number in the static HTML, not "0" — search
                                     engines only ever see this initial render, they
                                     don't execute the count-up animation below. JS
                                     still animates from 0 for real visitors (it
                                     overwrites this text the instant it runs; doesn't
                                     care what it started as). --}}
                                <span class="dark-stat-number" data-count>{{ $statTarget }}</span>
                                <span class="dark-stat-plus">+</span>
                            </div>
                            <div class="dark-stat-label">{{ convertUtf8($statistic->title) }}</div>
                            <div class="dark-stat-bar"><span class="dark-stat-bar-fill"></span></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
