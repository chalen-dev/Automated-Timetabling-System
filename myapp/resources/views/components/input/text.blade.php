@props(['label', 'name', 'value' => ''])

<div class="mb-3">
    <label>{{ $label }}:</label>
    <input type="text" name="{{ $name }}" value="{{ old($name, $value) }}" {{ $attributes }}>
    @error($name)
    <span class="!text-red-500">{{ $message }}</span>
    @enderror
</div>
