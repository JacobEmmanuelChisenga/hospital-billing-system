<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Financial Dashboard</h2>
            <p class="mt-1 text-sm text-gray-500">Where is the money coming from and going?</p>
        </div>
    </x-slot>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-dashboard-kpi label="Money In Today" :value="'K '.number_format($kpis['moneyInToday'], 2)" tone="green" :href="route('deposits.index')" />
        <x-dashboard-kpi label="Bills Posted Today" :value="'K '.number_format($kpis['spendingToday'], 2)" tone="orange" :href="route('billing.index')" />
        <x-dashboard-kpi label="Deposits Today" :value="'K '.number_format($kpis['depositsToday'], 2)" :href="route('deposits.index')" />
        <x-dashboard-kpi label="Membership Today" :value="'K '.number_format($kpis['membershipToday'], 2)" :href="route('membership-fees.index')" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <x-dashboard-chart :chart="$charts['revenueBreakdown']" />
        <x-dashboard-chart :chart="$charts['paymentMethods']" />
    </div>

    <div class="mt-6">
        <x-dashboard-chart :chart="$charts['depositVsSpending']" />
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        <a href="{{ route('deposits.create') }}" class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm transition hover:border-hospital-200">
            <p class="font-semibold text-gray-800"><i class="fa-solid fa-money-bill-wave mr-2 text-hospital-700"></i> Load Deposit</p>
            <p class="mt-1 text-sm text-gray-500">Add funds to a member account.</p>
        </a>
        <a href="{{ route('membership-fees.create') }}" class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm transition hover:border-hospital-200">
            <p class="font-semibold text-gray-800"><i class="fa-solid fa-id-card mr-2 text-hospital-700"></i> Membership Payment</p>
            <p class="mt-1 text-sm text-gray-500">Record scheme subscription fees.</p>
        </a>
        <a href="{{ route('reports.index') }}" class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm transition hover:border-hospital-200">
            <p class="font-semibold text-gray-800"><i class="fa-solid fa-chart-column mr-2 text-hospital-700"></i> Financial Reports</p>
            <p class="mt-1 text-sm text-gray-500">Statements, transactions, and exports.</p>
        </a>
    </div>
</x-app-layout>
