<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Patients</h2>
                <p class="mt-1 text-sm text-gray-500">Search and view high-cost patient records.</p>
            </div>
            @if (Auth::user()->canManagePatientDemographics())
                <a href="{{ route('patients.create') }}"
                   class="inline-flex items-center justify-center rounded-lg bg-hospital-700 px-4 py-2 text-sm font-medium text-white hover:bg-hospital-800 transition-colors">
                    <i class="fa-solid fa-user-plus mr-2"></i> Add Patient
                </a>
            @endif
        </div>
    </x-slot>

    <x-flash-messages />

    {{-- Search and filters --}}
    <div class="mb-6 rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('patients.index') }}" class="grid gap-4 md:grid-cols-4">
            <div class="md:col-span-2">
                <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                <input type="text" id="search" name="search" value="{{ $search }}"
                    placeholder="Name, patient no., membership no., NRC, MAN number, phone..."
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">
            </div>
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
                <select id="type" name="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">
                    <option value="">All types</option>
                    @foreach ($patientTypes as $patientType)
                        <option value="{{ $patientType->value }}" @selected($type === $patientType->value)>{{ $patientType->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">
                    <option value="">All statuses</option>
                    @foreach ($patientStatuses as $patientStatus)
                        <option value="{{ $patientStatus->value }}" @selected($status === $patientStatus->value)>{{ $patientStatus->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-4 flex gap-2">
                <button type="submit" class="inline-flex items-center rounded-lg bg-hospital-700 px-4 py-2 text-sm font-medium text-white hover:bg-hospital-800">
                    <i class="fa-solid fa-magnifying-glass mr-2"></i> Search
                </button>
                <a href="{{ route('patients.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Clear
                </a>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
        <div class="table-scroll -mx-4 px-4 sm:mx-0 sm:px-0">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Name</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Type</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Identifier</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Account</th>
                        @if (Auth::user()->canViewFinancialRecords())
                            <th class="px-4 py-3 text-right font-medium text-gray-600">Balance</th>
                        @endif
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($patients as $patient)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <a href="{{ route('patients.show', $patient) }}" class="font-medium text-hospital-700 hover:text-hospital-900">
                                    {{ $patient->name }}
                                </a>
                                <p class="text-xs text-gray-400">
                                    Patient No: {{ $patient->patient_number ?? '—' }}
                                    @if ($patient->membership)
                                        · Membership: {{ $patient->membership->membership_number }}
                                    @endif
                                </p>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $patient->type->label() }}</td>
                            <td class="px-4 py-3 text-gray-700">
                                @if ($patient->isMember())
                                    {{ $patient->membership?->membership_number ?? 'Pending' }}
                                @elseif ($patient->isCompanyPatient())
                                    {{ $patient->man_number ?? '—' }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-700">
                                @if ($patient->isDependant())
                                    {{ $patient->principalMember?->name ?? '—' }}
                                @elseif ($patient->isCompanyPatient())
                                    {{ $patient->company?->name ?? '—' }}
                                @else
                                    Own account
                                @endif
                            </td>
                            @if (Auth::user()->canViewFinancialRecords())
                                <td class="px-4 py-3 text-right font-medium text-gray-900">
                                    K {{ number_format((float) $patient->effectiveBalance(), 2) }}
                                </td>
                            @endif
                            <td class="px-4 py-3">
                                <span @class([
                                    'inline-flex rounded-full px-2 py-0.5 text-xs font-medium',
                                    'bg-green-100 text-green-800' => $patient->status === \App\Enums\PatientStatus::Active,
                                    'bg-gray-100 text-gray-600' => $patient->status === \App\Enums\PatientStatus::Inactive,
                                ])>
                                    {{ $patient->status->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('patients.show', $patient) }}" class="text-hospital-700 hover:text-hospital-900 mr-3" title="View profile">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                @if (Auth::user()->canManagePatientDemographics())
                                    <a href="{{ route('patients.edit', $patient) }}" class="text-gray-500 hover:text-gray-700" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ Auth::user()->canViewFinancialRecords() ? 7 : 6 }}" class="px-4 py-12 text-center text-gray-500">
                                <i class="fa-solid fa-users text-3xl text-gray-300 mb-3"></i>
                                <p>No patients found.</p>
                                @if (Auth::user()->canManagePatientDemographics())
                                    <a href="{{ route('patients.create') }}" class="mt-2 inline-block text-hospital-700 hover:underline">Register the first patient</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($patients->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">
                {{ $patients->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
