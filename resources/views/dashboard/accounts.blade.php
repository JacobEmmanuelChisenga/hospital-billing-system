<x-app-layout>
    <x-slot name="header">
        <div class="rounded-xl border border-violet-200 bg-violet-50 px-5 py-4">
            <h2 class="text-xl font-semibold text-gray-900">Financial Dashboard</h2>
            <p class="mt-1 text-sm text-gray-600">Where is the money coming from and going?</p>
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
        <x-dashboard-chart :chart="$charts['revenueBreakdown']" />
        <x-dashboard-chart :chart="$charts['paymentMethods']" />
    </div>

    <div class="mt-6">
        <x-dashboard-chart :chart="$charts['depositVsBilling']" height="tall" />
    </div>

    <div class="mt-6">
        <x-dashboard-recent-panel title="Recent Receipts" description="Bills, deposits, and membership payments today" :href="route('billing.index')">
            @if (count($recent) === 0)
                <p class="text-sm text-gray-500">No receipts issued yet today.</p>
            @else
                <x-table-scroll>
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead>
                            <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                <th class="pb-3 pr-4">Receipt</th>
                                <th class="pb-3 pr-4">Payer</th>
                                <th class="pb-3 pr-4">Amount</th>
                                <th class="pb-3">Time</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach ($recent as $row)
                                <tr>
                                    <td class="py-3 pr-4 font-medium text-gray-800">
                                        @if ($row['url'])
                                            <a href="{{ $row['url'] }}" class="hover:text-hospital-700 hover:underline">{{ $row['id'] }}</a>
                                        @else
                                            {{ $row['id'] }}
                                        @endif
                                    </td>
                                    <td class="py-3 pr-4 text-gray-600">{{ $row['payer'] }}</td>
                                    <td class="py-3 pr-4 font-medium text-gray-800">{{ $row['amount'] }}</td>
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
