@extends('app')

@section('title', 'Create Room Exclusive Day')

@section('content')
    <form action="{{route('rooms.room-exclusive-days.store', $room)}}" method="POST">
        @csrf

        <h3>Assign Exclusive Days to {{$room->room_name}}</h3>
        @if(isset($message))
            <div class="!text-red-500">{{$message}}</div>
        @endif
        <table class="w-full">
            <thead>
                <tr>
                    <td>Day</td>
                    <td></td>
                </tr>
            </thead>
            <tbody>
            @foreach($unassignedDays as $dayKey => $dayLabel)
                <tr>
                    <td>{{ $dayLabel }}</td>
                    <td>
                        <input type="checkbox" name="exclusive_days[]" value="{{ $dayKey }}">
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <button type="submit">Confirm</button>
        <a href="{{route('rooms.room-exclusive-days.index', $room)}}">Back</a>
    </form>
@endsection
