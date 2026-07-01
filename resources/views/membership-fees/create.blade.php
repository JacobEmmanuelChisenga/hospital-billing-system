<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Record Membership Payment</h2>
            <p class="mt-1 text-sm text-gray-500">Receive a scheme membership / subscription fee and activate membership. This does not add spendable balance.</p>
        </div>
    </x-slot>

    <x-flash-messages />

    <div class="max-w-2xl rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('membership-fees.store') }}" class="space-y-6">
            @csrf

            <div>
                <x-input-label for="patient_id" :value="__('Member or Dependant')" />
                <select id="patient_id" name="patient_id" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">
                    <option value="">Select member or dependant...</option>
                    @foreach ($patients as $patient)
                        <option value="{{ $patient->id }}" @selected((string) old('patient_id', $selectedPatientId) === (string) $patient->id)>
                            {{ $patient->name }}
                            @if ($patient->membership) ({{ $patient->membership->membership_number }}) @endif
                            — {{ $patient->type->label() }}
                            @if ($patient->isDependant() && $patient->principalMember)
                                of {{ $patient->principalMember->name }}
                            @endif
                        </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('patient_id')" class="mt-2" />
                <p class="mt-1 text-xs text-gray-500">Members joining the scheme and dependants both pay membership fees.</p>
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <x-input-label for="amount" :value="__('Amount (K)')" />
                    <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01"
                        class="mt-1 block w-full" :value="old('amount')" required />
                    <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="payment_method" :value="__('Payment Method')" />
                    <select id="payment_method" name="payment_method" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">
                        @foreach ($paymentMethods as $value => $label)
                            <option value="{{ $value }}" @selected(old('payment_method', 'cash') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('payment_method')" class="mt-2" />
                </div>
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <x-input-label for="payment_date" :value="__('Payment Date')" />
                    <x-text-input id="payment_date" name="payment_date" type="date"
                        class="mt-1 block w-full" :value="old('payment_date', now()->toDateString())" required />
                    <x-input-error :messages="$errors->get('payment_date')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="reference" :value="__('Reference / Receipt No.')" />
                    <x-text-input id="reference" name="reference" type="text"
                        class="mt-1 block w-full" :value="old('reference')" placeholder="Optional reference number" />
                    <x-input-error :messages="$errors->get('reference')" class="mt-2" />
                </div>
            </div>

            <div>
                <x-input-label for="expiry_date" :value="__('Valid Until (Expiry Date)')" />
                <x-text-input id="expiry_date" name="expiry_date" type="date"
                    class="mt-1 block w-full" :value="old('expiry_date', now()->addYear()->toDateString())" required />
                <p class="mt-1 text-xs text-gray-500">Membership status will be active until this date. Typically one year from payment.</p>
                <x-input-error :messages="$errors->get('expiry_date')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="notes" :value="__('Notes')" />
                <textarea id="notes" name="notes" rows="2"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">{{ old('notes') }}</textarea>
                <x-input-error :messages="$errors->get('notes')" class="mt-2" />
            </div>

            <div class="flex items-center gap-3 border-t border-gray-100 pt-6">
                <button type="submit" class="inline-flex items-center rounded-lg bg-hospital-700 px-4 py-2 text-sm font-medium text-white hover:bg-hospital-800">
                    <i class="fa-solid fa-id-card mr-2"></i> Record Payment
                </button>
                <a href="{{ route('membership-fees.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
