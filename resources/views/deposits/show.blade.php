<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Deposit Details" subtitle="{{ $deposit->patient->name }} · {{ $deposit->deposit_date->format('d M Y') }}">
            <x-slot name="actions">
                <a href="{{ route('deposits.receipt', $deposit) }}" class="btn-primary">
                    <i class="fa-solid fa-print"></i> Print Receipt
                </a>
                <a href="{{ route('deposits.index') }}" class="btn-secondary">
                    <i class="fa-solid fa-arrow-left"></i> Back to Deposits
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <x-flash-messages />

    <div class="grid gap-6 lg:grid-cols-3">
        <div @class([
            'card card-body lg:col-span-1',
            'border-emerald-200 bg-emerald-50' => ! $deposit->isReversed(),
            'border-red-200 bg-red-50' => $deposit->isReversed(),
        ])>
            <p class="section-subtitle">Deposit Amount</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">K {{ number_format((float) $deposit->amount, 2) }}</p>
            <p class="mt-2">
                @if ($deposit->isReversed())
                    <span class="badge badge-danger">Reversed</span>
                @else
                    <span class="badge badge-success">Active</span>
                @endif
            </p>
            <p class="mt-3 text-sm text-slate-600">
                Member balance after deposit:
                <span class="font-medium">K {{ number_format((float) $deposit->patient->balance, 2) }}</span>
            </p>
        </div>

        <div class="card card-body lg:col-span-2">
            <h3 class="section-title">Deposit Information</h3>
            <dl class="mt-4 grid gap-4 sm:grid-cols-2 text-sm">
                <div>
                    <dt class="text-slate-500">Member</dt>
                    <dd class="mt-1 font-medium">
                        <a href="{{ route('patients.show', $deposit->patient) }}" class="action-link">
                            {{ $deposit->patient->name }}
                        </a>
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500">Deposit Date</dt>
                    <dd class="mt-1 font-medium">{{ $deposit->deposit_date->format('d M Y') }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Payment Method</dt>
                    <dd class="mt-1 font-medium">{{ $deposit->payment_method?->label() ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Reference</dt>
                    <dd class="mt-1 font-medium">{{ $deposit->reference ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Loaded By</dt>
                    <dd class="mt-1 font-medium">{{ $deposit->createdBy->name }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Recorded At</dt>
                    <dd class="mt-1 font-medium">{{ $deposit->created_at->format('d M Y H:i') }}</dd>
                </div>
                @if ($deposit->notes)
                    <div class="sm:col-span-2">
                        <dt class="text-slate-500">Notes</dt>
                        <dd class="mt-1">{{ $deposit->notes }}</dd>
                    </div>
                @endif
                @if ($deposit->isReversed())
                    <div class="sm:col-span-2 border-t border-slate-100 pt-4">
                        <dt class="text-slate-500">Reversal Reason</dt>
                        <dd class="mt-1">{{ $deposit->reversal_reason }}</dd>
                        <p class="form-hint mt-2">
                            Reversed by {{ $deposit->reversedBy?->name }} on {{ $deposit->reversed_at->format('d M Y H:i') }}
                        </p>
                    </div>
                @endif
            </dl>
        </div>
    </div>

    @if (! $deposit->isReversed())
        <div class="card card-body mt-6 max-w-2xl border-red-100">
            <h3 class="section-title text-red-800">Reverse Deposit</h3>
            <p class="section-subtitle">
                Reversing will deduct K {{ number_format((float) $deposit->amount, 2) }} from the member's balance.
                The deposit record is kept for audit purposes.
            </p>
            <form method="POST" action="{{ route('deposits.reverse', $deposit) }}" class="mt-4 space-y-4"
                  onsubmit="return confirm('Are you sure you want to reverse this deposit? The member balance will be reduced.');">
                @csrf
                <div>
                    <x-input-label for="reversal_reason" :value="__('Reason for Reversal')" />
                    <textarea id="reversal_reason" name="reversal_reason" rows="3" required
                        class="form-input mt-1"
                        placeholder="Explain why this deposit is being reversed (min. 10 characters)...">{{ old('reversal_reason') }}</textarea>
                    <x-input-error :messages="$errors->get('reversal_reason')" class="mt-2" />
                </div>
                <button type="submit" class="btn-danger">
                    <i class="fa-solid fa-rotate-left"></i> Reverse Deposit
                </button>
            </form>
        </div>
    @endif
</x-app-layout>
