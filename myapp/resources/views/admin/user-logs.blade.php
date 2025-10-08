@extends('app')

@section('title', 'User Logs')

@section('content')
    <div class="w-full p-4">

        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-xl font-bold mb-0 text-white">User Logs</h1>
        </div>

        <!-- Grouped Logs by Date -->
        @forelse ($logs as $date => $dailyLogs)
            <div class="mb-6 bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-gray-200 px-4 py-2 font-semibold text-gray-700">
                    {{ \Carbon\Carbon::parse($date)->format('F d, Y') }}
                </div>

                <table class="w-full table-auto text-left border-separate border-spacing-0 break-words">
                    <thead class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                        <tr>
                            <th class="px-3 py-2 font-semibold border-b border-gray-200">ID</th>
                            <th class="px-3 py-2 font-semibold border-b border-gray-200">User</th>
                            <th class="px-3 py-2 font-semibold border-b border-gray-200">Action</th>
                            <th class="px-3 py-2 font-semibold border-b border-gray-200">Description</th>
                            <th class="px-3 py-2 font-semibold border-b border-gray-200">IP</th>
                            <th class="px-3 py-2 font-semibold border-b border-gray-200">User Agent</th>
                            <th class="px-3 py-2 font-semibold border-b border-gray-200">Time</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 text-sm">
                        @foreach ($dailyLogs as $log)
                            <tr class="border-t border-gray-200 hover:bg-gray-50 transition-colors">
                                <td class="px-3 py-2 align-top">{{ $log->id }}</td>
                                <td class="px-3 py-2 align-top">{{ $log->user->name ?? 'N/A' }}</td>
                                <td class="px-3 py-2 align-top">{{ $log->action }}</td>
                                <td class="px-3 py-2 align-top break-words">{{ $log->description }}</td>
                                <td class="px-3 py-2 align-top">{{ $log->ip_address }}</td>
                                <td class="px-3 py-2 align-top break-words text-xs">{{ $log->user_agent }}</td>
                                <td class="px-3 py-2 align-top whitespace-nowrap text-xs">
                                    {{ $log->created_at->format('H:i:s') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @empty
            <div class="text-center text-gray-200">No logs found.</div>
        @endforelse

        <!-- Pagination Buttons -->
        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    </div>
@endsection
