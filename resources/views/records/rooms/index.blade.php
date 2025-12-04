@extends('app')

@section('title', 'Rooms')

@section('content')
    <div class="w-full p-4">

        <!-- Header: Title + Search + Create Button -->
        <div class="flex justify-between items-center mb-6">
            <div class="flex gap-20 items-center justify-center">
                <h1 class="text-xl font-bold mb-0 text-white">Rooms</h1>
                <livewire:input.search-bar
                    :action="route('rooms.index')"
                    placeholder="Search rooms, course, or days..."
                />
            </div>

            <a href="{{ route('rooms.create') }}"
               class="bg-yellow-500 text-[#5e0b0b] px-4 py-2 rounded-lg font-semibold shadow hover:bg-yellow-600 active:bg-yellow-700 transition-all duration-150">
                Create
            </a>
        </div>

        <!-- Table -->
        <table class="w-full text-left border-separate border-spacing-0 bg-white rounded-lg shadow-md overflow-hidden">
            <thead class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
            <tr>
                <th class="px-6 py-3 font-semibold">Room Name</th>
                <th class="px-6 py-3 font-semibold">Room Type</th>
                <th class="px-6 py-3 font-semibold">Room Capacity</th>
                <th class="px-6 py-3 font-semibold">Class Days</th>
                <th class="px-6 py-3 font-semibold text-center">Action</th>
            </tr>
            </thead>
            <tbody class="text-gray-700">
            @forelse($rooms as $room)
                <tr class="border-t border-gray-200 hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-3">{{ $room->room_name }}</td>
                    <td class="px-6 py-3">{{ $room->room_type }}</td>
                    <td class="px-6 py-3">{{ $room->room_capacity ?? 'none' }}</td>
                    <td class="px-6 py-3">
                        {{
                            $room->roomExclusiveDays->isNotEmpty()
                            ? $room->roomExclusiveDays
                                ->pluck('exclusive_day')
                                ->map(fn($day) => ucfirst($day))
                                ->implode(', ')
                            : 'No Specific Day/s'
                        }}
                    </td>
                    <td class="px-6 py-3 text-center">
                        <div class="flex flex-row gap-2 justify-center items-center">
                            <!-- Set Specific Days Button -->
                            <a href="{{ route('rooms.room-exclusive-days.index', $room) }}"
                               class="bg-gray-200 text-gray-800 px-3 py-2 rounded-lg font-semibold shadow hover:bg-gray-300 hover:text-gray-900 active:bg-gray-400 transition-all duration-150">
                                Set Specific Days
                            </a>

                            <!-- Show Button -->
                            <a href="{{ route('rooms.show', $room) }}"
                               class="text-gray-600 px-3 py-2 rounded-lg hover:bg-gray-200 hover:text-gray-800 active:bg-gray-300 transition-all duration-150 flex items-center justify-center w-10 h-10">
                                <i class="bi-card-list"></i>
                            </a>

                            <!-- Edit Button -->
                            <a href="{{ route('rooms.edit', $room) }}"
                               class="text-gray-600 px-3 py-2 rounded-lg hover:bg-gray-200 hover:text-gray-800 active:bg-gray-300 transition-all duration-150 flex items-center justify-center w-10 h-10">
                                <i class="bi bi-pencil-square"></i>
                            </a>

                            <!-- Delete Button -->
                            <livewire:buttons.delete
                                action="rooms.destroy"
                                :params="$room"
                                item_name="room"
                                btnType="icon"
                                class="text-gray-600 px-3 py-2 rounded-lg hover:bg-gray-200 hover:text-gray-800 active:bg-gray-300 transition-all duration-150 flex items-center justify-center w-10 h-10"
                            />
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center py-6 text-gray-500">
                        No rooms found.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
