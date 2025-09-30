@props(['action','model','item_name','btnType' => 'normal'])

<form action="{{ route($action, $model) }}" method="POST" class="flex items-center justify-center">
    @csrf
    @method('DELETE')
    <button
        type="submit"
        onclick="return confirm('Are you sure you want to delete this {{$item_name}}?')"
        class="text-red-500 bg-transparent border-none p-1 flex items-center justify-center rounded"
    >
        @if($btnType === 'normal')
            <p>Delete</p>
        @elseif($btnType === 'icon')
            <i class="bi bi-trash"></i>
        @endif
    </button>
</form>
