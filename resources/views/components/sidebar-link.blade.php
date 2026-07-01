@props(['active' => false, 'href' => '#', 'disabled' => false, 'icon' => null])

@php
    $classes = $active
        ? 'flex items-center gap-3 rounded-lg bg-hospital-700 px-3 py-2.5 text-sm font-medium text-white'
        : ($disabled
            ? 'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-hospital-300/50 cursor-not-allowed'
            : 'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-hospital-100 hover:bg-hospital-700 hover:text-white transition-colors');
@endphp

@if ($disabled)
    <span {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)
            <i class="{{ $icon }} w-5 text-center shrink-0"></i>
        @endif
        {{ $slot }}
    </span>
@else
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)
            <i class="{{ $icon }} w-5 text-center shrink-0"></i>
        @endif
        {{ $slot }}
    </a>
@endif
