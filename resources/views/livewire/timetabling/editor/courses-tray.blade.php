{{-- courses-tray.blade.php --}}

<div
    x-data="{ open: false }"
    @click.outside="open = false"
    x-on:courses-tray:retract.window="open = false"
    class="fixed top-30 right-0 z-50"
>

    <div class="flex flex-row items-start align-center">
        {{-- Toggle Button --}}
        <button
            class="flex justify-center h-30 bg-red-800 p-3.5 rounded-tl-md rounded-bl-md cursor-pointer hover:bg-gray-400"
            @click.stop="open = !open"
        >
            <span class="[writing-mode:vertical-rl] [text-orientation:mixed] leading-none text-center text-white">
                Tray
            </span>
        </button>

        {{-- Courses Tray Panel --}}
        <div
            x-show="open"
            x-cloak
            id="coursesTray"
            class="bg-white shadow-md rounded-bl-xl p-6 space-y-4 w-[400px] h-200 overflow-y-auto"
        >
            <h2 class="text-xl font-semibold text-gray-800 mb-2">
                Courses Tray
            </h2>

            {{-- This container will be populated by JS using window.sessionGroupsData --}}
            <div id="sessionGroupsContainer" class="space-y-4">
                {{-- JS will render session group tables here, following the prototype structure --}}
            </div>
        </div>
    </div>
</div>
