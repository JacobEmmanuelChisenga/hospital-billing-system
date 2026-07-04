@props(['hint' => null])

<div {{ $attributes->merge(['class' => 'filter-panel']) }}>
    {{ $slot }}

    @if ($hint)
        <p class="form-hint">{{ $hint }}</p>
    @endif
</div>
