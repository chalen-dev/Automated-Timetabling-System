<label
    class="@error($name) text-red-500 @enderror"
>
    @if($is_required)
        {{$label}} <span class="text-red-500">*</span>
    @endif
    {{$label}}
</label>
