@extends('app')

@section('title', 'User Logs')

@section('content')
    <div class="w-full p-4">

        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-xl font-bold mb-0 text-white">User Logs</h1>
        </div>

        @forelse ($logs as $date => $dailyLogs)
            <div class="mb-6 bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-gray-200 px-4 py-2 font-semibold text-gray-700">
                    {{ \Carbon\Carbon::parse($date)->format('F d, Y') }}
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Details</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($dailyLogs as $log)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 text-sm text-gray-900">
                                    {{ $log->user->name ?? 'Someone' }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-900">
                                    {{ friendly_action($log->action, $log->model_type) }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-600">
                                    @if ($log->details !== null && trim($log->details) !== '')
                                        @php $details = json_decode($log->details, true); @endphp

                                        @if (is_array($details))
                                            <ul class="ml-4 list-disc">
                                                @foreach($details as $key => $value)
                                                    <li><strong>{{ ucfirst($key) }}:</strong> {{ $value }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            {{ $log->details }}
                                        @endif
                                    @else
                                        <span class="text-gray-400 italic">none</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-400">
                                    {{ $log->created_at->format('h:i A') }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="text-center text-gray-300">No logs found.</div>
        @endforelse

        <!-- Pagination Buttons -->
        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    </div>
@endsection
