<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Bill #{{ $bill->id }}</h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $bill->patient->name }} &middot; {{ $bill->visit_date->format('d M Y') }}
                    @if ($bill->isVoided())
                        <span class="text-red-600 font-medium">(VOIDED)</span>
                    @endif
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if (! $bill->isVoided())
                    <a href="{{ route('billing.receipt', $bill) }}" target="_blank"
                       class="inline-flex items-center rounded-lg bg-hospital-700 px-4 py-2 text-sm font-medium text-white hover:bg-hospital-800">
                        <i class="fa-solid fa-print mr-2"></i> Print Receipt
                    </a>
                @endif
                <a href="{{ route('billing.index') }}"
                   class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Billing
                </a>
            </div>
        </div>
    </x-slot>

    <x-flash-messages />

    <div class="grid gap-6 lg:grid-cols-3">
        <div @class([
            'rounded-xl border p-6',
            'border-red-200 bg-red-50' => $bill->isVoided(),
            'border-hospital-200 bg-hospital-50' => ! $bill->isVoided(),
        ])>
            <p class="text-sm font-medium text-gray-600">Bill Total</p>
            <p class="mt-2 text-3xl font-bold text-gray-900">K {{ number_format((float) $bill->total_amount, 2) }}</p>
            <p class="mt-2 text-sm text-gray-600">Charged to: {{ $bill->payerName() }}</p>
            <p class="mt-1 text-sm text-gray-600">
                Remaining balance: K {{ number_format($bill->payerBalanceAfter(), 2) }}
            </p>
        </div>

        <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm lg:col-span-2">
            <h3 class="text-base font-semibold text-gray-800">Bill Details</h3>
            <dl class="mt-4 grid gap-4 sm:grid-cols-2 text-sm">
                <div><dt class="text-gray-500">Patient</dt><dd class="mt-1 font-medium">{{ $bill->patient->name }}</dd></div>
                <div><dt class="text-gray-500">Visit Type</dt><dd class="mt-1 font-medium">{{ $bill->visit_type->label() }}</dd></div>
                <div><dt class="text-gray-500">Posted By</dt><dd class="mt-1 font-medium">{{ $bill->createdBy->name }}</dd></div>
                <div><dt class="text-gray-500">Status</dt><dd class="mt-1 font-medium">{{ $bill->status->label() }}</dd></div>
                @if ($bill->isVoided())
                    <div class="sm:col-span-2">
                        <dt class="text-gray-500">Void Reason</dt>
                        <dd class="mt-1 text-gray-900">{{ $bill->void_reason }}</dd>
                        <p class="mt-1 text-xs text-gray-500">
                            Voided by {{ $bill->voidedBy?->name }} on {{ $bill->voided_at?->format('d M Y H:i') }}
                        </p>
                    </div>
                @endif
            </dl>

            <table class="mt-6 w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-left text-gray-500">
                        <th class="pb-2 font-medium">Charge</th>
                        <th class="pb-2 font-medium text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ([
                        'Consultation' => $bill->consultation_amount,
                        'Pharmacy' => $bill->pharmacy_amount,
                        'Lab' => $bill->lab_amount,
                        'Ward / Bed' => $bill->ward_amount,
                        'Other' => $bill->other_amount,
                    ] as $label => $amount)
                        <tr>
                            <td class="py-2 text-gray-700">{{ $label }}</td>
                            <td class="py-2 text-right">K {{ number_format((float) $amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if (! $bill->isVoided() && Auth::user()->canManageVisits())
        <div class="mt-6 max-w-2xl rounded-xl border border-red-100 bg-white p-6 shadow-sm">
            <h3 class="text-base font-semibold text-red-800">Void Bill</h3>
            <p class="mt-1 text-sm text-gray-600">
                Voiding restores K {{ number_format((float) $bill->total_amount, 2) }} to {{ $bill->payerName() }}.
            </p>
            <form method="POST" action="{{ route('billing.void', $bill) }}" class="mt-4 space-y-4"
                  onsubmit="return confirm('Void this bill? The payer balance will be restored.');">
                @csrf
                <div>
                    <x-input-label for="void_reason" :value="__('Reason for Voiding')" />
                    <textarea id="void_reason" name="void_reason" rows="3" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500"
                        placeholder="Explain why this bill is being voided...">{{ old('void_reason') }}</textarea>
                    <x-input-error :messages="$errors->get('void_reason')" class="mt-2" />
                </div>
                <button type="submit" class="inline-flex items-center rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                    <i class="fa-solid fa-ban mr-2"></i> Void Bill
                </button>
            </form>
        </div>
    @endif
</x-app-layout>
