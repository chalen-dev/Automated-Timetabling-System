@extends('pages.app')

@section('title', 'Edit Course')

@section('content')
    <form action="{{route('courses.update', $course)}}" method="POST" class="flex flex-col gap-4 justify-start">
        @csrf
        @method('PUT')

        <x-input.text
            label="Course Title"
            name="course_title"
            :value="old('course_title', $course->course_title)"
        />

        <x-input.text
            label="Course Name"
            name="course_name"
            :value="old('course_name', $course->course_name)"
        />

        <x-input.select
            label="Course Type"
            name="course_type"
            :options="[
                'major' => 'Major',
                'minor' => 'Minor',
                'pe' => 'PE',
                'nstp' => 'NSTP',
                'other' => 'Other'
            ]"
            default="minor"
            :value="old('course_type', $course->course_type)"
        />

        <x-input.number
            label="Class Hours"
            name="class_hours"
            :default="1"
            :min="1"
            :max="9"
            :step="1"
            :value="old('class_hours', $course->class_hours)"
        />

        <div class="flex flex-row gap-4">

            <x-input.number
                label="Total Lecture Class Days per Week"
                name="total_lecture_class_days"
                :default="0"
                :min="0"
                :max="6"
                :step="1"
                :value="old('total_lecture_class_days', $course->total_lecture_class_days)"
            />

            <x-input.number
                label="Total Laboratory Class Days per Week"
                name="total_laboratory_class_days"
                :default="0"
                :min="0"
                :max="6"
                :step="1"
                :value="old('total_laboratory_class_days', $course->total_laboratory_class_days)"
            />

        </div>

        @if($errors->has('total_days'))
            <div class="!text-red-500">{{$errors->first('total_days')}}</div>
        @endif

        <x-input.number
            label="Number of Units"
            name="unit_load"
            :default="0.0"
            :min="0.0"
            :max="10.0"
            :step="0.1"
            :value="old('unit_load', $course->unit_load)"
        />

        <x-input.select
            label="Course Duration"
            name="duration_type"
            :options="[
                'semestral' => 'Semestral',
                'term' => 'Term'
            ]"
            default="term"
            :value="old('duration_type', $course->duration_type)"
        />




        <button type="submit">Confirm Changes</button>
        <a href="{{route('courses.index')}}">Back</a>
    </form>
@endsection
