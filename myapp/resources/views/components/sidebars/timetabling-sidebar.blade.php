@props([
    'timetable' => \App\Models\Timetable::class
])

<nav class="flex flex-col left-0 top-29 pl-5 fixed h-3/4 w-37">
    <div class="flex flex-col flex-1 justify-between text-center bg-white p-4 rounded-2xl shadow-2xl">
        <div class="flex flex-col gap-6">
            <a href="{{route('timetables.timetable-editing-pane.index', $timetable)}}">
                <div class="hover:bg-[#911A141A] justify-center transition-transform duration-300 pt-2 pb-2 flex flex-col h-16 items-center gap-2 rounded-2xl">
                    <span>View Timetable</span>
                </div>
            </a>
            <a href="{{route('timetables.session-groups.index', $timetable)}}">
                <div class="hover:bg-[#911A141A] justify-center transition-transform duration-300 pt-2 pb-2 flex flex-col h-16 items-center gap-2 rounded-2xl">
                    <span>Class Sessions</span>
                </div>
            </a>
            <a href="{{route('timetables.timetable-professors.index', $timetable)}}">
                <div class="hover:bg-[#911A141A] justify-center transition-transform duration-300 pt-2 pb-2 flex flex-col h-16 items-center gap-2 rounded-2xl">
                        Professors
                </div>
            </a>
            <a href="{{route('timetables.timetable-rooms.index', $timetable)}}">
                <div class="hover:bg-[#911A141A] justify-center transition-transform duration-300 pt-2 pb-2 flex flex-col h-16 items-center gap-2 rounded-2xl">
                    <span>Rooms</span>
                </div>
            </a>
            <a href="{{route('timetables.generate-timetable.index', $timetable)}}">
                <div class="hover:bg-[#911A141A] justify-center transition-transform duration-300 pt-2 pb-2 flex flex-col h-16 items-center gap-2 rounded-2xl">
                        <span>Generate Timetable</span>
                </div>
            </a>
        </div>
        <a href="">
            <div class="hover:bg-[#911A141A] justify-center transition-transform duration-300 pt-2 pb-2 flex flex-col h-16 items-center gap-2 rounded-2xl">
                button
            </div>
        </a>
    </div>
</nav>
