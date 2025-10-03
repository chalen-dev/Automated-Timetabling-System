@props([
    'timetable' => \App\Models\Timetable::class
])

<nav>
    <ul>
        <li>
            <a href="{{route('timetables.timetable-editing-pane.index', $timetable)}}">
                Edit Timetable
            </a>
        </li>
        <li>
            <a href="{{route('timetables.session-groups.index', $timetable)}}">
                Class Sessions
            </a>
        </li>
        <li>
            <a href="">
                Professors
            </a>
        </li>
        <li>
            <a href="">
                Rooms
            </a>
        </li>
    </ul>
    <div>
        <a href=""></a>
    </div>
</nav>
