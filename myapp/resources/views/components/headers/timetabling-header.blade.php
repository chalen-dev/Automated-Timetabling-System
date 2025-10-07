<nav class="relative mb-4 z-2 w-full flex justify-center items-center content-center bg-[#5e0b0b] h-18 shadow-2xl">
    <div class="h-18 w-full flex items-center justify-between pl-9 pr-9 pt-6 pb-6 bg-white rounded-2xl">
        <button class="flex items-center justify-center w-15 h-15">
            <a href="{{route('timetables.index')}}">
                <i class="bi bi-arrow-left text-2xl font-bold"></i>
            </a>
        </button>
        <div class="flex w-full flex-col text-center box-content">
            <h1 class="font-bold text-[18px]">Timetable View</h1>
            <p>{{$timetable->timetable_name}} {{$timetable->semester}} semester ({{$timetable->academic_year}})</p>
        </div>
        <div>
            <img src="{{ asset('umtc_logo.png') }}" class="w-15 h-15" alt="UMTC Logo">
        </div>
    </div>

</nav>
