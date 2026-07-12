<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Bill #{{ $bill->id }}" :subtitle="$bill->patient->name . ' · ' . $bill->visit_date->format('d M Y') . ($bill->isVoided() ? ' (VOIDED)' : '')">
            <x-slot name="actions">
                @if (! $bill->isVoided() && ($bill->isPaid() || ! $bill->isCashBill()))
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
            'border-amber-200 bg-amber-50' => ! $bill->isVoided() && $bill->isOutstanding(),
            'border-hospital-200 bg-hospital-50' => ! $bill->isVoided() && ! $bill->isOutstanding(),
        ])>
            @if ($bill->isCashBill() && $bill->isOutstanding())
                <p class="section-subtitle text-amber-800">Outstanding Bill</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">K {{ number_format((float) $bill->total_amount, 2) }}</p>
                <p class="mt-2 text-sm text-amber-800">Payment Type: Pay As You Go</p>
                <p class="mt-1 text-sm text-slate-600">Collect payment to close the visit.</p>
            @elseif ($bill->isCashBill() && $bill->isPaid())
                <p class="section-subtitle text-hospital-700">Paid in Full</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">K {{ number_format((float) $bill->total_amount, 2) }}</p>
                <p class="mt-2 text-sm text-slate-600">
                    {{ $bill->payment_method?->label() }} · {{ $bill->paid_at?->format('d M Y H:i') }}
                </p>
                <p class="mt-1 text-sm text-slate-600">Received by {{ $bill->paidBy?->name }}</p>
            @else
                <p class="section-subtitle">Bill Total</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">K {{ number_format((float) $bill->total_amount, 2) }}</p>
                <p class="mt-2 text-sm text-slate-600">Charged to: {{ $bill->payerName() }}</p>
                @if (! $bill->isCashBill())
                    <p class="mt-1 text-sm text-slate-600">
                        Remaining balance: K {{ number_format($bill->payerBalanceAfter(), 2) }}
                    </p>
                @endif
            @endif
        </div>

        <div class="card card-body lg:col-span-2">
            <h3 class="section-title">Bill Details</h3>
            <dl class="mt-4 grid gap-4 sm:grid-cols-2 text-sm">
                <div><dt class="text-slate-500">Patient</dt><dd class="mt-1 font-medium">{{ $bill->patient->name }}</dd></div>
                <div><dt class="text-slate-500">Patient Type</dt><dd class="mt-1 font-medium">{{ $bill->patient->type->label() }}</dd></div>
                <div><dt class="text-slate-500">Visit Type</dt><dd class="mt-1 font-medium">{{ $bill->visit_type->label() }}</dd></div>
                <div><dt class="text-slate-500">Posted By</dt><dd class="mt-1 font-medium">{{ $bill->createdBy->name }}</dd></div>
                <div><dt class="text-slate-500">Status</dt><dd class="mt-1 font-medium">
                    @if ($bill->isOutstanding())
                        Awaiting Payment
                    @elseif ($bill->isPaid() && $bill->isCashBill())
                        Paid
                    @else
                        {{ $bill->status->label() }}
                    @endif
                </dd></div>
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

    @if ($bill->isOutstanding() && Auth::user()->canPerformFinancialOperations())
        <div class="card card-body mt-6 max-w-2xl border-hospital-200">
            <h3 class="section-title">Collect Payment</h3>
            <p class="section-subtitle">
                Record how {{ $bill->patient->name }} paid K {{ number_format((float) $bill->total_amount, 2) }}.
            </p>
            <form method="POST" action="{{ route('billing.collect-payment', $bill) }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <x-input-label for="payment_method" :value="__('Payment Method')" />
                    <select id="payment_method" name="payment_method" required class="form-input mt-1">
                        <option value="">Select payment method...</option>
                        @foreach ($paymentMethods as $method)
                            <option value="{{ $method->value }}" @selected(old('payment_method') === $method->value)>
                                {{ $method->label() }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('payment_method')" class="mt-2" />
                </div>
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-cash-register"></i> Record Payment & Print Receipt
                </button>
            </form>
        </div>
    @endif

    @if (! $bill->isVoided() && Auth::user()->canManageVisits())
        <div class="card card-body mt-6 max-w-2xl border-red-100">
            <h3 class="section-title text-red-800">Void Bill</h3>
            <p class="section-subtitle">
                @if ($bill->isCashBill())
                    Voiding cancels this unpaid casual caller bill.
                @else
                    Voiding restores K {{ number_format((float) $bill->total_amount, 2) }} to {{ $bill->payerName() }}.
                @endif
            </p>
            <form method="POST" action="{{ route('billing.void', $bill) }}" class="mt-4 space-y-4"
                  onsubmit="return confirm('Void this bill?{{ $bill->isCashBill() ? '' : ' The payer balance will be restored.' }}');">
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
