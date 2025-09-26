@props(['label' => '', 'name', 'value' => ''])

<div class="mb-3 flex flex-col gap-4">
    <div class="flex flex-row gap-4">
        @if($label ?? false)
            <label for="{{ $name }}" class="form-label">{{ $label }}</label>
        @endif
        <input
            type="number"
            name="{{ $name }}"
            id="{{ $name }}"
            value="{{ old($name, $default ?? $value) }}"
            @if(isset($min)) min="{{ $min }}" @endif
            @if(isset($max)) max="{{ $max }}" @endif
            @if(isset($step)) step="{{ $step }}" @endif
        >
    </div>


    @error($name)
        <div class="!text-red-500">{{ $message }}</div>
    @enderror
</div>
