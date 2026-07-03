@extends('reports.pdf.layout', ['title' => 'Member Accounts Report'])

@section('content')
    <table class="data">
        <thead>
            <tr>
                <th>Member</th>
                <th>HC Number</th>
                <th>Balance (K)</th>
                <th>Deposits (K)</th>
                <th>Bills (K)</th>
                <th>Dependants</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($accounts as $row)
                <tr>
                    <td>{{ $row['member']->name }}</td>
                    <td>{{ $row['member']->hc_number ?? '—' }}</td>
                    <td class="num">{{ number_format($row['current_balance'], 2) }}</td>
                    <td class="num">{{ number_format($row['deposits_in_period'], 2) }}</td>
                    <td class="num">{{ number_format($row['bills_in_period'], 2) }}</td>
                    <td class="num">{{ $row['dependants_count'] }}</td>
                    <td>{{ $row['member']->status->label() }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
