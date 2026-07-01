<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Load Member Deposit</h2>
            <p class="mt-1 text-sm text-gray-500">Add funds to a member's high-cost account balance.</p>
        </div>
    </x-slot>

    <x-flash-messages />

    <div class="max-w-2xl rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('deposits.store') }}"
              x-data="{ amount: @js(old('amount', '')), threshold: @js($largeDepositThreshold) }"
              class="space-y-6">
            @csrf

            <div>
                <x-input-label for="patient_id" :value="__('Member Account')" />
                <select id="patient_id" name="patient_id" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">
                    <option value="">Select member...</option>
                    @foreach ($members as $member)
                        <option value="{{ $member->id }}" @selected((string) old('patient_id', $selectedPatientId) === (string) $member->id)>
                            {{ $member->name }}
                            @if ($member->hc_number) ({{ $member->hc_number }}) @endif
                            — Balance: K {{ number_format((float) $member->balance, 2) }}
                        </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('patient_id')" class="mt-2" />
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <x-input-label for="amount" :value="__('Amount (K)')" />
                    <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01"
                        x-model="amount"
                        class="mt-1 block w-full" :value="old('amount')" required />
                    <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="deposit_date" :value="__('Deposit Date')" />
                    <x-text-input id="deposit_date" name="deposit_date" type="date"
                        class="mt-1 block w-full" :value="old('deposit_date', now()->toDateString())" required />
                    <x-input-error :messages="$errors->get('deposit_date')" class="mt-2" />
                </div>
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

            <div>
                <x-input-label for="reference" :value="__('Reference / Receipt No.')" />
                <x-text-input id="reference" name="reference" type="text" class="mt-1 block w-full"
                    :value="old('reference')" placeholder="Optional reference number" />
                <x-input-error :messages="$errors->get('reference')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="notes" :value="__('Notes')" />
                <textarea id="notes" name="notes" rows="2"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">{{ old('notes') }}</textarea>
                <x-input-error :messages="$errors->get('notes')" class="mt-2" />
            </div>

            {{-- Staff must confirm deposits at or above the configured threshold. --}}
            <div x-show="parseFloat(amount) >= threshold" x-cloak
                 class="rounded-lg border border-amber-200 bg-amber-50 p-4">
                <p class="text-sm font-medium text-amber-800">
                    <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                    Large deposit — please confirm
                </p>
                <p class="mt-1 text-sm text-amber-700">
                    This amount is at or above K {{ number_format($largeDepositThreshold, 2) }}. Tick the box below to confirm.
                </p>
                <label class="mt-3 flex items-start gap-2">
                    <input type="checkbox" name="confirm_large_deposit" value="1"
                        @checked(old('confirm_large_deposit'))
                        class="mt-1 rounded border-gray-300 text-hospital-600 focus:ring-hospital-500">
                    <span class="text-sm text-amber-900">I confirm this large deposit amount is correct.</span>
                </label>
                <x-input-error :messages="$errors->get('confirm_large_deposit')" class="mt-2" />
            </div>

            <div class="flex items-center gap-3 border-t border-gray-100 pt-6">
                <button type="submit" class="inline-flex items-center rounded-lg bg-hospital-700 px-4 py-2 text-sm font-medium text-white hover:bg-hospital-800">
                    <i class="fa-solid fa-money-bill-wave mr-2"></i> Load Deposit
                </button>
                <a href="{{ route('deposits.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
