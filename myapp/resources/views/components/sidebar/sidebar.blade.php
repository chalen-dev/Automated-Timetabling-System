<aside
    class="fixed top-0 left-0 h-screen w-64 transition-transform duration-300 z-50 bg-white shadow"
    :class="$store.sidebar.open ? 'translate-x-0' : '-translate-x-full'"
>
    <div class="flex justify-end p-4">
        <!-- CLOSE BUTTON -->
        <button @click="$store.sidebar.open = false">✕</button>
    </div>

    <ul class="flex flex-col gap-6 px-4 p-10">
        <li class="flex flex-row justify-start items-center h-10 pl-10">
            <a href="{{ route('records.timetables.index') }}">Timetables</a>
        </li>
        <li class="flex flex-row justify-start items-center h-10 pl-10">
            <a href="{{ route('records.courses.index') }}">Courses</a>
        </li>
        <li class="flex flex-row justify-start items-center h-10 pl-10">
            <a href="{{ route('records.professors.index') }}">Professors</a>
        </li>
        <li class="flex flex-row justify-start items-center h-10 pl-10">
            <a href="{{ route('records.rooms.index') }}">Rooms</a>
        </li>
        <li class="flex flex-row justify-start items-center h-10 pl-10">
            <a href="{{ route('records.academic-programs.index') }}">Programs</a>
        </li>
    </ul>
</aside>

<!-- Overlay -->
<div
    class="fixed inset-0 bg-black/30 z-40"
    x-show="$store.sidebar.open"
    x-transition.opacity
    @click="$store.sidebar.open = false"
></div>
