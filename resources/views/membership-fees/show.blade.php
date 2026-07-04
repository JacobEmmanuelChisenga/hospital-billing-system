<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Membership Payment" subtitle="{{ $fee->patient->name }} — paid {{ $fee->payment_date->format('d M Y') }}">
            <x-slot name="actions">
                <a href="{{ route('membership-fees.receipt', $fee) }}" class="btn-primary">
                    <i class="fa-solid fa-print"></i> Print Receipt
                </a>
                <a href="{{ route('membership-fees.index') }}" class="btn-ghost">&larr; Membership Payments</a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <x-flash-messages />

    <div class="card card-body max-w-2xl">
        <dl class="grid gap-4 sm:grid-cols-2 text-sm">
            <div>
                <dt class="text-slate-500">Member / Dependant</dt>
                <dd class="mt-1 font-medium">
                    <a href="{{ route('patients.show', $fee->patient) }}" class="action-link">
                        {{ $fee->patient->name }}
                    </a>
                    <span class="text-xs text-slate-500">({{ $fee->patient->type->label() }})</span>
                </dd>
            </div>
            @if ($fee->principalPatient)
                <div>
                    <dt class="text-slate-500">Principal Member</dt>
                    <dd class="mt-1 font-medium">
                        <a href="{{ route('patients.show', $fee->principalPatient) }}" class="action-link">
                            {{ $fee->principalPatient->name }}
                        </a>
                    </dd>
                </div>
            @endif
            @if ($fee->patient->membership)
                <div>
                    <dt class="text-slate-500">Membership Number</dt>
                    <dd class="mt-1 font-medium">{{ $fee->patient->membership->membership_number }}</dd>
                </div>
            @endif
            <div>
                <dt class="text-slate-500">Amount</dt>
                <dd class="mt-1 text-lg font-medium">K {{ number_format((float) $fee->amount, 2) }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Payment Method</dt>
                <dd class="mt-1 font-medium">{{ $fee->payment_method?->label() ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Reference</dt>
                <dd class="mt-1 font-medium">{{ $fee->reference ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Recorded By</dt>
                <dd class="mt-1 font-medium">{{ $fee->createdBy->name }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Payment Date</dt>
                <dd class="mt-1 font-medium">{{ $fee->payment_date->format('d M Y') }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Valid Until</dt>
                <dd class="mt-1 font-medium">{{ $fee->expiry_date->format('d M Y') }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Status</dt>
                <dd class="mt-1">
                    @if ($fee->isExpired())
                        <span class="badge badge-danger">Expired</span>
                    @elseif ($fee->isExpiringSoon())
                        <span class="badge badge-warning">Expiring Soon</span>
                    @else
                        <span class="badge badge-success">Active</span>
                    @endif
                </dd>
            </div>
            @if ($fee->notes)
                <div class="sm:col-span-2">
                    <dt class="text-slate-500">Notes</dt>
                    <dd class="mt-1 text-slate-700">{{ $fee->notes }}</dd>
                </div>
            @endif
        </dl>
    </div>
</x-app-layout>
