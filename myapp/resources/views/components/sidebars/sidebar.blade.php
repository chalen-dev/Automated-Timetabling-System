<aside
    class="fixed top-0 left-0 h-screen w-64 transition-transform duration-300 z-50 bg-white shadow"
    :class="$store.sidebar.open ? 'translate-x-0' : '-translate-x-full'"
>
    <div class="flex justify-end p-4">
        <!-- CLOSE BUTTON -->
        <button @click="$store.sidebar.open = false">✕</button>
    </div>

    <div class="flex flex-col gap-6 px-4 p-10 font-bold">
        <div class="hover:bg-[#911A141A] transition-transform duration-300 pl-10 flex flex-row justify-start items-center h-13 gap-2 rounded-2xl">
            <svg class="xmlns=http://www.w3.org/2000/svg viewBox=0 0 24 24 w-6 h-6 stroke-current fill-none">
                <path
                    d="M3 4h18v18H3V4zM3 10h18M8 2v4M16 2v4"
                    stroke-width="1.6"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>
            <a href="{{ route('timetables.index') }}">Timetables</a>
        </div>
        <div class="hover:bg-[#911A141A] transition-transform duration-300 pl-10 flex flex-row justify-start items-center h-13 gap-2 rounded-2xl">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                class="w-6 h-6 stroke-current fill-none"
            >
                <path
                    d="M4 19.5V4a2 2 0 0 1 2-2h10l4 4v13.5a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2Z"
                    stroke-width="1.6"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>
            <a href="{{ route('courses.index') }}">Courses</a>
        </div>
        <div class="hover:bg-[#911A141A] transition-transform duration-300 pl-10 flex flex-row justify-start items-center h-13 gap-2 rounded-2xl">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                class="w-6 h-6 stroke-current fill-none"
            >
                <path
                    d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm-7 9a7 7 0 0 1 14 0Z"
                    stroke-width="1.6"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>
            <a href="{{ route('professors.index') }}">Professors</a>
        </div>
        <div class="hover:bg-[#911A141A] transition-transform duration-300 pl-10 flex flex-row justify-start items-center h-13 gap-2 rounded-2xl">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                class="w-6 h-6 stroke-current fill-none"
            >
                <path
                    d="M3 21V3h18v18H3Zm10-9h6M7 8h6v6H7V8Z"
                    stroke-width="1.6"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>
            <a href="{{ route('rooms.index') }}">Rooms</a>
        </div>
        <div class="hover:bg-[#911A141A] transition-transform duration-300 pl-10 flex flex-row justify-start items-center h-13 gap-2 rounded-2xl">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                class="w-6 h-6 stroke-current"
            >
                <path
                    d="M12 2l9 5-9 5-9-5 9-5zm0 10l9 5-9 5-9-5 9-5z"
                    stroke-width="1.6"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>
            <a href="{{ route('academic-programs.index') }}">Programs</a>
        </div>
    </div>
</aside>

<!-- Overlay -->
<div
    class="fixed inset-0 bg-black/30 z-40"
    x-show="$store.sidebar.open"
    x-transition.opacity
    @click="$store.sidebar.open = false"
></div>
