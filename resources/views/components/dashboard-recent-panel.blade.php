@props([
    'title',
    'description' => null,
    'href' => null,
    'hrefLabel' => 'View all',
])

<div class="rounded-xl border border-gray-100 bg-white shadow-sm">
    <div class="flex items-start justify-between gap-4 border-b border-gray-100 px-5 py-4">
        <div>
            <h3 class="text-base font-semibold text-gray-800">{{ $title }}</h3>
            @if ($description)
                <p class="mt-1 text-sm text-gray-500">{{ $description }}</p>
            @endif
        </div>
        @if ($href)
            <a href="{{ $href }}" class="shrink-0 text-sm font-medium text-hospital-700 hover:underline">{{ $hrefLabel }}</a>
        @endif
    </div>
    <div class="px-5 py-4">
        {{ $slot }}
    </div>
</div>
