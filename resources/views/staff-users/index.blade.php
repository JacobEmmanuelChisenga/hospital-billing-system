<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Staff Users</h2>
                <p class="mt-1 text-sm text-gray-500">Manage staff accounts, roles, and access status.</p>
            </div>
            <a href="{{ route('staff-users.create') }}"
               class="inline-flex items-center justify-center rounded-lg bg-hospital-700 px-4 py-2 text-sm font-medium text-white hover:bg-hospital-800">
                <i class="fa-solid fa-plus mr-2"></i> Add Staff User
            </a>
        </div>
    </x-slot>

    <x-flash-messages />

    <div class="mb-6 rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('staff-users.index') }}" class="grid gap-4 md:grid-cols-5">
            <div class="md:col-span-2">
                <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                <input type="text" id="search" name="search" value="{{ $search }}"
                    placeholder="Name or email..."
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">
            </div>
            <div>
                <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                <select id="role" name="role" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">
                    <option value="">All roles</option>
                    @foreach ($roles as $roleOption)
                        <option value="{{ $roleOption->value }}" @selected($role === $roleOption->value)>{{ $roleOption->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $statusOption)
                        <option value="{{ $statusOption->value }}" @selected($status === $statusOption->value)>{{ $statusOption->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="inline-flex items-center rounded-lg bg-hospital-700 px-4 py-2 text-sm font-medium text-white hover:bg-hospital-800">
                    <i class="fa-solid fa-filter mr-2"></i> Filter
                </button>
                <a href="{{ route('staff-users.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Clear</a>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
        <div class="table-scroll -mx-4 px-4 sm:mx-0 sm:px-0">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Name</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Email</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Role</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($users as $staffUser)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $staffUser->name }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $staffUser->email }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $staffUser->role->label() }}</td>
                            <td class="px-4 py-3">
                                @if ($staffUser->isActive())
                                    <span class="inline-flex rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">Active</span>
                                @else
                                    <span class="inline-flex rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('staff-users.edit', $staffUser) }}" class="text-hospital-700 hover:underline">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-gray-500">No staff users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">{{ $users->links() }}</div>
        @endif
    </div>
</x-app-layout>
