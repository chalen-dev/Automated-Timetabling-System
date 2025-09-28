@extends('app')

@section('title', 'Professors')

@section('content')
    <div class="flex justify-between">
        <h1>List of Professors</h1>
        <a href="{{route('records.professors.create')}}">Create</a>
    </div>

    <table class="w-full">
        <thead>
            <tr>
                <td>First Name</td>
                <td>Last Name</td>
                <td>Professor Type</td>
                <td>Max Unit Load</td>
                <td></td>
            </tr>
        </thead>
        <tbody>
            @foreach($professors as $professor)
            <tr>
                <td>{{$professor->first_name}}</td>
                <td>{{$professor->last_name}}</td>
                <td>{{$professor->professor_type}}</td>
                <td>{{$professor->max_unit_load}}</td>
                <td class = "flex flex-row gap-4 justify-end">
                    <a class = 'flex items-center justify-center w-10 h-10' href="{{route('records.professors.show', $professor)}}">
                        <i class="bi-card-list"></i>
                    </a>
                    <a class = 'flex items-center justify-center w-10 h-10' href="{{route('records.professors.edit', $professor)}}">
                        <i class="bi bi-pencil-square"></i>
                    </a>

                    <x-buttons.delete action="records.professors.destroy" :model='$professor' item_name='professor' btnType='icon'/>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

@endsection
