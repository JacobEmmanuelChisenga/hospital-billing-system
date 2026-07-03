@props([])

<div {{ $attributes->merge(['class' => 'table-scroll -mx-4 px-4 sm:mx-0 sm:px-0']) }}>
    {{ $slot }}
</div>
