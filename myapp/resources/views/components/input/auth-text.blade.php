@props(['label', 'type', 'placeholder', 'name', 'value' => ''])
<div class="mb-3 flex flex-col gap-1 w-full">
    <label>{{$label}}</label>
    <input type="{{$type}}" name="{{$name}}" placeholder="{{$placeholder}}" value="{{old($name, $value)}}" class="border border-gray-300 rounded-lg px-4 py-2 text-sm w-full focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent transition">
    @error($name)
        <span class="!text-red-500">{{$message}}</span>
    @enderror
</div>


