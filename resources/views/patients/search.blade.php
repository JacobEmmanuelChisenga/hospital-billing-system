<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Patient Search" subtitle="Find a patient by name, number, phone, or identifier." />
    </x-slot>

    <x-flash-messages />

    <x-filter-panel>
        <form method="GET" action="{{ route('patients.search') }}" class="flex flex-col gap-3 sm:flex-row">
            <div class="flex-1">
                <label for="search" class="sr-only">Search</label>
                <input type="text" id="search" name="search" value="{{ $search }}" autofocus
                    placeholder="Name, patient no., membership no., NRC, MAN number, phone..."
                    class="form-input">
            </div>
            <button type="submit" class="btn-primary sm:w-auto">
                <i class="fa-solid fa-magnifying-glass"></i> Search
            </button>
        </form>
    </x-filter-panel>

    @if ($search !== '')
        <x-data-panel>
            <x-table-scroll>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Identifier</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($patients as $patient)
                            <tr>
                                <td class="font-medium">{{ $patient->name }}</td>
                                <td>{{ $patient->type->label() }}</td>
                                <td>
                                    @if ($patient->isMember())
                                        {{ $patient->membership?->membership_number ?? 'Pending' }}
                                    @elseif ($patient->isCompanyPatient())
                                        {{ $patient->man_number ?? '—' }}
                                    @else
                                        {{ $patient->principalMember?->name ?? '—' }}
                                    @endif
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('patients.show', $patient) }}" class="action-link">View profile</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="!py-12 text-center text-slate-500">No patients match your search.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-table-scroll>

            @if ($patients->hasPages())
                <x-slot name="footer">{{ $patients->links() }}</x-slot>
            @endif
        </x-data-panel>
    @else
        <p class="text-sm text-slate-500">Enter a search term to find patients.</p>
    @endif
</x-app-layout>
