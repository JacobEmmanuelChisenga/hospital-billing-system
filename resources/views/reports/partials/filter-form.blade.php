@php
    $action = $action ?? request()->url();
    $preset = $preset ?? 'month';
    $exportCsvRoute = $exportCsvRoute ?? ($exportRoute ?? null);
    $exportPdfRoute = $exportPdfRoute ?? null;
@endphp

<form method="GET" action="{{ $action }}" class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm no-print">
    <div class="grid gap-4 md:grid-cols-6">
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

        @isset($showVisitTypeFilter)
            <div>
                <label class="block text-sm font-medium text-gray-700">Visit Type</label>
                <select name="visit_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">
                    <option value="">All</option>
                    @foreach (\App\Enums\VisitType::cases() as $visitType)
                        <option value="{{ $visitType->value }}" @selected(request('visit_type') === $visitType->value)>{{ $visitType->label() }}</option>
                    @endforeach
                </select>
            </div>
        @endisset

        <div class="flex flex-wrap items-end gap-2 {{ isset($showVisitTypeFilter) ? '' : 'md:col-span-2' }}">
            <button type="submit" class="inline-flex items-center rounded-lg bg-hospital-700 px-4 py-2 text-sm font-medium text-white hover:bg-hospital-800">
                <i class="fa-solid fa-filter mr-2"></i> Apply
            </button>
            @if ($exportCsvRoute)
                <a href="{{ $exportCsvRoute }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    <i class="fa-solid fa-file-csv mr-2"></i> Download CSV
                </a>
            @endif
            @if ($exportPdfRoute)
                <a href="{{ $exportPdfRoute }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    <i class="fa-solid fa-file-pdf mr-2"></i> Download PDF
                </a>
            @endif
            @isset($printButton)
                <button type="button" onclick="window.print()" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    <i class="fa-solid fa-print mr-2"></i> Print
                </button>
            @endisset
        </div>
    </div>
    <p class="mt-2 text-xs text-gray-500">
        Showing data from {{ $from->format('d M Y') }} to {{ $to->format('d M Y') }}.
        CSV opens in Excel; PDF downloads a printable report file.
    </p>
</form>
