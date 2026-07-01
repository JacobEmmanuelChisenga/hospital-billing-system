<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Record Bill</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $patient->name }} — {{ $patient->type->label() }}</p>
            </div>
            <a href="{{ route('billing.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                <i class="fa-solid fa-arrow-left mr-2"></i> Back to Search
            </a>
        </div>
    </x-slot>

    <x-flash-messages />

    {{-- Patient and balance summary --}}
    <div class="mb-6 grid gap-4 lg:grid-cols-3">
        <div @class([
            'rounded-xl border p-5 lg:col-span-1',
            'border-amber-200 bg-amber-50' => $availableBalance < $lowBalanceThreshold,
            'border-hospital-200 bg-hospital-50' => $availableBalance >= $lowBalanceThreshold,
        ])>
            <p class="text-sm font-medium text-gray-600">Available Balance</p>
            <p class="mt-1 text-2xl font-bold text-gray-900">K {{ number_format($availableBalance, 2) }}</p>
            <p class="mt-1 text-sm text-gray-600">Payer: {{ $patient->effectiveBalanceOwnerLabel() }}</p>
            @if ($availableBalance < $lowBalanceThreshold)
                <p class="mt-2 text-sm font-medium text-amber-800">
                    <i class="fa-solid fa-triangle-exclamation mr-1"></i> Low balance warning
                </p>
            @endif
            @if ($patient->isDependant() && $patient->principalMember)
                <p class="mt-1 text-xs text-gray-500">Patient: {{ $patient->name }} ({{ $patient->relationship }})</p>
            @endif
        </div>

        <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm lg:col-span-2 text-sm">
            <dl class="grid gap-3 sm:grid-cols-2">
                <div>
                    <dt class="text-gray-500">HC Number</dt>
                    <dd class="font-medium">{{ $patient->hc_number ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">File Number</dt>
                    <dd class="font-medium">{{ $patient->file_number ?? '—' }}</dd>
                </div>
                @if ($patient->isCompanyPatient())
                    <div class="sm:col-span-2">
                        <dt class="text-gray-500">Company</dt>
                        <dd class="font-medium">{{ $patient->company?->name ?? '—' }}</dd>
                    </div>
                @endif
            </dl>
        </div>
    </div>

    <div class="max-w-3xl rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('billing.store') }}"
              x-data="{
                  consultation: @js(old('consultation_amount', '')),
                  pharmacy: @js(old('pharmacy_amount', '')),
                  lab: @js(old('lab_amount', '')),
                  ward: @js(old('ward_amount', '')),
                  other: @js(old('other_amount', '')),
                  available: @js($availableBalance),
                  threshold: @js($lowBalanceThreshold),
                  get total() {
                      return (parseFloat(this.consultation) || 0)
                           + (parseFloat(this.pharmacy) || 0)
                           + (parseFloat(this.lab) || 0)
                           + (parseFloat(this.ward) || 0)
                           + (parseFloat(this.other) || 0);
                  },
                  get balanceAfter() { return this.available - this.total; },
                  get isLowAfter() { return this.balanceAfter < this.threshold; },
                  get isInsufficient() { return this.total > this.available; }
              }"
              class="space-y-6">
            @csrf
            <input type="hidden" name="patient_id" value="{{ $patient->id }}">

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="visit_date" :value="__('Visit Date')" />
                    <x-text-input id="visit_date" name="visit_date" type="date" class="mt-1 block w-full"
                        :value="old('visit_date', now()->toDateString())" required />
                    <x-input-error :messages="$errors->get('visit_date')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="visit_type" :value="__('Visit Type')" />
                    <select id="visit_type" name="visit_type" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">
                        @foreach (\App\Enums\VisitType::cases() as $visitType)
                            <option value="{{ $visitType->value }}" @selected(old('visit_type') === $visitType->value)>{{ $visitType->label() }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('visit_type')" class="mt-2" />
                </div>
                <div class="sm:col-span-2">
                    <x-input-label for="ward_bed" :value="__('Ward / Bed')" />
                    <x-text-input id="ward_bed" name="ward_bed" type="text" class="mt-1 block w-full"
                        :value="old('ward_bed')" placeholder="e.g. Ward 3, Bed 12" />
                </div>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-800 mb-3">Itemised Charges (K)</h3>
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ([
                        'consultation_amount' => 'Consultation',
                        'pharmacy_amount' => 'Pharmacy',
                        'lab_amount' => 'Lab / Investigations',
                        'ward_amount' => 'Ward / Bed',
                        'other_amount' => 'Other',
                    ] as $field => $label)
                        <div>
                            <x-input-label :for="$field" :value="$label" />
                            <x-text-input :id="$field" :name="$field" type="number" step="0.01" min="0"
                                x-model="{{ str_replace('_amount', '', $field) }}"
                                class="mt-1 block w-full" :value="old($field)" />
                            <x-input-error :messages="$errors->get($field)" class="mt-2" />
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Live total and balance warnings --}}
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="font-medium text-gray-700">Bill Total</span>
                    <span class="font-bold text-gray-900" x-text="'K ' + total.toFixed(2)"></span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Balance after bill</span>
                    <span class="font-medium" :class="isLowAfter ? 'text-amber-700' : 'text-gray-900'"
                          x-text="'K ' + balanceAfter.toFixed(2)"></span>
                </div>
                <p x-show="isLowAfter && !isInsufficient" x-cloak class="text-sm text-amber-700">
                    <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                    Balance will fall below the low-balance threshold (K {{ number_format($lowBalanceThreshold, 2) }}).
                </p>
                <div x-show="isInsufficient" x-cloak class="rounded border border-red-200 bg-red-50 p-3 mt-2">
                    <p class="text-sm font-medium text-red-800">Insufficient balance</p>
                    <p class="text-sm text-red-700 mt-1">The bill exceeds the available balance. Confirm to proceed anyway.</p>
                    <label class="mt-2 flex items-start gap-2">
                        <input type="checkbox" name="confirm_insufficient_balance" value="1"
                            @checked(old('confirm_insufficient_balance'))
                            class="mt-1 rounded border-gray-300 text-hospital-600 focus:ring-hospital-500">
                        <span class="text-sm text-red-900">I confirm billing despite insufficient balance.</span>
                    </label>
                    <x-input-error :messages="$errors->get('confirm_insufficient_balance')" class="mt-2" />
                </div>
            </div>

            <div>
                <x-input-label for="notes" :value="__('Notes')" />
                <textarea id="notes" name="notes" rows="2"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">{{ old('notes') }}</textarea>
            </div>

            <div class="flex items-center gap-3 border-t border-gray-100 pt-6">
                <button type="submit" class="inline-flex items-center rounded-lg bg-hospital-700 px-4 py-2 text-sm font-medium text-white hover:bg-hospital-800">
                    <i class="fa-solid fa-file-invoice-dollar mr-2"></i> Post Bill &amp; Print Receipt
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
