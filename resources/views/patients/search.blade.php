<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Patient Search</h2>
            <p class="mt-1 text-sm text-gray-500">Find a patient by name, number, phone, or identifier.</p>
        </div>
    </x-slot>

    <x-flash-messages />

    <div class="mb-6 rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('patients.search') }}" class="flex flex-col gap-3 sm:flex-row">
            <div class="flex-1">
                <label for="search" class="sr-only">Search</label>
                <input type="text" id="search" name="search" value="{{ $search }}" autofocus
                    placeholder="Name, patient no., membership no., NRC, MAN number, phone..."
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">
            </div>
            <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg bg-hospital-700 px-4 py-2.5 text-sm font-medium text-white hover:bg-hospital-800 sm:w-auto">
                <i class="fa-solid fa-magnifying-glass mr-2"></i> Search
            </button>
        </form>
    </div>

    @if ($search !== '')
        <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
            <x-table-scroll>
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">Name</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">Type</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">Identifier</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($patients as $patient)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium">{{ $patient->name }}</td>
                                <td class="px-4 py-3">{{ $patient->type->label() }}</td>
                                <td class="px-4 py-3">
                                    @if ($patient->isMember())
                                        {{ $patient->membership?->membership_number ?? 'Pending' }}
                                    @elseif ($patient->isCompanyPatient())
                                        {{ $patient->man_number ?? '—' }}
                                    @else
                                        {{ $patient->principalMember?->name ?? '—' }}
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('patients.show', $patient) }}" class="text-hospital-700 hover:underline">View profile</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-12 text-center text-gray-500">No patients match your search.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-table-scroll>

            @if ($patients->hasPages())
                <div class="border-t border-gray-100 px-4 py-3">{{ $patients->links() }}</div>
            @endif
        </div>
    @else
        <p class="text-sm text-gray-500">Enter a search term to find patients.</p>
    @endif
</x-app-layout>
