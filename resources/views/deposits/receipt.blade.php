@extends('layouts.receipt', [
    'backUrl' => route('deposits.show', $deposit),
    'receiptTitle' => 'Deposit Receipt #'.str_pad($deposit->id, 6, '0', STR_PAD_LEFT),
])

@section('content')
    <div class="text-center border-b border-gray-300 pb-4 mb-4">
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">{{ config('hospital.name') }}</p>
        <p class="text-lg font-bold">{{ config('hospital.section') }}</p>
        <p class="text-sm text-gray-600">{{ config('hospital.system_name') }}</p>
        <p class="mt-3 text-base font-semibold">DEPOSIT RECEIPT</p>
        <p class="text-sm text-gray-500">Receipt #{{ str_pad($deposit->id, 6, '0', STR_PAD_LEFT) }}</p>
        @if ($deposit->isReversed())
            <p class="mt-2 text-sm font-semibold text-red-600">** REVERSED **</p>
        @endif
    </div>

    <div class="space-y-3 text-sm mb-6">
        <div class="flex justify-between">
            <span class="text-gray-500">Date</span>
            <span>{{ $deposit->deposit_date->format('d M Y') }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-500">Member</span>
            <span class="font-medium">{{ $deposit->patient->name }}</span>
        </div>
        @if ($deposit->patient->hc_number)
            <div class="flex justify-between">
                <span class="text-gray-500">HC Number</span>
                <span>{{ $deposit->patient->hc_number }}</span>
            </div>
        @endif
        <div class="flex justify-between">
            <span class="text-gray-500">Payment Method</span>
            <span>{{ $deposit->payment_method?->label() ?? '—' }}</span>
        </div>
        @if ($deposit->reference)
            <div class="flex justify-between">
                <span class="text-gray-500">Reference</span>
                <span>{{ $deposit->reference }}</span>
            </div>
        @endif
        <div class="flex justify-between">
            <span class="text-gray-500">Received By</span>
            <span>{{ $deposit->createdBy->name }}</span>
        </div>
    </div>

    <table class="w-full text-sm mb-4">
        <tbody>
            <tr class="border-t-2 border-gray-800">
                <td class="pt-2 font-bold">DEPOSIT AMOUNT</td>
                <td class="pt-2 text-right font-bold text-lg">K {{ number_format((float) $deposit->amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="border-t border-gray-200 pt-3 text-sm">
        <div class="flex justify-between">
            <span class="text-gray-500">Available Balance</span>
            <span class="font-medium">K {{ number_format((float) $deposit->patient->balance, 2) }}</span>
        </div>
    </div>

    @if ($deposit->notes)
        <p class="mt-4 text-xs text-gray-500">Notes: {{ $deposit->notes }}</p>
    @endif

    <p class="mt-8 text-center text-xs text-gray-400">
        Thank you — {{ config('hospital.name') }}<br>
        This deposit is available for medical expenses. Not valid if reversed.
    </p>
@endsection
