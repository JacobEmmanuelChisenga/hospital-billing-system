<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Audit Log</h2>
            <p class="mt-1 text-sm text-gray-500">Who changed what — patient, deposit, and billing actions.</p>
        </div>
    </x-slot>

    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm no-print">
        <form method="GET" action="{{ route('audit-logs.index') }}" class="grid gap-4 md:grid-cols-6">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Quick Range</label>
                <select name="preset" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">
                    <option value="today" @selected($preset === 'today')>Today</option>
                    <option value="week" @selected($preset === 'week')>This Week</option>
                    <option value="month" @selected($preset === 'month')>This Month</option>
                    <option value="custom" @selected($preset === 'custom')>Custom Range</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">From</label>
                <input type="date" name="from_date" value="{{ request('from_date', $from->format('Y-m-d')) }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">To</label>
                <input type="date" name="to_date" value="{{ request('to_date', $to->format('Y-m-d')) }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Action</label>
                <select name="action_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">
                    <option value="">All actions</option>
                    @foreach (\App\Enums\AuditActionType::cases() as $type)
                        <option value="{{ $type->value }}" @selected($selectedActionType === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Staff User</label>
                <select name="user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">
                    <option value="">All staff</option>
                    @foreach ($staffUsers as $staffUser)
                        <option value="{{ $staffUser->id }}" @selected($selectedUserId === $staffUser->id)>{{ $staffUser->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-4">
                <label class="block text-sm font-medium text-gray-700">Search description</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search in description..."
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">
            </div>
            <div class="md:col-span-2 flex items-end gap-2">
                <button type="submit" class="inline-flex items-center rounded-lg bg-hospital-700 px-4 py-2 text-sm font-medium text-white hover:bg-hospital-800">
                    <i class="fa-solid fa-filter mr-2"></i> Apply
                </button>
                <a href="{{ route('audit-logs.export', request()->query()) }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    <i class="fa-solid fa-file-csv mr-2"></i> Export CSV
                </a>
                <a href="{{ route('audit-logs.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Clear</a>
            </div>
        </form>
        <p class="mt-2 text-xs text-gray-500">
            Showing entries from {{ $from->format('d M Y') }} to {{ $to->format('d M Y') }}.
        </p>
    </div>

    <div class="mt-6 overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Date &amp; Time</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Action</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Staff User</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Description</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Related</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-700 whitespace-nowrap">{{ $log->created_at->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $log->action_type->badgeClass() }}">
                                    {{ $log->action_type->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $log->user?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $log->description }}</td>
                            <td class="px-4 py-3 text-gray-700">
                                @if ($url = $log->relatedUrl())
                                    <a href="{{ $url }}" class="text-hospital-700 hover:underline">{{ $log->relatedSummary() }}</a>
                                @elseif ($log->relatedSummary())
                                    {{ $log->relatedSummary() }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('audit-logs.show', $log) }}" class="text-hospital-700 hover:underline">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-gray-500">No audit entries match these filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
