<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Visit — {{ $visit->patient->name }}</h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $visit->visit_date->format('d M Y') }} · {{ $visit->visit_type->label() }}
                    · <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $visit->status->badgeClass() }}">{{ $visit->status->label() }}</span>
                </p>
            </div>
            <a href="{{ route('visits.index') }}" class="text-sm text-hospital-700 hover:underline">&larr; All Visits</a>
        </div>
    </x-slot>

    <x-flash-messages />

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm lg:col-span-1">
            <h3 class="text-base font-semibold text-gray-800">Patient</h3>
            <dl class="mt-4 space-y-3 text-sm">
                <div>
                    <dt class="text-gray-500">Name</dt>
                    <dd class="font-medium"><a href="{{ route('patients.show', $visit->patient) }}" class="text-hospital-700 hover:underline">{{ $visit->patient->name }}</a></dd>
                </div>
                <div>
                    <dt class="text-gray-500">Membership</dt>
                    <dd>
                        @if ($visit->patient->isCompanyPatient())
                            <span class="text-gray-600">N/A (company patient)</span>
                        @elseif ($visit->patient->isDependant())
                            @php($principal = $visit->patient->principalMember)
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $principal?->membershipStanding()->badgeClass() ?? 'bg-gray-100 text-gray-600' }}">
                                {{ $principal?->membershipStanding()->label() ?? 'No principal member' }}
                            </span>
                            <span class="mt-1 block text-xs text-gray-500">Covered by {{ $principal?->name ?? 'principal member' }}</span>
                        @else
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $visit->patient->membershipStanding()->badgeClass() }}">
                                {{ $visit->patient->membershipStanding()->label() }}
                            </span>
                        @endif
                    </dd>
                </div>
                @if (Auth::user()->canManageVisits() || Auth::user()->canViewFinancialRecords())
                    <div>
                        <dt class="text-gray-500">Available Balance</dt>
                        <dd class="font-medium text-lg">K {{ number_format($availableBalance, 2) }}</dd>
                    </div>
                @endif
                <div>
                    <dt class="text-gray-500">Opened By</dt>
                    <dd>{{ $visit->openedBy->name }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm lg:col-span-2">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-800">Clinical Notes</h3>
                @if ($visit->canRecordClinicalNotes() && Auth::user()->canRecordClinicalNotes())
                    <a href="{{ route('clinical-notes.edit', $visit) }}" class="text-sm text-hospital-700 hover:underline">
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
                                <dt class="text-gray-500">{{ $label }}</dt>
                                <dd class="mt-1 text-gray-900">{{ $value }}</dd>
                            </div>
                        @endif
                    @endforeach
                    <div class="sm:col-span-2 text-xs text-gray-500">
                        Recorded by {{ $visit->clinicalNote->recordedBy->name }}
                    </div>
                </dl>
            @else
                @if (Auth::user()->canRecordClinicalNotes() && ! $visit->canRecordClinicalNotes())
                    <p class="mt-4 text-sm text-gray-500">This visit is <span class="font-medium">{{ $visit->status->label() }}</span>. You can record notes once the patient is ready for consultation.</p>
                @else
                    <p class="mt-4 text-sm text-gray-500">No clinical notes recorded yet. The nurse documents the visit before charges are posted.</p>
                @endif
            @endif
        </div>
    </div>

    @if (Auth::user()->canManageVisits() || Auth::user()->canViewFinancialRecords())
    <div class="mt-6 rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
        <h3 class="text-base font-semibold text-gray-800">Services & Charges</h3>

        @if ($visit->canAddCharges() && Auth::user()->canManageVisits())
            <form method="POST" action="{{ route('visits.charges.store', $visit) }}" class="mt-4 grid gap-4 md:grid-cols-3 border-b border-gray-100 pb-6">
                @csrf
                <div class="md:col-span-2">
                    <x-input-label for="billable_service_id" :value="__('Service')" />
                    <select id="billable_service_id" name="billable_service_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">
                        @foreach ($billableServices as $service)
                            <option value="{{ $service->id }}">
                                {{ $service->name }} — {{ $service->category->label() }} — K {{ number_format((float) $service->price, 2) }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">The clerk selects the service; the system applies the fixed catalogue price.</p>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="inline-flex items-center rounded-lg bg-hospital-700 px-4 py-2 text-sm font-medium text-white hover:bg-hospital-800">
                        <i class="fa-solid fa-plus mr-2"></i> Add Charge
                    </button>
                </div>
            </form>
        @endif

        <table class="mt-4 min-w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 text-left text-gray-500">
                    <th class="pb-2 font-medium">Category</th>
                    <th class="pb-2 font-medium">Description</th>
                    <th class="pb-2 font-medium text-right">Amount</th>
                    @if ($visit->canAddCharges() && Auth::user()->canManageVisits())
                        <th class="pb-2 font-medium text-right">Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($visit->chargeLines as $line)
                    <tr>
                        <td class="py-2">{{ $line->category->label() }}</td>
                        <td class="py-2">{{ $line->description }}</td>
                        <td class="py-2 text-right font-medium">K {{ number_format((float) $line->amount, 2) }}</td>
                        @if ($visit->canAddCharges() && Auth::user()->canManageVisits())
                            <td class="py-2 text-right">
                                <form method="POST" action="{{ route('visits.charges.destroy', [$visit, $line]) }}" class="inline"
                                      onsubmit="return confirm('Remove this charge?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline text-xs">Remove</button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-6 text-center text-gray-500">No charges recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="border-t-2 border-gray-800">
                    <td colspan="2" class="pt-3 font-bold">TOTAL</td>
                    <td class="pt-3 text-right font-bold text-lg">K {{ number_format($visit->chargesTotal(), 2) }}</td>
                    @if ($visit->canAddCharges() && Auth::user()->canManageVisits())
                        <td></td>
                    @endif
                </tr>
            </tfoot>
        </table>

        @if (($visit->canAddCharges() || $visit->isOpen()) && Auth::user()->canManageVisits())
            <div class="mt-6 flex flex-wrap gap-3 border-t border-gray-100 pt-6">
                @if ($visit->canAddCharges())
                <form method="POST" action="{{ route('visits.post-bill', $visit) }}" class="inline"
                      onsubmit="return confirm('Post bill and deduct balance? This will complete the visit.');">
                    @csrf
                    @if ($visit->chargesTotal() > $availableBalance)
                        <div class="mb-3 rounded-lg border border-amber-200 bg-amber-50 p-4 w-full">
                            <p class="text-sm text-amber-800">Insufficient balance. Confirm to proceed.</p>
                            <label class="mt-2 flex items-center gap-2 text-sm">
                                <input type="checkbox" name="confirm_insufficient_balance" value="1" class="rounded border-gray-300 text-hospital-600">
                                I confirm billing with insufficient balance
                            </label>
                        </div>
                    @endif
                    <button type="submit" class="inline-flex items-center rounded-lg bg-hospital-700 px-4 py-2 text-sm font-medium text-white hover:bg-hospital-800"
                            @disabled($visit->chargeLines->isEmpty())>
                        <i class="fa-solid fa-file-invoice-dollar mr-2"></i> Post Bill & Finish Visit
                    </button>
                </form>
                @endif
                <form method="POST" action="{{ route('visits.cancel', $visit) }}" class="inline"
                      onsubmit="return confirm('Cancel this visit?');">
                    @csrf
                    <button type="submit" class="inline-flex items-center rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50">
                        Cancel Visit
                    </button>
                </form>
            </div>
        @elseif ($visit->bill)
            <div class="mt-6 flex gap-3 border-t border-gray-100 pt-6">
                <a href="{{ route('billing.show', $visit->bill) }}" class="text-sm text-hospital-700 hover:underline">View Bill #{{ $visit->bill->id }}</a>
                @if (Auth::user()->canViewFinancialRecords())
                    <a href="{{ route('billing.receipt', $visit->bill) }}" class="text-sm text-hospital-700 hover:underline">Print Receipt</a>
                @endif
            </div>
        @endif
    </div>
    @endif
</x-app-layout>
