<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold text-gray-900">Administrator Dashboard</h2>
            <div class="flex items-center gap-3">
                <div class="hidden text-right sm:block">
                    <p class="text-sm font-semibold text-gray-900">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-gray-500">{{ Auth::user()->role->label() }}</p>
                </div>
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-200 text-slate-600">
                    <i class="fa-solid fa-user"></i>
                </div>
            </div>
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
                :status="$kpi['status'] ?? null"
            />
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <x-dashboard-chart :chart="$charts['systemActivity']" />
        <x-dashboard-chart :chart="$charts['userActivity']" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <x-dashboard-chart :chart="$charts['auditEvents']" />

        <div class="rounded-xl border border-gray-100 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h3 class="text-base font-semibold text-gray-800">Recent Audit Logs</h3>
            </div>
            <div class="px-5 py-2">
                @if (count($recent) === 0)
                    <p class="py-4 text-sm text-gray-500">No audit events recorded yet.</p>
                @else
                    <ul class="divide-y divide-gray-100">
                        @foreach ($recent as $row)
                            <li class="flex items-start justify-between gap-4 py-3">
                                <a href="{{ $row['url'] }}" class="text-sm font-medium text-gray-800 hover:text-hospital-700 hover:underline">
                                    {{ $row['description'] }}
                                </a>
                                <span class="shrink-0 text-xs text-gray-400">{{ $row['time'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
            <div class="border-t border-gray-100 px-5 py-4">
                <a href="{{ route('audit-logs.index') }}" class="text-sm font-medium text-hospital-700 hover:underline">
                    View all audit logs
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
