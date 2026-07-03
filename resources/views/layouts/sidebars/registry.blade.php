<div>
    <x-sidebar-section title="Patients" />
    <div class="space-y-1">
        <x-sidebar-link
            :href="route('patients.create')"
            :active="request()->routeIs('patients.create') && ! request()->filled('type')"
            icon="fa-solid fa-user-plus"
        >
            Register Patient
        </x-sidebar-link>
        <x-sidebar-link
            :href="route('patients.index')"
            :active="request()->routeIs('patients.index') || request()->routeIs('patients.show') || request()->routeIs('patients.edit')"
            icon="fa-solid fa-address-book"
        >
            Patient Directory
        </x-sidebar-link>
        <x-sidebar-link
            :href="route('visits.index')"
            :active="request()->routeIs('visits.*') && ! request()->routeIs('charges.*')"
            icon="fa-solid fa-folder-open"
        >
            Patient Visits
        </x-sidebar-link>
    </div>
</div>

<div>
    <x-sidebar-section title="Membership" />
    <div class="space-y-1">
        <x-sidebar-link
            :href="route('patients.create', ['type' => 'member'])"
            :active="request()->routeIs('patients.create') && request('type') === 'member'"
            icon="fa-solid fa-id-card"
        >
            Register Member
        </x-sidebar-link>
        <x-sidebar-link
            :href="route('patients.create', ['type' => 'dependant'])"
            :active="request()->routeIs('patients.create') && request('type') === 'dependant'"
            icon="fa-solid fa-link"
        >
            Dependants
        </x-sidebar-link>
    </div>
</div>

<div>
    <x-sidebar-section title="Charges" />
    <div class="space-y-1">
        <x-sidebar-link
            :href="route('charges.pending')"
            :active="request()->routeIs('charges.pending')"
            icon="fa-solid fa-clock"
        >
            Pending Charges
        </x-sidebar-link>
        <x-sidebar-link
            :href="route('charges.post')"
            :active="request()->routeIs('charges.post')"
            icon="fa-solid fa-file-invoice-dollar"
        >
            Post Charges
        </x-sidebar-link>
        <x-sidebar-link
            :href="route('charges.history')"
            :active="request()->routeIs('charges.history')"
            icon="fa-solid fa-clock-rotate-left"
        >
            Charge History
        </x-sidebar-link>
    </div>
</div>

<div>
    <x-sidebar-section title="Search" />
    <div class="space-y-1">
        <x-sidebar-link
            :href="route('patients.search')"
            :active="request()->routeIs('patients.search')"
            icon="fa-solid fa-magnifying-glass"
        >
            Patient Search
        </x-sidebar-link>
    </div>
</div>
