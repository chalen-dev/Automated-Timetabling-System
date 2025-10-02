@extends('app')

@section('title', 'Create Course')


@section('content')
    <div class="flex flex-col gap-10 justify-center items-center pl-20 pr-20">
        <h1>Create Course</h1>
        <form class="flex flex-col gap-4 justify-start" action="{{route('courses.store')}}" method="POST" >
            @csrf

            <div class="flex justify-center gap-7">
                <div class="flex flex-col justify-center items-stretch">
                    <x-input.text
                        label="Course Title"
                        name="course_title"
                    />

                    <x-input.text
                        label="Course Name"
                        name="course_name"
                    />
                </div>

                <div class="flex flex-col justify-center gap-5">
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
                </div>

                <div class="flex flex-col justify-center gap-5">
                    <div class="flex flex-row gap-5">
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
                </div>
            </div>
            <div class="flex justify-center items-center gap-20">
                <x-input.number
                    label="Number of Units"
                    name="unit_load"
                    :default="0.0"
                    :min="0.0"
                    :max="10.0"
                    :step="0.1"
                />

                <x-input.radio-group
                    label="Course Duration"
                    name="duration_type"
                    :options="$durationTypeOptions"
                    default=""
                />
            </div>

            <div class="flex justify-center items-center gap-100">
                <a href="{{route('courses.index')}}">Back</a>
                <button type="submit">Create</button>
            </div>

        </form>
    </div>
@endsection
