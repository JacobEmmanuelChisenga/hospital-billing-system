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
        'green' => 'text-emerald-700',
        'blue' => 'text-blue-700',
        'purple' => 'text-violet-700',
        'slate' => 'text-slate-700',
        default => 'text-hospital-800',
    };

    $trendClasses = match ($trend) {
        'up' => 'text-emerald-600',
        'down' => 'text-red-600',
        default => 'text-slate-500',
    };
@endphp

<div @class(['stat-card', 'stat-card--' . $tone])>
    <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
    <p class="mt-2 text-3xl font-bold tracking-tight {{ $valueClasses }}">{{ $value }}</p>
    @if ($trendLabel)
        <p class="mt-2 flex items-center gap-1.5 text-xs font-semibold {{ $trendClasses }}">
            @if ($trend === 'up')
                <i class="fa-solid fa-arrow-trend-up"></i>
            @elseif ($trend === 'down')
                <i class="fa-solid fa-arrow-trend-down"></i>
            @endif
            <span>{{ $trendLabel }}</span>
        </p>
    @elseif ($status)
        <p class="mt-2 text-xs font-semibold text-emerald-600">{{ $status }}</p>
    @elseif ($hint)
        <p class="mt-2 text-xs text-slate-400">{{ $hint }}</p>
    @endif
    @if ($href)
        <a href="{{ $href }}" class="action-link mt-3 inline-flex items-center gap-1 text-xs">
            View details
            <i class="fa-solid fa-arrow-right text-[10px]"></i>
        </a>
    @endif
</div>
