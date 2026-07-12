<x-app-layout>
    <x-slot name="header">
        <x-page-header title="{{ $patient->name }}" subtitle="{{ $patient->type->label() }} · {{ $patient->status->label() }}">
            <x-slot name="actions">
                @if (Auth::user()->canManageVisits() && ! $patient->openVisit())
                    <a href="{{ route('visits.create', ['patient_id' => $patient->id]) }}" class="btn-primary">
                        <i class="fa-solid fa-door-open"></i> Open Visit
                    </a>
                @elseif ($patient->openVisit() && (Auth::user()->canManageVisits() || Auth::user()->canRecordClinicalNotes()))
                    <a href="{{ route('visits.show', $patient->openVisit()) }}" class="btn-primary">
                        <i class="fa-solid fa-folder-open"></i> Active Visit
                    </a>
                @endif
                @if ($patient->isMember() && Auth::user()->canPerformFinancialOperations())
                    <a href="{{ route('deposits.create', ['patient_id' => $patient->id]) }}" class="btn-primary">
                        <i class="fa-solid fa-money-bill-wave"></i> Load Deposit
                    </a>
                @endif
                @if (($patient->isMember() || $patient->isDependant()) && Auth::user()->canPerformFinancialOperations())
                    <a href="{{ route('membership-fees.create', ['patient_id' => $patient->id]) }}" class="btn-secondary">
                        <i class="fa-solid fa-id-card"></i> Membership Payment
                    </a>
                @endif
                @if (Auth::user()->canViewFinancialRecords() && ! $patient->isCashPatient())
                    <a href="{{ route('reports.patient-statement', ['patient' => $patient, 'preset' => 'month']) }}" class="btn-secondary">
                        <i class="fa-solid fa-file-lines"></i> Statement
                    </a>
                @endif
                @if ($patient->isCompanyPatient() && $patient->company && Auth::user()->canAccessAccountsModules())
                    <a href="{{ route('company-accounts.show', $patient->company) }}" class="btn-primary">
                        <i class="fa-solid fa-building"></i> Company Account
                    </a>
                @endif
                @if (Auth::user()->canManagePatientDemographics())
                    <a href="{{ route('patients.edit', $patient) }}" class="btn-secondary">
                        <i class="fa-solid fa-pen-to-square"></i> Edit
                    </a>
                @endif
                <a href="{{ route('patients.index') }}" class="btn-secondary">
                    <i class="fa-solid fa-arrow-left"></i> Back to List
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <x-flash-messages />

    <div class="grid gap-6 lg:grid-cols-3">
        @if (Auth::user()->canViewFinancialRecords())
            @if ($patient->isCashPatient())
                <div class="card card-body border-sky-200 bg-sky-50 lg:col-span-1">
                    <p class="section-subtitle text-sky-700">Payment Type</p>
                    <p class="mt-2 text-2xl font-bold text-sky-900">Pay As You Go</p>
                    <p class="mt-2 text-sm text-sky-800">No deposit account — patient pays at Accounts after each visit.</p>
                    @if ($patient->outstandingBillTotal() > 0)
                        <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-3">
                            <p class="text-sm font-medium text-amber-800">Outstanding Bill</p>
                            <p class="mt-1 text-xl font-bold text-amber-900">K {{ number_format($patient->outstandingBillTotal(), 2) }}</p>
                        </div>
                    @endif
                </div>
            @else
                <div class="card card-body border-hospital-200 bg-hospital-50 lg:col-span-1">
                    <p class="section-subtitle text-hospital-700">Available Balance</p>
                    <p class="mt-2 text-3xl font-bold text-hospital-900">K {{ number_format((float) $patient->effectiveBalance(), 2) }}</p>
                    <p class="mt-2 text-sm text-hospital-700">
                        Charged to: <span class="font-medium">{{ $patient->effectiveBalanceOwnerLabel() }}</span>
                    </p>
                    @if ($patient->isMember())
                        <p class="form-hint mt-1">Member account balance</p>
                    @elseif ($patient->isDependant())
                        <p class="form-hint mt-1">Deducted from principal member account</p>
                    @else
                        <p class="form-hint mt-1">Deducted from company deposit pool</p>
                    @endif
                </div>
            @endif
        @endif

        <div class="card card-body {{ Auth::user()->canViewFinancialRecords() ? 'lg:col-span-2' : 'lg:col-span-3' }}">
            <h3 class="section-title">Patient Details</h3>
            <dl class="mt-4 grid gap-4 sm:grid-cols-2 text-sm">
                <div>
                    <dt class="text-slate-500">Patient Number</dt>
                    <dd class="mt-1 font-medium">{{ $patient->patient_number ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Full Name</dt>
                    <dd class="mt-1 font-medium">{{ $patient->name }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Hospital File Number</dt>
                    <dd class="mt-1 font-medium">{{ $patient->file_number ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">NRC Number</dt>
                    <dd class="mt-1 font-medium">{{ $patient->nrc_number ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Gender / Date of Birth</dt>
                    <dd class="mt-1 font-medium">
                        {{ $patient->gender ? ucfirst($patient->gender) : '—' }}
                        @if ($patient->date_of_birth)
                            · {{ $patient->date_of_birth->format('d M Y') }}
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500">Nationality</dt>
                    <dd class="mt-1 font-medium">{{ $patient->nationality ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Marital Status</dt>
                    <dd class="mt-1 font-medium">{{ $patient->marital_status ? ucfirst($patient->marital_status) : '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Phone Number</dt>
                    <dd class="mt-1 font-medium">{{ $patient->phone_number ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Alternative Phone</dt>
                    <dd class="mt-1 font-medium">{{ $patient->alternative_phone ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Email</dt>
                    <dd class="mt-1 font-medium">{{ $patient->email ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Contact Address</dt>
                    <dd class="mt-1 font-medium">{{ $patient->contact_address ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Town / City</dt>
                    <dd class="mt-1 font-medium">{{ $patient->town_city ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Next of Kin</dt>
                    <dd class="mt-1 font-medium">
                        {{ $patient->next_of_kin_name ?? '—' }}
                        @if ($patient->next_of_kin_relationship)
                            <span class="text-slate-500">({{ $patient->next_of_kin_relationship }})</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500">Next of Kin Phone</dt>
                    <dd class="mt-1 font-medium">{{ $patient->next_of_kin_phone ?? '—' }}</dd>
                </div>

                @if ($patient->isCashPatient())
                    <div>
                        <dt class="text-slate-500">Payment Type</dt>
                        <dd class="mt-1 font-medium">Pay As You Go</dd>
                    </div>
                @endif

                @if ($patient->isMember() || $patient->isDependant())
                    <div>
                        <dt class="text-slate-500">Membership Status</dt>
                        <dd class="mt-1">
                            <span class="badge {{ $patient->membershipStanding()->badgeClass() }}">
                                {{ $patient->membershipStanding()->label() }}
                            </span>
                        </dd>
                    </div>
                    @if ($patient->isMember() || $patient->isDependant())
                        <div>
                            <dt class="text-slate-500">Membership Number</dt>
                            <dd class="mt-1 font-medium">
                                {{ $patient->effectiveMembershipNumber() ?? 'Pending' }}
                                @if ($patient->isDependant())
                                    <span class="block text-xs text-slate-500">Principal member's membership</span>
                                @endif
                            </dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-slate-500">Valid Until</dt>
                        <dd class="mt-1 font-medium">
                            {{ $patient->membershipExpiryDate() ? \Illuminate\Support\Carbon::parse($patient->membershipExpiryDate())->format('d M Y') : '—' }}
                        </dd>
                    </div>
                @endif

                @if ($patient->isDependant())
                    <div>
                        <dt class="text-slate-500">Principal Member</dt>
                        <dd class="mt-1 font-medium">
                            @if ($patient->principalMember)
                                <a href="{{ route('patients.show', $patient->principalMember) }}" class="action-link">
                                    {{ $patient->principalMember->name }}
                                </a>
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Relationship</dt>
                        <dd class="mt-1 font-medium">{{ $patient->relationship ?? '—' }}</dd>
                    </div>
                @endif

                @if ($patient->isCompanyPatient())
                    <div>
                        <dt class="text-slate-500">Company</dt>
                        <dd class="mt-1 font-medium">{{ $patient->company?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Employee MAN Number</dt>
                        <dd class="mt-1 font-medium">{{ $patient->man_number ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Department</dt>
                        <dd class="mt-1 font-medium">{{ $patient->department ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Employment Status</dt>
                        <dd class="mt-1 font-medium">{{ $patient->employment_status ?? '—' }}</dd>
                    </div>
                @endif

                @if ($patient->notes)
                    <div class="sm:col-span-2">
                        <dt class="text-slate-500">Notes</dt>
                        <dd class="mt-1 text-slate-900">{{ $patient->notes }}</dd>
                    </div>
                @endif
            </dl>
        </div>
    </div>

    @if ($patient->isMember() && $patient->dependants->isNotEmpty())
        <x-data-panel title="Dependants" class="mt-6">
            <x-table-scroll>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Relationship</th>
                            <th>HC Number</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($patient->dependants as $dependant)
                            <tr>
                                <td>
                                    <a href="{{ route('patients.show', $dependant) }}" class="action-link">{{ $dependant->name }}</a>
                                </td>
                                <td>{{ $dependant->relationship ?? '—' }}</td>
                                <td>{{ $dependant->effectiveMembershipNumber() ?? '—' }}</td>
                                <td>{{ $dependant->status->label() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-table-scroll>
        </x-data-panel>
    @endif

    @if ($patient->isCashPatient() && $visitHistory?->isNotEmpty())
        <x-data-panel title="Visit History" class="mt-6">
            <x-table-scroll>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Visit</th>
                            <th class="text-right">Bill</th>
                            <th class="text-right">Payment</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($visitHistory as $visit)
                            @php($bill = $visit->bill)
                            <tr>
                                <td>{{ $visit->visit_date->format('d M Y') }}</td>
                                <td>{{ $visit->visit_type->label() }}</td>
                                <td class="text-right">
                                    @if ($bill)
                                        <a href="{{ route('billing.show', $bill) }}" class="action-link">
                                            K {{ number_format((float) $bill->total_amount, 2) }}
                                        </a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if ($bill?->isPaid())
                                        K {{ number_format((float) $bill->total_amount, 2) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    @if ($bill?->isPaid())
                                        <span class="badge badge-success">Paid</span>
                                    @elseif ($bill?->isOutstanding())
                                        <span class="badge badge-warning">Awaiting Payment</span>
                                    @elseif ($visit->status === \App\Enums\VisitStatus::Cancelled)
                                        <span class="badge badge-neutral">Cancelled</span>
                                    @else
                                        <span class="badge badge-neutral">{{ $visit->status->label() }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-table-scroll>
        </x-data-panel>
    @endif

    @if (Auth::user()->canAccessAccountsModules() && ! $patient->isCashPatient())
        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            <div class="card card-body">
                <h3 class="section-title">Recent Deposits</h3>
                @if ($patient->deposits->isEmpty())
                    <p class="mt-3 text-sm text-slate-500">No deposits recorded yet.</p>
                @else
                    <ul class="list-card mt-3 text-sm">
                        @foreach ($patient->deposits as $deposit)
                            <li class="flex justify-between py-2">
                                <span>{{ $deposit->deposit_date->format('d M Y') }}</span>
                                <span class="font-medium">K {{ number_format((float) $deposit->amount, 2) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="card card-body">
                <h3 class="section-title">Recent Bills</h3>
                @if ($patient->bills->isEmpty())
                    <p class="mt-3 text-sm text-slate-500">No bills recorded yet.</p>
                @else
                    <ul class="list-card mt-3 text-sm">
                        @foreach ($patient->bills as $bill)
                            <li class="flex justify-between py-2">
                                <span>
                                    <a href="{{ route('billing.show', $bill) }}" class="action-link">
                                        {{ $bill->visit_date->format('d M Y') }} · {{ $bill->visit_type->label() }}
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
