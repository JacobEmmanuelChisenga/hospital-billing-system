<x-app-layout>
    <x-slot name="header">
        <x-page-header
            title="Operations Dashboard"
            subtitle="What is happening with patient flow today?"
            theme="registry"
            :dashboard="true"
        />
    </x-slot>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
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
        <x-dashboard-chart :chart="$charts['patientFlow']" height="tall" />
        <x-dashboard-chart :chart="$charts['patientTypes']" />
    </div>

    <div class="mt-6">
        <x-dashboard-chart :chart="$charts['pendingWorkload']" />
    </div>

    <div class="mt-6">
        <x-dashboard-recent-panel title="Recent Registrations" description="Visits opened today" :href="route('visits.index')">
            @if (count($recent) === 0)
                <p class="text-sm text-slate-500">No visits registered yet today.</p>
            @else
                <x-table-scroll>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>ID</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recent as $row)
                                <tr>
                                    <td class="font-medium text-slate-800">
                                        <a href="{{ $row['url'] }}" class="action-link">{{ $row['patient'] }}</a>
                                    </td>
                                    <td>{{ $row['number'] }}</td>
                                    <td class="text-slate-500">{{ $row['time'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </x-table-scroll>
            @endif
        </x-dashboard-recent-panel>
    </div>
</x-app-layout>
