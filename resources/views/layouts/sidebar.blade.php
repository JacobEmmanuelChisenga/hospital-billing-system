{{--
    Left sidebar navigation — menu items are shown or hidden based on the
    signed-in user's role.
--}}
<aside
    x-data="{ open: false }"
    class="flex flex-col w-64 shrink-0 bg-hospital-900 text-white min-h-screen"
>
    <div class="px-5 py-6 border-b border-hospital-700">
        <a href="{{ route('dashboard') }}" class="block">
            <p class="text-xs font-semibold uppercase tracking-wider text-hospital-300">
                {{ config('hospital.name') }}
            </p>
            <p class="mt-1 text-lg font-bold leading-tight">
                {{ config('hospital.section') }}
            </p>
            <p class="mt-0.5 text-sm text-hospital-200">
                {{ config('hospital.system_name') }}
            </p>
        </a>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-6">
        <div>
            <p class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-hospital-400">Overview</p>
            <div class="space-y-1">
                <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="fa-solid fa-gauge-high">
                    Dashboard
                </x-sidebar-link>
            </div>
        </div>

        @if (Auth::user()->canAccessPatientModules())
            <div>
                <p class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-hospital-400">Patients</p>
                <div class="space-y-1">
                    <x-sidebar-link :href="route('patients.index')" :active="request()->routeIs('patients.*')" icon="fa-solid fa-users">
                        Patients
                    </x-sidebar-link>
                </div>
            </div>
        @endif

        @if (Auth::user()->canManageVisits() || Auth::user()->canRecordClinicalNotes() || Auth::user()->isAdministrator())
            <div>
                <p class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-hospital-400">Patient Records</p>
                <div class="space-y-1">
                    <x-sidebar-link :href="route('visits.index')" :active="request()->routeIs('visits.*') || request()->routeIs('clinical-notes.*')" icon="fa-solid fa-folder-open">
                        Patient Visits
                    </x-sidebar-link>
                </div>
            </div>
        @endif

        @if (Auth::user()->canPerformFinancialOperations())
            <div>
                <p class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-hospital-400">Payments & Receipts</p>
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
        @endif

        @if (Auth::user()->canViewFinancialRecords())
            <div>
                <p class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-hospital-400">Financial</p>
                <div class="space-y-1">
                    <x-sidebar-link :href="route('billing.index')" :active="request()->routeIs('billing.*')" icon="fa-solid fa-receipt">
                        Receipts
                    </x-sidebar-link>
                    <x-sidebar-link :href="route('reports.index')" :active="request()->routeIs('reports.*')" icon="fa-solid fa-chart-column">
                        Reports
                    </x-sidebar-link>
                </div>
            </div>
        @endif

        @if (Auth::user()->isAdministrator())
            <div>
                <p class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-hospital-400">Administration</p>
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
        @endif
    </nav>

    <div class="border-t border-hospital-700 px-4 py-4">
        <div class="mb-3">
            <p class="text-sm font-medium text-white truncate">{{ Auth::user()->name }}</p>
            <p class="text-xs text-hospital-300 truncate">{{ Auth::user()->email }}</p>
            <p class="mt-1 inline-flex items-center rounded-full bg-hospital-700 px-2 py-0.5 text-xs text-hospital-100">
                {{ Auth::user()->role->label() }}
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('profile.edit') }}" class="flex-1 text-center rounded-lg bg-hospital-800 px-3 py-2 text-xs font-medium text-hospital-100 hover:bg-hospital-700 transition-colors">
                <i class="fa-solid fa-user mr-1"></i> Profile
            </a>
            <form method="POST" action="{{ route('logout') }}" class="flex-1">
                @csrf
                <button type="submit" class="w-full rounded-lg bg-hospital-800 px-3 py-2 text-xs font-medium text-hospital-100 hover:bg-hospital-700 transition-colors">
                    <i class="fa-solid fa-right-from-bracket mr-1"></i> Sign Out
                </button>
            </form>
        </div>
    </div>
</aside>
