@props([
    'label' => '',
    'name',
    'value' => null,
    'options' => [],
    'default' => null,
    'disabled' => false,
])

<div class="mb-3 flex flex-col gap-2">
    @if($label)
        <label for="{{ $name }}" >{{ $label }}:</label>
    @endif

    <select
        name="{{ $name }}"
        id="{{ $name }}"
        class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 ring-black focus:border-transparent transition disabled:bg-gray-100 disabled:cursor-not-allowed"
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes }}
    >
        <option value="" {{ (old($name, $value) === null && $default === null) ? 'selected' : '' }}>
            Select an option
        </option>
        @foreach($options as $optionValue => $text)
            <option value="{{ $optionValue }}" {{ old($name, $value ?? $default) == $optionValue ? 'selected' : '' }}>
                {{ $text }}
            </option>
        @endforeach
    </select>

    @error($name)
    <span class="!text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>
