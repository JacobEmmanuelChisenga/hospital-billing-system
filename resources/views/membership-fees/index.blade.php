<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Membership Payments" subtitle="Scheme membership / subscription fees and expiry tracking for members and dependants.">
            <x-slot name="actions">
                <a href="{{ route('membership-fees.create') }}" class="btn-primary">
                    <i class="fa-solid fa-plus"></i> Record Payment
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <x-flash-messages />

    <x-filter-panel>
        <form method="GET" action="{{ route('membership-fees.index') }}" class="grid gap-4 md:grid-cols-6">
            <div class="md:col-span-2">
                <label for="search" class="form-label">Search</label>
                <input type="text" id="search" name="search" value="{{ $search }}"
                    placeholder="Member or dependant name..."
                    class="form-input">
            </div>
            <div>
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-input">
                    <option value="">All</option>
                    <option value="active" @selected($status === 'active')>Active</option>
                    <option value="expiring" @selected($status === 'expiring')>Expiring (30 days)</option>
                    <option value="expired" @selected($status === 'expired')>Expired</option>
                </select>
            </div>
            <div>
                <label for="from_date" class="form-label">Paid From</label>
                <input type="date" id="from_date" name="from_date" value="{{ $fromDate }}" class="form-input">
            </div>
            <div>
                <label for="to_date" class="form-label">Paid To</label>
                <input type="date" id="to_date" name="to_date" value="{{ $toDate }}" class="form-input">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                <a href="{{ route('membership-fees.index') }}" class="btn-secondary">Clear</a>
            </div>
        </form>
    </x-filter-panel>

    <x-data-panel>
        <x-table-scroll>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Payment Date</th>
                        <th>Member / Dependant</th>
                        <th>Principal</th>
                        <th class="text-right">Amount</th>
                        <th>Method</th>
                        <th>Valid Until</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($fees as $fee)
                        <tr>
                            <td class="whitespace-nowrap">{{ $fee->payment_date->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('patients.show', $fee->patient) }}" class="action-link font-medium">
                                    {{ $fee->patient->name }}
                                </a>
                                <span class="block text-xs text-slate-400">{{ $fee->patient->type->label() }}</span>
                            </td>
                            <td>
                                @if ($fee->principalPatient)
                                    <a href="{{ route('patients.show', $fee->principalPatient) }}" class="action-link">
                                        {{ $fee->principalPatient->name }}
                                    </a>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="text-right font-medium">K {{ number_format((float) $fee->amount, 2) }}</td>
                            <td>{{ $fee->payment_method?->label() ?? '—' }}</td>
                            <td>{{ $fee->expiry_date->format('d M Y') }}</td>
                            <td>
                                @if ($fee->isExpired())
                                    <span class="badge badge-danger">Expired</span>
                                @elseif ($fee->isExpiringSoon())
                                    <span class="badge badge-warning">Expiring Soon</span>
                                @else
                                    <span class="badge badge-success">Active</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('membership-fees.show', $fee) }}" class="action-link">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="!py-12 text-center text-slate-500">No membership payments recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-table-scroll>

        @if ($fees->hasPages())
            <x-slot name="footer">{{ $fees->links() }}</x-slot>
        @endif
    </x-data-panel>
</x-app-layout>
