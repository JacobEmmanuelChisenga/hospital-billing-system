@props([
    'title',
    'subtitle' => null,
    'theme' => null,
    'dashboard' => false,
])

<div @class([
    'page-header',
    'page-header--dashboard' => $dashboard,
    'page-header--registry' => $theme === 'registry',
    'page-header--consultant' => $theme === 'consultant',
    'page-header--accounts' => $theme === 'accounts',
    'page-header--admin' => $theme === 'admin',
])>
    <div class="min-w-0">
        <h2 class="page-title">{{ $title }}</h2>
        @if ($subtitle)
            <p class="page-subtitle">{{ $subtitle }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="flex shrink-0 flex-wrap items-center gap-2 sm:gap-3">
            {{ $actions }}
        </div>
    @endisset
</div>
