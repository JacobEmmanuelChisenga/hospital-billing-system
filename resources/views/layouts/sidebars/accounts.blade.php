<div>
    <x-sidebar-section title="Payments & Receipts" />
    <div class="space-y-1">
        <x-sidebar-link :href="route('deposits.index')" :active="request()->routeIs('deposits.*')" icon="fa-solid fa-money-bill-wave">
            Deposits
        </x-sidebar-link>
        <x-sidebar-link :href="route('company-accounts.index')" :active="request()->routeIs('company-accounts.*') || request()->routeIs('company-deposits.*')" icon="fa-solid fa-building">
            Company Accounts
        </x-sidebar-link>
        <x-sidebar-link :href="route('membership-fees.index')" :active="request()->routeIs('membership-fees.*')" icon="fa-solid fa-id-card">
            Membership Payments
        </x-sidebar-link>
    </div>
</div>

<div>
    <x-sidebar-section title="Financial" />
    <div class="space-y-1">
        <x-sidebar-link :href="route('billing.index')" :active="request()->routeIs('billing.*')" icon="fa-solid fa-receipt">
            Receipts
        </x-sidebar-link>
        <x-sidebar-link :href="route('reports.index')" :active="request()->routeIs('reports.*')" icon="fa-solid fa-chart-column">
            Reports
        </x-sidebar-link>
    </div>
</div>

<div>
    <x-sidebar-section title="Patients" />
    <div class="space-y-1">
        <x-sidebar-link :href="route('patients.index')" :active="request()->routeIs('patients.*')" icon="fa-solid fa-users">
            Patient Directory
        </x-sidebar-link>
    </div>
</div>
