<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Visit — {{ $visit->patient->name }}" :subtitle="$visit->visit_date->format('d M Y') . ' · Opened ' . $visit->created_at->format('d M Y, H:i') . ' · ' . $visit->visit_type->label()">
            <x-slot name="actions">
                <span class="badge {{ $visit->status->badgeClass() }}">{{ $visit->status->label() }}</span>
                <a href="{{ route('visits.index') }}" class="btn-ghost">&larr; All Visits</a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <x-flash-messages />

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="card card-body lg:col-span-1">
            <h3 class="section-title">Patient</h3>
            <dl class="mt-4 space-y-3 text-sm">
                <div>
                    <dt class="text-slate-500">Name</dt>
                    <dd class="font-medium"><a href="{{ route('patients.show', $visit->patient) }}" class="action-link">{{ $visit->patient->name }}</a></dd>
                </div>
                <div>
                    <dt class="text-slate-500">Membership</dt>
                    <dd>
                        @if ($visit->patient->isCompanyPatient())
                            <span class="text-slate-600">N/A (company patient)</span>
                        @elseif ($visit->patient->isDependant())
                            @php($principal = $visit->patient->principalMember)
                            <span class="badge {{ $principal?->membershipStanding()->badgeClass() ?? 'badge-neutral' }}">
                                {{ $principal?->membershipStanding()->label() ?? 'No principal member' }}
                            </span>
                            <span class="form-hint mt-1 block">Covered by {{ $principal?->name ?? 'principal member' }}</span>
                        @else
                            <span class="badge {{ $visit->patient->membershipStanding()->badgeClass() }}">
                                {{ $visit->patient->membershipStanding()->label() }}
                            </span>
                        @endif
                    </dd>
                </div>
                @if (Auth::user()->canManageVisits() || Auth::user()->canViewFinancialRecords())
                    <div>
                        <dt class="text-slate-500">Available Balance</dt>
                        <dd class="text-lg font-medium">K {{ number_format($availableBalance, 2) }}</dd>
                    </div>
                @endif
                <div>
                    <dt class="text-slate-500">Opened</dt>
                    <dd>{{ $visit->created_at->format('d M Y, H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Opened By</dt>
                    <dd>{{ $visit->openedBy->name }}</dd>
                </div>
            </dl>
        </div>

        <div class="card card-body lg:col-span-2">
            <div class="panel-header !px-0 !pt-0">
                <h3 class="section-title">Clinical Notes</h3>
                @if ($visit->canRecordClinicalNotes() && Auth::user()->canRecordClinicalNotes())
                    <a href="{{ route('clinical-notes.edit', $visit) }}" class="action-link">
                        {{ $visit->clinicalNote ? 'Edit Notes' : 'Record Notes' }}
                    </a>
                @endif
            </div>
            @if ($visit->clinicalNote)
                <dl class="mt-4 grid gap-4 sm:grid-cols-2 text-sm">
                    @foreach ([
                        'Complaint' => $visit->clinicalNote->complaint,
                        'Vitals' => $visit->clinicalNote->vitals,
                        'Examination' => $visit->clinicalNote->examination_findings,
                        'Diagnosis' => $visit->clinicalNote->diagnosis,
                        'Treatment' => $visit->clinicalNote->treatment_notes,
                        'Procedures' => $visit->clinicalNote->procedures_performed,
                        'Follow-up' => $visit->clinicalNote->follow_up_instructions,
                    ] as $label => $value)
                        @if ($value)
                            <div class="{{ $label === 'Follow-up' ? 'sm:col-span-2' : '' }}">
                                <dt class="text-slate-500">{{ $label }}</dt>
                                <dd class="mt-1 text-slate-900">{{ $value }}</dd>
                            </div>
                        @endif
                    @endforeach
                    <div class="sm:col-span-2 form-hint">
                        Recorded by {{ $visit->clinicalNote->recordedBy->name }}
                    </div>
                </dl>
            @else
                @if (Auth::user()->canRecordClinicalNotes() && ! $visit->canRecordClinicalNotes())
                    <p class="mt-4 text-sm text-slate-500">This visit is <span class="font-medium">{{ $visit->status->label() }}</span>. You can record notes once the patient is ready for consultation.</p>
                @else
                    <p class="mt-4 text-sm text-slate-500">No clinical notes recorded yet. The nurse documents the visit before charges are posted.</p>
                @endif
            @endif
        </div>
    </div>

    @if (Auth::user()->canManageVisits() || Auth::user()->canViewFinancialRecords())
    <div class="card card-body mt-6">
        <h3 class="section-title">Services & Charges</h3>

        @if ($visit->canAddCharges() && Auth::user()->canManageVisits())
            <form method="POST" action="{{ route('visits.charges.store', $visit) }}" class="mt-4 grid gap-4 md:grid-cols-3 border-b border-slate-100 pb-6">
                @csrf
                <div class="md:col-span-2">
                    <x-input-label for="billable_service_id" :value="__('Service')" />
                    <select id="billable_service_id" name="billable_service_id" required class="form-input mt-1">
                        @foreach ($billableServices as $service)
                            <option value="{{ $service->id }}">
                                {{ $service->name }} — {{ $service->category->label() }} — K {{ number_format((float) $service->price, 2) }}
                            </option>
                        @endforeach
                    </select>
                    <p class="form-hint mt-1">The clerk selects the service; the system applies the fixed catalogue price.</p>
                </div>
                <div class="flex items-end md:col-span-1">
                    <button type="submit" class="btn-primary w-full md:w-auto">
                        <i class="fa-solid fa-plus"></i> Add Charge
                    </button>
                </div>
            </form>
        @endif

        <x-table-scroll>
            <table class="data-table mt-4">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Description</th>
                        <th class="text-right">Amount</th>
                        @if ($visit->canAddCharges() && Auth::user()->canManageVisits())
                            <th class="text-right">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($visit->chargeLines as $line)
                        <tr>
                            <td>{{ $line->category->label() }}</td>
                            <td>{{ $line->description }}</td>
                            <td class="text-right font-medium">K {{ number_format((float) $line->amount, 2) }}</td>
                            @if ($visit->canAddCharges() && Auth::user()->canManageVisits())
                                <td class="text-right">
                                    <form method="POST" action="{{ route('visits.charges.destroy', [$visit, $line]) }}" class="inline"
                                          onsubmit="return confirm('Remove this charge?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="action-link text-red-600">Remove</button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="!py-6 text-center text-slate-500">No charges recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-slate-800">
                        <td colspan="2" class="pt-3 font-bold">TOTAL</td>
                        <td class="pt-3 text-right text-lg font-bold">K {{ number_format($visit->chargesTotal(), 2) }}</td>
                        @if ($visit->canAddCharges() && Auth::user()->canManageVisits())
                            <td></td>
                        @endif
                    </tr>
                </tfoot>
            </table>
        </x-table-scroll>

        @if (($visit->canAddCharges() || $visit->isOpen()) && Auth::user()->canManageVisits())
            <div class="panel-footer -mx-6 -mb-6 mt-6 flex flex-col gap-3 px-6 py-4 sm:flex-row sm:flex-wrap">
                @if ($visit->canAddCharges())
                <form method="POST" action="{{ route('visits.post-bill', $visit) }}" class="w-full sm:w-auto"
                      onsubmit="return confirm('Post bill and deduct balance? This will complete the visit.');">
                    @csrf
                    @if ($visit->chargesTotal() > $availableBalance)
                        <div class="mb-3 w-full rounded-lg border border-amber-200 bg-amber-50 p-4">
                            <p class="text-sm text-amber-800">Insufficient balance. Confirm to proceed.</p>
                            <label class="mt-2 flex items-center gap-2 text-sm">
                                <input type="checkbox" name="confirm_insufficient_balance" value="1" class="rounded border-slate-300 text-hospital-600">
                                I confirm billing with insufficient balance
                            </label>
                        </div>
                    @endif
                    <button type="submit" class="btn-primary w-full sm:w-auto"
                            @disabled($visit->chargeLines->isEmpty())>
                        <i class="fa-solid fa-file-invoice-dollar"></i> Post Bill & Finish Visit
                    </button>
                </form>
                @endif
                <form method="POST" action="{{ route('visits.cancel', $visit) }}" class="w-full sm:w-auto"
                      onsubmit="return confirm('Cancel this visit?');">
                    @csrf
                    <button type="submit" class="btn-danger w-full sm:w-auto">
                        Cancel Visit
                    </button>
                </form>
            </div>
        @elseif ($visit->bill)
            <div class="panel-footer -mx-6 -mb-6 mt-6 flex flex-col gap-2 px-6 py-4 sm:flex-row sm:gap-3">
                <a href="{{ route('billing.show', $visit->bill) }}" class="action-link">View Bill #{{ $visit->bill->id }}</a>
                @if (Auth::user()->canViewFinancialRecords())
                    <a href="{{ route('billing.receipt', $visit->bill) }}" class="action-link">Print Receipt</a>
                @endif
            </div>
        @endif
    </div>
    @endif
</x-app-layout>
