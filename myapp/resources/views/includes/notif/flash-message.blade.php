<!-- resources/views/includes/flash-messages.blade.php -->

@php
    $types = ['success', 'error', 'info', 'warning'];
@endphp

@foreach ($types as $type)
    @if (session($type))
        @php
            $colors = [
                'success' => 'bg-green-500 text-white',
                'error'   => 'bg-red-500 text-white',
                'info'    => 'bg-blue-500 text-white',
                'warning' => 'bg-yellow-500 text-black'
            ];
        @endphp

        <div
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 4000)"
            class="fixed top-5 left-1/2 -translate-x-1/2 rounded shadow-lg px-3 py-2 {{ $colors[$type] }}"
        >
            <span class="mr-2">{{ session($type) }}</span>
            <button
                @click="show = false"
                class="p-0 m-0 text-center font-bold !w-[20px]"
            >&times;</button>
        </div>
    @endif

@endforeach
