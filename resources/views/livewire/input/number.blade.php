<div class="mb-3 flex flex-col gap-4">
    <div class="flex flex-col gap-2">
        <livewire:text.label :name="$name" :label="$label" :is_required="$isRequired" />
        <input
            type="number"
            name="{{ $name }}"
            id="{{ $name }}"
            value="{{ old($name, $value ?? $default) }}"
            class="border  border-gray-300 rounded-lg px-4 py-2 text-sm w-full focus:outline-none focus:ring-2 focus:ring-[black] focus:border-transparent transition"
            @if(isset($min)) min="{{ $min }}" @endif
            @if(isset($max)) max="{{ $max }}" @endif
            @if(isset($step)) step="{{ $step }}" @endif
        >
    </div>


    @error($name)
    <div class="!text-red-500">{{ $message }}</div>
    @enderror
</div>
