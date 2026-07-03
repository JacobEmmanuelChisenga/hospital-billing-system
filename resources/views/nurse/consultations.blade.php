<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Consultation History</h2>
            <p class="mt-1 text-sm text-gray-500">Read-only record of consultations you have documented.</p>
        </div>
    </x-slot>

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <form method="GET" action="{{ route('nurse.consultations') }}" class="flex flex-1 flex-col gap-3 sm:flex-row">
            <div class="flex-1">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search patient..."
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">
            </div>
            <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg bg-hospital-700 px-4 py-2.5 text-sm font-medium text-white hover:bg-hospital-800 sm:w-auto">Search</button>
        </form>
        <div class="flex flex-wrap gap-2">
            @foreach (['today' => 'Today', 'yesterday' => 'Yesterday', 'week' => 'This Week'] as $value => $label)
                <a href="{{ route('nurse.consultations', array_filter(['period' => $value, 'search' => $search ?: null])) }}"
                   class="rounded-lg px-3 py-1.5 text-sm font-medium {{ $period === $value ? 'bg-hospital-700 text-white' : 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    <div class="space-y-3">
        @forelse ($visits as $visit)
            <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="font-semibold text-gray-900">{{ $visit->patient->name }}</p>
                        <p class="text-sm text-gray-500">{{ $visit->visit_date->format('d M Y') }} · {{ $visit->visit_type->label() }}</p>
                    </div>
                    <span class="inline-flex w-fit rounded-full px-2 py-0.5 text-xs font-medium {{ $visit->status->badgeClass() }}">{{ $visit->status->label() }}</span>
                </div>
                @if ($visit->clinicalNote?->complaint)
                    <p class="mt-3 text-sm text-gray-700"><span class="font-medium text-gray-500">Complaint:</span> {{ $visit->clinicalNote->complaint }}</p>
                @endif
                @if ($visit->clinicalNote?->diagnosis)
                    <p class="mt-1 text-sm text-gray-700"><span class="font-medium text-gray-500">Diagnosis:</span> {{ $visit->clinicalNote->diagnosis }}</p>
                @endif
                <p class="mt-2 text-xs text-gray-400">Recorded by {{ $visit->clinicalNote?->recordedBy?->name }}</p>
            </div>
        @empty
            <div class="rounded-xl border border-gray-100 bg-white p-8 text-center text-gray-500 shadow-sm">
                No consultations found for this period.
            </div>
        @endforelse
    </div>

    @if ($visits->hasPages())
        <div class="mt-4">{{ $visits->links() }}</div>
    @endif
</x-app-layout>
