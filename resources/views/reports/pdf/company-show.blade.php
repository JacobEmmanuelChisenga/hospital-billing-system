@extends('reports.pdf.layout', [
    'title' => 'Company Statement of Account',
    'subtitle' => $report['company']->name,
])

@section('content')
    <table class="grid">
        <tr><td class="label">Company</td><td>{{ $report['company']->name }}</td></tr>
        <tr><td class="label">Opening Balance</td><td>K {{ number_format($report['opening_balance'], 2) }}</td></tr>
        <tr><td class="label">Company Deposits</td><td>K {{ number_format($report['deposits_in_period'], 2) }}</td></tr>
        <tr><td class="label">Total Bills</td><td>K {{ number_format($report['bills_in_period'], 2) }}</td></tr>
        <tr><td class="label">Closing Balance</td><td>K {{ number_format($report['closing_balance'], 2) }}</td></tr>
    </table>

    <h2>Transactions</h2>
    <table class="data">
        <thead>
            <tr>
                <th>Date</th>
                <th>Reference</th>
                <th>Description</th>
                <th>Debit (K)</th>
                <th>Credit (K)</th>
                <th>Balance (K)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($report['lines'] as $line)
                <tr>
                    <td>{{ $line['date']->format('d M Y') }}</td>
                    <td>{{ $line['reference'] }}</td>
                    <td>{{ $line['description'] }}</td>
                    <td class="num">{{ $line['debit'] !== null ? number_format($line['debit'], 2) : '' }}</td>
                    <td class="num">{{ $line['credit'] !== null ? number_format($line['credit'], 2) : '' }}</td>
                    <td class="num">{{ number_format($line['balance'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
