<div class="text-center border-b border-gray-300 pb-4 mb-4">
    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">{{ config('hospital.name') }}</p>
    <p class="text-lg font-bold">{{ config('hospital.section') }}</p>
    <p class="text-sm text-gray-600">{{ config('hospital.system_name') }}</p>
    <p class="mt-3 text-base font-semibold">OFFICIAL RECEIPT</p>
    <p class="text-sm text-gray-500">Receipt #{{ str_pad($bill->id, 6, '0', STR_PAD_LEFT) }}</p>
</div>

<div class="space-y-3 text-sm mb-6">
    <div class="flex justify-between">
        <span class="text-gray-500">Date</span>
        <span>{{ $bill->visit_date->format('d M Y') }}</span>
    </div>
    <div class="flex justify-between">
        <span class="text-gray-500">Visit Type</span>
        <span>{{ $bill->visit_type->label() }}</span>
    </div>
    @if ($bill->ward_bed)
        <div class="flex justify-between">
            <span class="text-gray-500">Ward / Bed</span>
            <span>{{ $bill->ward_bed }}</span>
        </div>
    @endif
    <div class="flex justify-between">
        <span class="text-gray-500">Patient</span>
        <span class="font-medium">{{ $bill->patient->name }}</span>
    </div>
    @if ($bill->patient->hc_number)
        <div class="flex justify-between">
            <span class="text-gray-500">HC Number</span>
            <span>{{ $bill->patient->hc_number }}</span>
        </div>
    @endif
    <div class="flex justify-between">
        <span class="text-gray-500">Charged To</span>
        <span>{{ $bill->payerName() }}</span>
    </div>
    <div class="flex justify-between">
        <span class="text-gray-500">Posted By</span>
        <span>{{ $bill->createdBy->name }}</span>
    </div>
    <div class="flex justify-between">
        <span class="text-gray-500">Posted At</span>
        <span>{{ $bill->created_at->format('d M Y H:i') }}</span>
    </div>
</div>

<table class="w-full text-sm mb-4">
    <thead>
        <tr class="border-b border-gray-300">
            <th class="py-2 text-left font-medium text-gray-600">Description</th>
            <th class="py-2 text-right font-medium text-gray-600">Amount (K)</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-100">
        @foreach ([
            'Consultation' => $bill->consultation_amount,
            'Pharmacy' => $bill->pharmacy_amount,
            'Lab / Investigations' => $bill->lab_amount,
            'Ward / Bed' => $bill->ward_amount,
            'Other' => $bill->other_amount,
        ] as $label => $amount)
            @if ((float) $amount > 0)
                <tr>
                    <td class="py-1.5">{{ $label }}</td>
                    <td class="py-1.5 text-right">{{ number_format((float) $amount, 2) }}</td>
                </tr>
            @endif
        @endforeach
    </tbody>
    <tfoot>
        <tr class="border-t-2 border-gray-800">
            <td class="pt-2 font-bold">TOTAL</td>
            <td class="pt-2 text-right font-bold text-lg">K {{ number_format((float) $bill->total_amount, 2) }}</td>
        </tr>
    </tfoot>
</table>

<div class="border-t border-gray-200 pt-3 text-sm">
    <div class="flex justify-between">
        <span class="text-gray-500">Remaining Balance</span>
        <span class="font-medium">K {{ number_format($bill->payerBalanceAfter(), 2) }}</span>
    </div>
</div>

@if ($bill->notes)
    <p class="mt-4 text-xs text-gray-500">Notes: {{ $bill->notes }}</p>
@endif

<p class="mt-8 text-center text-xs text-gray-400">
    Thank you — {{ config('hospital.name') }}<br>
    Internal use only. Not valid if bill is voided.
</p>
