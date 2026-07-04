@props(['chart'])

@php
    $chartPayload = [
        'type' => $chart['type'],
        'data' => $chart['data'],
        'horizontal' => $chart['horizontal'] ?? false,
    ];
@endphp

<div class="card card-body">
    <h3 class="section-title">{{ $chart['title'] }}</h3>
    @if (! empty($chart['description']))
        <p class="section-subtitle">{{ $chart['description'] }}</p>
    @endif
    <div @class([
        'relative mt-5',
        'h-64 sm:h-72' => ($chart['height'] ?? 'default') === 'default',
        'h-56 sm:h-64' => ($chart['height'] ?? 'default') === 'compact',
        'h-72 sm:h-80' => ($chart['height'] ?? 'default') === 'tall',
    ])>
        <canvas
            data-dashboard-chart='@json($chartPayload)'
            aria-label="{{ $chart['title'] }}"
        ></canvas>
    </div>
    @if (! empty($chart['footer']))
        <p class="mt-4 text-center text-sm font-medium text-slate-600">{{ $chart['footer'] }}</p>
    @endif
</div>
