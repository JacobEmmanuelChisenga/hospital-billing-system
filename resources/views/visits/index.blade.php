<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Patient Visits" subtitle="Open visits, clinical notes, and charge posting.">
            @if (Auth::user()->canManageVisits())
                <x-slot name="actions">
                    <a href="{{ route('visits.create') }}" class="btn-primary">
                        <i class="fa-solid fa-plus"></i> Open Visit
                    </a>
                </x-slot>
            @endif
        </x-page-header>
    </x-slot>

    <x-flash-messages />

    <x-filter-panel>
        <form method="GET" action="{{ route('visits.index') }}" class="grid gap-4 md:grid-cols-4">
            <div class="md:col-span-2">
                <label for="search" class="form-label">Search Patient</label>
                <input type="text" id="search" name="search" value="{{ $search }}"
                    placeholder="Name, HC, NRC, Man Number..."
                    class="form-input">
            </div>
            <div>
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-input">
                    <option value="">All</option>
                    @foreach ($visitStatuses as $visitStatus)
                        <option value="{{ $visitStatus->value }}" @selected($status === $visitStatus->value)>{{ $visitStatus->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary">Filter</button>
                <a href="{{ route('visits.index') }}" class="btn-secondary">Clear</a>
            </div>
        </form>
    </x-filter-panel>

    <x-data-panel>
        <x-table-scroll>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Opened</th>
                        <th>Patient</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th class="text-right">Charges</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($visits as $visit)
                        <tr>
                            <td class="whitespace-nowrap">
                                <span class="block">{{ $visit->created_at->format('d M Y') }}</span>
                                <span class="text-xs text-slate-500">{{ $visit->created_at->format('H:i') }}</span>
                            </td>
                            <td>
                                <a href="{{ route('patients.show', $visit->patient) }}" class="action-link font-medium">{{ $visit->patient->name }}</a>
                            </td>
                            <td>{{ $visit->visit_type->label() }}</td>
                            <td>
                                <span class="badge {{ $visit->status->badgeClass() }}">{{ $visit->status->label() }}</span>
                            </td>
                            <td class="text-right font-medium">K {{ number_format($visit->chargesTotal(), 2) }}</td>
                            <td class="text-right">
                                <a href="{{ route('visits.show', $visit) }}" class="action-link">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="!py-12 text-center text-slate-500">No visits recorded yet.</td>
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
