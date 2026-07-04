<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Audit Log" subtitle="Who changed what — patient, deposit, and billing actions." />
    </x-slot>

    <x-filter-panel hint="Showing entries from {{ $from->format('d M Y') }} to {{ $to->format('d M Y') }}." class="no-print">
        <form method="GET" action="{{ route('audit-logs.index') }}" class="grid gap-4 md:grid-cols-6">
            <div class="md:col-span-2">
                <label class="form-label">Quick Range</label>
                <select name="preset" class="form-input">
                    <option value="today" @selected($preset === 'today')>Today</option>
                    <option value="week" @selected($preset === 'week')>This Week</option>
                    <option value="month" @selected($preset === 'month')>This Month</option>
                    <option value="custom" @selected($preset === 'custom')>Custom Range</option>
                </select>
            </div>
            <div>
                <label class="form-label">From</label>
                <input type="date" name="from_date" value="{{ request('from_date', $from->format('Y-m-d')) }}" class="form-input">
            </div>
            <div>
                <label class="form-label">To</label>
                <input type="date" name="to_date" value="{{ request('to_date', $to->format('Y-m-d')) }}" class="form-input">
            </div>
            <div>
                <label class="form-label">Action</label>
                <select name="action_type" class="form-input">
                    <option value="">All actions</option>
                    @foreach (\App\Enums\AuditActionType::cases() as $type)
                        <option value="{{ $type->value }}" @selected($selectedActionType === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Staff User</label>
                <select name="user_id" class="form-input">
                    <option value="">All staff</option>
                    @foreach ($staffUsers as $staffUser)
                        <option value="{{ $staffUser->id }}" @selected($selectedUserId === $staffUser->id)>{{ $staffUser->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-4">
                <label class="form-label">Search description</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search in description..." class="form-input">
            </div>
            <div class="md:col-span-2 flex flex-wrap items-end gap-2">
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-filter"></i> Apply
                </button>
                <a href="{{ route('audit-logs.export', request()->query()) }}" class="btn-secondary">
                    <i class="fa-solid fa-file-csv"></i> Download CSV
                </a>
                <a href="{{ route('audit-logs.export.pdf', request()->query()) }}" class="btn-secondary">
                    <i class="fa-solid fa-file-pdf"></i> Download PDF
                </a>
                <a href="{{ route('audit-logs.index') }}" class="btn-secondary">Clear</a>
            </div>
        </form>
    </x-filter-panel>

    <x-data-panel>
        <x-table-scroll>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date &amp; Time</th>
                        <th>Action</th>
                        <th>Staff User</th>
                        <th>Description</th>
                        <th>Related</th>
                        <th class="text-right">Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td class="whitespace-nowrap">{{ $log->created_at->format('d M Y H:i') }}</td>
                            <td>
                                <span class="badge {{ $log->action_type->badgeClass() }}">
                                    {{ $log->action_type->label() }}
                                </span>
                            </td>
                            <td>{{ $log->user?->name ?? '—' }}</td>
                            <td>{{ $log->description }}</td>
                            <td>
                                @if ($url = $log->relatedUrl())
                                    <a href="{{ $url }}" class="action-link">{{ $log->relatedSummary() }}</a>
                                @elseif ($log->relatedSummary())
                                    {{ $log->relatedSummary() }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('audit-logs.show', $log) }}" class="action-link">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="!py-12 text-center text-slate-500">No audit entries match these filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-table-scroll>

        @if ($logs->hasPages())
            <x-slot name="footer">{{ $logs->links() }}</x-slot>
        @endif
    </x-data-panel>
</x-app-layout>
