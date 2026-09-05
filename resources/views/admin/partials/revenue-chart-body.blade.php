<span class="chart-total">{{ $revenueChart['total'] }}</span>

<div class="chart-wrap" data-chart="line">
    <svg class="chart-svg" viewBox="0 0 {{ $revenueChart['width'] }} {{ $revenueChart['height'] }}" preserveAspectRatio="xMidYMid meet" role="img" aria-label="Revenue chart, total {{ $revenueChart['total'] }}">
        @foreach($revenueChart['gridlines'] as $line)
            <line class="chart-gridline" x1="46" y1="{{ $line['y'] }}" x2="{{ $revenueChart['width'] - 16 }}" y2="{{ $line['y'] }}"></line>
            <text class="chart-axis-label" x="40" y="{{ $line['y'] + 4 }}" text-anchor="end">{{ $line['label'] }}</text>
        @endforeach

        <path class="chart-area" d="{{ $revenueChart['areaPath'] }}"></path>
        <path class="chart-line" d="{{ $revenueChart['linePath'] }}"></path>

        <line class="chart-crosshair" data-crosshair x1="0" y1="20" x2="0" y2="{{ $revenueChart['baselineY'] }}" opacity="0"></line>

        @php($last = $revenueChart['points']->last())
        <circle class="chart-dot" cx="{{ $last['x'] }}" cy="{{ $last['y'] }}" r="4"></circle>
        <text class="chart-value-label" x="{{ $last['x'] }}" y="{{ $last['y'] - 12 }}" text-anchor="end">{{ $last['valueLabel'] }}</text>

        @foreach($revenueChart['points'] as $i => $point)
            @if($point['showLabel'])
                <text class="chart-axis-label" x="{{ $point['x'] }}" y="{{ $revenueChart['height'] - 6 }}" text-anchor="middle">{{ $point['label'] }}</text>
            @endif
            <circle class="chart-hit" data-index="{{ $i }}" data-label="{{ $point['label'] }}" data-value="{{ $point['valueLabel'] }}" cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="14"></circle>
        @endforeach
    </svg>
    <div class="chart-tooltip" data-tooltip hidden></div>
</div>
