@extends('app')

@section('title', 'Professor Details')

@section('content')
    <div class="flex flex-col gap-[20px] pt-[40px] pb-[40px] pr-[50px] pl-[50px] justify-center items-center bg-white rounded-2xl shadow-2xl w-full max-w-[600px]">
        <h1 class="font-bold text-[18px]">Professor Info</h1>

        <div class="flex flex-row gap-[15px] w-full">
            <div class="flex flex-col gap-[8px] w-[150px]">
                <p>First Name</p>
                <p>Last Name</p>
                <p>Professor Type</p>
                <p>Max Unit Load</p>
                <p>Professor Age</p>
                <p>Position</p>
            </div>
            <div class="flex flex-col gap-[8px] w-[10px]">
                <p>:</p>
                <p>:</p>
                <p>:</p>
                <p>:</p>
                <p>:</p>
                <p>:</p>
            </div>
            <div class="flex flex-col gap-[8px] flex-1">
                <p>{{$professor->first_name}}</p>
                <p>{{$professor->last_name}}</p>
                <p>{{$professor->professor_type}}</p>
                <p>{{$professor->max_unit_load}}</p>
                <p>{{$professor->professor_age ?? 'N/A'}}</p>
                <p>{{$professor->position ?? 'N/A'}}</p>
            </div>
        </div>

        <a href="{{route('professors.index')}}" class="flex flex-row w-full justify-center">
            <button class="pt-[10px] pb-[10px] pl-[20px] pr-[20px] rounded-[12px] text-[16px] bg-[#aaa] text-[#fff] cursor-pointer font-[600] hover:bg-[#828282]">
                <span>Back</span>
            </button>
        </a>
    </div>
@endsection
