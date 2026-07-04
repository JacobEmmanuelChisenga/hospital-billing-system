<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Consultation History" subtitle="Read-only record of consultations you have documented." />
    </x-slot>

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <x-filter-panel class="!mb-0 flex-1">
            <form method="GET" action="{{ route('nurse.consultations') }}" class="flex flex-col gap-3 sm:flex-row">
                <div class="flex-1">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search patient..."
                        class="form-input !mt-0">
                </div>
                <button type="submit" class="btn-primary w-full sm:w-auto">Search</button>
            </form>
        </x-filter-panel>

        <div class="flex flex-wrap gap-2">
            @foreach (['today' => 'Today', 'yesterday' => 'Yesterday', 'week' => 'This Week'] as $value => $label)
                <a href="{{ route('nurse.consultations', array_filter(['period' => $value, 'search' => $search ?: null])) }}"
                   @class([
                       'btn btn-sm',
                       'btn-primary' => $period === $value,
                       'btn-secondary' => $period !== $value,
                   ])>
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    <div class="space-y-3">
        @forelse ($visits as $visit)
            <div class="card card-body">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="font-semibold text-slate-900">{{ $visit->patient->name }}</p>
                        <p class="text-sm text-slate-500">
                            {{ $visit->visit_date->format('d M Y') }}
                            · Opened {{ $visit->created_at->format('H:i') }}
                            · {{ $visit->visit_type->label() }}
                        </p>
                    </div>
                    <span class="badge {{ $visit->status->badgeClass() }} w-fit">{{ $visit->status->label() }}</span>
                </div>
                @if ($visit->clinicalNote?->complaint)
                    <p class="mt-3 text-sm text-slate-700"><span class="font-medium text-slate-500">Complaint:</span> {{ $visit->clinicalNote->complaint }}</p>
                @endif
                @if ($visit->clinicalNote?->diagnosis)
                    <p class="mt-1 text-sm text-slate-700"><span class="font-medium text-slate-500">Diagnosis:</span> {{ $visit->clinicalNote->diagnosis }}</p>
                @endif
                <p class="mt-2 text-xs text-slate-400">
                    Recorded by {{ $visit->clinicalNote?->recordedBy?->name }}
                    @if ($visit->clinicalNote?->created_at)
                        at {{ $visit->clinicalNote->created_at->format('H:i') }}
                    @endif
                </p>
            </div>
        @empty
            <x-empty-state icon="fa-file-medical" message="No consultations found for this period." />
        @endforelse
    </div>

    @if ($visits->hasPages())
        <div class="mt-4">{{ $visits->links() }}</div>
    @endif
</x-app-layout>
