<div
    x-data="{ open: false }"
    @click.outside="open = false"
    class="fixed left-0 top-70 z-50"
>
    <div class="flex flex-row items-center">
        <div
            x-show="open"
            x-cloak
            class="bg-white shadow-lg p-4 rounded-sm"
        >
            <h3 class="text-base font-semibold mb-3">Tools</h3>

            <div class="space-y-2 text-sm">
                <button
                    type="button"
                    class="px-4 py-2 rounded-md bg-green-600 text-white text-sm hover:bg-green-700"
                    onclick="window.saveTimetableToExcel()"
                >
                    Save changes
                </button>
                <a
                    href="{{ route('timetables.export_formatted', $timetable->id) }}"
                    class="w-full block px-3 py-2 rounded-md bg-gray-100 hover:bg-gray-200 text-left"
                >
                    Download XLSX
                </a>

            </div>
        </div>

        <button
            class="bg-white p-3.5 rounded-tr-md rounded-br-md cursor-pointer hover:bg-gray-400"
            @click.stop="open = !open"
        >
            <span class="[writing-mode:vertical-rl] [text-orientation:mixed] leading-none text-center">
                Tools
            </span>
        </button>
    </div>
</div>
