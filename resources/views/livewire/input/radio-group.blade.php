
<div class="mb-3 flex flex-col gap-2">
    <livewire:text.label :name="$name" :label="$label" :is_required="$isRequired" />

    <div class="flex flex-col gap-2">
        @foreach($options as $optionValue => $optionLabel)
            <label class="inline-flex items-center gap-2 cursor-pointer">
                <input
                    type="radio"
                    name="{{ $name }}"
                    value="{{ $optionValue }}"
                    @checked(old($name, $value) == $optionValue)
                    class="text-blue-600 focus:ring-blue-500"
                >
                <span>{{ $optionLabel }}</span>
            </label>
        @endforeach
    </div>

    @error($name)
    <p class="!text-red-500">{{ $message }}</p>
    @enderror
</div>
