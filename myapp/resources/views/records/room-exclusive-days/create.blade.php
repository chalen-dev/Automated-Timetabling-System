@extends('app')

@section('title', 'Create Room Exclusive Day')

@section('content')
    <div class="w-full p-4">

        <!-- Header: Title + Buttons -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-xl font-bold mb-0 text-white">
                Assign Exclusive Days to {{ $room->room_name }}
            </h1>
            <div class="flex gap-4">
                <!-- Confirm button: yellow -->
                <button type="submit" form="exclusiveDaysForm"
                        class="bg-yellow-500 text-[#5e0b0b] px-4 py-2 rounded-lg font-semibold shadow hover:bg-yellow-600 active:bg-yellow-700 transition-all duration-150">
                    Confirm
                </button>

                <!-- Back button: gray -->
                <a href="{{ route('rooms.room-exclusive-days.index', $room) }}"
                   class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg font-semibold shadow hover:bg-gray-300 hover:text-gray-900 active:bg-gray-400 transition-all duration-150">
                    Back
                </a>
            </div>
        </div>

        @if(isset($message))
            <div class="text-red-500 mb-4">{{ $message }}</div>
        @endif

        <!-- Days Table Form -->
        <form id="exclusiveDaysForm" action="{{ route('rooms.room-exclusive-days.store', $room) }}" method="POST">
            @csrf
            <table class="w-full text-left border-separate border-spacing-0 bg-white rounded-lg shadow-md overflow-hidden">
                <thead class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-3 font-semibold">Day</th>
                    <th class="px-6 py-3 font-semibold text-center">Select</th>
                </tr>
                </thead>
                <tbody class="text-gray-700">
                @forelse($unassignedDays as $dayKey => $dayLabel)
                    <tr class="border-t border-gray-200 hover:bg-gray-50 transition-colors cursor-pointer"
                        onclick="if(event.target.type !== 'checkbox') this.querySelector('input[type=checkbox]').click()">
                        <td class="px-6 py-3">{{ $dayLabel }}</td>
                        <td class="px-6 py-3 text-center">
                            <input type="checkbox" name="exclusive_days[]" value="{{ $dayKey }}">
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="text-center text-gray-500">No days available.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </form>

    </div>
@endsection
