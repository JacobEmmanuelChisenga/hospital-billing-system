@extends('reports.pdf.layout', ['title' => 'Transaction Report'])

@section('content')
    <table class="data">
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Party</th>
                <th>Reference</th>
                <th>Amount (K)</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transactions as $row)
                <tr>
                    <td>{{ $row['date'] instanceof \DateTimeInterface ? $row['date']->format('d M Y') : $row['date'] }}</td>
                    <td>{{ $row['type'] }}</td>
                    <td>{{ $row['party'] }}</td>
                    <td>{{ $row['reference'] }}</td>
                    <td class="num">{{ $row['direction'] === 'in' ? '+' : '-' }}{{ number_format($row['amount'], 2) }}</td>
                    <td>{{ $row['status'] }}</td>
                </tr>
            @empty
                <tr><td colspan="6">No transactions in this period.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
