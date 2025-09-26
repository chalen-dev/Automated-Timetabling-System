<div class="mb-3">
    @if($label ?? false)
        <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    @endif
    <input
        type="number"
        name="{{ $name }}"
        id="{{ $name }}"
        class="form-control"
        value="{{ old($name, $default ?? '') }}"
        @if(isset($min)) min="{{ $min }}" @endif
        @if(isset($max)) max="{{ $max }}" @endif
        @if(isset($step)) step="{{ $step }}" @endif
    >

    @error($name)
    <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
</div>
