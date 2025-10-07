@extends('app')

@section('title', 'User Logs')

@section('content')
    <div class="p-4">
        <h1 class="text-xl font-bold mb-4">User Logs</h1>

        <table class="min-w-full border border-gray-300">
            <thead>
            <tr class="bg-gray-100">
                <th class="px-4 py-2 border">ID</th>
                <th class="px-4 py-2 border">User</th>
                <th class="px-4 py-2 border">Action</th>
                <th class="px-4 py-2 border">Description</th>
                <th class="px-4 py-2 border">IP</th>
                <th class="px-4 py-2 border">User Agent</th>
                <th class="px-4 py-2 border">Date</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($logs as $log)
                <tr class="border-b">
                    <td class="px-4 py-2 border">{{ $log->id }}</td>
                    <td class="px-4 py-2 border">{{ $log->user->name ?? 'N/A' }}</td>
                    <td class="px-4 py-2 border">{{ $log->action }}</td>
                    <td class="px-4 py-2 border">{{ $log->description }}</td>
                    <td class="px-4 py-2 border">{{ $log->ip_address }}</td>
                    <td class="px-4 py-2 border">{{ $log->user_agent }}</td>
                    <td class="px-4 py-2 border">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-4">No logs found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>

        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    </div>
@endsection
