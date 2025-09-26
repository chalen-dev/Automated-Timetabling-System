<div>
    <label>{{$label}}</label>
    <input type="{{$type}}" name="{{$name}}" placeholder="{{$placeholder}}">
    @error($name)
        <span class="!text-red-500">{{$message}}</span>
    @enderror
</div>


