@php
    $action = $action ?? request()->url();
    $preset = $preset ?? 'month';
    $exportCsvRoute = $exportCsvRoute ?? ($exportRoute ?? null);
    $exportPdfRoute = $exportPdfRoute ?? null;
@endphp

<form method="GET" action="{{ $action }}" class="filter-panel no-print">
    <div class="grid gap-4 md:grid-cols-6">
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

        @isset($showVisitTypeFilter)
            <div>
                <label class="form-label">Visit Type</label>
                <select name="visit_type" class="form-input">
                    <option value="">All</option>
                    @foreach (\App\Enums\VisitType::cases() as $visitType)
                        <option value="{{ $visitType->value }}" @selected(request('visit_type') === $visitType->value)>{{ $visitType->label() }}</option>
                    @endforeach
                </select>
            </div>
        @endisset

        <div class="flex flex-wrap items-end gap-2 {{ isset($showVisitTypeFilter) ? '' : 'md:col-span-2' }}">
            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-filter"></i> Apply
            </button>
            @if ($exportCsvRoute)
                <a href="{{ $exportCsvRoute }}" class="btn-secondary">
                    <i class="fa-solid fa-file-csv"></i> Download CSV
                </a>
            @endif
            @if ($exportPdfRoute)
                <a href="{{ $exportPdfRoute }}" class="btn-secondary">
                    <i class="fa-solid fa-file-pdf"></i> Download PDF
                </a>
            @endif
            @isset($printButton)
                <button type="button" onclick="window.print()" class="btn-secondary">
                    <i class="fa-solid fa-print"></i> Print
                </button>
            @endisset
        </div>
    </div>
    <p class="form-hint">
        Showing data from {{ $from->format('d M Y') }} to {{ $to->format('d M Y') }}.
        CSV opens in Excel; PDF downloads a printable report file.
    </p>
</form>
