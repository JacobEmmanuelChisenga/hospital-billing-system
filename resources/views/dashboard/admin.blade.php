<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">System Dashboard</h2>
            <p class="mt-1 text-sm text-gray-500">Is the system healthy and being used correctly?</p>
        </div>
    </x-slot>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-dashboard-kpi label="Active Staff" :value="number_format($kpis['activeUsers'])" tone="blue" :href="route('staff-users.index')" />
        <x-dashboard-kpi label="Audit Events Today" :value="number_format($kpis['auditEventsToday'])" :href="route('audit-logs.index')" />
        <x-dashboard-kpi label="Visits Today" :value="number_format($kpis['visitsToday'])" :href="route('visits.index')" />
        <x-dashboard-kpi label="Bills Today" :value="number_format($kpis['billsToday'])" tone="green" :href="route('billing.index')" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <x-dashboard-chart :chart="$charts['systemActivity']" />
        <x-dashboard-chart :chart="$charts['auditEvents']" />
    </div>

    <div class="mt-6">
        <x-dashboard-chart :chart="$charts['userActivity']" />
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        <a href="{{ route('audit-logs.index') }}" class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm transition hover:border-hospital-200">
            <p class="font-semibold text-gray-800"><i class="fa-solid fa-clipboard-list mr-2 text-hospital-700"></i> Audit Log</p>
            <p class="mt-1 text-sm text-gray-500">Review staff actions and compliance.</p>
        </a>
        <a href="{{ route('staff-users.index') }}" class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm transition hover:border-hospital-200">
            <p class="font-semibold text-gray-800"><i class="fa-solid fa-user-gear mr-2 text-hospital-700"></i> Staff Users</p>
            <p class="mt-1 text-sm text-gray-500">Manage accounts and access.</p>
        </a>
        <a href="{{ route('billable-services.index') }}" class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm transition hover:border-hospital-200">
            <p class="font-semibold text-gray-800"><i class="fa-solid fa-list-check mr-2 text-hospital-700"></i> Service Catalogue</p>
            <p class="mt-1 text-sm text-gray-500">Maintain billable services and prices.</p>
        </a>
    </div>
</x-app-layout>
