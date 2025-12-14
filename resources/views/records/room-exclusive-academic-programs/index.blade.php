@extends('app')

@section('title', 'Room Exclusive Academic Programs')

@section('content')
    <div class="w-full p-4">

        <!-- Header: Title + Add + Back Button -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-xl font-bold text-white">
                Exclusive Academic Programs for {{ $room->room_name }}
            </h1>
            <div class="flex gap-4">
                <!-- Add button: yellow -->
                <a href="{{ route('rooms.room-exclusive-academic-programs.create', $room) }}"
                   class="bg-yellow-500 text-[#5e0b0b] px-4 py-2 rounded-lg font-semibold shadow hover:bg-yellow-600 active:bg-yellow-700 transition-all duration-150">
                    Add
                </a>

                <!-- Back button: gray -->
                <a href="{{ route('rooms.index') }}"
                   class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg font-semibold shadow hover:bg-gray-300 hover:text-gray-900 active:bg-gray-400 transition-all duration-150">
                    Back
                </a>
            </div>
        </div>

        <!-- Table -->
        <table class="w-full text-left border-separate border-spacing-0 bg-white rounded-lg shadow-md overflow-hidden">
            <thead class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
            <tr>
                <th class="px-6 py-3 font-semibold">Program Name</th>
                <th class="px-6 py-3 font-semibold">Abbreviation</th>
                <th class="px-6 py-3 font-semibold text-center">Action</th>
            </tr>
            </thead>
            <tbody class="text-gray-700">
            @forelse($assignedExclusivePrograms as $item)
                @php
                    $program = $item->academicProgram;
                @endphp
                <tr class="border-t border-gray-200 hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-3">
                        {{ $program?->program_name ?? 'Unknown Program' }}
                    </td>
                    <td class="px-6 py-3">
                        {{ $program?->program_abbreviation ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-3 text-center">
                        <livewire:buttons.delete
                            action="rooms.room-exclusive-academic-programs.destroy"
                            :params="[$room, $item]"
                            item_name="program"
                            btnType="icon"
                            class="text-gray-600 px-3 py-2 rounded-lg hover:bg-gray-200 hover:text-gray-800 active:bg-gray-300 transition-all duration-150 flex items-center justify-center w-10 h-10"
                        />
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center py-6 text-gray-500">
                        No exclusive academic programs assigned for this room yet.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
