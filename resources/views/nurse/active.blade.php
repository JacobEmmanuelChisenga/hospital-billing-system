<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Active Visits" subtitle="Open consultations for today — tap to record or continue notes." />
    </x-slot>

    <x-flash-messages />

    <x-filter-panel>
        <form method="GET" action="{{ route('nurse.active') }}" class="flex flex-col gap-3 sm:flex-row">
            <div class="flex-1">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search patient..."
                    class="form-input !mt-0">
            </div>
            <button type="submit" class="btn-primary w-full sm:w-auto">Search</button>
        </form>
    </x-filter-panel>

    <x-data-panel>
        <x-table-scroll>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Opened</th>
                        <th>Visit</th>
                        <th>Status</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($visits as $visit)
                        <tr>
                            <td class="font-medium">{{ $visit->patient->name }}</td>
                            <td class="whitespace-nowrap">{{ $visit->created_at->format('H:i') }}</td>
                            <td>{{ $visit->visit_type->label() }}</td>
                            <td>
                                <span class="badge {{ $visit->status->badgeClass() }}">{{ $visit->status->label() }}</span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('clinical-notes.edit', $visit) }}" class="action-link">
                                    {{ $visit->clinicalNote ? 'Edit consultation' : 'Start consultation' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="!py-12 text-center text-slate-500">No active visits today.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-table-scroll>

        @if ($visits->hasPages())
            <x-slot name="footer">{{ $visits->links() }}</x-slot>
        @endif
    </x-data-panel>
</x-app-layout>
