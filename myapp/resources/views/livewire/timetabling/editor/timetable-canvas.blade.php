{{-- timetable-canvas.blade.php --}}

<div class="timetable-editor">


    @if ($rooms->isNotEmpty())
        <div class="relative overflow-x-auto rounded-lg shadow-md flex flex-col justify-center items-center">

            <div class="flex flex-col bg-white w-[1400px] rounded-t-lg shadow-sm">

                {{-- TERM SELECTOR --}}
                <div class="flex flex-row justify-center w-full p-3 gap-6 bg-gray-100 border-b border-gray-200 rounded-t-lg">
                    <button
                        type="button"
                        data-term-index="0"
                        class="term-button px-6 py-2 bg-red-700 text-white font-semibold rounded-lg shadow hover:bg-red-800 transition">
                        1st Term
                    </button>

                    <button
                        type="button"
                        data-term-index="1"
                        class="term-button px-6 py-2 bg-gray-200 text-gray-700 font-semibold rounded-lg shadow hover:bg-gray-300 transition">
                        2nd Term
                    </button>
                </div>

                {{-- DAY SELECTOR --}}
                <div class="grid grid-cols-6 w-full bg-white p-3 gap-2 border-b border-gray-200">
                    @foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'] as $index => $day)
                        <button
                            type="button"
                            data-day-index="{{ $index }}"
                            class="day-button py-2 text-center rounded-lg text-sm font-medium bg-gray-200 text-gray-700
                            hover:bg-red-700 hover:text-white transition-all duration-200 shadow-md hover:shadow-lg">
                            {{ $day }}
                        </button>
                    @endforeach
                </div>
            </div>



            <table class="w-[1400px] max-w-[1400px] border-collapse table-auto text-xs md:text-sm rounded-lg">
                <thead class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                <tr>
                    <th class="border border-gray-200 px-3 py-2 text-center w-24">
                        Time
                    </th>

                    @foreach ($rooms as $room)
                        <th class="border border-gray-200 px-3 py-2 text-center whitespace-normal break-words">
                            {{ $room->room_name }}
                        </th>
                    @endforeach
                </tr>
                </thead>

                <tbody class="text-gray-700">
                @foreach ($timeslots as $index => $slot)
                    <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-gray-100 transition-colors">
                        {{-- Time column --}}
                        <td class="border border-gray-200 px-3 py-2 text-center text-sm">
                            {{ $slot }}
                        </td>

                        {{-- Room cells --}}
                        @foreach ($rooms as $room)
                            <td class="border border-gray-200 px-3 py-2 text-center text-sm">
                                {{-- Session for this room & timeslot goes here later --}}
                            </td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-gray-500 text-sm">
            No rooms found for this timetable.
        </p>
    @endif
</div>
