<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Today's Queue</h2>
            <p class="mt-1 text-sm text-gray-500">Patients ready for consultation today.</p>
        </div>
    </x-slot>

    <x-flash-messages />

    <div class="mb-6 rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('nurse.queue') }}" class="flex flex-col gap-3 sm:flex-row">
            <div class="flex-1">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search patient..."
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">
            </div>
            <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg bg-hospital-700 px-4 py-2.5 text-sm font-medium text-white hover:bg-hospital-800 sm:w-auto">Search</button>
        </form>
    </div>

    <div class="space-y-3">
        @forelse ($visits as $visit)
            <a href="{{ route('clinical-notes.edit', $visit) }}"
               class="block rounded-xl border border-gray-100 bg-white p-4 shadow-sm transition hover:border-hospital-200 hover:shadow-md">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-900">{{ $visit->patient->name }}</p>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ $visit->visit_type->label() }}
                            @if ($visit->patient->membership?->membership_number)
                                · {{ $visit->patient->membership->membership_number }}
                            @endif
                        </p>
                    </div>
                    <span class="shrink-0 rounded-full bg-indigo-100 px-2.5 py-1 text-xs font-medium text-indigo-800">Waiting</span>
                </div>
            </a>
        @empty
            <div class="rounded-xl border border-gray-100 bg-white p-8 text-center text-gray-500 shadow-sm">
                <i class="fa-solid fa-list-check mb-3 text-3xl text-gray-300"></i>
                <p>No patients waiting for consultation today.</p>
            </div>
        @endforelse
    </div>
</x-app-layout>
