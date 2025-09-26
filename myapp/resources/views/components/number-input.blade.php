@php
    $inputValue = old($name, $value ?? '');
    if (is_array($inputValue)) $inputValue = '';
@endphp

<div class="mb-3">
    <label>{{ $label }}:</label>
    <input
        type="number"
        name="{{ $name }}"
        value="{{ $inputValue }}"
        min="{{ $min ?? '' }}"
        max="{{ $max ?? '' }}"
        step="{{ $step ?? '' }}"
        {{ $attributes }}
    >
    @error($name)
    <span class="text-red-500">{{ $message }}</span>
    @enderror
</div>
