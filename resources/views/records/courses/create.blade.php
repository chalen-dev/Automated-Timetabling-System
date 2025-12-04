@extends('app')

@section('title', 'Create Course')

@section('content')
    <div class="flex flex-col pt-[40px] pb-[40px] pr-[50px] pl-[50px] gap-[50px] justify-center items-center bg-white rounded-2xl shadow-2xl">
        <h1 class="font-bold text-[18px]">Create Course</h1>
        <form class="flex flex-col" action="{{route('courses.store')}}" method="POST" >
            @csrf

            <div class="flex justify-center align-top gap-7">
                <div class="flex flex-col justify-between items-stretch">
                    <livewire:input.text
                        label="Course Title"
                        name="course_title"
                        isRequired
                    />

                    <livewire:input.text
                        label="Course Name"
                        name="course_name"
                        isRequired
                    />
                </div>

                <div class="flex flex-col justify-between gap-5">
                    <livewire:input.select
                        label="Course Type"
                        name="course_type"
                        :options="$courseTypeOptions"
                        default=""
                        isRequired
                    />

                    <livewire:input.number
                        label="Class Hours"
                        name="class_hours"
                        :default="1"
                        :min="1"
                        :max="9"
                        :step="1"
                        isRequired
                    />
                </div>

                <div class="flex flex-col justify-between gap-5">
                        <livewire:input.number
                            label="Total Lecture Class Days per Week"
                            name="total_lecture_class_days"
                            :default="0"
                            :min="0"
                            :max="6"
                            :step="1"
                            isRequired
                        />

                        <livewire:input.number
                            label="Total Laboratory Class Days per Week"
                            name="total_laboratory_class_days"
                            :default="0"
                            :min="0"
                            :max="6"
                            :step="1"
                            isRequired
                        />
                    @if($errors->has('total_days'))
                        <div class="!text-red-500">{{$errors->first('total_days')}}</div>
                    @endif
                </div>
                <div class="flex justify-between flex-col items-center gap-[20px]">
                    <livewire:input.number
                        label="Number of Units"
                        name="unit_load"
                        :default="0.0"
                        :min="0.0"
                        :max="10.0"
                        :step="0.1"
                        isRequired
                    />

                    <livewire:input.radio-group
                        label="Course Duration"
                        name="duration_type"
                        :options="$durationTypeOptions"
                        default=""
                        isRequired
                    />
                </div>
            </div>
            <div class="flex flex-row w-full justify-between items-center mt-[100px]">
                <livewire:buttons.back
                    :route="'courses.index'"
                />
                <livewire:buttons.create submit/>
            </div>
        </form>

    </div>
@endsection
