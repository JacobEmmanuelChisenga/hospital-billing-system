<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Active Visits</h2>
            <p class="mt-1 text-sm text-gray-500">Open consultations for today — tap to record or continue notes.</p>
        </div>
    </x-slot>

    <x-flash-messages />

    <div class="mb-6 rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('nurse.active') }}" class="flex flex-col gap-3 sm:flex-row">
            <div class="flex-1">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search patient..."
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">
            </div>
            <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg bg-hospital-700 px-4 py-2.5 text-sm font-medium text-white hover:bg-hospital-800 sm:w-auto">Search</button>
        </form>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
        <x-table-scroll>
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Patient</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Visit</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($visits as $visit)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium">{{ $visit->patient->name }}</td>
                            <td class="px-4 py-3">{{ $visit->visit_type->label() }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $visit->status->badgeClass() }}">{{ $visit->status->label() }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('clinical-notes.edit', $visit) }}" class="text-hospital-700 hover:underline">
                                    {{ $visit->clinicalNote ? 'Edit consultation' : 'Start consultation' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-12 text-center text-gray-500">No active visits today.</td>
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
