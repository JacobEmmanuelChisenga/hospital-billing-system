<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Member Deposits</h2>
                <p class="mt-1 text-sm text-gray-500">Load and track deposits into member accounts.</p>
            </div>
            <a href="{{ route('deposits.create') }}"
               class="inline-flex items-center justify-center rounded-lg bg-hospital-700 px-4 py-2 text-sm font-medium text-white hover:bg-hospital-800">
                <i class="fa-solid fa-plus mr-2"></i> Load Deposit
            </a>
        </div>
    </x-slot>

    <x-flash-messages />

    <div class="mb-6 rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('deposits.index') }}" class="grid gap-4 md:grid-cols-5">
            <div class="md:col-span-2">
                <label for="search" class="block text-sm font-medium text-gray-700">Member</label>
                <input type="text" id="search" name="search" value="{{ $search }}"
                    placeholder="Search by member name or HC number..."
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">
                    <option value="">All</option>
                    <option value="active" @selected($status === 'active')>Active</option>
                    <option value="reversed" @selected($status === 'reversed')>Reversed</option>
                </select>
            </div>
            <div>
                <label for="from_date" class="block text-sm font-medium text-gray-700">From</label>
                <input type="date" id="from_date" name="from_date" value="{{ $fromDate }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">
            </div>
            <div>
                <label for="to_date" class="block text-sm font-medium text-gray-700">To</label>
                <input type="date" id="to_date" name="to_date" value="{{ $toDate }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">
            </div>
            <div class="md:col-span-5 flex gap-2">
                <button type="submit" class="inline-flex items-center rounded-lg bg-hospital-700 px-4 py-2 text-sm font-medium text-white hover:bg-hospital-800">
                    <i class="fa-solid fa-filter mr-2"></i> Filter
                </button>
                <a href="{{ route('deposits.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Clear</a>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
        <div class="table-scroll -mx-4 px-4 sm:mx-0 sm:px-0">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Date</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Member</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Reference</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Amount</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Loaded By</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($deposits as $deposit)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-700">{{ $deposit->deposit_date->format('d M Y') }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('patients.show', $deposit->patient) }}" class="font-medium text-hospital-700 hover:underline">
                                    {{ $deposit->patient->name }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $deposit->reference ?? '—' }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">K {{ number_format((float) $deposit->amount, 2) }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $deposit->createdBy->name }}</td>
                            <td class="px-4 py-3">
                                @if ($deposit->isReversed())
                                    <span class="inline-flex rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800">Reversed</span>
                                @else
                                    <span class="inline-flex rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">Active</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('deposits.show', $deposit) }}" class="text-hospital-700 hover:text-hospital-900">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-gray-500">
                                <i class="fa-solid fa-money-bill-wave text-3xl text-gray-300 mb-3"></i>
                                <p>No deposits found.</p>
                                <a href="{{ route('deposits.create') }}" class="mt-2 inline-block text-hospital-700 hover:underline">Load the first deposit</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($deposits->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">{{ $deposits->links() }}</div>
        @endif
    </div>
</x-app-layout>
