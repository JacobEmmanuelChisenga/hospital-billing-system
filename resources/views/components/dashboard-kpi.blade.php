@props([
    'label',
    'value',
    'hint' => null,
    'href' => null,
    'tone' => 'default',
    'trend' => null,
    'trendLabel' => null,
    'status' => null,
])

@php
    $valueClasses = match ($tone) {
        'amber' => 'text-amber-700',
        'orange' => 'text-orange-700',
        'green' => 'text-green-700',
        'blue' => 'text-blue-700',
        'purple' => 'text-purple-700',
        'slate' => 'text-slate-700',
        default => 'text-hospital-800',
    };

    $trendClasses = match ($trend) {
        'up' => 'text-green-600',
        'down' => 'text-red-600',
        default => 'text-gray-500',
    };
@endphp

<div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
    <p class="text-sm font-medium text-gray-500">{{ $label }}</p>
    <p class="mt-2 text-3xl font-bold {{ $valueClasses }}">{{ $value }}</p>
    @if ($trendLabel)
        <p class="mt-1 flex items-center gap-1 text-xs font-medium {{ $trendClasses }}">
            @if ($trend === 'up')
                <i class="fa-solid fa-arrow-trend-up"></i>
            @elseif ($trend === 'down')
                <i class="fa-solid fa-arrow-trend-down"></i>
            @endif
            <span>{{ $trendLabel }}</span>
        </p>
    @elseif ($status)
        <p class="mt-1 text-xs font-semibold text-green-600">{{ $status }}</p>
    @elseif ($hint)
        <p class="mt-1 text-xs text-gray-400">{{ $hint }}</p>
    @endif
    @if ($href)
        <a href="{{ $href }}" class="mt-2 inline-block text-xs text-hospital-700 hover:underline">View details</a>
    @endif
</div>
