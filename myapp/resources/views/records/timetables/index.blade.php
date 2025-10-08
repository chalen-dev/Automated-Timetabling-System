@extends('app')

@section('title', 'Timetables')

@section('content')
    <div class="flex flex-col h-[calc(100vh-55px)] w-full">
        <h1 class="text-16px text-[#ffffff] font-semibold space-1px p-3 ml-2">Timetables</h1>


        <ul class="flex flex-wrap flex-row gap-7">
            <li>
                <a class="flex justify-center items-center flex-col h-50 w-75 flex-wrap p-3 bg-white shadow-2xl rounded-2xl hover:-translate-y-[6px] hover:shadow-[0_8px_18px_rgba(0,0,0,0.08)] transition-all duration-300 ease-in-out"
                   href="{{route('timetables.create')}}">
                    <svg class=" w-[36px] h-[36px] bg-[#f0f0f5] p-[6px] rounded-[10px]">
                        <path
                            d="M12 5v14M5 12h14"
                            stroke="#555"
                            stroke-width="1.8"
                            stroke-linecap="round"
                        />
                    </svg>
                    <span>Create New Timetable</span>
                </a>
            </li>

            @foreach($timetables as $timetable)
                <li class="flex flex-col justify-between h-50 w-75 p-5 bg-white shadow-2xl rounded-2xl hover:-translate-y-[6px] hover:shadow-[0_8px_18px_rgba(0,0,0,0.08)] transition-all duration-300 ease-in-out">
                    <a href="{{route('timetables.timetable-editing-pane.index', $timetable)}}">
                        <div class="flex flex-col items-center pt-3 pb-3">
                            <p class="font-bold">{{$timetable->timetable_name}}</p>
                            <p>{{$timetable->semester}} semester ({{$timetable->academic_year}})</p>
                        </div>
                    </a>
                    <div class="flex flex-col justify-evenly">
                        <div class="flex flex-row justify-evenly">
                            <a class="flex flex-col items-center justify-center pt-[5px] pb-[5px] pl-[10px] pr-[10px] hover:bg-[#cecece] hover:rounded-[10px]" href="{{route('timetables.show', $timetable)}}">
                                <i class="bi-card-list"></i>
                                <span>Info</span>
                            </a>
                            <a class='flex flex-col items-center justify-center pt-[5px] pb-[5px] pl-[10px] pr-[10px] hover:bg-[#cecece] hover:rounded-[10px]' href="{{route('timetables.edit', $timetable)}}">
                                <i class="bi bi-pencil-square"></i>
                                <span>Edit</span>
                            </a>
                            <a class='flex flex-col items-center justify-center cursor-pointer p-[5px] hover:bg-red-100 hover:rounded-[10px]'>
                                <x-buttons.delete action="timetables.destroy" :params="$timetable" item_name="timetable" btnType="icon"/>
                                <x-buttons.delete action="timetables.destroy" :params="$timetable" item_name="timetable" btnType="normal"/>
                            </a>
                        </div>
                        <div class="bg-red-500">
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>

@endsection
