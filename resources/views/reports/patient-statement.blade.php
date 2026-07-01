<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Patient Statement</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $statement['patient']->name }} — {{ $statement['patient']->type->label() }}</p>
            </div>
            <a href="{{ route('patients.show', $statement['patient']) }}" class="text-sm text-hospital-700 hover:underline no-print">&larr; Patient Profile</a>
        </div>
    </x-slot>

    @include('reports.partials.filter-form', [
        'exportRoute' => route('reports.patient-statement.export', array_merge(['patient' => $statement['patient']], request()->query())),
        'printButton' => true,
    ])

    <div class="mt-6 rounded-xl border border-gray-100 bg-white p-6 shadow-sm print-report">
        <div class="text-center border-b border-gray-200 pb-4 mb-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">{{ config('hospital.name') }}</p>
            <p class="text-lg font-bold">{{ config('hospital.section') }}</p>
            <p class="mt-2 text-base font-semibold">Patient Statement</p>
        </div>

        <dl class="grid gap-3 sm:grid-cols-2 text-sm mb-6">
            <div><dt class="text-gray-500">Patient</dt><dd class="font-medium">{{ $statement['patient']->name }}</dd></div>
            <div><dt class="text-gray-500">HC Number</dt><dd class="font-medium">{{ $statement['patient']->hc_number ?? '—' }}</dd></div>
            <div><dt class="text-gray-500">Account / Payer</dt><dd class="font-medium">{{ $statement['payer_label'] }}</dd></div>
            <div><dt class="text-gray-500">Period</dt><dd class="font-medium">{{ $from->format('d M Y') }} — {{ $to->format('d M Y') }}</dd></div>
        </dl>

        <div class="grid gap-4 sm:grid-cols-3 mb-6 text-sm">
            <div class="rounded-lg bg-gray-50 p-3">
                <p class="text-gray-500">Opening Balance</p>
                <p class="font-bold text-lg">K {{ number_format($statement['opening_balance'], 2) }}</p>
            </div>
            <div class="rounded-lg bg-gray-50 p-3">
                <p class="text-gray-500">Credits (Deposits)</p>
                <p class="font-bold text-lg text-green-700">K {{ number_format($statement['deposits_total'], 2) }}</p>
            </div>
            <div class="rounded-lg bg-gray-50 p-3">
                <p class="text-gray-500">Debits (Bills)</p>
                <p class="font-bold text-lg text-red-700">K {{ number_format($statement['bills_total'], 2) }}</p>
            </div>
        </div>

        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 text-left text-gray-500">
                    <th class="pb-2 font-medium">Date</th>
                    <th class="pb-2 font-medium">Description</th>
                    <th class="pb-2 font-medium text-right">Debit</th>
                    <th class="pb-2 font-medium text-right">Credit</th>
                    <th class="pb-2 font-medium">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($statement['lines'] as $line)
                    <tr>
                        <td class="py-2">{{ $line['date']->format('d M Y') }}</td>
                        <td class="py-2">{{ $line['description'] }}</td>
                        <td class="py-2 text-right text-red-700">{{ $line['debit'] ? number_format($line['debit'], 2) : '—' }}</td>
                        <td class="py-2 text-right text-green-700">{{ $line['credit'] ? number_format($line['credit'], 2) : '—' }}</td>
                        <td class="py-2 text-gray-600">{{ $line['status'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-gray-500">No activity in this period.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="border-t-2 border-gray-800">
                    <td colspan="2" class="pt-3 font-bold">Closing Balance</td>
                    <td colspan="3" class="pt-3 text-right font-bold text-lg">K {{ number_format($statement['closing_balance'], 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</x-app-layout>
