@props([
    'icon' => 'fa-inbox',
    'message',
    'actionLabel' => null,
    'actionHref' => null,
])

<div {{ $attributes->merge(['class' => 'empty-state']) }}>
    <i class="fa-solid {{ $icon }} empty-state-icon"></i>
    <p class="empty-state-text">{{ $message }}</p>
    @if ($actionLabel && $actionHref)
        <a href="{{ $actionHref }}" class="action-link mt-3 inline-block">{{ $actionLabel }}</a>
    @endif
</div>
