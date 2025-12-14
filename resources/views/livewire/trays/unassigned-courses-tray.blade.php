<div
    x-data="{ open: false }"
    @click.outside="open = false"
    class="fixed top-60 right-0 z-40"
>
    <div class="flex flex-row items-start align-center">
        <button
            class="flex justify-center h-30 bg-red-800 p-3.5 rounded-tl-md rounded-bl-md cursor-pointer hover:bg-gray-400"
            @click.stop="open = !open"
        >
            <span class="[writing-mode:vertical-rl] [text-orientation:mixed] leading-none text-center text-white">
                Unplaced
            </span>
        </button>

        <div
            x-show="open"
            x-cloak
            class="bg-white shadow-md rounded-bl-xl p-6 space-y-4 w-[480px] h-100 overflow-y-auto"
        >
            <h2 class="text-xl font-semibold text-gray-800">
                Unplaced Course Sessions
            </h2>

            @if (empty($unplacedGroups))
                <div class="text-sm text-gray-500 italic">
                    No unplaced sessions found (or the Unassigned sheet is missing).
                </div>
            @else
                @foreach ($unplacedGroups as $group)
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <div class="flex items-center justify-between bg-gray-100 px-4 py-2">
                            <div class="font-semibold text-gray-800 text-sm">
                                {{ $group['group_label'] }}
                            </div>
                            <div class="text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-full px-2 py-1">
                                {{ $group['count'] }} unplaced
                            </div>
                        </div>

                        <div class="divide-y divide-gray-200">
                            @foreach ($group['items'] as $item)
                                <div class="px-4 py-2">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="font-semibold text-gray-800 text-[12px] leading-tight">
                                                {{ $item['course_title'] !== '' ? $item['course_title'] : 'Course Session #' . $item['course_session_id'] }}
                                            </div>

                                            <div class="text-[11px] text-gray-700 leading-tight mt-0.5">
                                                <span class="font-semibold">Issue:</span> {{ $item['reason_title'] }}
                                            </div>

                                            @if (!empty($item['reason_hint']))
                                                <div class="text-[11px] text-gray-500 leading-tight">
                                                    {{ $item['reason_hint'] }}
                                                </div>
                                            @endif

                                            <div class="text-[10px] text-gray-500 leading-tight mt-0.5">
                                                <span class="font-semibold">Term tried:</span> {{ $item['terms_tried'] }}
                                            </div>

                                            {{-- Optional: show raw reason for debugging --}}
                                            {{-- <div class="text-[10px] text-gray-400">Code: {{ $item['reason_raw'] }}</div> --}}
                                        </div>

                                        <div class="text-[10px] text-gray-400 whitespace-nowrap">
                                            #{{ $item['course_session_id'] }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
