<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Service Catalogue" subtitle="Manage billable services and fixed prices used when posting visit charges.">
            <x-slot name="actions">
                <a href="{{ route('billable-services.create') }}" class="btn-primary">
                    <i class="fa-solid fa-plus"></i> Add Service
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <x-flash-messages />

    <x-filter-panel>
        <form method="GET" action="{{ route('billable-services.index') }}" class="grid gap-4 md:grid-cols-5">
            <div class="md:col-span-2">
                <label for="search" class="form-label">Search</label>
                <input type="text" id="search" name="search" value="{{ $search }}"
                    placeholder="Service name..."
                    class="form-input">
            </div>
            <div>
                <label for="category" class="form-label">Category</label>
                <select id="category" name="category" class="form-input">
                    <option value="">All categories</option>
                    @foreach ($categories as $categoryOption)
                        <option value="{{ $categoryOption->value }}" @selected($category === $categoryOption->value)>{{ $categoryOption->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-input">
                    <option value="">All</option>
                    <option value="active" @selected($status === 'active')>Active</option>
                    <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                <a href="{{ route('billable-services.index') }}" class="btn-secondary">Clear</a>
            </div>
        </form>
    </x-filter-panel>

    <x-data-panel>
        <x-table-scroll>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Category</th>
                        <th class="text-right">Price</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($services as $service)
                        <tr>
                            <td class="font-medium">{{ $service->name }}</td>
                            <td>{{ $service->category->label() }}</td>
                            <td class="text-right font-medium">K {{ number_format((float) $service->price, 2) }}</td>
                            <td>
                                @if ($service->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-neutral">Inactive</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('billable-services.edit', $service) }}" class="action-link">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="!py-12 text-center text-slate-500">
                                No services in the catalogue yet.
                                <a href="{{ route('billable-services.create') }}" class="action-link">Add the first service</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-table-scroll>

        @if ($services->hasPages())
            <x-slot name="footer">{{ $services->links() }}</x-slot>
        @endif
    </x-data-panel>
</x-app-layout>
