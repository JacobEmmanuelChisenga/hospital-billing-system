<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Edit Service — {{ $service->name }}" subtitle="Update the name, category, price, or availability of this catalogue item." />
    </x-slot>

    <x-flash-messages />

    <div class="card card-body max-w-2xl">
        @include('billable-services.partials.form', ['service' => $service, 'categories' => $categories])
    </div>
</x-app-layout>
