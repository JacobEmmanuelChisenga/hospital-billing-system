<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Deposit Details</h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $deposit->patient->name }} &middot; {{ $deposit->deposit_date->format('d M Y') }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('deposits.receipt', $deposit) }}"
                   class="inline-flex items-center rounded-lg bg-hospital-700 px-4 py-2 text-sm font-medium text-white hover:bg-hospital-800">
                    <i class="fa-solid fa-print mr-2"></i> Print Receipt
                </a>
                <a href="{{ route('deposits.index') }}"
                   class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Back to Deposits
                </a>
            </div>
        </div>
    </x-slot>

    <x-flash-messages />

    <div class="grid gap-6 lg:grid-cols-3">
        <div @class([
            'rounded-xl border p-6 lg:col-span-1',
            'border-green-200 bg-green-50' => ! $deposit->isReversed(),
            'border-red-200 bg-red-50' => $deposit->isReversed(),
        ])>
            <p class="text-sm font-medium text-gray-600">Deposit Amount</p>
            <p class="mt-2 text-3xl font-bold text-gray-900">K {{ number_format((float) $deposit->amount, 2) }}</p>
            <p class="mt-2 text-sm">
                @if ($deposit->isReversed())
                    <span class="font-medium text-red-800">Reversed</span>
                @else
                    <span class="font-medium text-green-800">Active</span>
                @endif
            </p>
            <p class="mt-3 text-sm text-gray-600">
                Member balance after deposit:
                <span class="font-medium">K {{ number_format((float) $deposit->patient->balance, 2) }}</span>
            </p>
        </div>

        <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm lg:col-span-2">
            <h3 class="text-base font-semibold text-gray-800">Deposit Information</h3>
            <dl class="mt-4 grid gap-4 sm:grid-cols-2 text-sm">
                <div>
                    <dt class="text-gray-500">Member</dt>
                    <dd class="mt-1 font-medium">
                        <a href="{{ route('patients.show', $deposit->patient) }}" class="text-hospital-700 hover:underline">
                            {{ $deposit->patient->name }}
                        </a>
                    </dd>
                </div>
                <div>
                    <dt class="text-gray-500">Deposit Date</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $deposit->deposit_date->format('d M Y') }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Payment Method</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $deposit->payment_method?->label() ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Reference</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $deposit->reference ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Loaded By</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $deposit->createdBy->name }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Recorded At</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $deposit->created_at->format('d M Y H:i') }}</dd>
                </div>
                @if ($deposit->notes)
                    <div class="sm:col-span-2">
                        <dt class="text-gray-500">Notes</dt>
                        <dd class="mt-1 text-gray-900">{{ $deposit->notes }}</dd>
                    </div>
                @endif
                @if ($deposit->isReversed())
                    <div class="sm:col-span-2 border-t border-gray-100 pt-4">
                        <dt class="text-gray-500">Reversal Reason</dt>
                        <dd class="mt-1 text-gray-900">{{ $deposit->reversal_reason }}</dd>
                        <p class="mt-2 text-xs text-gray-500">
                            Reversed by {{ $deposit->reversedBy?->name }} on {{ $deposit->reversed_at->format('d M Y H:i') }}
                        </p>
                    </div>
                @endif
            </dl>
        </div>
    </div>

    @if (! $deposit->isReversed())
        <div class="mt-6 max-w-2xl rounded-xl border border-red-100 bg-white p-6 shadow-sm">
            <h3 class="text-base font-semibold text-red-800">Reverse Deposit</h3>
            <p class="mt-1 text-sm text-gray-600">
                Reversing will deduct K {{ number_format((float) $deposit->amount, 2) }} from the member's balance.
                The deposit record is kept for audit purposes.
            </p>
            <form method="POST" action="{{ route('deposits.reverse', $deposit) }}" class="mt-4 space-y-4"
                  onsubmit="return confirm('Are you sure you want to reverse this deposit? The member balance will be reduced.');">
                @csrf
                <div>
                    <x-input-label for="reversal_reason" :value="__('Reason for Reversal')" />
                    <textarea id="reversal_reason" name="reversal_reason" rows="3" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500"
                        placeholder="Explain why this deposit is being reversed (min. 10 characters)...">{{ old('reversal_reason') }}</textarea>
                    <x-input-error :messages="$errors->get('reversal_reason')" class="mt-2" />
                </div>
                <button type="submit" class="inline-flex items-center rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                    <i class="fa-solid fa-rotate-left mr-2"></i> Reverse Deposit
                </button>
            </form>
        </div>
    @endif
</x-app-layout>
