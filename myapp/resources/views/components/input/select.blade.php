@props(['label' => '', 'name', 'value' => null, 'options' => [], 'default' => null])

<div class="mb-3">
    @if($label)
        <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    @endif
    <select name="{{ $name }}" id="{{ $name }}" class="form-select">
        <option value="" {{ (old($name, $value) === null && $default === null) ? 'selected' : '' }}> Select an option </option>
        @foreach($options as $optionValue => $text)
            <option value="{{ $optionValue }}"
                {{ old($name, $value ?? $default) == $optionValue ? 'selected' : '' }}>
                {{ $text }}
            </option>
        @endforeach
    </select>

    @error($name)
        <div class="!text-red-500">{{ $message }}</div>
    @enderror
</div>
