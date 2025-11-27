{{-- resources/views/timetabling/timetable-editing-pane/editor.blade.php --}}
@extends('app')

@section('title', $timetable->timetable_name)

@section('content')
    <div>

        <div
            x-data="{ open: false }"
            @click.outside="open = false"
            class="fixed top-30 left-0 z-50"
        >
            <!-- Wrapper that slides BOTH panel + button -->
            <div
                class="flex flex-row items-center transform transition-all duration-300"
                :class="open ? 'translate-x-0' : '-translate-x-[270px]'"
            >
                <!-- Legend panel -->
                <div
                    class="bg-white shadow-lg p-6 rounded-sm"
                    x-cloak
                >
                    <div class="legend-item flex items-center space-x-2">
                        <svg class="legend-icon check w-5 h-5 text-green-600" viewBox="0 0 20 20">
                            <path d="M4 10l4 4 8-8" fill="none" stroke="currentColor" stroke-width="2" />
                        </svg>
                        <span>Valid placement / swap</span>
                    </div>

                    <div class="legend-item flex items-center space-x-2">
                        <svg class="legend-icon cross w-5 h-5 text-red-600" viewBox="0 0 20 20">
                            <path d="M5 5l10 10M15 5L5 15" fill="none" stroke="currentColor" stroke-width="2" />
                        </svg>
                        <span>Invalid move</span>
                    </div>

                    <div class="legend-item flex items-center space-x-2">
                        <svg class="legend-icon lock w-5 h-5 text-gray-600" viewBox="0 0 20 20">
                            <rect x="4" y="9" width="12" height="8" rx="2" ry="2"
                                  fill="none" stroke="currentColor" stroke-width="2" />
                            <path d="M8 9V7a4 4 0 0 1 8 0v2"
                                  fill="none" stroke="currentColor" stroke-width="2"/>
                        </svg>
                        <span>Locked value</span>
                    </div>
                </div>

                <!-- Button (slides WITH panel, but stays visible) -->
                <button
                    class="bg-white p-3.5 rounded-tr-md rounded-br-md cursor-pointer hover:bg-gray-400"
                    @click.stop="open = !open"
                >
                    <span class="[writing-mode:vertical-rl] [text-orientation:mixed] leading-none text-center">
                        Legend
                    </span>
                </button>
            </div>
        </div>

        <div
            x-data="{ open: false }"
            @click.outside="open = false"
            class="fixed top-60 -left-9 z-50"
        >
            <!-- Sliding wrapper (panel + vertical button) -->
            <div
                class="flex flex-row items-start transform transition-all duration-300"
                :class="open ? 'translate-x-0' : '-translate-x-[260px]'"
            >
            <!-- Toolbar panel -->
            <div
                class="toolbar  p-6 rounded-sm space-y-3 flex items-center"
                x-cloak
            >

                <div class="flex flex-col space-y-3 bg-white shadow-lg p-6 rounded-sm align-center">
                    <input
                        id="newRoomName"
                        type="text"
                        placeholder="New room name"
                        class="border px-2 py-1 rounded w-full"
                    />

                    <button
                        type="button"
                        onclick="addRoom()"
                        class="bg-blue-600 text-white px-3 py-2 rounded w-full"
                    >
                        Add Room
                    </button>

                    <button
                        type="button"
                        onclick="downloadXLSX()"
                        class="bg-green-600 text-white px-3 py-2 rounded w-full"
                    >
                        Download XLSX
                    </button>
                </div>

                <!-- Vertical button tab (slides with panel, always visible) -->
                <button
                    class="bg-white h-30 p-3.5 rounded-tr-md rounded-br-md shadow-lg cursor-pointer hover:bg-gray-400"
                    @click.stop="open = !open"
                >
                    <span class="[writing-mode:vertical-rl] [text-orientation:mixed] leading-none text-center">
                        Tools
                    </span>
                </button>

            </div>

        </div>
    </div>

        <div class = tray>

        </div>
        <div>

        </div>
    </div>
@endsection
