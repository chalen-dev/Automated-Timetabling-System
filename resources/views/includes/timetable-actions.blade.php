@php
    /** @var \App\Models\Records\Timetable $rowTimetable */

    $user = auth()->user();
    $isOwner = $user && $user->id === $rowTimetable->user_id;
    $isAdmin = $user && $user->role === 'admin';
@endphp

<div class="flex flex-row justify-evenly gap-4">

    <a class="flex flex-col items-center p-[5px] hover:bg-[#cecece] hover:rounded-[10px]"
       href="{{ route('timetables.show', $rowTimetable) }}">
        <i class="bi-card-list"></i>
        <span>Info</span>
    </a>

    <a class="flex flex-col items-center p-[5px] hover:bg-[#cecece] hover:rounded-[10px]"
       href="{{ route('timetables.copy', $rowTimetable) }}">
        <i class="bi bi-files"></i>
        <span>Copy</span>
    </a>

    @if ($isOwner || $isAdmin)
        <a class="flex flex-col items-center p-[5px] hover:bg-[#cecece] hover:rounded-[10px]"
           href="{{ route('timetables.edit', $rowTimetable) }}">
            <i class="bi bi-pencil-square"></i>
            <span>Edit</span>
        </a>

        <livewire:buttons.delete
            action="timetables.destroy"
            :params="$rowTimetable"
            item_name="timetable"
            btnType="iconWithText"/>
    @endif

</div>
