@props(['label', 'type', 'placeholder', 'name', 'value' => ''])
<div class="mb-3 flex flex-col gap-1 w-full">
    <label>{{$label}}</label>
    <input type="{{$type}}" name="{{$name}}" placeholder="{{$placeholder}}" value="{{old($name, $value)}}">
    @error($name)
        <span class="!text-red-500">{{$message}}</span>
    @enderror
</div>


