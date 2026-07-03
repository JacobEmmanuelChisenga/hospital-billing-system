@props(['chart'])

<div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
    <h3 class="text-base font-semibold text-gray-800">{{ $chart['title'] }}</h3>
    <p class="mt-1 text-sm text-gray-500">{{ $chart['description'] }}</p>
    <div class="relative mt-4 h-64 sm:h-72">
        <canvas data-dashboard-chart='@json($chart)' aria-label="{{ $chart['title'] }}"></canvas>
    </div>
</div>
