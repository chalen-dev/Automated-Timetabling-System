@extends('pages.app')

@section('title', 'Professors')

@section('content')
    <h1>Professors</h1>
    <a href="{{route('admin.professors.create')}}">Create</a>
    <ul>
        @foreach($professors as $professor)
            <li class="flex gap-10">
                <p>{{$professor->first_name}}</p>
                <p>{{$professor->last_name}}</p>
                <p>{{$professor->professor_type}}</p>
                <p>{{$professor->max_unit_load}}</p>
                <a href='{{route('admin.professors.show', $professor)}}'>View</a>
                <a href='{{route('admin.professors.edit', $professor)}}'>Edit</a>
                <x-buttons.delete action="admin.professors.destroy" :model="$professor" item_name="professor" />
            </li>
        @endforeach
    </ul>
@endsection
