<div
    x-data="{ open: false }"
    @click.outside="open = false"
    class="fixed top-30 right-0 z-50"
>
    <div class="flex flex-row items-start align-center ">
        {{-- Toggle Button --}}
        <button
            class="flex justify-center h-30 bg-red-800 p-3.5 rounded-tl-md rounded-bl-md cursor-pointer hover:bg-gray-400"
            @click.stop="open = !open"
        >
            <span class="[writing-mode:vertical-rl] [text-orientation:mixed] leading-none text-center">
                Courses
            </span>
        </button>

        {{-- Courses Tray Panel --}}
        <div
            x-show="open"
            x-cloak
            class="bg-white shadow-md rounded-bl-xl p-6 space-y-8 w-[400px] h-200"
        >
            <h2 class="text-xl font-semibold text-gray-800">
                Courses Tray
            </h2>

            @foreach ($sessionGroups as $group)
                <div class="border rounded-lg shadow-sm bg-gray-50">
                    <div class="flex items-center justify-between px-4 py-2 bg-gray-100 border-b">
                        <h3 class="font-semibold text-gray-700">
                            Session Group {{ $group->name ?? $group->id }}
                        </h3>
                    </div>

                    <table class="w-full table-fixed border-collapse">
                        <tbody>
                        @foreach ($group->courseSessions as $cs)
                            <tr>
                                <td class="border px-3 py-2 text-sm text-gray-700 bg-white">
                                    {{ $cs->course_code ?? $cs->id }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>
    </div>
</div>
