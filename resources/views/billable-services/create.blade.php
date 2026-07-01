<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Add Service</h2>
            <p class="mt-1 text-sm text-gray-500">Add a new item to the fixed-price service catalogue.</p>
        </div>
    </x-slot>

    <x-flash-messages />

    <div class="max-w-2xl rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
        @include('billable-services.partials.form', ['categories' => $categories])
    </div>
</x-app-layout>
