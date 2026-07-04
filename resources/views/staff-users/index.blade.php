<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Staff Users" subtitle="Manage staff accounts, roles, and access status.">
            <x-slot name="actions">
                <a href="{{ route('staff-users.create') }}" class="btn-primary">
                    <i class="fa-solid fa-plus"></i> Add Staff User
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <x-flash-messages />

    <x-filter-panel>
        <form method="GET" action="{{ route('staff-users.index') }}" class="grid gap-4 md:grid-cols-5">
            <div class="md:col-span-2">
                <label for="search" class="form-label">Search</label>
                <input type="text" id="search" name="search" value="{{ $search }}"
                    placeholder="Name or email..."
                    class="form-input">
            </div>
            <div>
                <label for="role" class="form-label">Role</label>
                <select id="role" name="role" class="form-input">
                    <option value="">All roles</option>
                    @foreach ($roles as $roleOption)
                        <option value="{{ $roleOption->value }}" @selected($role === $roleOption->value)>{{ $roleOption->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-input">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $statusOption)
                        <option value="{{ $statusOption->value }}" @selected($status === $statusOption->value)>{{ $statusOption->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                <a href="{{ route('staff-users.index') }}" class="btn-secondary">Clear</a>
            </div>
        </form>
    </x-filter-panel>

    <x-data-panel>
        <x-table-scroll>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $staffUser)
                        <tr>
                            <td class="font-medium">{{ $staffUser->name }}</td>
                            <td>{{ $staffUser->email }}</td>
                            <td>{{ $staffUser->role->label() }}</td>
                            <td>
                                @if ($staffUser->isActive())
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-danger">Inactive</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('staff-users.edit', $staffUser) }}" class="action-link">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="!py-12 text-center text-slate-500">No staff users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-table-scroll>

        @if ($users->hasPages())
            <x-slot name="footer">{{ $users->links() }}</x-slot>
        @endif
    </x-data-panel>
</x-app-layout>
