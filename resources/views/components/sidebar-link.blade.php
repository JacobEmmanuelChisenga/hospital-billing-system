@props(['active' => false, 'href' => '#', 'disabled' => false, 'icon' => null])

@php
    $classes = $active
        ? 'flex items-center gap-3 rounded-lg bg-hospital-700 px-3 py-3 text-sm font-medium text-white sm:py-2.5'
        : ($disabled
            ? 'flex items-center gap-3 rounded-lg px-3 py-3 text-sm font-medium text-hospital-300/50 cursor-not-allowed sm:py-2.5'
            : 'flex items-center gap-3 rounded-lg px-3 py-3 text-sm font-medium text-hospital-100 transition-colors hover:bg-hospital-700 hover:text-white sm:py-2.5');
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
