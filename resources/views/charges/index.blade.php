<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="$pageTitle" :subtitle="$pageDescription" />
    </x-slot>

    <x-flash-messages />

    <x-filter-panel>
        <form method="GET" action="{{ url()->current() }}" class="flex flex-col gap-3 sm:flex-row">
            <div class="flex-1">
                <label for="search" class="form-label">Search patient</label>
                <input type="text" id="search" name="search" value="{{ $search }}"
                    placeholder="Name, patient number, phone..."
                    class="form-input">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary w-full sm:w-auto">
                    <i class="fa-solid fa-magnifying-glass"></i> Search
                </button>
            </div>
        </form>
    </x-filter-panel>

    <x-data-panel>
        <x-table-scroll>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Patient</th>
                        <th>Visit</th>
                        <th>Status</th>
                        <th class="text-right">Charges</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($visits as $visit)
                        <tr>
                            <td>{{ $visit->visit_date->format('d M Y') }}</td>
                            <td class="font-medium">{{ $visit->patient->name }}</td>
                            <td class="font-medium">{{ $visit->visitNumber() }}</td>
                            <td>
                                <span class="badge {{ $visit->status->badgeClass() }}">{{ $visit->status->label() }}</span>
                                @if ($visit->patient->isCashPatient())
                                    <span class="badge badge-neutral ml-1">Casual Caller</span>
                                @endif
                            </td>
                            <td class="text-right font-medium">K {{ number_format($visit->chargesTotal(), 2) }}</td>
                            <td class="text-right">
                                <a href="{{ route('visits.show', $visit) }}" class="action-link">
                                    {{ $visit->status === \App\Enums\VisitStatus::AwaitingBilling ? 'Post charges' : 'View' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="!py-12 text-center text-slate-500">No visits found.</td>
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
