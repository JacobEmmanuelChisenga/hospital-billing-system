<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">{{ $report['company']->name }}</h2>
                <p class="mt-1 text-sm text-gray-500">Company account usage report.</p>
            </div>
            <a href="{{ route('reports.companies', request()->query()) }}" class="text-sm text-hospital-700 hover:underline no-print">&larr; Companies</a>
        </div>
    </x-slot>

    @include('reports.partials.filter-form', [
        'exportCsvRoute' => route('reports.companies.show.export', array_merge(['company' => $report['company']], request()->query())),
        'exportPdfRoute' => route('reports.companies.show.export.pdf', array_merge(['company' => $report['company']], request()->query())),
        'printButton' => true,
    ])

    <div class="mt-6 grid gap-4 md:grid-cols-3 print-report">
        <div class="rounded-xl border border-hospital-200 bg-hospital-50 p-5">
            <p class="text-sm text-hospital-700">Pool Balance</p>
            <p class="mt-1 text-2xl font-bold">K {{ number_format($report['current_balance'], 2) }}</p>
        </div>
        <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Deposits in Period</p>
            <p class="mt-1 text-2xl font-bold text-green-700">K {{ number_format($report['deposits_in_period'], 2) }}</p>
        </div>
        <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Bills in Period</p>
            <p class="mt-1 text-2xl font-bold text-red-700">K {{ number_format($report['bills_in_period'], 2) }}</p>
        </div>
    </div>

    <div class="mt-6 overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm print-report">
        <div class="px-4 py-3 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Bills in Period</h3>
        </div>
        <div class="table-scroll -mx-4 px-4 sm:mx-0 sm:px-0">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Date</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Patient</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Visit</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Amount</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($report['bills'] as $bill)
                        <tr>
                            <td class="px-4 py-3">{{ $bill->visit_date->format('d M Y') }}</td>
                            <td class="px-4 py-3">{{ $bill->patient->name }}</td>
                            <td class="px-4 py-3">{{ $bill->visit_type->label() }}</td>
                            <td class="px-4 py-3 text-right font-medium">K {{ number_format((float) $bill->total_amount, 2) }}</td>
                            <td class="px-4 py-3">{{ $bill->status->label() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-gray-500">No bills in this period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
