<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Reports" subtitle="Financial summary and activity for the selected period." />
    </x-slot>

    @include('reports.partials.filter-form', [
        'exportCsvRoute' => route('reports.index.export', request()->query()),
        'exportPdfRoute' => route('reports.index.export.pdf', request()->query()),
        'printButton' => true,
    ])

    <div class="mt-6 grid gap-4 md:grid-cols-2 lg:grid-cols-4 print-report">
        <x-dashboard-kpi label="Member Deposits" :value="'K ' . number_format($summary['member_deposits_total'], 2)" />
        <x-dashboard-kpi label="Company Deposits" :value="'K ' . number_format($summary['company_deposits_total'], 2)" />
        <x-dashboard-kpi label="Bills Posted" :value="'K ' . number_format($summary['bills_total'], 2)" />
        <x-dashboard-kpi label="Voided Bills" :value="(string) $summary['voided_bills_count']" tone="amber" :hint="'K ' . number_format($summary['voided_bills_total'], 2)" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="card card-body print-report">
            <h3 class="section-title">Visit Type Summary</h3>
            <x-table-scroll class="mt-4">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th class="text-center">Bills</th>
                            <th class="text-right">Total (K)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($summary['visit_summary'] as $row)
                            <tr>
                                <td>{{ $row['type']->label() }}</td>
                                <td class="text-center">{{ $row['count'] }}</td>
                                <td class="text-right font-medium">{{ number_format($row['total'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-table-scroll>
        </div>

        <div class="card card-body print-report">
            <h3 class="section-title">Current Balances</h3>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">Active Members</dt><dd class="font-medium">{{ $summary['active_members'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Total Member Balances</dt><dd class="font-medium">K {{ number_format($summary['total_member_balance'], 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Company Patients</dt><dd class="font-medium">{{ $summary['active_company_patients'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Total Company Pools</dt><dd class="font-medium">K {{ number_format($summary['total_company_balance'], 2) }}</dd></div>
                <div class="flex justify-between border-t border-slate-100 pt-3"><dt class="text-slate-500">Reversed Deposits</dt><dd class="font-medium text-red-700">{{ $summary['reversed_deposits_count'] }} (K {{ number_format($summary['reversed_deposits_total'], 2) }})</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Memberships Expiring (30 days)</dt><dd class="font-medium">{{ $summary['expiring_memberships'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Expired Memberships</dt><dd class="font-medium">{{ $summary['expired_memberships'] }}</dd></div>
            </dl>
        </div>
    </div>

    <div class="mt-6 flex flex-wrap gap-3 no-print">
        <a href="{{ route('reports.transactions', request()->query()) }}" class="btn-secondary">
            <i class="fa-solid fa-list"></i> Transaction Detail
        </a>
        @if (Auth::user()->canAccessAccountsModules())
            <a href="{{ route('reports.member-accounts', request()->query()) }}" class="btn-secondary">
                <i class="fa-solid fa-users"></i> Member Accounts
            </a>
            <a href="{{ route('reports.companies', request()->query()) }}" class="btn-secondary">
                <i class="fa-solid fa-building"></i> Company Reports
            </a>
        @endif
    </div>
</x-app-layout>
