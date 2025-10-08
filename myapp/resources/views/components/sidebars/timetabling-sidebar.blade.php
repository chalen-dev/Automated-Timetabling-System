@props([
    'timetable' => \App\Models\Timetable::class
])

<nav class="flex flex-col left-0 top-29 pl-5 fixed h-3/4 w-37">
    <div class="flex flex-col flex-1 justify-between text-center bg-white p-4 rounded-2xl shadow-2xl">
        <div class="flex flex-col gap-6">
            <a href="{{route('timetables.timetable-editing-pane.index', $timetable)}}">
                <div class="{{ request()->routeIs('timetables.timetable-editing-pane.index') ? 'bg-[#5e0b0b] text-[#ffffff]' : 'hover:bg-[#911A141A]' }} justify-center transition-transform duration-300 pt-2 pb-2 flex flex-col h-16 items-center gap-2 rounded-2xl">
                    <span>View Timetable</span>
                </div>
            </a>
            <a href="{{route('timetables.session-groups.index', $timetable)}}">
                <div class="{{ request()->routeIs('timetables.session-groups.index') ? 'bg-[#5e0b0b] text-[#ffffff]' : 'hover:bg-[#911A141A]' }} justify-center transition-transform duration-300 pt-2 pb-2 flex flex-col h-16 items-center gap-2 rounded-2xl">
                    <span>Class Sessions</span>
                </div>
            </a>
            <a href="{{route('timetables.timetable-professors.index', $timetable)}}">
                <div class="{{ request()->routeIs('timetables.timetable-professors.index') ? 'bg-[#5e0b0b] text-[#ffffff]' : 'hover:bg-[#911A141A]' }} justify-center transition-transform duration-300 pt-2 pb-2 flex flex-col h-16 items-center gap-2 rounded-2xl">
                    <span>Professors</span>
                </div>
            </a>
            <a href="{{route('timetables.timetable-rooms.index', $timetable)}}">
                <div class="{{ request()->routeIs('timetables.timetable-rooms.index') ? 'bg-[#5e0b0b] text-[#ffffff]' : 'hover:bg-[#911A141A]' }} justify-center transition-transform duration-300 pt-2 pb-2 flex flex-col h-16 items-center gap-2 rounded-2xl">
                    <span>Rooms</span>
                </div>
            </a>
            <a href="{{route('timetables.generate-timetable.index', $timetable)}}">
                <div class="{{ request()->routeIs('timetables.generate-timetable.index') ? 'bg-[#5e0b0b] text-[#ffffff]' : 'hover:bg-[#911A141A]' }} justify-center transition-transform duration-300 pt-2 pb-2 flex flex-col h-16 items-center gap-2 rounded-2xl">
                    <span>Generate Timetable</span>
                </div>
            </a>
        </div>
    </div>
</nav>
