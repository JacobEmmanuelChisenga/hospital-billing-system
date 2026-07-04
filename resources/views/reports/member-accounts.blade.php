<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Member Accounts Report" subtitle="Balances and period activity for all member accounts.">
            <x-slot name="actions">
                <a href="{{ route('reports.index', request()->query()) }}" class="btn-ghost no-print">&larr; Reports</a>
            </x-slot>
        </x-page-header>
    </x-slot>

    @include('reports.partials.filter-form', [
        'exportCsvRoute' => route('reports.member-accounts.export', request()->query()),
        'exportPdfRoute' => route('reports.member-accounts.export.pdf', request()->query()),
        'printButton' => true,
    ])

    <x-data-panel class="mt-6 print-report">
        <x-table-scroll>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>HC Number</th>
                        <th class="text-right">Current Balance</th>
                        <th class="text-right">Deposits</th>
                        <th class="text-right">Bills</th>
                        <th class="text-center">Dependants</th>
                        <th class="text-right no-print">Statement</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($accounts as $row)
                        <tr>
                            <td class="font-medium">{{ $row['member']->name }}</td>
                            <td>{{ $row['member']->hc_number ?? '—' }}</td>
                            <td class="text-right font-medium">K {{ number_format($row['current_balance'], 2) }}</td>
                            <td class="text-right text-emerald-700">K {{ number_format($row['deposits_in_period'], 2) }}</td>
                            <td class="text-right text-red-700">K {{ number_format($row['bills_in_period'], 2) }}</td>
                            <td class="text-center">{{ $row['dependants_count'] }}</td>
                            <td class="text-right no-print">
                                <a href="{{ route('reports.patient-statement', array_merge(['patient' => $row['member']], request()->query())) }}" class="action-link">Statement</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-table-scroll>
    </x-data-panel>
</x-app-layout>
