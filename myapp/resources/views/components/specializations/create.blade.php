@props(['professor', 'courses'])

<div x-data="{ open: true }" x-show="open" style="display:none;">
    <div style="position:fixed; inset:0; background:rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center;">
        <div style="background:white; padding:20px; width:300px; border-radius:8px;">
            <h3 class="font-bold mb-3">Add Specialization</h3>

            <form action="{{ route('professors.specializations.store', $professor) }}" method="POST">
                @csrf
                <select name="course_id" class="w-full border rounded p-2 mb-3">
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}">{{ $course->name }}</option>
                    @endforeach
                </select>

                <div style="text-align:right;">
                    <button type="button" @click="open = false">Cancel</button>
                    <button type="submit" class="bg-blue-500 text-white px-3 py-1 rounded">Add</button>
                </div>
            </form>
        </div>
    </div>
</div>
