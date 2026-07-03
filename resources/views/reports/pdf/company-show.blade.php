@extends('reports.pdf.layout', [
    'title' => $report['company']->name,
    'subtitle' => 'Company account usage report',
])

@section('content')
    <table class="grid">
        <tr><td class="label">Pool Balance</td><td>K {{ number_format($report['current_balance'], 2) }}</td></tr>
        <tr><td class="label">Deposits in Period</td><td>K {{ number_format($report['deposits_in_period'], 2) }}</td></tr>
        <tr><td class="label">Bills in Period</td><td>K {{ number_format($report['bills_in_period'], 2) }}</td></tr>
    </table>

    <h2>Bills in Period</h2>
    <table class="data">
        <thead>
            <tr>
                <th>Date</th>
                <th>Patient</th>
                <th>Visit</th>
                <th>Amount (K)</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($report['bills'] as $bill)
                <tr>
                    <td>{{ $bill->visit_date->format('d M Y') }}</td>
                    <td>{{ $bill->patient->name }}</td>
                    <td>{{ $bill->visit_type->label() }}</td>
                    <td class="num">{{ number_format((float) $bill->total_amount, 2) }}</td>
                    <td>{{ $bill->status->label() }}</td>
                </tr>
            @empty
                <tr><td colspan="5">No bills in this period.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
