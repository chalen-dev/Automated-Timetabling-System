@extends('app')

@section('title', 'User Logs')

@section('content')
    <div class="w-full p-4">

        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-xl font-bold mb-0 text-white">User Logs</h1>
        </div>

        <!-- Responsive Table (No Horizontal Scrollbar) -->
        <div class="bg-white rounded-lg shadow-md">
            <table class="w-full table-auto text-left border-separate border-spacing-0 break-words">
                <thead class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                <tr>
                    <th class="px-3 py-2 font-semibold border-b border-gray-200">ID</th>
                    <th class="px-3 py-2 font-semibold border-b border-gray-200">User</th>
                    <th class="px-3 py-2 font-semibold border-b border-gray-200">Action</th>
                    <th class="px-3 py-2 font-semibold border-b border-gray-200">Description</th>
                    <th class="px-3 py-2 font-semibold border-b border-gray-200">IP</th>
                    <th class="px-3 py-2 font-semibold border-b border-gray-200">User Agent</th>
                    <th class="px-3 py-2 font-semibold border-b border-gray-200">Date</th>
                </tr>
                </thead>
                <tbody class="text-gray-700 text-sm">
                @forelse ($logs as $log)
                    <tr class="border-t border-gray-200 hover:bg-gray-50 transition-colors">
                        <td class="px-3 py-2 align-top">{{ $log->id }}</td>
                        <td class="px-3 py-2 align-top">{{ $log->user->name ?? 'N/A' }}</td>
                        <td class="px-3 py-2 align-top">{{ $log->action }}</td>
                        <td class="px-3 py-2 align-top break-words">{{ $log->description }}</td>
                        <td class="px-3 py-2 align-top">{{ $log->ip_address }}</td>
                        <td class="px-3 py-2 align-top break-words text-xs">{{ $log->user_agent }}</td>
                        <td class="px-3 py-2 align-top whitespace-nowrap text-xs">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">No logs found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    </div>
@endsection
