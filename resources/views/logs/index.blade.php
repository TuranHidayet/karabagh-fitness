@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Logs</h2>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Date</th>
                <th>Level</th>
                <th>Message</th>
                <th>Method</th>
                <th>Path</th>
                <th>IP</th>
                <th>User ID</th>
                <th>Payload</th>
                <th>User Agent</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
            <tr>
                <td>{{ $log->created_at }}</td>
                <td>{{ $log->level }}</td>
                <td>{{ $log->message }}</td>
                <td>{{ $log->method }}</td>
                <td>{{ $log->path }}</td>
                <td>{{ $log->ip }}</td>
                <td>{{ $log->user_id }}</td>
                <td><pre>{{ json_encode($log->payload, JSON_PRETTY_PRINT) }}</pre></td>
                <td>{{ $log->ua }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $logs->links() }}
</div>
@endsection
