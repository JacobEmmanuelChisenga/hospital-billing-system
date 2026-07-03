@php
    $headerTone = match ($theme ?? 'default') {
        'registry' => 'border-blue-200 bg-blue-50',
        'nurse' => 'border-emerald-200 bg-emerald-50',
        'accounts' => 'border-violet-200 bg-violet-50',
        'admin' => 'border-slate-300 bg-slate-100',
        default => 'border-gray-100 bg-white',
    };
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="rounded-xl border px-5 py-4 {{ $headerTone }}">
            <h2 class="text-xl font-semibold text-gray-900">Operations Dashboard</h2>
            <p class="mt-1 text-sm text-gray-600">What is happening with patient flow today?</p>
        </div>
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
                <p class="text-sm text-gray-500">No visits registered yet today.</p>
            @else
                <x-table-scroll>
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead>
                            <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                <th class="pb-3 pr-4">Patient</th>
                                <th class="pb-3 pr-4">ID</th>
                                <th class="pb-3">Time</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach ($recent as $row)
                                <tr>
                                    <td class="py-3 pr-4 font-medium text-gray-800">
                                        <a href="{{ $row['url'] }}" class="hover:text-hospital-700 hover:underline">{{ $row['patient'] }}</a>
                                    </td>
                                    <td class="py-3 pr-4 text-gray-600">{{ $row['number'] }}</td>
                                    <td class="py-3 text-gray-500">{{ $row['time'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </x-table-scroll>
            @endif
        </x-dashboard-recent-panel>
    </div>
</x-app-layout>
