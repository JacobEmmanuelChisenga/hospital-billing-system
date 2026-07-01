<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Sign In — {{ config('hospital.system_name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex">
            {{-- Left panel: hospital branding (hidden on small screens) --}}
            <div class="hidden lg:flex lg:w-1/2 bg-hospital-900 text-white flex-col justify-center px-12">
                <div class="max-w-md">
                    <p class="text-sm font-semibold uppercase tracking-wider text-hospital-300">
                        {{ config('hospital.name') }}
                    </p>
                    <h1 class="mt-3 text-3xl font-bold leading-tight">
                        {{ config('hospital.section') }}
                    </h1>
                    <p class="mt-2 text-xl text-hospital-200">
                        {{ config('hospital.system_name') }}
                    </p>
                    <p class="mt-6 text-hospital-300 leading-relaxed">
                        Secure staff access for patient accounts, deposits, billing, and reports.
                        Contact your administrator if you need an account.
                    </p>
                </div>
            </div>

            {{-- Right panel: login form --}}
            <div class="flex flex-1 flex-col justify-center items-center px-6 py-12 bg-gray-50">
                <div class="w-full max-w-md">
                    <div class="lg:hidden mb-8 text-center">
                        <p class="text-xs font-semibold uppercase tracking-wider text-hospital-600">
                            {{ config('hospital.name') }}
                        </p>
                        <h1 class="mt-1 text-xl font-bold text-hospital-900">
                            {{ config('hospital.system_name') }}
                        </h1>
                    </div>

                    <div class="bg-white shadow-md rounded-xl px-8 py-8 border border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-900">Staff Sign In</h2>
                        <p class="mt-1 text-sm text-gray-500">
                            Session expires after {{ config('hospital.session_lifetime_minutes') }} minutes of inactivity.
                        </p>

                        <div class="mt-6">
                            {{ $slot }}
                        </div>
                    </div>

                    <p class="mt-6 text-center text-xs text-gray-400">
                        &copy; {{ date('Y') }} {{ config('hospital.name') }}. Internal use only.
                    </p>
                </div>
            </div>
        </div>
    </body>
</html>
