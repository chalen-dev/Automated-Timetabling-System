<div
    x-data="{ open: false }"
    @click.outside="open = false"
    class="fixed top-60 right-0 z-40"
>
    <div class="flex flex-row items-start align-center">
        {{-- Toggle Button (vertical, hanging from right) --}}
        <button
            class="flex justify-center h-30 bg-red-800 p-3.5 rounded-tl-md rounded-bl-md cursor-pointer hover:bg-gray-400"
            @click.stop="open = !open"
        >
                    <span class="[writing-mode:vertical-rl] [text-orientation:mixed] leading-none text-center text-white">
                        Filters
                    </span>
        </button>

        {{-- Filters Tray Panel --}}
        <div
            x-show="open"
            x-cloak
            id="filtersTrayPanel"
            class="bg-white shadow-md rounded-bl-xl p-6 space-y-5 w-[400px] h-100 overflow-y-auto"
        >
            <h2 class="text-xl font-semibold text-gray-800 mb-2">
                Class Session Filters
            </h2>

            {{-- Programs --}}
            <div class="space-y-2">
                <h3 class="text-xs font-semibold text-gray-600 uppercase tracking-wide">
                    Programs
                </h3>
                <div class="flex flex-wrap gap-2">
                    <button
                        type="button"
                        class="program-filter-btn inline-flex items-center justify-center px-3 py-1.5 rounded-full text-xs font-semibold border transition
                                       border-transparent bg-gray-200 text-gray-800 hover:bg-gray-300"
                        data-program-id="all"
                    >
                        All Programs
                    </button>

                    @foreach($sessionGroupsByProgram as $programId => $groups)
                        @php
                            $abbr = $groups->first()->academicProgram->program_abbreviation ?? 'Unknown';
                        @endphp
                        <button
                            type="button"
                            class="program-filter-btn inline-flex items-center justify-center px-3 py-1.5 rounded-full text-xs font-semibold border transition
                                           border-transparent bg-gray-200 text-gray-800 hover:bg-gray-300"
                            data-program-id="{{ $programId }}"
                        >
                            {{ $abbr }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Year Level --}}
            <div class="space-y-2" id="year-filter-menu">
                <h3 class="text-xs font-semibold text-gray-600 uppercase tracking-wide">
                    Year Level
                </h3>
                <div class="flex flex-wrap gap-2">
                    <button
                        type="button"
                        class="year-filter-btn inline-flex items-center justify-center px-3 py-1.5 rounded-full text-xs font-semibold border transition
                                       border-transparent bg-gray-200 text-gray-800 hover:bg-gray-300"
                        data-year="all"
                    >
                        All Years
                    </button>
                    <button
                        type="button"
                        class="year-filter-btn inline-flex items-center justify-center px-3 py-1.5 rounded-full text-xs font-semibold border transition
                                       border-transparent bg-gray-200 text-gray-800 hover:bg-gray-300"
                        data-year="1st"
                    >
                        1st Year
                    </button>
                    <button
                        type="button"
                        class="year-filter-btn inline-flex items-center justify-center px-3 py-1.5 rounded-full text-xs font-semibold border transition
                                       border-transparent bg-gray-200 text-gray-800 hover:bg-gray-300"
                        data-year="2nd"
                    >
                        2nd Year
                    </button>
                    <button
                        type="button"
                        class="year-filter-btn inline-flex items-center justify-center px-3 py-1.5 rounded-full text-xs font-semibold border transition
                                       border-transparent bg-gray-200 text-gray-800 hover:bg-gray-300"
                        data-year="3rd"
                    >
                        3rd Year
                    </button>
                    <button
                        type="button"
                        class="year-filter-btn inline-flex items-center justify-center px-3 py-1.5 rounded-full text-xs font-semibold border transition
                                       border-transparent bg-gray-200 text-gray-800 hover:bg-gray-300"
                        data-year="4th"
                    >
                        4th Year
                    </button>
                </div>
            </div>

            {{-- Time of Day --}}
            <div class="space-y-2" id="time-filter-menu">
                <h3 class="text-xs font-semibold text-gray-600 uppercase tracking-wide">
                    Time of Day
                </h3>
                <div class="flex flex-wrap gap-2">
                    <button
                        type="button"
                        class="time-filter-btn inline-flex items-center justify-center px-3 py-1.5 rounded-full text-xs font-semibold border transition
                                       border-transparent bg-gray-200 text-gray-800 hover:bg-gray-300"
                        data-time="all"
                    >
                        All Times
                    </button>
                    <button
                        type="button"
                        class="time-filter-btn inline-flex items-center justify-center px-3 py-1.5 rounded-full text-xs font-semibold border transition
                                       border-transparent bg-gray-200 text-gray-800 hover:bg-gray-300"
                        data-time="morning"
                    >
                        Morning
                    </button>
                    <button
                        type="button"
                        class="time-filter-btn inline-flex items-center justify-center px-3 py-1.5 rounded-full text-xs font-semibold border transition
                                       border-transparent bg-gray-200 text-gray-800 hover:bg-gray-300"
                        data-time="afternoon"
                    >
                        Afternoon
                    </button>
                    <button
                        type="button"
                        class="time-filter-btn inline-flex items-center justify-center px-3 py-1.5 rounded-full text-xs font-semibold border transition
                                       border-transparent bg-gray-200 text-gray-800 hover:bg-gray-300"
                        data-time="evening"
                    >
                        Evening
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
