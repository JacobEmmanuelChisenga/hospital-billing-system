<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Statement of Account" subtitle="{{ $report['company']->name }} — Company account">
            <x-slot name="actions">
                <a href="{{ route('reports.companies', request()->query()) }}" class="btn-ghost no-print">&larr; Companies</a>
            </x-slot>
        </x-page-header>
    </x-slot>

    @include('reports.partials.filter-form', [
        'exportCsvRoute' => route('reports.companies.show.export', array_merge(['company' => $report['company']], request()->query())),
        'exportPdfRoute' => route('reports.companies.show.export.pdf', array_merge(['company' => $report['company']], request()->query())),
        'printButton' => true,
    ])

    <div class="card card-body mt-6 print-report">
        <div class="border-b border-slate-200 pb-4 mb-4 text-center">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ config('hospital.name') }}</p>
            <p class="text-lg font-bold">{{ config('hospital.section') }}</p>
            <p class="mt-2 text-base font-semibold">Company Statement of Account</p>
            <p class="section-subtitle mt-1">{{ $from->format('d M Y') }} — {{ $to->format('d M Y') }}</p>
        </div>

        <dl class="mb-6 grid gap-3 sm:grid-cols-2 text-sm">
            <div><dt class="text-slate-500">Company</dt><dd class="font-medium">{{ $report['company']->name }}</dd></div>
            <div><dt class="text-slate-500">Period</dt><dd class="font-medium">{{ $from->format('d M Y') }} — {{ $to->format('d M Y') }}</dd></div>
        </dl>

        <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4 text-sm">
            <x-dashboard-kpi label="Opening Balance" :value="'K ' . number_format($report['opening_balance'], 2)" tone="slate" />
            <x-dashboard-kpi label="Company Deposits" :value="'K ' . number_format($report['deposits_in_period'], 2)" tone="green" />
            <x-dashboard-kpi label="Total Bills" :value="'K ' . number_format($report['bills_in_period'], 2)" tone="amber" />
            <x-dashboard-kpi label="Closing Balance" :value="'K ' . number_format($report['closing_balance'], 2)" />
        </div>

        <x-table-scroll>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Reference</th>
                        <th>Description</th>
                        <th class="text-right">Debit</th>
                        <th class="text-right">Credit</th>
                        <th class="text-right">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($report['lines'] as $line)
                        <tr @class(['font-medium' => $line['is_opening'] ?? false])>
                            <td class="whitespace-nowrap">{{ $line['date']->format('d M Y') }}</td>
                            <td class="text-slate-600">{{ $line['reference'] }}</td>
                            <td>{{ $line['description'] }}</td>
                            <td class="text-right text-red-700">{{ $line['debit'] !== null ? number_format($line['debit'], 2) : '' }}</td>
                            <td class="text-right text-emerald-700">{{ $line['credit'] !== null ? number_format($line['credit'], 2) : '' }}</td>
                            <td class="text-right font-medium">{{ number_format($line['balance'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-table-scroll>
    </div>
</x-app-layout>
