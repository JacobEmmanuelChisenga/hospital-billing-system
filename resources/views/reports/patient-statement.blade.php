<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Statement of Account</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $statement['patient']->name }} — {{ $statement['patient']->type->label() }}</p>
            </div>
            <a href="{{ route('patients.show', $statement['patient']) }}" class="text-sm text-hospital-700 hover:underline no-print">&larr; Patient Profile</a>
        </div>
    </x-slot>

    @include('reports.partials.filter-form', [
        'exportCsvRoute' => route('reports.patient-statement.export', array_merge(['patient' => $statement['patient']], request()->query())),
        'exportPdfRoute' => route('reports.patient-statement.export.pdf', array_merge(['patient' => $statement['patient']], request()->query())),
        'printButton' => true,
    ])

    <div class="mt-6 rounded-xl border border-gray-100 bg-white p-6 shadow-sm print-report">
        <div class="text-center border-b border-gray-200 pb-4 mb-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">{{ config('hospital.name') }}</p>
            <p class="text-lg font-bold">{{ config('hospital.section') }}</p>
            <p class="mt-2 text-base font-semibold">Statement of Account</p>
            <p class="mt-1 text-sm text-gray-500">{{ $from->format('d M Y') }} — {{ $to->format('d M Y') }}</p>
        </div>

        <dl class="grid gap-3 sm:grid-cols-2 text-sm mb-6">
            <div><dt class="text-gray-500">Patient</dt><dd class="font-medium">{{ $statement['patient']->name }}</dd></div>
            <div><dt class="text-gray-500">Membership</dt><dd class="font-medium">{{ $statement['membership_number'] ?? $statement['patient']->hc_number ?? '—' }}</dd></div>
            <div><dt class="text-gray-500">Account / Payer</dt><dd class="font-medium">{{ $statement['payer_label'] }}</dd></div>
            <div><dt class="text-gray-500">Period</dt><dd class="font-medium">{{ $from->format('d M Y') }} — {{ $to->format('d M Y') }}</dd></div>
        </dl>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6 text-sm">
            <div class="rounded-lg bg-gray-50 p-3">
                <p class="text-gray-500">Opening Balance</p>
                <p class="font-bold text-lg">K {{ number_format($statement['opening_balance'], 2) }}</p>
            </div>
            <div class="rounded-lg bg-gray-50 p-3">
                <p class="text-gray-500">Total Deposits</p>
                <p class="font-bold text-lg text-green-700">K {{ number_format($statement['deposits_total'], 2) }}</p>
            </div>
            <div class="rounded-lg bg-gray-50 p-3">
                <p class="text-gray-500">Total Bills</p>
                <p class="font-bold text-lg text-red-700">K {{ number_format($statement['bills_total'], 2) }}</p>
            </div>
            <div class="rounded-lg border border-hospital-200 bg-hospital-50 p-3">
                <p class="text-hospital-700">Closing Balance</p>
                <p class="font-bold text-lg">K {{ number_format($statement['closing_balance'], 2) }}</p>
            </div>
        </div>

        <div class="table-scroll">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 text-left text-gray-500">
                        <th class="pb-2 pr-3 font-medium">Date</th>
                        <th class="pb-2 pr-3 font-medium">Reference</th>
                        <th class="pb-2 pr-3 font-medium">Description</th>
                        <th class="pb-2 pr-3 font-medium text-right">Debit</th>
                        <th class="pb-2 pr-3 font-medium text-right">Credit</th>
                        <th class="pb-2 font-medium text-right">Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($statement['lines'] as $line)
                        <tr @class(['font-medium' => $line['is_opening'] ?? false])>
                            <td class="py-2 pr-3 whitespace-nowrap">{{ $line['date']->format('d M Y') }}</td>
                            <td class="py-2 pr-3 text-gray-600">{{ $line['reference'] }}</td>
                            <td class="py-2 pr-3">{{ $line['description'] }}</td>
                            <td class="py-2 pr-3 text-right text-red-700">{{ $line['debit'] !== null ? number_format($line['debit'], 2) : '' }}</td>
                            <td class="py-2 pr-3 text-right text-green-700">{{ $line['credit'] !== null ? number_format($line['credit'], 2) : '' }}</td>
                            <td class="py-2 text-right font-medium">{{ number_format($line['balance'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
