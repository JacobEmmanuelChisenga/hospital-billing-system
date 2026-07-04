<x-app-layout>
    <x-slot name="header">
        <x-page-header
            title="Clinical Dashboard"
            subtitle="How many patients am I treating and what conditions?"
            theme="nurse"
            :dashboard="true"
        />
    </x-slot>

    <div class="grid gap-4 sm:grid-cols-3">
        @foreach ($kpis as $kpi)
            <x-dashboard-kpi
                :label="$kpi['label']"
                :value="$kpi['value']"
                :tone="$kpi['tone']"
                :href="$kpi['href'] ?? null"
                :trend="$kpi['trend'] ?? null"
                :trendLabel="$kpi['trendLabel'] ?? null"
                :hint="$kpi['hint'] ?? null"
            />
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <x-dashboard-chart :chart="$charts['patientsSeen']" />
        <x-dashboard-chart :chart="$charts['caseStatus']" />
    </div>

    <div class="mt-6">
        <x-dashboard-chart :chart="$charts['diagnoses']" />
    </div>

    <div class="mt-6">
        <x-dashboard-recent-panel title="Today's Queue" description="Patients waiting for or in consultation" :href="route('nurse.queue')">
            @if (count($recent) === 0)
                <p class="text-sm text-slate-500">No patients in the queue right now.</p>
            @else
                <x-table-scroll>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>ID</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recent as $row)
                                <tr>
                                    <td class="font-medium text-slate-800">
                                        <a href="{{ $row['url'] }}" class="action-link">{{ $row['patient'] }}</a>
                                    </td>
                                    <td>{{ $row['number'] }}</td>
                                    <td>
                                        <span class="badge {{ $row['statusClass'] }}">{{ $row['status'] }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </x-table-scroll>
            @endif
        </x-dashboard-recent-panel>
    </div>
</x-app-layout>
