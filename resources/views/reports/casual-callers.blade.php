<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Casual Caller Report" subtitle="Pay-as-you-go bills, collections, and outstanding amounts for the selected period.">
            <x-slot name="actions">
                <a href="{{ route('reports.index', request()->query()) }}" class="btn-ghost no-print">&larr; Reports</a>
            </x-slot>
        </x-page-header>
    </x-slot>

    @include('reports.partials.filter-form', [
        'exportCsvRoute' => route('reports.casual-callers.export', request()->query()),
        'exportPdfRoute' => route('reports.casual-callers.export.pdf', request()->query()),
        'printButton' => true,
    ])

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4 print-report">
        <x-dashboard-kpi label="Active Casual Callers" :value="(string) $report['summary']['active_patients']" />
        <x-dashboard-kpi label="Bills Issued" :value="'K ' . number_format($report['summary']['billed_total'], 2)" tone="blue" :hint="$report['summary']['bills_count'] . ' bill(s)'" />
        <x-dashboard-kpi label="Collected (by visit date)" :value="'K ' . number_format($report['summary']['paid_total'], 2)" tone="green" :hint="$report['summary']['paid_count'] . ' paid'" />
        <x-dashboard-kpi label="Outstanding (this period)" :value="'K ' . number_format($report['summary']['outstanding_total'], 2)" tone="amber" :hint="$report['summary']['outstanding_count'] . ' unpaid'" />
    </div>

    <div class="mt-4 grid gap-4 sm:grid-cols-2 print-report">
        <x-dashboard-kpi label="Cash Received (payment date)" :value="'K ' . number_format($report['summary']['collected_in_period'], 2)" tone="green" :hint="$report['summary']['collected_count'] . ' payment(s) in period'" />
        <x-dashboard-kpi label="Current Outstanding (all)" :value="'K ' . number_format($report['summary']['current_outstanding'], 2)" tone="orange" />
    </div>

    @if ($report['payment_methods']->isNotEmpty())
        <x-data-panel title="Collections by Payment Method" class="mt-6 print-report">
            <x-table-scroll>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Method</th>
                            <th class="text-center">Payments</th>
                            <th class="text-right">Total (K)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($report['payment_methods'] as $row)
                            <tr>
                                <td>{{ $row['method'] }}</td>
                                <td class="text-center">{{ $row['count'] }}</td>
                                <td class="text-right font-medium">{{ number_format($row['total'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-table-scroll>
        </x-data-panel>
    @endif

    <x-data-panel title="Bills by Visit Date" class="mt-6 print-report">
        <x-table-scroll>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Patient</th>
                        <th>File No.</th>
                        <th>Visit</th>
                        <th class="text-right">Amount</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th class="text-right no-print">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($report['bills'] as $row)
                        <tr>
                            <td>{{ $row['bill']->visit_date->format('d M Y') }}</td>
                            <td class="font-medium">{{ $row['patient']->name }}</td>
                            <td>{{ $row['patient']->file_number ?? '—' }}</td>
                            <td>{{ $row['visit_label'] }}</td>
                            <td class="text-right font-medium">K {{ number_format($row['amount'], 2) }}</td>
                            <td>
                                <span @class([
                                    'badge',
                                    'badge-success' => $row['status'] === 'Paid',
                                    'badge-warning' => $row['status'] === 'Awaiting Payment',
                                ])>{{ $row['status'] }}</span>
                            </td>
                            <td>
                                @if ($row['payment_method'])
                                    {{ $row['payment_method'] }}
                                    @if ($row['paid_at'])
                                        <span class="block text-xs text-slate-500">{{ $row['paid_at']->format('d M Y H:i') }}</span>
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-right no-print">
                                <a href="{{ route('billing.show', $row['bill']) }}" class="action-link">View Bill</a>
                                <span class="text-slate-300 mx-1">|</span>
                                <a href="{{ route('patients.show', $row['patient']) }}" class="action-link">Patient</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="!py-12 text-center text-slate-500">No casual caller bills in this period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-table-scroll>
    </x-data-panel>
</x-app-layout>
