<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Add Service" subtitle="Add a new item to the fixed-price service catalogue." />
    </x-slot>

    <x-flash-messages />

    <div class="card card-body max-w-2xl">
        @include('billable-services.partials.form', ['categories' => $categories])
    </div>
</x-app-layout>
