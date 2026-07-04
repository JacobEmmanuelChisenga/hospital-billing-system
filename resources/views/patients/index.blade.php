<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Patients" subtitle="Search and view high-cost patient records.">
            @if (Auth::user()->canManagePatientDemographics())
                <x-slot name="actions">
                    <a href="{{ route('patients.create') }}" class="btn-primary">
                        <i class="fa-solid fa-user-plus"></i> Add Patient
                    </a>
                </x-slot>
            @endif
        </x-page-header>
    </x-slot>

    <x-flash-messages />

    <x-filter-panel>
        <form method="GET" action="{{ route('patients.index') }}" class="grid gap-4 md:grid-cols-4">
            <div class="md:col-span-2">
                <label for="search" class="form-label">Search</label>
                <input type="text" id="search" name="search" value="{{ $search }}"
                    placeholder="Name, patient no., membership no., NRC, MAN number, phone..."
                    class="form-input">
            </div>
            <div>
                <label for="type" class="form-label">Type</label>
                <select id="type" name="type" class="form-input">
                    <option value="">All types</option>
                    @foreach ($patientTypes as $patientType)
                        <option value="{{ $patientType->value }}" @selected($type === $patientType->value)>{{ $patientType->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-input">
                    <option value="">All statuses</option>
                    @foreach ($patientStatuses as $patientStatus)
                        <option value="{{ $patientStatus->value }}" @selected($status === $patientStatus->value)>{{ $patientStatus->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-4 flex gap-2">
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-magnifying-glass"></i> Search
                </button>
                <a href="{{ route('patients.index') }}" class="btn-secondary">Clear</a>
            </div>
        </form>
    </x-filter-panel>

    <x-data-panel>
        <x-table-scroll>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Identifier</th>
                        <th>Account</th>
                        @if (Auth::user()->canViewFinancialRecords())
                            <th class="text-right">Balance</th>
                        @endif
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($patients as $patient)
                        <tr>
                            <td>
                                <a href="{{ route('patients.show', $patient) }}" class="action-link font-medium">
                                    {{ $patient->name }}
                                </a>
                                <p class="text-xs text-slate-400">
                                    Patient No: {{ $patient->patient_number ?? '—' }}
                                    @if ($patient->membership)
                                        · Membership: {{ $patient->membership->membership_number }}
                                    @endif
                                </p>
                            </td>
                            <td>{{ $patient->type->label() }}</td>
                            <td>
                                @if ($patient->isMember())
                                    {{ $patient->membership?->membership_number ?? 'Pending' }}
                                @elseif ($patient->isCompanyPatient())
                                    {{ $patient->man_number ?? '—' }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if ($patient->isDependant())
                                    {{ $patient->principalMember?->name ?? '—' }}
                                @elseif ($patient->isCompanyPatient())
                                    {{ $patient->company?->name ?? '—' }}
                                @else
                                    Own account
                                @endif
                            </td>
                            @if (Auth::user()->canViewFinancialRecords())
                                <td class="text-right font-semibold text-slate-900">
                                    K {{ number_format((float) $patient->effectiveBalance(), 2) }}
                                </td>
                            @endif
                            <td>
                                <span @class([
                                    'badge',
                                    'badge-success' => $patient->status === \App\Enums\PatientStatus::Active,
                                    'badge-neutral' => $patient->status === \App\Enums\PatientStatus::Inactive,
                                ])>
                                    {{ $patient->status->label() }}
                                </span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('patients.show', $patient) }}" class="icon-btn" title="View profile">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                @if (Auth::user()->canManagePatientDemographics())
                                    <a href="{{ route('patients.edit', $patient) }}" class="icon-btn" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ Auth::user()->canViewFinancialRecords() ? 7 : 6 }}" class="!py-12 text-center text-slate-500">
                                <i class="fa-solid fa-users mb-3 text-3xl text-slate-300"></i>
                                <p>No patients found.</p>
                                @if (Auth::user()->canManagePatientDemographics())
                                    <a href="{{ route('patients.create') }}" class="action-link mt-2 inline-block">Register the first patient</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-table-scroll>

        @if ($patients->hasPages())
            <x-slot name="footer">{{ $patients->links() }}</x-slot>
        @endif
    </x-data-panel>
</x-app-layout>
