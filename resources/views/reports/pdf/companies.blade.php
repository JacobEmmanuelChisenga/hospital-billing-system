@extends('reports.pdf.layout', ['title' => 'Company Reports'])

@section('content')
    <table class="data">
        <thead>
            <tr>
                <th>Company</th>
                <th>Patients</th>
                <th>Pool Balance (K)</th>
                <th>Deposits (K)</th>
                <th>Bills (K)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($companies as $row)
                <tr>
                    <td>{{ $row['company']->name }}</td>
                    <td class="num">{{ $row['company']->patients_count }}</td>
                    <td class="num">{{ number_format($row['current_balance'], 2) }}</td>
                    <td class="num">{{ number_format($row['deposits_in_period'], 2) }}</td>
                    <td class="num">{{ number_format($row['bills_in_period'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="5">No company accounts found.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
