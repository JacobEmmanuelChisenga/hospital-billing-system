<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Membership Payment</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $fee->patient->name }} — paid {{ $fee->payment_date->format('d M Y') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('membership-fees.receipt', $fee) }}"
                   class="inline-flex items-center rounded-lg bg-hospital-700 px-4 py-2 text-sm font-medium text-white hover:bg-hospital-800">
                    <i class="fa-solid fa-print mr-2"></i> Print Receipt
                </a>
                <a href="{{ route('membership-fees.index') }}" class="text-sm text-hospital-700 hover:underline self-center">&larr; Membership Payments</a>
            </div>
        </div>
    </x-slot>

    <x-flash-messages />

    <div class="max-w-2xl rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
        <dl class="grid gap-4 sm:grid-cols-2 text-sm">
            <div>
                <dt class="text-gray-500">Member / Dependant</dt>
                <dd class="mt-1 font-medium">
                    <a href="{{ route('patients.show', $fee->patient) }}" class="text-hospital-700 hover:underline">
                        {{ $fee->patient->name }}
                    </a>
                    <span class="text-xs text-gray-500">({{ $fee->patient->type->label() }})</span>
                </dd>
            </div>
            @if ($fee->principalPatient)
                <div>
                    <dt class="text-gray-500">Principal Member</dt>
                    <dd class="mt-1 font-medium">
                        <a href="{{ route('patients.show', $fee->principalPatient) }}" class="text-hospital-700 hover:underline">
                            {{ $fee->principalPatient->name }}
                        </a>
                    </dd>
                </div>
            @endif
            @if ($fee->patient->membership)
                <div>
                    <dt class="text-gray-500">Membership Number</dt>
                    <dd class="mt-1 font-medium">{{ $fee->patient->membership->membership_number }}</dd>
                </div>
            @endif
            <div>
                <dt class="text-gray-500">Amount</dt>
                <dd class="mt-1 font-medium text-lg">K {{ number_format((float) $fee->amount, 2) }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Payment Method</dt>
                <dd class="mt-1 font-medium">{{ $fee->payment_method?->label() ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Reference</dt>
                <dd class="mt-1 font-medium">{{ $fee->reference ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Recorded By</dt>
                <dd class="mt-1 font-medium">{{ $fee->createdBy->name }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Payment Date</dt>
                <dd class="mt-1 font-medium">{{ $fee->payment_date->format('d M Y') }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Valid Until</dt>
                <dd class="mt-1 font-medium">{{ $fee->expiry_date->format('d M Y') }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Status</dt>
                <dd class="mt-1">
                    @if ($fee->isExpired())
                        <span class="inline-flex rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800">Expired</span>
                    @elseif ($fee->isExpiringSoon())
                        <span class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">Expiring Soon</span>
                    @else
                        <span class="inline-flex rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">Active</span>
                    @endif
                </dd>
            </div>
            @if ($fee->notes)
                <div class="sm:col-span-2">
                    <dt class="text-gray-500">Notes</dt>
                    <dd class="mt-1 text-gray-700">{{ $fee->notes }}</dd>
                </div>
            @endif
        </dl>
    </div>
</x-app-layout>
