<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">{{ $patient->name }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $patient->type->label() }} &middot; {{ $patient->status->label() }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if (Auth::user()->canManageVisits() && ! $patient->openVisit())
                    <a href="{{ route('visits.create', ['patient_id' => $patient->id]) }}"
                       class="inline-flex items-center rounded-lg bg-hospital-700 px-4 py-2 text-sm font-medium text-white hover:bg-hospital-800">
                        <i class="fa-solid fa-door-open mr-2"></i> Open Visit
                    </a>
                @elseif ($patient->openVisit() && (Auth::user()->canManageVisits() || Auth::user()->canRecordClinicalNotes()))
                    <a href="{{ route('visits.show', $patient->openVisit()) }}"
                       class="inline-flex items-center rounded-lg bg-hospital-700 px-4 py-2 text-sm font-medium text-white hover:bg-hospital-800">
                        <i class="fa-solid fa-folder-open mr-2"></i> Active Visit
                    </a>
                @endif
                @if ($patient->isMember() && Auth::user()->canPerformFinancialOperations())
                    <a href="{{ route('deposits.create', ['patient_id' => $patient->id]) }}"
                       class="inline-flex items-center rounded-lg bg-hospital-700 px-4 py-2 text-sm font-medium text-white hover:bg-hospital-800">
                        <i class="fa-solid fa-money-bill-wave mr-2"></i> Load Deposit
                    </a>
                @endif
                @if (($patient->isMember() || $patient->isDependant()) && Auth::user()->canPerformFinancialOperations())
                    <a href="{{ route('membership-fees.create', ['patient_id' => $patient->id]) }}"
                       class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        <i class="fa-solid fa-id-card mr-2"></i> Membership Payment
                    </a>
                @endif
                @if (Auth::user()->canViewFinancialRecords())
                    <a href="{{ route('reports.patient-statement', ['patient' => $patient, 'preset' => 'month']) }}"
                       class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        <i class="fa-solid fa-file-lines mr-2"></i> Statement
                    </a>
                @endif
                @if ($patient->isCompanyPatient() && $patient->company && Auth::user()->canAccessAccountsModules())
                    <a href="{{ route('company-accounts.show', $patient->company) }}"
                       class="inline-flex items-center rounded-lg bg-hospital-700 px-4 py-2 text-sm font-medium text-white hover:bg-hospital-800">
                        <i class="fa-solid fa-building mr-2"></i> Company Account
                    </a>
                @endif
                @if (Auth::user()->canManagePatientDemographics())
                    <a href="{{ route('patients.edit', $patient) }}"
                       class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        <i class="fa-solid fa-pen-to-square mr-2"></i> Edit
                    </a>
                @endif
                <a href="{{ route('patients.index') }}"
                   class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Back to List
                </a>
            </div>
        </div>
    </x-slot>

    <x-flash-messages />

    <div class="grid gap-6 lg:grid-cols-3">
        @if (Auth::user()->canViewFinancialRecords())
            {{-- Balance summary card --}}
            <div class="rounded-xl border border-hospital-200 bg-hospital-50 p-6 lg:col-span-1">
                <p class="text-sm font-medium text-hospital-700">Available Balance</p>
                <p class="mt-2 text-3xl font-bold text-hospital-900">K {{ number_format((float) $patient->effectiveBalance(), 2) }}</p>
                <p class="mt-2 text-sm text-hospital-700">
                    Charged to: <span class="font-medium">{{ $patient->effectiveBalanceOwnerLabel() }}</span>
                </p>
                @if ($patient->isMember())
                    <p class="mt-1 text-xs text-hospital-600">Member account balance</p>
                @elseif ($patient->isDependant())
                    <p class="mt-1 text-xs text-hospital-600">Deducted from principal member account</p>
                @else
                    <p class="mt-1 text-xs text-hospital-600">Deducted from company deposit pool</p>
                @endif
            </div>
        @endif

        {{-- Patient details --}}
        <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm {{ Auth::user()->canViewFinancialRecords() ? 'lg:col-span-2' : 'lg:col-span-3' }}">
            <h3 class="text-base font-semibold text-gray-800">Patient Details</h3>
            <dl class="mt-4 grid gap-4 sm:grid-cols-2 text-sm">
                <div>
                    <dt class="text-gray-500">Patient Number</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $patient->patient_number ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Full Name</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $patient->name }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">File Number</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $patient->file_number ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">NRC Number</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $patient->nrc_number ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Gender / Date of Birth</dt>
                    <dd class="mt-1 font-medium text-gray-900">
                        {{ $patient->gender ? ucfirst($patient->gender) : '—' }}
                        @if ($patient->date_of_birth)
                            · {{ $patient->date_of_birth->format('d M Y') }}
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-gray-500">Nationality</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $patient->nationality ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Marital Status</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $patient->marital_status ? ucfirst($patient->marital_status) : '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Phone Number</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $patient->phone_number ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Alternative Phone</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $patient->alternative_phone ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Email</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $patient->email ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Contact Address</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $patient->contact_address ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Town / City</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $patient->town_city ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Next of Kin</dt>
                    <dd class="mt-1 font-medium text-gray-900">
                        {{ $patient->next_of_kin_name ?? '—' }}
                        @if ($patient->next_of_kin_relationship)
                            <span class="text-gray-500">({{ $patient->next_of_kin_relationship }})</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-gray-500">Next of Kin Phone</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $patient->next_of_kin_phone ?? '—' }}</dd>
                </div>

                @if ($patient->isMember() || $patient->isDependant())
                    <div>
                        <dt class="text-gray-500">Membership Status</dt>
                        <dd class="mt-1">
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $patient->membershipStanding()->badgeClass() }}">
                                {{ $patient->membershipStanding()->label() }}
                            </span>
                        </dd>
                    </div>
                    @if ($patient->isMember())
                        <div>
                            <dt class="text-gray-500">Membership Number</dt>
                            <dd class="mt-1 font-medium text-gray-900">{{ $patient->membership?->membership_number ?? 'Pending' }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-gray-500">Valid Until</dt>
                        <dd class="mt-1 font-medium text-gray-900">
                            {{ $patient->membershipExpiryDate() ? \Illuminate\Support\Carbon::parse($patient->membershipExpiryDate())->format('d M Y') : '—' }}
                        </dd>
                    </div>
                @endif

                @if ($patient->isDependant())
                    <div>
                        <dt class="text-gray-500">Principal Member</dt>
                        <dd class="mt-1 font-medium text-gray-900">
                            @if ($patient->principalMember)
                                <a href="{{ route('patients.show', $patient->principalMember) }}" class="text-hospital-700 hover:underline">
                                    {{ $patient->principalMember->name }}
                                </a>
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Relationship</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $patient->relationship ?? '—' }}</dd>
                    </div>
                @endif

                @if ($patient->isCompanyPatient())
                    <div>
                        <dt class="text-gray-500">Company</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $patient->company?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Employee MAN Number</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $patient->man_number ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Department</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $patient->department ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Employment Status</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $patient->employment_status ?? '—' }}</dd>
                    </div>
                @endif

                @if ($patient->notes)
                    <div class="sm:col-span-2">
                        <dt class="text-gray-500">Notes</dt>
                        <dd class="mt-1 text-gray-900">{{ $patient->notes }}</dd>
                    </div>
                @endif
            </dl>
        </div>
    </div>

    @if ($patient->isMember() && $patient->dependants->isNotEmpty())
        <div class="mt-6 rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
            <h3 class="text-base font-semibold text-gray-800">Dependants</h3>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-left text-gray-500">
                            <th class="pb-2 font-medium">Name</th>
                            <th class="pb-2 font-medium">Relationship</th>
                            <th class="pb-2 font-medium">HC Number</th>
                            <th class="pb-2 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($patient->dependants as $dependant)
                            <tr>
                                <td class="py-2">
                                    <a href="{{ route('patients.show', $dependant) }}" class="text-hospital-700 hover:underline">{{ $dependant->name }}</a>
                                </td>
                                <td class="py-2 text-gray-700">{{ $dependant->relationship ?? '—' }}</td>
                                <td class="py-2 text-gray-700">{{ $dependant->hc_number ?? '—' }}</td>
                                <td class="py-2 text-gray-700">{{ $dependant->status->label() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (Auth::user()->canAccessAccountsModules())
        {{-- Recent financial activity --}}
        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <h3 class="text-base font-semibold text-gray-800">Recent Deposits</h3>
                @if ($patient->deposits->isEmpty())
                    <p class="mt-3 text-sm text-gray-500">No deposits recorded yet.</p>
                @else
                    <ul class="mt-3 divide-y divide-gray-50 text-sm">
                        @foreach ($patient->deposits as $deposit)
                            <li class="flex justify-between py-2">
                                <span>{{ $deposit->deposit_date->format('d M Y') }}</span>
                                <span class="font-medium">K {{ number_format((float) $deposit->amount, 2) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <h3 class="text-base font-semibold text-gray-800">Recent Bills</h3>
                @if ($patient->bills->isEmpty())
                    <p class="mt-3 text-sm text-gray-500">No bills recorded yet.</p>
                @else
                    <ul class="mt-3 divide-y divide-gray-50 text-sm">
                        @foreach ($patient->bills as $bill)
                            <li class="flex justify-between py-2">
                                <span>
                                    <a href="{{ route('billing.show', $bill) }}" class="text-hospital-700 hover:underline">
                                        {{ $bill->visit_date->format('d M Y') }} &middot; {{ $bill->visit_type->label() }}
                                    </a>
                                    @if ($bill->status === \App\Enums\BillStatus::Voided)
                                        <span class="text-xs text-red-600">(voided)</span>
                                    @endif
                                </span>
                                <span class="font-medium">K {{ number_format((float) $bill->total_amount, 2) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    @endif
</x-app-layout>
