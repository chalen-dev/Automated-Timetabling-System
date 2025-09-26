<form action="{{route('courses.destroy', $course)}}" method="POST">
    @csrf
    @method('DELETE')
    <button
        type="submit"
        onclick="return confirm('Are you sure you want to delete this course?')"
        class="!text-red-500 !w-20 !bg-transparent !border-red-500 !border-1"
    >
        Delete
    </button>
</form>
