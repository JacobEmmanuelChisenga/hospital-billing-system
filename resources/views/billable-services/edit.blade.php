<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Edit Service — {{ $service->name }}</h2>
            <p class="mt-1 text-sm text-gray-500">Update the name, category, price, or availability of this catalogue item.</p>
        </div>
    </x-slot>

    <x-flash-messages />

    <div class="max-w-2xl rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
        @include('billable-services.partials.form', ['service' => $service, 'categories' => $categories])
    </div>
</x-app-layout>
