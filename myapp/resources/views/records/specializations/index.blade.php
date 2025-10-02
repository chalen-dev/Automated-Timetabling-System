@extends('app')

@section('title', 'Specializations')

@section('content')
    <h1>{{$professor->last_name}}, {{$professor->first_name}}'s Specializations</h1>
    <a href="{{route('records.professors.specializations.create', $professor)}}">Add</a>
    <a href="{{route('records.professors.index')}}">Back</a>
    <table class="w-full">
        <thead>
            <tr>
                <td>Course Title</td>
                <td>Course Name</td>
                <td></td>
            </tr>
        </thead>
        <tbody>
            @foreach($specializations as $specialization)
            <tr>
                <td>{{$specialization->course->course_title}}</td>
                <td>{{$specialization->course->course_name}}</td>
                <td>
                    <x-buttons.delete
                        action="records.professors.specializations.destroy"
                        :params='[$professor, $specialization]'
                        item_name='specialization'
                        btnType='icon'
                    />
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endsection
