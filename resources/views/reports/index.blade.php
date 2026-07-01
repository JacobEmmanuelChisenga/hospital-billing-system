<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Reports</h2>
            <p class="mt-1 text-sm text-gray-500">Financial summary and activity for the selected period.</p>
        </div>
    </x-slot>

    @include('reports.partials.filter-form', ['printButton' => true])

    <div class="mt-6 grid gap-4 md:grid-cols-2 lg:grid-cols-4 print-report">
        <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Member Deposits</p>
            <p class="mt-1 text-2xl font-bold text-hospital-800">K {{ number_format($summary['member_deposits_total'], 2) }}</p>
        </div>
        <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Company Deposits</p>
            <p class="mt-1 text-2xl font-bold text-hospital-800">K {{ number_format($summary['company_deposits_total'], 2) }}</p>
        </div>
        <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Bills Posted</p>
            <p class="mt-1 text-2xl font-bold text-hospital-800">K {{ number_format($summary['bills_total'], 2) }}</p>
        </div>
        <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Voided Bills</p>
            <p class="mt-1 text-2xl font-bold text-red-700">{{ $summary['voided_bills_count'] }}</p>
            <p class="text-xs text-gray-400">K {{ number_format($summary['voided_bills_total'], 2) }}</p>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm print-report">
            <h3 class="text-base font-semibold text-gray-800">Visit Type Summary</h3>
            <table class="mt-4 w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-left text-gray-500">
                        <th class="pb-2 font-medium">Type</th>
                        <th class="pb-2 font-medium text-center">Bills</th>
                        <th class="pb-2 font-medium text-right">Total (K)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($summary['visit_summary'] as $row)
                        <tr>
                            <td class="py-2">{{ $row['type']->label() }}</td>
                            <td class="py-2 text-center">{{ $row['count'] }}</td>
                            <td class="py-2 text-right font-medium">{{ number_format($row['total'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm print-report">
            <h3 class="text-base font-semibold text-gray-800">Current Balances</h3>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Active Members</dt><dd class="font-medium">{{ $summary['active_members'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Total Member Balances</dt><dd class="font-medium">K {{ number_format($summary['total_member_balance'], 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Company Patients</dt><dd class="font-medium">{{ $summary['active_company_patients'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Total Company Pools</dt><dd class="font-medium">K {{ number_format($summary['total_company_balance'], 2) }}</dd></div>
                <div class="flex justify-between border-t border-gray-100 pt-3"><dt class="text-gray-500">Reversed Deposits</dt><dd class="font-medium text-red-700">{{ $summary['reversed_deposits_count'] }} (K {{ number_format($summary['reversed_deposits_total'], 2) }})</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Memberships Expiring (30 days)</dt><dd class="font-medium">{{ $summary['expiring_memberships'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Expired Memberships</dt><dd class="font-medium">{{ $summary['expired_memberships'] }}</dd></div>
            </dl>
        </div>
    </div>

    <div class="mt-6 flex flex-wrap gap-3 no-print">
        <a href="{{ route('reports.transactions', request()->query()) }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            <i class="fa-solid fa-list mr-2"></i> Transaction Detail
        </a>
        @if (Auth::user()->canAccessAccountsModules())
            <a href="{{ route('reports.member-accounts', request()->query()) }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                <i class="fa-solid fa-users mr-2"></i> Member Accounts
            </a>
            <a href="{{ route('reports.companies', request()->query()) }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                <i class="fa-solid fa-building mr-2"></i> Company Reports
            </a>
        @endif
    </div>
</x-app-layout>
