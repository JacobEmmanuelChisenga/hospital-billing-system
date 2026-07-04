@props([
    'title',
    'description' => null,
    'href' => null,
    'hrefLabel' => 'View all',
])

<div class="data-panel">
    <div class="panel-header">
        <div>
            <h3 class="section-title">{{ $title }}</h3>
            @if ($description)
                <p class="section-subtitle">{{ $description }}</p>
            @endif
        </div>
        @if ($href)
            <a href="{{ $href }}" class="action-link shrink-0 text-sm">{{ $hrefLabel }}</a>
        @endif
    </div>
    <div class="panel-body !pt-0">
        {{ $slot }}
    </div>
</div>
