@extends('reports.pdf.layout', ['title' => 'Financial Summary Report'])

@section('content')
    <table class="grid">
        <tr><td class="label">Member Deposits</td><td>K {{ number_format($summary['member_deposits_total'], 2) }}</td></tr>
        <tr><td class="label">Company Deposits</td><td>K {{ number_format($summary['company_deposits_total'], 2) }}</td></tr>
        <tr><td class="label">Bills Posted</td><td>K {{ number_format($summary['bills_total'], 2) }}</td></tr>
        <tr><td class="label">Voided Bills</td><td>{{ $summary['voided_bills_count'] }} (K {{ number_format($summary['voided_bills_total'], 2) }})</td></tr>
    </table>

    <h2>Visit Type Summary</h2>
    <table class="data">
        <thead>
            <tr>
                <th>Type</th>
                <th>Bills</th>
                <th>Total (K)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($summary['visit_summary'] as $row)
                <tr>
                    <td>{{ $row['type']->label() }}</td>
                    <td class="num">{{ $row['count'] }}</td>
                    <td class="num">{{ number_format($row['total'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Current Balances</h2>
    <table class="grid">
        <tr><td class="label">Active Members</td><td>{{ $summary['active_members'] }}</td></tr>
        <tr><td class="label">Total Member Balances</td><td>K {{ number_format($summary['total_member_balance'], 2) }}</td></tr>
        <tr><td class="label">Company Patients</td><td>{{ $summary['active_company_patients'] }}</td></tr>
        <tr><td class="label">Total Company Pools</td><td>K {{ number_format($summary['total_company_balance'], 2) }}</td></tr>
        <tr><td class="label">Reversed Deposits</td><td>{{ $summary['reversed_deposits_count'] }} (K {{ number_format($summary['reversed_deposits_total'], 2) }})</td></tr>
        <tr><td class="label">Memberships Expiring (30 days)</td><td>{{ $summary['expiring_memberships'] }}</td></tr>
        <tr><td class="label">Expired Memberships</td><td>{{ $summary['expired_memberships'] }}</td></tr>
    </table>
@endsection
