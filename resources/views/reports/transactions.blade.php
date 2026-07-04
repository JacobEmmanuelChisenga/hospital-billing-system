<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Transaction Report" subtitle="All deposits, bills, voids, and reversals in the period.">
            <x-slot name="actions">
                <a href="{{ route('reports.index', request()->query()) }}" class="btn-ghost no-print">&larr; Reports</a>
            </x-slot>
        </x-page-header>
    </x-slot>

    @include('reports.partials.filter-form', [
        'showVisitTypeFilter' => true,
        'exportCsvRoute' => route('reports.transactions.export', request()->query()),
        'exportPdfRoute' => route('reports.transactions.export.pdf', request()->query()),
        'printButton' => true,
    ])

    <x-data-panel class="mt-6 print-report">
        <x-table-scroll>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Party</th>
                        <th>Reference</th>
                        <th class="text-right">Amount (K)</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $row)
                        <tr>
                            <td class="whitespace-nowrap">
                                {{ $row['date'] instanceof \DateTimeInterface ? $row['date']->format('d M Y') : $row['date'] }}
                            </td>
                            <td>{{ $row['type'] }}</td>
                            <td>{{ $row['party'] }}</td>
                            <td class="text-slate-500">{{ $row['reference'] }}</td>
                            <td class="text-right font-medium @if($row['direction'] === 'in') text-emerald-700 @else text-red-700 @endif">
                                {{ $row['direction'] === 'in' ? '+' : '-' }}{{ number_format($row['amount'], 2) }}
                            </td>
                            <td>{{ $row['status'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="!py-12 text-center text-slate-500">No transactions in this period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-table-scroll>
    </x-data-panel>
</x-app-layout>
