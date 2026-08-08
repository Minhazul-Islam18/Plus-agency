<div class="statistics-section dark-statistics-section dark-section-seam"
    style="@if (!empty($be->statistics_bg)) background-image: url('{{ asset('assets/front/img/' . $be->statistics_bg) }}'); background-size:cover; @endif position: relative; overflow: hidden;"
    id="statisticsSection">
    <div class="statistics-overlay"
        style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: #{{ $be->statistics_overlay_color ?? '000000' }}; opacity: {{ $be->statistics_overlay_opacity ?? '0.6' }}; z-index: 0; pointer-events: none;">
    </div>
    <span class="dark-statistics-bg-texture"></span>
    <div class="statistics-container" style="position: relative; z-index: 2;">
        <div class="container">
            <div class="dark-stat-grid reveal-stagger" style="--d:.1s">
                @foreach ($statistics as $key => $statistic)
                    <div class="dark-stat-card" data-stat data-target="{{ (int) preg_replace('/\D/', '', $statistic->quantity) }}">
                        <span class="dark-stat-glow"></span>
                        <div class="dark-stat-content">
                            <span class="dark-stat-icon"><i class="{{ $statistic->icon }}"></i></span>
                            <div class="dark-stat-number-row">
                                <span class="dark-stat-number" data-count>0</span>
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
