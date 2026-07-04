<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Company Accounts" subtitle="Manage company deposit pools shared by company patients.">
            <x-slot name="actions">
                <a href="{{ route('company-accounts.create') }}" class="btn-primary">
                    <i class="fa-solid fa-plus"></i> Add Company
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <x-flash-messages />

    <x-filter-panel>
        <form method="GET" action="{{ route('company-accounts.index') }}" class="flex gap-4">
            <div class="flex-1">
                <label for="search" class="form-label">Search company</label>
                <input type="text" id="search" name="search" value="{{ $search }}"
                    placeholder="Company name..."
                    class="form-input">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-magnifying-glass"></i> Search
                </button>
                <a href="{{ route('company-accounts.index') }}" class="btn-secondary">Clear</a>
            </div>
        </form>
    </x-filter-panel>

    <x-data-panel>
        <x-table-scroll>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th class="text-center">Patients</th>
                        <th class="text-right">Pool Balance</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($companies as $company)
                        <tr>
                            <td>
                                <a href="{{ route('company-accounts.show', $company) }}" class="action-link font-medium">
                                    {{ $company->name }}
                                </a>
                            </td>
                            <td>
                                {{ $company->contact_person ?? '—' }}
                                @if ($company->phone)
                                    <span class="block text-xs text-slate-400">{{ $company->phone }}</span>
                                @endif
                            </td>
                            <td>
                                @if ($company->status === 'active')
                                    <span class="badge badge-success">{{ ucfirst($company->status) }}</span>
                                @else
                                    <span class="badge badge-danger">{{ ucfirst($company->status) }}</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $company->patients_count }}</td>
                            <td class="text-right font-medium">K {{ number_format((float) $company->balance, 2) }}</td>
                            <td class="text-right">
                                <a href="{{ route('company-accounts.show', $company) }}" class="action-link">View</a>
                                <span class="text-slate-300 mx-1">|</span>
                                <a href="{{ route('company-accounts.edit', $company) }}" class="action-link">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="!py-12 text-center">
                                <x-empty-state icon="fa-building" message="No company accounts yet. Create the company account before Records Clerk registers company patients." action-label="Create the first company" :action-href="route('company-accounts.create')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-table-scroll>

        @if ($companies->hasPages())
            <x-slot name="footer">{{ $companies->links() }}</x-slot>
        @endif
    </x-data-panel>
</x-app-layout>
