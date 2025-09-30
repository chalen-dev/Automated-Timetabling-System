@extends('app')

@section('title', 'Specializations')

@section('content')
    <h1>{{$professor->last_name}}, {{$professor->first_name}}'s Specializations</h1>
    <a href="{{route('records.specializations.create', $professor)}}">Add</a>
    <a href="{{route('records.professors.index')}}">Back</a>
    <table>
        <thead>
            <tr>
                <td>Course Title</td>
                <td>Course Name</td>
                <td></td>
            </tr>
        </thead>
        <tbody>
            <tr>
                @foreach($specializations as $specialization)
                    <td>{{$specialization->course->course_title}}</td>
                    <td>{{$specialization->course->course_name}}</td>
                    <td>
                        <x-buttons.delete action="records.specializations.destroy" :model='$specialization' item_name='specialization' btnType='icon'/>
                    </td>
                @endforeach
            </tr>
        </tbody>
    </table>
@endsection
