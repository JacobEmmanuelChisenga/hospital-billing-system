@extends('reports.pdf.layout', ['title' => 'Audit Log Report'])

@section('content')
    <table class="data">
        <thead>
            <tr>
                <th>Date &amp; Time</th>
                <th>Action</th>
                <th>Staff User</th>
                <th>Description</th>
                <th>Related Record</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row[0] }}</td>
                    <td>{{ $row[1] }}</td>
                    <td>{{ $row[2] }}</td>
                    <td>{{ $row[3] }}</td>
                    <td>{{ $row[4] }}</td>
                </tr>
            @empty
                <tr><td colspan="5">No audit events in this period.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
