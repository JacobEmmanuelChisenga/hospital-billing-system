<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Dashboard</h2>
            <p class="mt-1 text-sm text-gray-500">
                Welcome back, {{ Auth::user()->name }}. You are signed in as {{ Auth::user()->role->label() }}.
            </p>
        </div>
    </x-slot>

    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-100">
            <p class="text-sm font-medium text-gray-500">Today's Deposits</p>
            <p class="mt-2 text-2xl font-bold text-hospital-800">K {{ number_format((float) $todaysDepositsTotal, 2) }}</p>
            <p class="mt-1 text-xs text-gray-400">{{ $todaysDepositsCount }} deposit(s) loaded today</p>
            @if (Auth::user()->canPerformFinancialOperations())
                <a href="{{ route('deposits.index') }}" class="mt-1 inline-block text-xs text-hospital-700 hover:underline">View deposits</a>
            @endif
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-100">
            <p class="text-sm font-medium text-gray-500">Today's Bills</p>
            <p class="mt-2 text-2xl font-bold text-hospital-800">K {{ number_format((float) $todaysBillsTotal, 2) }}</p>
            <p class="mt-1 text-xs text-gray-400">{{ $todaysBillsCount }} bill(s) posted today</p>
            @if (Auth::user()->canViewFinancialRecords())
                <a href="{{ route('billing.index') }}" class="mt-1 inline-block text-xs text-hospital-700 hover:underline">View receipts</a>
            @elseif (Auth::user()->canManageVisits())
                <a href="{{ route('visits.index') }}" class="mt-1 inline-block text-xs text-hospital-700 hover:underline">Patient visits</a>
            @endif
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-100">
            <p class="text-sm font-medium text-gray-500">Active Patients</p>
            <p class="mt-2 text-2xl font-bold text-hospital-800">{{ number_format($activePatientCount) }}</p>
            @if (Auth::user()->canAccessPatientModules())
                <a href="{{ route('patients.index') }}" class="mt-1 inline-block text-xs text-hospital-700 hover:underline">View all patients</a>
            @endif
        </div>
    </div>

    <div class="mt-6 rounded-xl bg-white p-6 shadow-sm border border-gray-100">
        <h3 class="text-base font-semibold text-gray-800">Your Role</h3>
        @if (Auth::user()->isAdministrator())
            <p class="mt-2 text-sm text-gray-600">Manage staff users, system settings, and audit logs. View reports and operational records.</p>
        @elseif (Auth::user()->isAccountsStaff())
            <p class="mt-2 text-sm text-gray-600">Receive membership payments and deposits, print receipts, and produce financial reports. You do not register patients or post bills.</p>
        @elseif (Auth::user()->isRegistryClerk())
            <p class="mt-2 text-sm text-gray-600">Register patients, open visits, review services, and post charges. Nurse records clinical notes before you finish the visit.</p>
        @elseif (Auth::user()->isNurse())
            <p class="mt-2 text-sm text-gray-600">Record clinical notes on open patient visits. You do not handle registration, deposits, or billing.</p>
        @endif
    </div>
</x-app-layout>
