@props([
    'title' => null,
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'data-panel']) }}>
    @if ($title)
        <div class="panel-header">
            <div>
                <h3 class="section-title">{{ $title }}</h3>
                @if ($description)
                    <p class="section-subtitle">{{ $description }}</p>
                @endif
            </div>
            @isset($headerActions)
                <div class="flex shrink-0 items-center gap-2">
                    {{ $headerActions }}
                </div>
            @endisset
        </div>
        <div class="panel-body">
            {{ $slot }}
        </div>
    @else
        <div class="table-scroll -mx-4 px-4 sm:mx-0 sm:px-0">
            {{ $slot }}
        </div>
    @endif

    @isset($footer)
        <div class="panel-footer">
            {{ $footer }}
        </div>
    @endisset
</div>
