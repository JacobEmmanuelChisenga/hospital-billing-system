<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Member Accounts Report</h2>
                <p class="mt-1 text-sm text-gray-500">Balances and period activity for all member accounts.</p>
            </div>
            <a href="{{ route('reports.index', request()->query()) }}" class="text-sm text-hospital-700 hover:underline no-print">&larr; Reports</a>
        </div>
    </x-slot>

    @include('reports.partials.filter-form', [
        'exportRoute' => route('reports.member-accounts.export', request()->query()),
        'printButton' => true,
    ])

    <div class="mt-6 overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm print-report">
        <div class="table-scroll -mx-4 px-4 sm:mx-0 sm:px-0">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Member</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">HC Number</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Current Balance</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Deposits</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Bills</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600">Dependants</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600 no-print">Statement</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($accounts as $row)
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $row['member']->name }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $row['member']->hc_number ?? '—' }}</td>
                            <td class="px-4 py-3 text-right font-medium">K {{ number_format($row['current_balance'], 2) }}</td>
                            <td class="px-4 py-3 text-right text-green-700">K {{ number_format($row['deposits_in_period'], 2) }}</td>
                            <td class="px-4 py-3 text-right text-red-700">K {{ number_format($row['bills_in_period'], 2) }}</td>
                            <td class="px-4 py-3 text-center text-gray-700">{{ $row['dependants_count'] }}</td>
                            <td class="px-4 py-3 text-right no-print">
                                <a href="{{ route('reports.patient-statement', array_merge(['patient' => $row['member']], request()->query())) }}" class="text-hospital-700 hover:underline text-xs">Statement</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
