{{-- Simple bar chart. Needs $title and $data (label => number). Optional $tone: yellow|pink|blue|green --}}
@php
    $max = max(1, max($data ?: [0]));
    $tone = $tone ?? 'yellow';
@endphp
<figure class="chart chart-{{ $tone }}">
    <figcaption>{{ $title }} <span>{{ array_sum($data) }} total</span></figcaption>
    <div class="bars" role="img" aria-label="{{ $title }}: {{ implode(', ', array_map(fn ($k, $v) => "$k $v", array_keys($data), $data)) }}">
        @foreach ($data as $label => $value)
            <div class="bar-col" title="{{ $label }}: {{ $value }}">
                <span class="bar-val">{{ $value ?: '' }}</span>
                <span class="bar-track"><span class="bar" style="height: {{ $value ? max(4, round($value / $max * 100)) : 2 }}%"></span></span>
                <span class="bar-lbl">{{ \Illuminate\Support\Str::afterLast($label, ' ') }}</span>
            </div>
        @endforeach
    </div>
</figure>
