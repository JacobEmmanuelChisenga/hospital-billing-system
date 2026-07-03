{{--
    Left sidebar navigation — drawer on mobile, fixed column on large screens.
--}}
<aside
    x-cloak
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    class="fixed inset-y-0 left-0 z-50 flex w-72 max-w-[min(100vw,20rem)] flex-col bg-hospital-900 text-white shadow-xl transition-transform duration-200 ease-in-out lg:static lg:z-auto lg:w-64 lg:max-w-none lg:shrink-0 lg:shadow-none"
    aria-label="Main navigation"
>
    <div class="flex items-start justify-between gap-3 border-b border-hospital-700 px-4 py-5 sm:px-5 sm:py-6">
        <a href="{{ route('dashboard') }}" class="block min-w-0 flex-1" @click="sidebarOpen = false">
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
        <button
            type="button"
            @click="sidebarOpen = false"
            class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-hospital-200 hover:bg-hospital-800 lg:hidden"
            aria-label="Close navigation menu"
        >
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>
    </div>

    <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-4" @click="if ($event.target.closest('a')) sidebarOpen = false">
        <div>
            <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="fa-solid fa-gauge-high">
                Dashboard
            </x-sidebar-link>
        </div>

        @if (Auth::user()->isRegistryClerk())
            @include('layouts.sidebars.registry')
        @elseif (Auth::user()->isNurse())
            @include('layouts.sidebars.nurse')
        @elseif (Auth::user()->isAccountsStaff())
            @include('layouts.sidebars.accounts')
        @elseif (Auth::user()->isAdministrator())
            @include('layouts.sidebars.admin')
        @endif
    </nav>

    <div class="border-t border-hospital-700 px-4 py-4">
        <div class="mb-3">
            <p class="truncate text-sm font-medium text-white">{{ Auth::user()->name }}</p>
            <p class="truncate text-xs text-hospital-300">{{ Auth::user()->email }}</p>
            <p class="mt-1 inline-flex items-center rounded-full bg-hospital-700 px-2 py-0.5 text-xs text-hospital-100">
                {{ Auth::user()->role->label() }}
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('profile.edit') }}" class="flex-1 rounded-lg bg-hospital-800 px-3 py-2.5 text-center text-xs font-medium text-hospital-100 transition-colors hover:bg-hospital-700">
                <i class="fa-solid fa-user mr-1"></i> Profile
            </a>
            <form method="POST" action="{{ route('logout') }}" class="flex-1">
                @csrf
                <button type="submit" class="w-full rounded-lg bg-hospital-800 px-3 py-2.5 text-xs font-medium text-hospital-100 transition-colors hover:bg-hospital-700">
                    <i class="fa-solid fa-right-from-bracket mr-1"></i> Sign Out
                </button>
            </form>
        </div>
    </div>
</aside>
