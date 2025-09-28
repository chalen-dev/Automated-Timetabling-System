@extends('app')

@section('title', 'Create Course')


@section('content')
    <form action="{{route('admin.courses.store')}}" method="POST" class="flex flex-col gap-4 justify-start">
        @csrf

        <x-input.text
            label="Course Title"
            name="course_title"
        />

        <x-input.text
            label="Course Name"
            name="course_name"
        />

        <x-input.select
            label="Course Type"
            name="course_type"
            :options="$courseTypeOptions"
            default=""
        />

        <x-input.number
            label="Class Hours"
            name="class_hours"
            :default="1"
            :min="1"
            :max="9"
            :step="1"
        />

        <div class="flex flex-row gap-4">

            <x-input.number
                label="Total Lecture Class Days per Week"
                name="total_lecture_class_days"
                :default="0"
                :min="0"
                :max="6"
                :step="1"
            />

            <x-input.number
                label="Total Laboratory Class Days per Week"
                name="total_laboratory_class_days"
                :default="0"
                :min="0"
                :max="6"
                :step="1"
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
        />

        <x-input.select
            label="Course Duration"
            name="duration_type"
            :options="$durationTypeOptions"
            default=""
        />


        <button type="submit">Create</button>


    </form>
    <a href="{{route('admin.courses.index')}}">Back</a>

@endsection
