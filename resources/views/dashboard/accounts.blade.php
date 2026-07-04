<x-app-layout>
    <x-slot name="header">
        <x-page-header
            title="Financial Dashboard"
            subtitle="Where is the money coming from and going?"
            theme="accounts"
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
        <x-dashboard-chart :chart="$charts['revenueBreakdown']" />
        <x-dashboard-chart :chart="$charts['paymentMethods']" />
    </div>

    <div class="mt-6">
        <x-dashboard-chart :chart="$charts['depositVsBilling']" height="tall" />
    </div>

    <div class="mt-6">
        <x-dashboard-recent-panel title="Recent Receipts" description="Bills, deposits, and membership payments today" :href="route('billing.index')">
            @if (count($recent) === 0)
                <p class="text-sm text-slate-500">No receipts issued yet today.</p>
            @else
                <x-table-scroll>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Receipt</th>
                                <th>Payer</th>
                                <th>Amount</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recent as $row)
                                <tr>
                                    <td class="font-medium text-slate-800">
                                        @if ($row['url'])
                                            <a href="{{ $row['url'] }}" class="action-link">{{ $row['id'] }}</a>
                                        @else
                                            {{ $row['id'] }}
                                        @endif
                                    </td>
                                    <td>{{ $row['payer'] }}</td>
                                    <td class="font-medium text-slate-800">{{ $row['amount'] }}</td>
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
