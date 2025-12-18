@php use Illuminate\Support\Str; @endphp
@php
    $user = auth()->user();

    $isAdmin = $user && $user->role === 'admin';
    $isOwner = $user && $activeTimetable && $user->id === $activeTimetable->user_id;

    $canEditRecords =
        $activeTimetable
        && (
            $isAdmin
            || $isOwner
            || $activeTimetable->allow_non_owner_record_edit
        );
@endphp
<div
    x-data="{open: false}"

>
    @auth
        <!-- if current route is under timetables -->
        @if(
            Str::is('timetables.*.*', $currentRouteName)
            && !request()->routeIs('timetables.timetable-editing-pane.editor')
        )
            <!-- 1. Timetabling Section Left Sidebar -->
            <aside class="flex flex-col left-0 top-29 pl-5 fixed h-3/4 w-37">
                <div class="flex flex-col flex-1 justify-between text-center bg-white p-4 rounded-2xl shadow-2xl">
                    <div class="flex flex-col gap-6">
                        <a href="{{route('timetables.timetable-editing-pane.index', $timetable)}}">
                            <div class="{{ request()->routeIs('timetables.timetable-editing-pane.index') ? 'bg-[#5e0b0b] text-[#ffffff]' : 'hover:bg-[#911A141A]' }} justify-center transition-transform duration-300 pt-2 pb-2 flex flex-col h-16 items-center gap-2 rounded-2xl">
                                <span>View Timetable</span>
                            </div>
                        </a>
                        <a href="{{route('timetables.timetable-overview.index', $timetable)}}">
                            <div class="{{ request()->routeIs('timetables.timetable-overview.index') ? 'bg-[#5e0b0b] text-[#ffffff]' : 'hover:bg-[#911A141A]' }} justify-center transition-transform duration-300 pt-2 pb-2 flex flex-col h-16 items-center gap-2 rounded-2xl">
                                <span>Overview</span>
                            </div>
                        </a>
                        @if($canEditRecords)
                            <a href="{{route('timetables.session-groups.index', $timetable)}}">
                                <div class="{{ request()->routeIs('timetables.session-groups.index')
                                        ? 'bg-[#5e0b0b] text-[#ffffff]'
                                        : 'hover:bg-[#911A141A]' }}
                                        justify-center transition-transform duration-300 pt-2 pb-2
                                        flex flex-col h-16 items-center gap-2 rounded-2xl">
                                    <span>Class Sessions</span>
                                </div>
                            </a>
                        @endif

                        <!--
                        <a href="{{-- route('timetables.timetable-professors.index', $timetable) --}}">
                            <div class="{{-- request()->routeIs('timetables.timetable-professors.index') ? 'bg-[#5e0b0b] text-[#ffffff]' : 'hover:bg-[#911A141A]' --}} justify-center transition-transform duration-300 pt-2 pb-2 flex flex-col h-16 items-center gap-2 rounded-2xl">
                                <span>Professors</span>
                            </div>
                        </a>
                        -->
                        @if($canEditRecords)
                            <a href="{{route('timetables.timetable-rooms.index', $timetable)}}">
                                <div class="{{ request()->routeIs('timetables.timetable-rooms.index')
                                    ? 'bg-[#5e0b0b] text-[#ffffff]'
                                    : 'hover:bg-[#911A141A]' }}
                                    justify-center transition-transform duration-300 pt-2 pb-2
                                    flex flex-col h-16 items-center gap-2 rounded-2xl">
                                    <span>Rooms</span>
                                </div>
                            </a>
                        @endif
                        @can('manageAccess', $timetable)
                        <a href="{{route('timetables.generate-timetable.index', $timetable)}}">
                            <div class="{{ request()->routeIs('timetables.generate-timetable.index') ? 'bg-[#5e0b0b] text-[#ffffff]' : 'hover:bg-[#911A141A]' }} justify-center transition-transform duration-300 pt-2 pb-2 flex flex-col h-16 items-center gap-2 rounded-2xl">
                                <span>Generate Timetable</span>
                            </div>
                        </a>
                        @endcan
                        @can('manageAccess', $timetable)
                            <a href="{{ route('timetables.settings.edit', $timetable) }}">
                                <div
                                    class="{{ request()->routeIs('timetables.settings.*')
                                            ? 'bg-[#5e0b0b] text-[#ffffff]'
                                            : 'hover:bg-[#911A141A]' }}
                                        justify-center transition-transform duration-300 pt-2 pb-2
                                        flex flex-col h-16 items-center gap-2 rounded-2xl"
                                >
                                    <span>Settings</span>
                                </div>
                            </a>
                        @endcan
                    </div>
                </div>
            </aside>

        @else

            <!-- 2. Records Section Sidebar (The main, retractable one) -->
            <aside
                x-cloak
                @toggle-sidebar.window="open = !open"
                x-transition:enter="transform transition ease-out duration-300"
                x-transition:enter-start="-translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transform transition ease-in duration-300"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="-translate-x-full"
                class="fixed top-0 left-0 h-screen w-64 bg-white shadow-lg z-50"
                :class="open
                    ? 'fixed top-0 left-0 h-screen w-64 transition-transform duration-300 z-50 bg-white shadow translate-x-0'
                    : 'fixed top-0 left-0 h-screen w-64 transition-transform duration-300 z-50 bg-white shadow -translate-x-full'"
            >
                <div class="flex justify-end p-4">
                    <!-- CLOSE BUTTON -->
                    <button
                        @click="open = false"
                        x-transition
                        class="cursor-pointer p-1 px-2 rounded-[6px] hover:bg-[#5e0b0b] hover:text-[#ffffff] transition-transform duration-500"
                    >
                        ✕
                    </button>
                </div>

                <div class="flex flex-col gap-6 px-4 p-10 font-bold">
                    <a href="{{ route('timetables.index') }}">
                        <div class="{{ request()->routeIs('timetables.index', 'timetables.create', 'timetables.edit') ? 'bg-[#5e0b0b] text-[#ffffff]' : 'hover:bg-[#911A141A]' }} transition-transform duration-300 pl-10 flex flex-row justify-start items-center h-13 gap-2 rounded-2xl">
                            <svg class="xmlns=http://www.w3.org/2000/svg viewBox=0 0 24 24 w-6 h-6 stroke-current fill-none">
                                <path
                                    d="M3 4h18v18H3V4zM3 10h18M8 2v4M16 2v4"
                                    stroke-width="1.6"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                            <span>Timetables</span>
                        </div>
                    </a>
                    <a href="{{ route('courses.index') }}">
                        <div class="{{ request()->routeIs('courses.index', 'courses.create', 'courses.edit') ? 'bg-[#5e0b0b] text-[#ffffff]' : 'hover:bg-[#911A141A]' }} transition-transform duration-300 pl-10 flex flex-row justify-start items-center h-13 gap-2 rounded-2xl">
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
                            <span>Courses</span>
                        </div>
                    </a>
                    <!--
                    <a href="{{-- route('professors.index') --}}">
                        <div class="{{-- request()->routeIs('professors.index', 'professors.create', 'professors.edit') ? 'bg-[#5e0b0b] text-[#ffffff]' : 'hover:bg-[#911A141A]' --}} transition-transform duration-300 pl-10 flex flex-row justify-start items-center h-13 gap-2 rounded-2xl">
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
                            <span>Professors</span>
                        </div>
                    </a>
                    -->
                    <a href="{{ route('rooms.index') }}">
                        <div class="{{ request()->routeIs('rooms.index', 'rooms.create', 'rooms.edit') ? 'bg-[#5e0b0b] text-[#ffffff]' : 'hover:bg-[#911A141A]' }} transition-transform duration-300 pl-10 flex flex-row justify-start items-center h-13 gap-2 rounded-2xl">
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
                            <span>Rooms</span>
                        </div>
                    </a>
                    <a href="{{ route('academic-programs.index') }}">
                        <div class="{{ request()->routeIs('academic-programs.index', 'academic-programs.create', 'academic-programs.edit') ? 'bg-[#5e0b0b] text-[#ffffff]' : 'hover:bg-[#911A141A]' }} transition-transform duration-300 pl-10 flex flex-row justify-start items-center h-13 gap-2 rounded-2xl">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                class="w-6 h-6 stroke-current fill-none"
                            >
                                <path
                                    d="M12 2l9 5-9 5-9-5 9-5zm0 10l9 5-9 5-9-5 9-5z"
                                    stroke-width="1.6"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                            <span>Programs</span>
                        </div>
                    </a>
                    <a href="{{ route('admin.user-logs') }}">
                        <div class="{{ request()->routeIs('admin.user-logs') ? 'bg-[#5e0b0b] text-[#ffffff]' : 'hover:bg-[#911A141A]' }} transition-transform duration-300 pl-10 flex flex-row justify-start items-center h-13 gap-2 rounded-2xl">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-6 h-6 stroke-current fill-none">
                                <path d="M3 6h18M3 12h18M3 18h18" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>User Logs</span>
                        </div>
                    </a>
                    {{-- Admin-only menus --}}
                    @if(auth()->check() && auth()->user()->role === 'admin')
                        <a href="{{ route('admin.pending_users') }}">
                            <div class="{{ request()->routeIs('admin.pending_users') ? 'bg-[#5e0b0b] text-[#ffffff]' : 'hover:bg-[#911A141A]' }} transition-transform duration-300 pl-10 flex flex-row justify-start items-center h-13 gap-2 rounded-2xl">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-6 h-6 stroke-current fill-none">
                                    <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm-7 9a7 7 0 0 1 14 0Z" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <span>Pending Users</span>
                            </div>
                        </a>
                    @endif
                </div>
            </aside>

            <!-- Overlay -->
            <div
                class="fixed inset-0 bg-black/30 z-40"
                @click="open = false"
                x-show="open"
                x-transition.opacity
            ></div>

        @endif


    @endauth



</div>
