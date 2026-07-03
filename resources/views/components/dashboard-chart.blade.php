@props(['chart'])

@php
    $chartPayload = [
        'type' => $chart['type'],
        'data' => $chart['data'],
        'horizontal' => $chart['horizontal'] ?? false,
    ];
@endphp

<div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
    <h3 class="text-base font-semibold text-gray-800">{{ $chart['title'] }}</h3>
    @if (! empty($chart['description']))
        <p class="mt-1 text-sm text-gray-500">{{ $chart['description'] }}</p>
    @endif
    <div @class([
        'relative mt-4',
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
        <p class="mt-3 text-center text-sm font-medium text-gray-600">{{ $chart['footer'] }}</p>
    @endif
</div>
