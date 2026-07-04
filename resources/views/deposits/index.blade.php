<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Member Deposits" subtitle="Load and track deposits into member accounts.">
            <x-slot name="actions">
                <a href="{{ route('deposits.create') }}" class="btn-primary">
                    <i class="fa-solid fa-plus"></i> Load Deposit
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <x-flash-messages />

    <x-filter-panel>
        <form method="GET" action="{{ route('deposits.index') }}" class="grid gap-4 md:grid-cols-5">
            <div class="md:col-span-2">
                <label for="search" class="form-label">Member</label>
                <input type="text" id="search" name="search" value="{{ $search }}"
                    placeholder="Search by member name or HC number..."
                    class="form-input">
            </div>
            <div>
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-input">
                    <option value="">All</option>
                    <option value="active" @selected($status === 'active')>Active</option>
                    <option value="reversed" @selected($status === 'reversed')>Reversed</option>
                </select>
            </div>
            <div>
                <label for="from_date" class="form-label">From</label>
                <input type="date" id="from_date" name="from_date" value="{{ $fromDate }}" class="form-input">
            </div>
            <div>
                <label for="to_date" class="form-label">To</label>
                <input type="date" id="to_date" name="to_date" value="{{ $toDate }}" class="form-input">
            </div>
            <div class="md:col-span-5 flex gap-2">
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                <a href="{{ route('deposits.index') }}" class="btn-secondary">Clear</a>
            </div>
        </form>
    </x-filter-panel>

    <x-data-panel>
        <x-table-scroll>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Member</th>
                        <th>Reference</th>
                        <th class="text-right">Amount</th>
                        <th>Loaded By</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($deposits as $deposit)
                        <tr>
                            <td>{{ $deposit->deposit_date->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('patients.show', $deposit->patient) }}" class="action-link font-medium">
                                    {{ $deposit->patient->name }}
                                </a>
                            </td>
                            <td>{{ $deposit->reference ?? '—' }}</td>
                            <td class="text-right font-semibold text-slate-900">K {{ number_format((float) $deposit->amount, 2) }}</td>
                            <td>{{ $deposit->createdBy->name }}</td>
                            <td>
                                @if ($deposit->isReversed())
                                    <span class="badge-danger">Reversed</span>
                                @else
                                    <span class="badge-success">Active</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('deposits.show', $deposit) }}" class="icon-btn" title="View deposit">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="!py-12 text-center text-slate-500">
                                <i class="fa-solid fa-money-bill-wave mb-3 text-3xl text-slate-300"></i>
                                <p>No deposits found.</p>
                                <a href="{{ route('deposits.create') }}" class="action-link mt-2 inline-block">Load the first deposit</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-table-scroll>

        @if ($deposits->hasPages())
            <x-slot name="footer">{{ $deposits->links() }}</x-slot>
        @endif
    </x-data-panel>
</x-app-layout>
