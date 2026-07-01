<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Membership Payments</h2>
                <p class="mt-1 text-sm text-gray-500">Scheme membership / subscription fees and expiry tracking for members and dependants.</p>
            </div>
            <a href="{{ route('membership-fees.create') }}"
               class="inline-flex items-center justify-center rounded-lg bg-hospital-700 px-4 py-2 text-sm font-medium text-white hover:bg-hospital-800">
                <i class="fa-solid fa-plus mr-2"></i> Record Payment
            </a>
        </div>
    </x-slot>

    <x-flash-messages />

    <div class="mb-6 rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('membership-fees.index') }}" class="grid gap-4 md:grid-cols-6">
            <div class="md:col-span-2">
                <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                <input type="text" id="search" name="search" value="{{ $search }}"
                    placeholder="Member or dependant name..."
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">
                    <option value="">All</option>
                    <option value="active" @selected($status === 'active')>Active</option>
                    <option value="expiring" @selected($status === 'expiring')>Expiring (30 days)</option>
                    <option value="expired" @selected($status === 'expired')>Expired</option>
                </select>
            </div>
            <div>
                <label for="from_date" class="block text-sm font-medium text-gray-700">Paid From</label>
                <input type="date" id="from_date" name="from_date" value="{{ $fromDate }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">
            </div>
            <div>
                <label for="to_date" class="block text-sm font-medium text-gray-700">Paid To</label>
                <input type="date" id="to_date" name="to_date" value="{{ $toDate }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="inline-flex items-center rounded-lg bg-hospital-700 px-4 py-2 text-sm font-medium text-white hover:bg-hospital-800">
                    <i class="fa-solid fa-filter mr-2"></i> Filter
                </button>
                <a href="{{ route('membership-fees.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Clear</a>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Payment Date</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Member / Dependant</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Principal</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Amount</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Method</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Valid Until</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($fees as $fee)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-700">{{ $fee->payment_date->format('d M Y') }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('patients.show', $fee->patient) }}" class="font-medium text-hospital-700 hover:underline">
                                    {{ $fee->patient->name }}
                                </a>
                                <span class="block text-xs text-gray-400">{{ $fee->patient->type->label() }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @if ($fee->principalPatient)
                                    <a href="{{ route('patients.show', $fee->principalPatient) }}" class="text-hospital-700 hover:underline">
                                        {{ $fee->principalPatient->name }}
                                    </a>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-medium">K {{ number_format((float) $fee->amount, 2) }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $fee->payment_method?->label() ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $fee->expiry_date->format('d M Y') }}</td>
                            <td class="px-4 py-3">
                                @if ($fee->isExpired())
                                    <span class="inline-flex rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800">Expired</span>
                                @elseif ($fee->isExpiringSoon())
                                    <span class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">Expiring Soon</span>
                                @else
                                    <span class="inline-flex rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">Active</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('membership-fees.show', $fee) }}" class="text-hospital-700 hover:underline">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-gray-500">No membership payments recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($fees->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">{{ $fees->links() }}</div>
        @endif
    </div>
</x-app-layout>
