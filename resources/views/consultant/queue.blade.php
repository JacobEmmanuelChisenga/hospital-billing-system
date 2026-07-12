<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Today's Queue" subtitle="Patients ready for consultation today." />
    </x-slot>

    <x-flash-messages />

    <x-filter-panel>
        <form method="GET" action="{{ route('consultant.queue') }}" class="flex flex-col gap-3 sm:flex-row">
            <div class="flex-1">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search patient..."
                    class="form-input !mt-0">
            </div>
            <button type="submit" class="btn-primary w-full sm:w-auto">Search</button>
        </form>
    </x-filter-panel>

    <div class="space-y-3">
        @forelse ($visits as $visit)
            <a href="{{ route('clinical-notes.edit', $visit) }}" class="list-card">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-semibold text-slate-900">{{ $visit->patient->name }}</p>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ $visit->visit_type->label() }}
                            · Opened {{ $visit->created_at->format('H:i') }}
                            @if ($visit->patient->effectiveMembershipNumber())
                                · {{ $visit->patient->effectiveMembershipNumber() }}
                            @endif
                        </p>
                    </div>
                    <span class="badge-info shrink-0">Waiting</span>
                </div>
            </a>
        @empty
            <x-empty-state
                icon="fa-list-check"
                message="No patients waiting for consultation today."
            />
        @endforelse
    </div>
</x-app-layout>
