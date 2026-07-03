<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Clinical Dashboard</h2>
            <p class="mt-1 text-sm text-gray-500">How many patients am I treating and what conditions?</p>
        </div>
    </x-slot>

    <div class="grid gap-4 sm:grid-cols-3">
        <x-dashboard-kpi label="Patients Waiting" :value="number_format($kpis['patientsWaiting'])" tone="amber" :href="route('nurse.queue')" />
        <x-dashboard-kpi label="Patients Seen" :value="number_format($kpis['patientsSeen'])" tone="green" :href="route('nurse.consultations', ['period' => 'today'])" />
        <x-dashboard-kpi label="Pending Consultations" :value="number_format($kpis['pendingConsultations'])" tone="orange" :href="route('nurse.active')" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <x-dashboard-chart :chart="$charts['patientsSeen']" />
        <x-dashboard-chart :chart="$charts['caseStatus']" />
    </div>

    <div class="mt-6">
        <x-dashboard-chart :chart="$charts['diagnoses']" />
    </div>

    <div class="mt-6">
        <a href="{{ route('nurse.queue') }}" class="block rounded-xl border border-hospital-200 bg-hospital-50 p-5 shadow-sm transition hover:bg-hospital-100">
            <p class="font-semibold text-hospital-900"><i class="fa-solid fa-list-check mr-2"></i> Go to Today's Queue</p>
            <p class="mt-1 text-sm text-hospital-800">Start or continue patient consultations.</p>
        </a>
    </div>
</x-app-layout>
