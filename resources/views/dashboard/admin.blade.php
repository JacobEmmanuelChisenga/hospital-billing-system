<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Administrator Dashboard" theme="admin" :dashboard="true">
            <x-slot name="actions">
                <div class="flex items-center gap-3">
                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-semibold text-slate-900">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-slate-500">{{ Auth::user()->role->label() }}</p>
                    </div>
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-slate-200 text-slate-600">
                        <i class="fa-solid fa-user"></i>
                    </div>
                </div>
            </x-slot>
        </x-page-header>
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

        <div class="data-panel">
            <div class="panel-header">
                <h3 class="section-title">Recent Audit Logs</h3>
            </div>
            <div class="panel-body !py-2">
                @if (count($recent) === 0)
                    <p class="py-4 text-sm text-slate-500">No audit events recorded yet.</p>
                @else
                    <ul class="divide-y divide-slate-100">
                        @foreach ($recent as $row)
                            <li class="flex items-start justify-between gap-4 py-3">
                                <a href="{{ $row['url'] }}" class="text-sm font-medium text-slate-800 hover:text-hospital-700 hover:underline">
                                    {{ $row['description'] }}
                                </a>
                                <span class="shrink-0 text-xs text-slate-400">{{ $row['time'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
            <div class="panel-footer">
                <a href="{{ route('audit-logs.index') }}" class="action-link text-sm">
                    View all audit logs
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
