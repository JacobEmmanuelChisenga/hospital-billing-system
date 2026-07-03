<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Service Catalogue</h2>
                <p class="mt-1 text-sm text-gray-500">Manage billable services and fixed prices used when posting visit charges.</p>
            </div>
            <a href="{{ route('billable-services.create') }}"
               class="inline-flex items-center justify-center rounded-lg bg-hospital-700 px-4 py-2 text-sm font-medium text-white hover:bg-hospital-800">
                <i class="fa-solid fa-plus mr-2"></i> Add Service
            </a>
        </div>
    </x-slot>

    <x-flash-messages />

    <div class="mb-6 rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('billable-services.index') }}" class="grid gap-4 md:grid-cols-5">
            <div class="md:col-span-2">
                <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                <input type="text" id="search" name="search" value="{{ $search }}"
                    placeholder="Service name..."
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">
            </div>
            <div>
                <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                <select id="category" name="category" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">
                    <option value="">All categories</option>
                    @foreach ($categories as $categoryOption)
                        <option value="{{ $categoryOption->value }}" @selected($category === $categoryOption->value)>{{ $categoryOption->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">
                    <option value="">All</option>
                    <option value="active" @selected($status === 'active')>Active</option>
                    <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="inline-flex items-center rounded-lg bg-hospital-700 px-4 py-2 text-sm font-medium text-white hover:bg-hospital-800">
                    <i class="fa-solid fa-filter mr-2"></i> Filter
                </button>
                <a href="{{ route('billable-services.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Clear</a>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
        <div class="table-scroll -mx-4 px-4 sm:mx-0 sm:px-0">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Service</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Category</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Price</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($services as $service)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $service->name }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $service->category->label() }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">K {{ number_format((float) $service->price, 2) }}</td>
                            <td class="px-4 py-3">
                                @if ($service->is_active)
                                    <span class="inline-flex rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">Active</span>
                                @else
                                    <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('billable-services.edit', $service) }}" class="text-hospital-700 hover:underline">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-gray-500">
                                No services in the catalogue yet.
                                <a href="{{ route('billable-services.create') }}" class="text-hospital-700 hover:underline">Add the first service</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($services->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">{{ $services->links() }}</div>
        @endif
    </div>
</x-app-layout>
