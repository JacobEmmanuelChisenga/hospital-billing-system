<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Register Patient</h2>
            <p class="mt-1 text-sm text-gray-500">Create a new member, dependant, or company patient account.</p>
        </div>
    </x-slot>

    <x-flash-messages />

    <div class="max-w-3xl rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('patients.store') }}" class="space-y-6">
            @csrf

            @include('patients.partials.form')

            <div class="flex items-center gap-3 border-t border-gray-100 pt-6">
                <button type="submit" class="inline-flex items-center rounded-lg bg-hospital-700 px-4 py-2 text-sm font-medium text-white hover:bg-hospital-800">
                    <i class="fa-solid fa-floppy-disk mr-2"></i> Register Patient
                </button>
                <a href="{{ route('patients.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
