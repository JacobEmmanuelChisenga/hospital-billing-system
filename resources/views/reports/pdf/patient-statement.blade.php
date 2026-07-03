@extends('reports.pdf.layout', [
    'title' => 'Patient Statement',
    'subtitle' => $statement['patient']->name,
])

@section('content')
    <table class="grid">
        <tr><td class="label">Patient</td><td>{{ $statement['patient']->name }}</td></tr>
        <tr><td class="label">HC Number</td><td>{{ $statement['patient']->hc_number ?? '—' }}</td></tr>
        <tr><td class="label">Account / Payer</td><td>{{ $statement['payer_label'] }}</td></tr>
        <tr><td class="label">Opening Balance</td><td>K {{ number_format($statement['opening_balance'], 2) }}</td></tr>
        <tr><td class="label">Credits (Deposits)</td><td>K {{ number_format($statement['deposits_total'], 2) }}</td></tr>
        <tr><td class="label">Debits (Bills)</td><td>K {{ number_format($statement['bills_total'], 2) }}</td></tr>
        <tr><td class="label">Closing Balance</td><td>K {{ number_format($statement['closing_balance'], 2) }}</td></tr>
    </table>

    <h2>Statement Lines</h2>
    <table class="data">
        <thead>
            <tr>
                <th>Date</th>
                <th>Description</th>
                <th>Debit (K)</th>
                <th>Credit (K)</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($statement['lines'] as $line)
                <tr>
                    <td>{{ $line['date']->format('d M Y') }}</td>
                    <td>{{ $line['description'] }}</td>
                    <td class="num">{{ $line['debit'] ? number_format($line['debit'], 2) : '—' }}</td>
                    <td class="num">{{ $line['credit'] ? number_format($line['credit'], 2) : '—' }}</td>
                    <td>{{ $line['status'] }}</td>
                </tr>
            @empty
                <tr><td colspan="5">No activity in this period.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
