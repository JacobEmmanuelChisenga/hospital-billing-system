<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Receipts" subtitle="View and print bills posted by the Registry Clerk from patient visits." />
    </x-slot>

    <x-flash-messages />

    @if ($outstandingCashBills->isNotEmpty())
        <x-data-panel title="Outstanding Casual Caller Bills" class="mb-6">
            <p class="mb-4 text-sm text-slate-600">These patients must pay at Accounts before their visit can be closed.</p>
            <x-table-scroll>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Patient</th>
                            <th>Visit</th>
                            <th class="text-right">Amount</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($outstandingCashBills as $bill)
                            <tr>
                                <td>{{ $bill->visit_date->format('d M Y') }}</td>
                                <td class="font-medium">{{ $bill->patient->name }}</td>
                                <td>{{ $bill->visit_type->label() }}</td>
                                <td class="text-right font-medium">K {{ number_format((float) $bill->total_amount, 2) }}</td>
                                <td class="text-right">
                                    <a href="{{ route('billing.show', $bill) }}" class="action-link">Collect Payment</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-table-scroll>
        </x-data-panel>
    @endif

    <x-data-panel title="Today's Posted Bills">
        @if ($todaysBills->isEmpty())
            <p class="text-sm text-slate-500">No bills posted today.</p>
        @else
            <x-table-scroll>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Patient</th>
                            <th>Visit</th>
                            <th>Status</th>
                            <th class="text-right">Amount</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($todaysBills as $bill)
                            <tr>
                                <td>{{ $bill->created_at->format('H:i') }}</td>
                                <td class="font-medium">{{ $bill->patient->name }}</td>
                                <td>{{ $bill->visit_type->label() }}</td>
                                <td>
                                    @if ($bill->isCashBill() && $bill->isOutstanding())
                                        <span class="badge badge-warning">Awaiting Payment</span>
                                    @elseif ($bill->isCashBill() && $bill->isPaid())
                                        <span class="badge badge-success">Paid</span>
                                    @else
                                        <span class="badge badge-neutral">{{ $bill->status->label() }}</span>
                                    @endif
                                </td>
                                <td class="text-right font-medium">K {{ number_format((float) $bill->total_amount, 2) }}</td>
                                <td class="text-right">
                                    <a href="{{ route('billing.show', $bill) }}" class="action-link">View</a>
                                    @if ($bill->isPaid() || ! $bill->isCashBill())
                                        <span class="text-slate-300 mx-1">|</span>
                                        <a href="{{ route('billing.receipt', $bill) }}" class="action-link">Receipt</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-table-scroll>
        @endif
    </x-data-panel>
</x-app-layout>
