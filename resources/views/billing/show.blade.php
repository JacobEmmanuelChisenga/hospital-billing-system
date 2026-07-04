<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Bill #{{ $bill->id }}" :subtitle="$bill->patient->name . ' · ' . $bill->visit_date->format('d M Y') . ($bill->isVoided() ? ' (VOIDED)' : '')">
            <x-slot name="actions">
                @if (! $bill->isVoided())
                    <a href="{{ route('billing.receipt', $bill) }}" target="_blank" class="btn-primary">
                        <i class="fa-solid fa-print"></i> Print Receipt
                    </a>
                @endif
                <a href="{{ route('billing.index') }}" class="btn-secondary">
                    <i class="fa-solid fa-arrow-left"></i> Billing
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <x-flash-messages />

    <div class="grid gap-6 lg:grid-cols-3">
        <div @class([
            'card card-body',
            'border-red-200 bg-red-50' => $bill->isVoided(),
            'border-hospital-200 bg-hospital-50' => ! $bill->isVoided(),
        ])>
            <p class="section-subtitle">Bill Total</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">K {{ number_format((float) $bill->total_amount, 2) }}</p>
            <p class="mt-2 text-sm text-slate-600">Charged to: {{ $bill->payerName() }}</p>
            <p class="mt-1 text-sm text-slate-600">
                Remaining balance: K {{ number_format($bill->payerBalanceAfter(), 2) }}
            </p>
        </div>

        <div class="card card-body lg:col-span-2">
            <h3 class="section-title">Bill Details</h3>
            <dl class="mt-4 grid gap-4 sm:grid-cols-2 text-sm">
                <div><dt class="text-slate-500">Patient</dt><dd class="mt-1 font-medium">{{ $bill->patient->name }}</dd></div>
                <div><dt class="text-slate-500">Visit Type</dt><dd class="mt-1 font-medium">{{ $bill->visit_type->label() }}</dd></div>
                <div><dt class="text-slate-500">Posted By</dt><dd class="mt-1 font-medium">{{ $bill->createdBy->name }}</dd></div>
                <div><dt class="text-slate-500">Status</dt><dd class="mt-1 font-medium">{{ $bill->status->label() }}</dd></div>
                @if ($bill->isVoided())
                    <div class="sm:col-span-2">
                        <dt class="text-slate-500">Void Reason</dt>
                        <dd class="mt-1 text-slate-900">{{ $bill->void_reason }}</dd>
                        <p class="form-hint mt-1">
                            Voided by {{ $bill->voidedBy?->name }} on {{ $bill->voided_at?->format('d M Y H:i') }}
                        </p>
                    </div>
                @endif
            </dl>

            <x-table-scroll class="mt-6">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Charge</th>
                            <th class="text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ([
                            'Consultation' => $bill->consultation_amount,
                            'Pharmacy' => $bill->pharmacy_amount,
                            'Lab' => $bill->lab_amount,
                            'Ward / Bed' => $bill->ward_amount,
                            'Other' => $bill->other_amount,
                        ] as $label => $amount)
                            <tr>
                                <td>{{ $label }}</td>
                                <td class="text-right">K {{ number_format((float) $amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-table-scroll>
        </div>
    </div>

    @if (! $bill->isVoided() && Auth::user()->canManageVisits())
        <div class="card card-body mt-6 max-w-2xl border-red-100">
            <h3 class="section-title text-red-800">Void Bill</h3>
            <p class="section-subtitle">
                Voiding restores K {{ number_format((float) $bill->total_amount, 2) }} to {{ $bill->payerName() }}.
            </p>
            <form method="POST" action="{{ route('billing.void', $bill) }}" class="mt-4 space-y-4"
                  onsubmit="return confirm('Void this bill? The payer balance will be restored.');">
                @csrf
                <div>
                    <x-input-label for="void_reason" :value="__('Reason for Voiding')" />
                    <textarea id="void_reason" name="void_reason" rows="3" required
                        class="form-input mt-1"
                        placeholder="Explain why this bill is being voided...">{{ old('void_reason') }}</textarea>
                    <x-input-error :messages="$errors->get('void_reason')" class="mt-2" />
                </div>
                <button type="submit" class="btn-danger">
                    <i class="fa-solid fa-ban"></i> Void Bill
                </button>
            </form>
        </div>
    @endif
</x-app-layout>
