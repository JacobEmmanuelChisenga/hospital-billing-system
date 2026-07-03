<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Company Reports</h2>
                <p class="mt-1 text-sm text-gray-500">Company pool balances and usage in the selected period.</p>
            </div>
            <a href="{{ route('reports.index', request()->query()) }}" class="text-sm text-hospital-700 hover:underline no-print">&larr; Reports</a>
        </div>
    </x-slot>

    @include('reports.partials.filter-form', ['printButton' => true])

    <div class="mt-6 overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm print-report">
        <div class="table-scroll -mx-4 px-4 sm:mx-0 sm:px-0">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Company</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600">Patients</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Pool Balance</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Deposits</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Bills</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600 no-print">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($companies as $row)
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $row['company']->name }}</td>
                            <td class="px-4 py-3 text-center text-gray-700">{{ $row['company']->patients_count }}</td>
                            <td class="px-4 py-3 text-right font-medium">K {{ number_format($row['current_balance'], 2) }}</td>
                            <td class="px-4 py-3 text-right text-green-700">K {{ number_format($row['deposits_in_period'], 2) }}</td>
                            <td class="px-4 py-3 text-right text-red-700">K {{ number_format($row['bills_in_period'], 2) }}</td>
                            <td class="px-4 py-3 text-right no-print">
                                <a href="{{ route('reports.companies.show', array_merge(['company' => $row['company']], request()->query())) }}" class="text-hospital-700 hover:underline text-xs">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-gray-500">No company accounts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
