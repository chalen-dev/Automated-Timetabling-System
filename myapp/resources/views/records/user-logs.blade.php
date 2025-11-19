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
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase w-[100px]">More</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($dailyLogs as $log)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 text-sm text-gray-900">{{ $log->user->name ?? 'Someone' }}</td>
                                <td class="px-4 py-2 text-sm text-gray-900">{{ friendly_action($log->action, $log->model_type) }}</td>

                                <!-- User-friendly details (collapsed) -->
                                <td class="px-4 py-2 text-sm text-gray-600">
                                    @if ($log->details !== null && trim($log->details) !== '')
                                        @php $details = json_decode($log->details, true); @endphp
                                        <div class="details-content max-h-16 overflow-hidden transition-all duration-300">
                                            @if(is_array($details))
                                                <ul class="ml-4 list-disc">
                                                    @foreach($details as $key => $value)
                                                        <li><strong>{{ ucfirst($key) }}:</strong> {{ $value }}</li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                {{ $log->details }}
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-gray-400 italic">none</span>
                                    @endif
                                </td>

                                <td class="px-4 py-2 text-sm text-gray-400">{{ $log->created_at->format('h:i A') }}</td>

                                <!-- Technical Details button -->
                                <td class="px-2 py-2 text-center">
                                    <button
                                        class="show-technical-details px-3 py-1 bg-blue-600 text-white rounded text-xs"
                                        data-ip="{{ $log->ip_address }}"
                                        data-agent="{{ $log->user_agent }}"
                                        data-details='@json($log->details)'
                                    >
                                        Show More
                                    </button>
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

        <div class="mt-4">{{ $logs->links() }}</div>
    </div>

    <!-- Technical Details Modal -->
    <div id="technicalModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg w-11/12 max-w-lg p-6 relative">
            <button id="closeModal" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">&times;</button>
            <h2 class="text-lg font-bold mb-4">Technical Details</h2>
            <div id="modalContent" class="text-sm text-gray-700 space-y-2"></div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('technicalModal');
            const modalContent = document.getElementById('modalContent');
            const closeModal = document.getElementById('closeModal');

            document.querySelectorAll('.show-technical-details').forEach(btn => {
                btn.addEventListener('click', () => {
                    const ip = btn.dataset.ip;
                    const agent = btn.dataset.agent;
                    const details = btn.dataset.details;

                    let parsedDetails;
                    try { parsedDetails = JSON.parse(details); } catch { parsedDetails = details; }

                    modalContent.innerHTML = `
                <p><strong>IP Address:</strong> ${ip}</p>
                <p><strong>User Agent:</strong> ${agent}</p>
            `;

                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                });
            });

            closeModal.addEventListener('click', () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            });

            modal.addEventListener('click', (e) => {
                if(e.target === modal){
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }
            });
        });
    </script>
@endsection
