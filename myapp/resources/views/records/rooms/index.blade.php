@extends('app')

@section('title', 'Rooms')

@section('content')
    <div class="flex justify-between">
        <h1>Rooms</h1>
        <a href="{{route('rooms.create')}}">Create</a>
    </div>

    <table class="w-full">
        <thead>
            <tr>
                <td>Room Name</td>
                <td>Room Type</td>
                <td>Room Capacity</td>
                <td>Exclusive Days</td>
                <td></td>
            </tr>
        </thead>
        <tbody>
            @foreach($rooms as $room)
            <tr>
                <td>{{$room->room_name}}</td>
                <td>{{$room->room_type}}</td>
                <td>{{$room->room_capacity ?? 'none'}}</td>
                <td>
                    {{
                        $room->roomExclusiveDays->isNotEmpty()
                        ?
                        $room->roomExclusiveDays
                            ->pluck('exclusive_day')
                            ->map(fn($day) => ucfirst($day)) //Makes each day of the Week have uppercase first letter
                            ->implode(', ')
                        :
                        'N/A'
                    }}
                </td>
                <td class="whitespace-nowrap px-2">
                    <div class="flex flex-row gap-2 justify-end">
                        <a href="{{route('rooms.room-exclusive-days.index', $room)}}">
                            Set Specific Days
                        </a>
                        <a class = 'flex items-center justify-center w-10 h-10' href="{{route('rooms.show', $room)}}">
                            <i class="bi-card-list"></i>
                        </a>
                        <a class = 'flex items-center justify-center w-10 h-10' href="{{route('rooms.edit', $room)}}">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <x-buttons.delete action="rooms.destroy" :params='$room' item_name='room' btnType='icon'/>
                    </div>
                </td>



            </tr>

            @endforeach
        </tbody>
    </table>
@endsection
