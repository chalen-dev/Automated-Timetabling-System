@extends('app')

@section('title', 'Edit Course')

@section('content')
    <div class="flex flex-col pt-[40px] pb-[40px] pr-[50px] pl-[50px] gap-[50px] justify-center items-center bg-white rounded-2xl shadow-2xl">
        <h1 class="font-bold text-[18px]">Edit Course</h1>
        <form class="flex flex-col gap-10 w-full" action="{{ route('courses.update', $course) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="flex justify-center gap-7">
                <div class="flex flex-col justify-between items-stretch gap-5">
                    <livewire:input.text
                        label="Course Title"
                        name="course_title"
                        :value="old('course_title', $course->course_title)"
                    />

                    <livewire:input.text
                        label="Course Name"
                        name="course_name"
                        :value="old('course_name', $course->course_name)"
                    />
                </div>

                <div class="flex flex-col justify-between gap-5">
                    <x-input.select
                        label="Course Type"
                        name="course_type"
                        :options="$courseTypeOptions"
                        default=""
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
                </div>
                    <div class="flex flex-col justify-between gap-5">
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
                    @if($errors->has('total_days'))
                        <div class="!text-red-500">{{ $errors->first('total_days') }}</div>
                    @endif
                </div>

                <div class="flex justify-between flex-col items-center gap-[20px]">
                    <x-input.number
                        label="Number of Units"
                        name="unit_load"
                        :default="0.0"
                        :min="0.0"
                        :max="10.0"
                        :step="0.1"
                        :value="old('unit_load', $course->unit_load)"
                    />

                    <x-input.radio-group
                        label="Course Duration"
                        name="duration_type"
                        :options="$durationTypeOptions"
                        default=""
                        :value="old('duration_type', $course->duration_type)"
                    />
                </div>
            </div>

            <!-- Added gap above buttons for breathing room -->
            <div class="flex flex-row w-full justify-between items-center mt-[40px]">
                <a href="{{ route('courses.index') }}">
                    <button type="button" class="pt-[10px] pb-[10px] pl-[20px] pr-[20px] rounded-[12px] text-[16px] bg-[#aaa] text-[#fff] cursor-pointer font-[600]">
                        <span>Back</span>
                    </button>
                </a>

                <button type="submit" class="pt-[10px] pb-[10px] pl-[20px] pr-[20px] rounded-[12px] text-[16px] bg-[#5e0b0b] text-[#fff] cursor-pointer font-[600]">
                    <span>Confirm Changes</span>
                </button>
            </div>
        </form>
    </div>
@endsection
