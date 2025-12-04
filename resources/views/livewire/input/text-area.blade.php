
<div class="mb-3 flex flex-col gap-2">
    <livewire:text.label :name="$name" :label="$label" :is_required="$isRequired" />

    <!--This is a non-resizeable text area-->
    <textarea
        name="{{ $name }}"
        id="{{ $name }}"
        rows="{{ $rows }}"
        class="w-full border border-gray-300 rounded-md p-2 focus:ring-2 focus:ring-black focus:outline-none resize-none"
    >
        {{ $value ?: old($name, $default) }}
    </textarea>

    @error($name)
    <p class="!text-red-500">{{ $message }}</p>
    @enderror
</div>
