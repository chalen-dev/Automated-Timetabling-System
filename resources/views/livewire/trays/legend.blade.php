{{-- legend.blade.php --}}

<div
    x-data="{ open: false }"
    @click.outside="open = false"
    class="fixed top-30 left-0 z-50"
>
    <div class="flex flex-row items-center gap-0">
        <div
            x-show="open"
            x-cloak
            class="bg-white shadow-lg p-6 rounded-sm"
        >
            <div class="legend-item flex items-center space-x-2">
                <livewire:icons.check />
                <span>Valid placement / swap</span>
            </div>

            <div class="legend-item flex items-center space-x-2">
                <livewire:icons.cross />
                <span>Invalid move</span>
            </div>

            <div class="legend-item flex items-center space-x-2">
                <livewire:icons.lock />
                <span>Locked value</span>
            </div>
        </div>

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
