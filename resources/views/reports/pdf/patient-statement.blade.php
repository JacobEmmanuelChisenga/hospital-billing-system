@extends('reports.pdf.layout', [
    'title' => 'Statement of Account',
    'subtitle' => $statement['patient']->name,
])

@section('content')
    <table class="grid">
        <tr><td class="label">Patient</td><td>{{ $statement['patient']->name }}</td></tr>
        <tr><td class="label">Membership</td><td>{{ $statement['membership_number'] ?? $statement['patient']->hc_number ?? '—' }}</td></tr>
        <tr><td class="label">Account / Payer</td><td>{{ $statement['payer_label'] }}</td></tr>
        <tr><td class="label">Opening Balance</td><td>K {{ number_format($statement['opening_balance'], 2) }}</td></tr>
        <tr><td class="label">Total Deposits</td><td>K {{ number_format($statement['deposits_total'], 2) }}</td></tr>
        <tr><td class="label">Total Bills</td><td>K {{ number_format($statement['bills_total'], 2) }}</td></tr>
        <tr><td class="label">Closing Balance</td><td>K {{ number_format($statement['closing_balance'], 2) }}</td></tr>
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
            @foreach ($statement['lines'] as $line)
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
