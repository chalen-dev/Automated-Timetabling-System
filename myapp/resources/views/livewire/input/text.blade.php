<div class="mb-3 flex flex-col gap-2">
    <label>{{ $label }}:</label>
    <input class = "w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 ring-black focus:border-transparent transition" type="text" name="{{ $name }}" value="{{ old($name, $value) }}" {{ $attributes }}>
    @error($name)
    <span class="!text-red-500">{{ $message }}</span>
    @enderror
</div>
