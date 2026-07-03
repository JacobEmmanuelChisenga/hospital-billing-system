<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Company Accounts</h2>
                <p class="mt-1 text-sm text-gray-500">Manage company deposit pools shared by company patients.</p>
            </div>
            <a href="{{ route('company-accounts.create') }}"
               class="inline-flex items-center justify-center rounded-lg bg-hospital-700 px-4 py-2 text-sm font-medium text-white hover:bg-hospital-800">
                <i class="fa-solid fa-plus mr-2"></i> Add Company
            </a>
        </div>
    </x-slot>

    <x-flash-messages />

    <div class="mb-6 rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('company-accounts.index') }}" class="flex gap-4">
            <div class="flex-1">
                <label for="search" class="block text-sm font-medium text-gray-700">Search company</label>
                <input type="text" id="search" name="search" value="{{ $search }}"
                    placeholder="Company name..."
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="inline-flex items-center rounded-lg bg-hospital-700 px-4 py-2 text-sm font-medium text-white hover:bg-hospital-800">
                    <i class="fa-solid fa-magnifying-glass mr-2"></i> Search
                </button>
                <a href="{{ route('company-accounts.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Clear</a>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
        <div class="table-scroll -mx-4 px-4 sm:mx-0 sm:px-0">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Company</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Contact</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600">Patients</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Pool Balance</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($companies as $company)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <a href="{{ route('company-accounts.show', $company) }}" class="font-medium text-hospital-700 hover:underline">
                                    {{ $company->name }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-gray-700">
                                {{ $company->contact_person ?? '—' }}
                                @if ($company->phone)
                                    <span class="block text-xs text-gray-400">{{ $company->phone }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span @class([
                                    'inline-flex rounded-full px-2 py-0.5 text-xs font-medium',
                                    'bg-green-100 text-green-800' => $company->status === 'active',
                                    'bg-red-100 text-red-800' => $company->status === 'suspended',
                                ])>
                                    {{ ucfirst($company->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center text-gray-700">{{ $company->patients_count }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">K {{ number_format((float) $company->balance, 2) }}</td>
                            <td class="px-4 py-3 text-right space-x-3">
                                <a href="{{ route('company-accounts.show', $company) }}" class="text-hospital-700 hover:text-hospital-900">
                                    <i class="fa-solid fa-eye"></i> View
                                </a>
                                <a href="{{ route('company-accounts.edit', $company) }}" class="text-gray-500 hover:text-gray-700">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-gray-500">
                                <i class="fa-solid fa-building text-3xl text-gray-300 mb-3"></i>
                                <p>No company accounts yet.</p>
                                <p class="mt-1 text-sm">Create the company account before Records Clerk registers company patients.</p>
                                <a href="{{ route('company-accounts.create') }}" class="mt-2 inline-block text-hospital-700 hover:underline">Create the first company</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($companies->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">{{ $companies->links() }}</div>
        @endif
    </div>
</x-app-layout>
