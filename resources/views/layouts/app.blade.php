<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('hospital.system_name') }} — {{ config('hospital.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body
        x-data="{ sidebarOpen: false }"
        @keydown.escape.window="sidebarOpen = false"
        :class="{ 'max-lg:overflow-hidden': sidebarOpen }"
        class="font-sans antialiased bg-gray-100"
    >
        {{-- Mobile sidebar backdrop --}}
        <div
            x-show="sidebarOpen"
            x-cloak
            x-transition:enter="transition-opacity ease-linear duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="sidebarOpen = false"
            class="fixed inset-0 z-40 bg-gray-900/60 lg:hidden"
            aria-hidden="true"
        ></div>

        <div class="flex min-h-[100dvh]">
            @include('layouts.sidebar')

            <div class="flex min-w-0 flex-1 flex-col">
                {{-- Mobile top bar --}}
                <div class="sticky top-0 z-30 flex items-center gap-3 border-b border-gray-200 bg-white px-4 py-3 shadow-sm lg:hidden">
                    <button
                        type="button"
                        @click="sidebarOpen = true"
                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-50"
                        aria-label="Open navigation menu"
                    >
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-gray-900">{{ config('hospital.system_name') }}</p>
                        <p class="truncate text-xs text-gray-500">{{ Auth::user()->role->label() }}</p>
                    </div>
                    <a
                        href="{{ route('profile.edit') }}"
                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-50"
                        aria-label="Profile"
                    >
                        <i class="fa-solid fa-user"></i>
                    </a>
                </div>

                @isset($header)
                    <header class="border-b border-gray-200 bg-white shadow-sm">
                        <div class="px-4 py-4 sm:px-6 sm:py-5">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <main class="flex-1 p-4 sm:p-6">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
