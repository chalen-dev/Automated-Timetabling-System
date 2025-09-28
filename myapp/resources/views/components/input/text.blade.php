@props(['label', 'name', 'value' => ''])

<div class="mb-3 flex flex-col gap-2">
    <label>{{ $label }}:</label>
    <input class = "w-100" type="text" name="{{ $name }}" value="{{ old($name, $value) }}" {{ $attributes }}>
    @error($name)
    <span class="!text-red-500">{{ $message }}</span>
    @enderror
</div>
