<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Transaction Report</h2>
                <p class="mt-1 text-sm text-gray-500">All deposits, bills, voids, and reversals in the period.</p>
            </div>
            <a href="{{ route('reports.index', request()->query()) }}" class="text-sm text-hospital-700 hover:underline no-print">&larr; Reports</a>
        </div>
    </x-slot>

    @include('reports.partials.filter-form', [
        'showVisitTypeFilter' => true,
        'exportRoute' => route('reports.transactions.export', request()->query()),
        'printButton' => true,
    ])

    <div class="mt-6 overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm print-report">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Date</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Type</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Party</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Reference</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Amount (K)</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($transactions as $row)
                        <tr>
                            <td class="px-4 py-3 text-gray-700">
                                {{ $row['date'] instanceof \DateTimeInterface ? $row['date']->format('d M Y') : $row['date'] }}
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $row['type'] }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $row['party'] }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $row['reference'] }}</td>
                            <td class="px-4 py-3 text-right font-medium @if($row['direction'] === 'in') text-green-700 @else text-red-700 @endif">
                                {{ $row['direction'] === 'in' ? '+' : '-' }}{{ number_format($row['amount'], 2) }}
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $row['status'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-gray-500">No transactions in this period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
