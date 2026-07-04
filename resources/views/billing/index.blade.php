<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Receipts" subtitle="View and print bills posted by the Registry Clerk from patient visits." />
    </x-slot>

    <x-flash-messages />

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
                                <td class="text-right font-medium">K {{ number_format((float) $bill->total_amount, 2) }}</td>
                                <td class="text-right">
                                    <a href="{{ route('billing.show', $bill) }}" class="action-link">View</a>
                                    <span class="text-slate-300 mx-1">|</span>
                                    <a href="{{ route('billing.receipt', $bill) }}" class="action-link">Receipt</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-table-scroll>
        @endif
    </x-data-panel>
</x-app-layout>
