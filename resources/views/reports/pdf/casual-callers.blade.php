@extends('reports.pdf.layout', ['title' => 'Casual Caller Report'])

@section('content')
    <table class="grid">
        <tr><td class="label">Active Casual Callers</td><td>{{ $report['summary']['active_patients'] }}</td></tr>
        <tr><td class="label">Bills Issued (K)</td><td>{{ number_format($report['summary']['billed_total'], 2) }} ({{ $report['summary']['bills_count'] }})</td></tr>
        <tr><td class="label">Paid by Visit Date (K)</td><td>{{ number_format($report['summary']['paid_total'], 2) }} ({{ $report['summary']['paid_count'] }})</td></tr>
        <tr><td class="label">Outstanding This Period (K)</td><td>{{ number_format($report['summary']['outstanding_total'], 2) }} ({{ $report['summary']['outstanding_count'] }})</td></tr>
        <tr><td class="label">Cash Received by Payment Date (K)</td><td>{{ number_format($report['summary']['collected_in_period'], 2) }} ({{ $report['summary']['collected_count'] }})</td></tr>
        <tr><td class="label">Current Outstanding All (K)</td><td>{{ number_format($report['summary']['current_outstanding'], 2) }}</td></tr>
    </table>

    @if ($report['payment_methods']->isNotEmpty())
        <h2>Collections by Payment Method</h2>
        <table class="data">
            <thead>
                <tr>
                    <th>Method</th>
                    <th>Payments</th>
                    <th>Total (K)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($report['payment_methods'] as $row)
                    <tr>
                        <td>{{ $row['method'] }}</td>
                        <td class="num">{{ $row['count'] }}</td>
                        <td class="num">{{ number_format($row['total'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2>Bills by Visit Date</h2>
    <table class="data">
        <thead>
            <tr>
                <th>Date</th>
                <th>Patient</th>
                <th>File No.</th>
                <th>Visit</th>
                <th>Amount (K)</th>
                <th>Status</th>
                <th>Payment</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($report['bills'] as $row)
                <tr>
                    <td>{{ $row['bill']->visit_date->format('d M Y') }}</td>
                    <td>{{ $row['patient']->name }}</td>
                    <td>{{ $row['patient']->file_number ?? '—' }}</td>
                    <td>{{ $row['visit_label'] }}</td>
                    <td class="num">{{ number_format($row['amount'], 2) }}</td>
                    <td>{{ $row['status'] }}</td>
                    <td>{{ $row['payment_method'] ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">No casual caller bills in this period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
