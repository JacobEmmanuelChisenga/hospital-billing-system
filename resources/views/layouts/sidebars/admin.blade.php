<div>
    <x-sidebar-section title="Administration" />
    <div class="space-y-1">
        <x-sidebar-link :href="route('audit-logs.index')" :active="request()->routeIs('audit-logs.*')" icon="fa-solid fa-clipboard-list">
            Audit Log
        </x-sidebar-link>
        <x-sidebar-link :href="route('system-settings.edit')" :active="request()->routeIs('system-settings.*')" icon="fa-solid fa-gear">
            System Settings
        </x-sidebar-link>
        <x-sidebar-link :href="route('billable-services.index')" :active="request()->routeIs('billable-services.*')" icon="fa-solid fa-list-check">
            Service Catalogue
        </x-sidebar-link>
        <x-sidebar-link :href="route('staff-users.index')" :active="request()->routeIs('staff-users.*')" icon="fa-solid fa-user-gear">
            Staff Users
        </x-sidebar-link>
    </div>
</div>

<div>
    <x-sidebar-section title="Overview" />
    <div class="space-y-1">
        <x-sidebar-link :href="route('patients.index')" :active="request()->routeIs('patients.*')" icon="fa-solid fa-users">
            Patients
        </x-sidebar-link>
        <x-sidebar-link :href="route('visits.index')" :active="request()->routeIs('visits.*')" icon="fa-solid fa-folder-open">
            Patient Visits
        </x-sidebar-link>
        <x-sidebar-link :href="route('billing.index')" :active="request()->routeIs('billing.*')" icon="fa-solid fa-receipt">
            Receipts
        </x-sidebar-link>
        <x-sidebar-link :href="route('reports.index')" :active="request()->routeIs('reports.*')" icon="fa-solid fa-chart-column">
            Reports
        </x-sidebar-link>
    </div>
</div>
