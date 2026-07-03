<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Patient Visits</h2>
                <p class="mt-1 text-sm text-gray-500">Open visits, clinical notes, and charge posting.</p>
            </div>
            @if (Auth::user()->canManageVisits())
                <a href="{{ route('visits.create') }}"
                   class="inline-flex items-center justify-center rounded-lg bg-hospital-700 px-4 py-2 text-sm font-medium text-white hover:bg-hospital-800">
                    <i class="fa-solid fa-plus mr-2"></i> Open Visit
                </a>
            @endif
        </div>
    </x-slot>

    <x-flash-messages />

    <div class="mb-6 rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('visits.index') }}" class="grid gap-4 md:grid-cols-4">
            <div class="md:col-span-2">
                <label for="search" class="block text-sm font-medium text-gray-700">Search Patient</label>
                <input type="text" id="search" name="search" value="{{ $search }}"
                    placeholder="Name, HC, NRC, Man Number..."
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">
                    <option value="">All</option>
                    @foreach ($visitStatuses as $visitStatus)
                        <option value="{{ $visitStatus->value }}" @selected($status === $visitStatus->value)>{{ $visitStatus->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="inline-flex items-center rounded-lg bg-hospital-700 px-4 py-2 text-sm font-medium text-white hover:bg-hospital-800">Filter</button>
                <a href="{{ route('visits.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Clear</a>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
        <x-table-scroll>
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Date</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Patient</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Type</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-600">Charges</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($visits as $visit)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">{{ $visit->visit_date->format('d M Y') }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('patients.show', $visit->patient) }}" class="font-medium text-hospital-700 hover:underline">{{ $visit->patient->name }}</a>
                        </td>
                        <td class="px-4 py-3">{{ $visit->visit_type->label() }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $visit->status->badgeClass() }}">{{ $visit->status->label() }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">K {{ number_format($visit->chargesTotal(), 2) }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('visits.show', $visit) }}" class="text-hospital-700 hover:underline">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-gray-500">No visits recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </x-table-scroll>
        @if ($visits->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">{{ $visits->links() }}</div>
        @endif
    </div>
</x-app-layout>
