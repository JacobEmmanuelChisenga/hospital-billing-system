<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Operations Dashboard</h2>
            <p class="mt-1 text-sm text-gray-500">What is happening with patient flow today?</p>
        </div>
    </x-slot>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-dashboard-kpi label="Today's Patients" :value="number_format($kpis['todaysPatients'])" :href="route('visits.index')" />
        <x-dashboard-kpi label="Pending Visits" :value="number_format($kpis['pendingVisits'])" tone="amber" :href="route('visits.index')" />
        <x-dashboard-kpi label="Pending Charges" :value="number_format($kpis['pendingCharges'])" tone="orange" :href="route('charges.pending')" />
        <x-dashboard-kpi label="Completed Today" :value="number_format($kpis['completedToday'])" tone="green" :href="route('charges.history')" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <x-dashboard-chart :chart="$charts['patientFlow']" />
        <x-dashboard-chart :chart="$charts['patientTypes']" />
    </div>

    <div class="mt-6">
        <x-dashboard-chart :chart="$charts['pendingQueue']" />
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        <a href="{{ route('patients.create') }}" class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm transition hover:border-hospital-200">
            <p class="font-semibold text-gray-800"><i class="fa-solid fa-user-plus mr-2 text-hospital-700"></i> Register Patient</p>
            <p class="mt-1 text-sm text-gray-500">Open a new member, dependant, or company record.</p>
        </a>
        <a href="{{ route('visits.create') }}" class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm transition hover:border-hospital-200">
            <p class="font-semibold text-gray-800"><i class="fa-solid fa-door-open mr-2 text-hospital-700"></i> Open Visit</p>
            <p class="mt-1 text-sm text-gray-500">Start today's patient journey.</p>
        </a>
        <a href="{{ route('charges.post') }}" class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm transition hover:border-hospital-200">
            <p class="font-semibold text-gray-800"><i class="fa-solid fa-file-invoice-dollar mr-2 text-hospital-700"></i> Post Charges</p>
            <p class="mt-1 text-sm text-gray-500">Bill services after nurse consultation.</p>
        </a>
    </div>
</x-app-layout>
