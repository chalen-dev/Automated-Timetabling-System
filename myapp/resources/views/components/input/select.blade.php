
<div class="mb-3">
    @if($label)
        <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    @endif
    <select name="{{ $name }}" id="{{ $name }}" class="form-select">
        @foreach($options as $value => $text)
            <option value="{{ $value }}" {{ old($name, $default) == $value ? 'selected' : '' }}>
                {{ $text }}
            </option>
        @endforeach
    </select>

    @error($name)
        <div class="!text-red-500">{{ $message }}</div>
    @enderror
</div>
