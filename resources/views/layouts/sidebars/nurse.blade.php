<div>
    <x-sidebar-section title="Patient Care" />
    <div class="space-y-1">
        <x-sidebar-link
            :href="route('nurse.queue')"
            :active="request()->routeIs('nurse.queue')"
            icon="fa-solid fa-list-check"
        >
            Today's Queue
        </x-sidebar-link>
        <x-sidebar-link
            :href="route('nurse.active')"
            :active="request()->routeIs('nurse.active')"
            icon="fa-solid fa-stethoscope"
        >
            Active Visits
        </x-sidebar-link>
        <x-sidebar-link
            :href="route('nurse.consultations')"
            :active="request()->routeIs('nurse.consultations')"
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
