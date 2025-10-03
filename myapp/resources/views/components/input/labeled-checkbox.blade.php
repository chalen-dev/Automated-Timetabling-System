@props([
    'label',
    'name',
    'value' => '1',       // what gets sent if checked
    'checkedValue' => null // database value for edit form
])

<div class="mb-3 flex flex-col gap-1 w-full">
    <label class="flex items-center gap-2">
        <input
            type="checkbox"
            name="{{ $name }}"
            value="{{ $value }}"
            {{ old($name, $checkedValue) ? 'checked' : '' }}
        >
        {{ $label }}
    </label>
    @error($name)
    <span class="!text-red-500">{{$message}}</span>
    @enderror
</div>
