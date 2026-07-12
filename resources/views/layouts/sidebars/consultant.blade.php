<div>
    <x-sidebar-section title="Patient Care" />
    <div class="space-y-1">
        <x-sidebar-link
            :href="route('consultant.queue')"
            :active="request()->routeIs('consultant.queue')"
            icon="fa-solid fa-list-check"
        >
            Today's Queue
        </x-sidebar-link>
        <x-sidebar-link
            :href="route('consultant.active')"
            :active="request()->routeIs('consultant.active')"
            icon="fa-solid fa-stethoscope"
        >
            Active Visits
        </x-sidebar-link>
        <x-sidebar-link
            :href="route('consultant.consultations')"
            :active="request()->routeIs('consultant.consultations')"
            icon="fa-solid fa-notes-medical"
        >
            Consultation History
        </x-sidebar-link>
    </div>
</div>

<div>
    <x-sidebar-section title="Patients" />
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
