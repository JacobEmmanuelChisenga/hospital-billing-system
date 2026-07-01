@extends('layouts.receipt', [
    'backUrl' => route('membership-fees.show', $fee),
    'receiptTitle' => 'Membership Receipt #'.str_pad($fee->id, 6, '0', STR_PAD_LEFT),
])

@section('content')
    <div class="text-center border-b border-gray-300 pb-4 mb-4">
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">{{ config('hospital.name') }}</p>
        <p class="text-lg font-bold">{{ config('hospital.section') }}</p>
        <p class="text-sm text-gray-600">{{ config('hospital.system_name') }}</p>
        <p class="mt-3 text-base font-semibold">MEMBERSHIP RECEIPT</p>
        <p class="text-sm text-gray-500">Receipt #{{ str_pad($fee->id, 6, '0', STR_PAD_LEFT) }}</p>
    </div>

    <div class="space-y-3 text-sm mb-6">
        <div class="flex justify-between">
            <span class="text-gray-500">Date</span>
            <span>{{ $fee->payment_date->format('d M Y') }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-500">Member / Dependant</span>
            <span class="font-medium">{{ $fee->patient->name }}</span>
        </div>
        @if ($fee->patient->membership)
            <div class="flex justify-between">
                <span class="text-gray-500">Membership Number</span>
                <span>{{ $fee->patient->membership->membership_number }}</span>
            </div>
        @endif
        @if ($fee->principalPatient)
            <div class="flex justify-between">
                <span class="text-gray-500">Principal Member</span>
                <span>{{ $fee->principalPatient->name }}</span>
            </div>
        @endif
        <div class="flex justify-between">
            <span class="text-gray-500">Payment Method</span>
            <span>{{ $fee->payment_method?->label() ?? '—' }}</span>
        </div>
        @if ($fee->reference)
            <div class="flex justify-between">
                <span class="text-gray-500">Reference</span>
                <span>{{ $fee->reference }}</span>
            </div>
        @endif
        <div class="flex justify-between">
            <span class="text-gray-500">Received By</span>
            <span>{{ $fee->createdBy->name }}</span>
        </div>
    </div>

    <table class="w-full text-sm mb-4">
        <tbody>
            <tr class="border-t-2 border-gray-800">
                <td class="pt-2 font-bold">MEMBERSHIP FEE</td>
                <td class="pt-2 text-right font-bold text-lg">K {{ number_format((float) $fee->amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="border-t border-gray-200 pt-3 text-sm">
        <div class="flex justify-between">
            <span class="text-gray-500">Membership Status</span>
            <span class="font-medium">{{ $fee->isExpired() ? 'Expired' : 'Active' }}</span>
        </div>
        <div class="flex justify-between mt-1">
            <span class="text-gray-500">Valid Until</span>
            <span class="font-medium">{{ $fee->expiry_date->format('d M Y') }}</span>
        </div>
    </div>

    @if ($fee->notes)
        <p class="mt-4 text-xs text-gray-500">Notes: {{ $fee->notes }}</p>
    @endif

    <p class="mt-8 text-center text-xs text-gray-400">
        Thank you — {{ config('hospital.name') }}<br>
        This is a membership / subscription fee. It is not spendable treatment balance.
    </p>
@endsection
