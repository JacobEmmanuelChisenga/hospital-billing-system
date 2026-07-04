<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Company Reports" subtitle="Company pool balances and usage in the selected period.">
            <x-slot name="actions">
                <a href="{{ route('reports.index', request()->query()) }}" class="btn-ghost no-print">&larr; Reports</a>
            </x-slot>
        </x-page-header>
    </x-slot>

    @include('reports.partials.filter-form', [
        'exportCsvRoute' => route('reports.companies.export', request()->query()),
        'exportPdfRoute' => route('reports.companies.export.pdf', request()->query()),
        'printButton' => true,
    ])

    <x-data-panel class="mt-6 print-report">
        <x-table-scroll>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Company</th>
                        <th class="text-center">Patients</th>
                        <th class="text-right">Pool Balance</th>
                        <th class="text-right">Deposits</th>
                        <th class="text-right">Bills</th>
                        <th class="text-right no-print">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($companies as $row)
                        <tr>
                            <td class="font-medium">{{ $row['company']->name }}</td>
                            <td class="text-center">{{ $row['company']->patients_count }}</td>
                            <td class="text-right font-medium">K {{ number_format($row['current_balance'], 2) }}</td>
                            <td class="text-right text-emerald-700">K {{ number_format($row['deposits_in_period'], 2) }}</td>
                            <td class="text-right text-red-700">K {{ number_format($row['bills_in_period'], 2) }}</td>
                            <td class="text-right no-print">
                                <a href="{{ route('reports.companies.show', array_merge(['company' => $row['company']], request()->query())) }}" class="action-link">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="!py-12 text-center text-slate-500">No company accounts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-table-scroll>
    </x-data-panel>
</x-app-layout>
