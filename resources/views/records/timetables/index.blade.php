@extends('app')

@section('title', 'Timetables')

@section('content')
    <div class="flex flex-col h-[calc(100vh-55px)] w-full">
        <h1 class="text-16px text-[#ffffff] font-semibold space-1px p-3 ml-2">
            Timetables
        </h1>

        {{-- CREATE NEW TIMETABLE --}}
        <ul class="flex flex-wrap flex-row gap-7 mb-8">
            <li>
                <a class="flex justify-center items-center flex-col h-50 w-75 flex-wrap p-3 bg-white shadow-2xl rounded-2xl hover:-translate-y-[6px] hover:shadow-[0_8px_18px_rgba(0,0,0,0.08)] transition-all duration-300 ease-in-out"
                   href="{{ route('timetables.create') }}">
                    <svg class="w-[36px] h-[36px] bg-[#f0f0f5] p-[6px] rounded-[10px]">
                        <path d="M12 5v14M5 12h14"
                              stroke="#555"
                              stroke-width="1.8"
                              stroke-linecap="round"/>
                    </svg>
                    <span>Create New Timetable</span>
                </a>
            </li>
        </ul>

        {{-- ================= NON-ADMIN ================= --}}
        @if(auth()->user()->role !== 'admin')

            {{-- PRIVATE --}}
            @if($private->isNotEmpty())
                <h2 class="text-[#ffffff] font-semibold mb-2 ml-2">My Timetables</h2>
                <ul class="flex flex-wrap flex-row gap-7 mb-8">
                    @foreach($private as $timetable)
                        {{-- CARD --}}
                        <li class="flex flex-col justify-between h-50 w-75 p-5 bg-white shadow-2xl rounded-2xl hover:-translate-y-[6px] hover:shadow-[0_8px_18px_rgba(0,0,0,0.08)] transition-all duration-300 ease-in-out">
                            <a href="{{ route('timetables.timetable-editing-pane.index', $timetable) }}">
                                <div class="flex flex-col items-center pt-3 pb-3">
                                    <p class="font-bold">{{ $timetable->timetable_name }}</p>
                                    <p>{{ $timetable->semester }} semester ({{ $timetable->academic_year }})</p>
                                </div>
                            </a>
                            <div class="flex flex-col justify-evenly">
                                <div class="flex flex-row justify-evenly">
                                    <a class="flex flex-col items-center justify-center pt-[5px] pb-[5px] pl-[10px] pr-[10px] hover:bg-[#cecece] hover:rounded-[10px]"
                                       href="{{ route('timetables.show', $timetable) }}">
                                        <i class="bi-card-list"></i>
                                        <span>Info</span>
                                    </a>
                                    <a class="flex flex-col items-center justify-center pt-[5px] pb-[5px] pl-[10px] pr-[10px] hover:bg-[#cecece] hover:rounded-[10px]"
                                       href="{{ route('timetables.edit', $timetable) }}">
                                        <i class="bi bi-pencil-square"></i>
                                        <span>Edit</span>
                                    </a>
                                    <a class="flex flex-col items-center justify-center pt-[5px] pb-[5px] pl-[10px] pr-[10px] hover:bg-[#cecece] hover:rounded-[10px]"
                                       href="{{ route('timetables.copy', $timetable) }}">
                                        <i class="bi bi-files"></i>
                                        <span>Copy</span>
                                    </a>
                                    <livewire:buttons.delete
                                        action="timetables.destroy"
                                        :params="$timetable"
                                        item_name="timetable"
                                        btnType="iconWithText"
                                    />
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif

            {{-- SHARED --}}
            @if($shared->isNotEmpty())
                <h2 class="text-[#ffffff] font-semibold mb-2 ml-2">Shared Timetables</h2>
                <ul class="flex flex-wrap flex-row gap-7 mb-8">
                    @foreach($shared as $timetable)
                        {{-- SAME CARD --}}
                        <li class="flex flex-col justify-between h-50 w-75 p-5 bg-white shadow-2xl rounded-2xl hover:-translate-y-[6px] hover:shadow-[0_8px_18px_rgba(0,0,0,0.08)] transition-all duration-300 ease-in-out">
                            <a href="{{ route('timetables.timetable-editing-pane.index', $timetable) }}">
                                <div class="flex flex-col items-center pt-3 pb-3">
                                    <p class="font-bold">{{ $timetable->timetable_name }}</p>
                                    <p>{{ $timetable->semester }} semester ({{ $timetable->academic_year }})</p>
                                </div>
                            </a>
                            <div class="flex flex-col justify-evenly">
                                <div class="flex flex-row justify-evenly">
                                    <a class="flex flex-col items-center justify-center pt-[5px] pb-[5px] pl-[10px] pr-[10px] hover:bg-[#cecece] hover:rounded-[10px]"
                                       href="{{ route('timetables.show', $timetable) }}">
                                        <i class="bi-card-list"></i>
                                        <span>Info</span>
                                    </a>
                                    <a class="flex flex-col items-center justify-center pt-[5px] pb-[5px] pl-[10px] pr-[10px] hover:bg-[#cecece] hover:rounded-[10px]"
                                       href="{{ route('timetables.edit', $timetable) }}">
                                        <i class="bi bi-pencil-square"></i>
                                        <span>Edit</span>
                                    </a>
                                    <a class="flex flex-col items-center justify-center pt-[5px] pb-[5px] pl-[10px] pr-[10px] hover:bg-[#cecece] hover:rounded-[10px]"
                                       href="{{ route('timetables.copy', $timetable) }}">
                                        <i class="bi bi-files"></i>
                                        <span>Copy</span>
                                    </a>
                                    <livewire:buttons.delete
                                        action="timetables.destroy"
                                        :params="$timetable"
                                        item_name="timetable"
                                        btnType="iconWithText"
                                    />
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif

            {{-- PUBLIC --}}
            @if($public->isNotEmpty())
                <h2 class="text-[#ffffff] font-semibold mb-2 ml-2">Public Timetables</h2>
                <ul class="flex flex-wrap flex-row gap-7 mb-8">
                    @foreach($public as $timetable)
                        {{-- SAME CARD --}}
                        <li class="flex flex-col justify-between h-50 w-75 p-5 bg-white shadow-2xl rounded-2xl hover:-translate-y-[6px] hover:shadow-[0_8px_18px_rgba(0,0,0,0.08)] transition-all duration-300 ease-in-out">
                            <a href="{{ route('timetables.timetable-editing-pane.index', $timetable) }}">
                                <div class="flex flex-col items-center pt-3 pb-3">
                                    <p class="font-bold">{{ $timetable->timetable_name }}</p>
                                    <p>{{ $timetable->semester }} semester ({{ $timetable->academic_year }})</p>
                                </div>
                            </a>
                            <div class="flex flex-col justify-evenly">
                                <div class="flex flex-row justify-evenly">
                                    <a class="flex flex-col items-center justify-center pt-[5px] pb-[5px] pl-[10px] pr-[10px] hover:bg-[#cecece] hover:rounded-[10px]"
                                       href="{{ route('timetables.show', $timetable) }}">
                                        <i class="bi-card-list"></i>
                                        <span>Info</span>
                                    </a>
                                    <a class="flex flex-col items-center justify-center pt-[5px] pb-[5px] pl-[10px] pr-[10px] hover:bg-[#cecece] hover:rounded-[10px]"
                                       href="{{ route('timetables.edit', $timetable) }}">
                                        <i class="bi bi-pencil-square"></i>
                                        <span>Edit</span>
                                    </a>
                                    <a class="flex flex-col items-center justify-center pt-[5px] pb-[5px] pl-[10px] pr-[10px] hover:bg-[#cecece] hover:rounded-[10px]"
                                       href="{{ route('timetables.copy', $timetable) }}">
                                        <i class="bi bi-files"></i>
                                        <span>Copy</span>
                                    </a>
                                    <livewire:buttons.delete
                                        action="timetables.destroy"
                                        :params="$timetable"
                                        item_name="timetable"
                                        btnType="iconWithText"
                                    />
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif

            {{-- ================= ADMIN ================= --}}
        @else

            {{-- OWNED --}}
            @if($owned->isNotEmpty())
                <h2 class="text-[#ffffff] font-semibold mb-2 ml-2">My Timetables</h2>
                <ul class="flex flex-wrap flex-row gap-7 mb-8">
                    @foreach($owned as $timetable)
                        {{-- SAME CARD --}}
                        <li class="flex flex-col justify-between h-50 w-75 p-5 bg-white shadow-2xl rounded-2xl hover:-translate-y-[6px] hover:shadow-[0_8px_18px_rgba(0,0,0,0.08)] transition-all duration-300 ease-in-out">
                            <a href="{{ route('timetables.timetable-editing-pane.index', $timetable) }}">
                                <div class="flex flex-col items-center pt-3 pb-3">
                                    <p class="font-bold">{{ $timetable->timetable_name }}</p>
                                    <p>{{ $timetable->semester }} semester ({{ $timetable->academic_year }})</p>
                                </div>
                            </a>
                            <div class="flex flex-col justify-evenly">
                                <div class="flex flex-row justify-evenly">
                                    <a class="flex flex-col items-center justify-center pt-[5px] pb-[5px] pl-[10px] pr-[10px] hover:bg-[#cecece] hover:rounded-[10px]"
                                       href="{{ route('timetables.show', $timetable) }}">
                                        <i class="bi-card-list"></i>
                                        <span>Info</span>
                                    </a>
                                    <a class="flex flex-col items-center justify-center pt-[5px] pb-[5px] pl-[10px] pr-[10px] hover:bg-[#cecece] hover:rounded-[10px]"
                                       href="{{ route('timetables.edit', $timetable) }}">
                                        <i class="bi bi-pencil-square"></i>
                                        <span>Edit</span>
                                    </a>
                                    <a class="flex flex-col items-center justify-center pt-[5px] pb-[5px] pl-[10px] pr-[10px] hover:bg-[#cecece] hover:rounded-[10px]"
                                       href="{{ route('timetables.copy', $timetable) }}">
                                        <i class="bi bi-files"></i>
                                        <span>Copy</span>
                                    </a>
                                    <livewire:buttons.delete
                                        action="timetables.destroy"
                                        :params="$timetable"
                                        item_name="timetable"
                                        btnType="iconWithText"
                                    />
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif

            {{-- PUBLIC --}}
            @if($public->isNotEmpty())
                <h2 class="text-[#ffffff] font-semibold mb-2 ml-2">Public Timetables</h2>
                <ul class="flex flex-wrap flex-row gap-7 mb-8">
                    @foreach($public as $timetable)
                        {{-- SAME CARD --}}
                        <li class="flex flex-col justify-between h-50 w-75 p-5 bg-white shadow-2xl rounded-2xl hover:-translate-y-[6px] hover:shadow-[0_8px_18px_rgba(0,0,0,0.08)] transition-all duration-300 ease-in-out">
                            <a href="{{ route('timetables.timetable-editing-pane.index', $timetable) }}">
                                <div class="flex flex-col items-center pt-3 pb-3">
                                    <p class="font-bold">{{ $timetable->timetable_name }}</p>
                                    <p>{{ $timetable->semester }} semester ({{ $timetable->academic_year }})</p>
                                </div>
                            </a>
                            <div class="flex flex-col justify-evenly">
                                <div class="flex flex-row justify-evenly">
                                    <a class="flex flex-col items-center justify-center pt-[5px] pb-[5px] pl-[10px] pr-[10px] hover:bg-[#cecece] hover:rounded-[10px]"
                                       href="{{ route('timetables.show', $timetable) }}">
                                        <i class="bi-card-list"></i>
                                        <span>Info</span>
                                    </a>
                                    <a class="flex flex-col items-center justify-center pt-[5px] pb-[5px] pl-[10px] pr-[10px] hover:bg-[#cecece] hover:rounded-[10px]"
                                       href="{{ route('timetables.edit', $timetable) }}">
                                        <i class="bi bi-pencil-square"></i>
                                        <span>Edit</span>
                                    </a>
                                    <a class="flex flex-col items-center justify-center pt-[5px] pb-[5px] pl-[10px] pr-[10px] hover:bg-[#cecece] hover:rounded-[10px]"
                                       href="{{ route('timetables.copy', $timetable) }}">
                                        <i class="bi bi-files"></i>
                                        <span>Copy</span>
                                    </a>
                                    <livewire:buttons.delete
                                        action="timetables.destroy"
                                        :params="$timetable"
                                        item_name="timetable"
                                        btnType="iconWithText"
                                    />
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif

            {{-- OTHER USERS --}}
            @if($others->isNotEmpty())
                <h2 class="text-[#ffffff] font-semibold mb-2 ml-2">Other Users’ Timetables</h2>
                <ul class="flex flex-wrap flex-row gap-7 mb-8">
                    @foreach($others as $timetable)
                        {{-- SAME CARD --}}
                        <li class="flex flex-col justify-between h-50 w-75 p-5 bg-white shadow-2xl rounded-2xl hover:-translate-y-[6px] hover:shadow-[0_8px_18px_rgba(0,0,0,0.08)] transition-all duration-300 ease-in-out">
                            <a href="{{ route('timetables.timetable-editing-pane.index', $timetable) }}">
                                <div class="flex flex-col items-center pt-3 pb-3">
                                    <p class="font-bold">{{ $timetable->timetable_name }}</p>
                                    <p>{{ $timetable->semester }} semester ({{ $timetable->academic_year }})</p>
                                </div>
                            </a>
                            <div class="flex flex-col justify-evenly">
                                <div class="flex flex-row justify-evenly">
                                    <a class="flex flex-col items-center justify-center pt-[5px] pb-[5px] pl-[10px] pr-[10px] hover:bg-[#cecece] hover:rounded-[10px]"
                                       href="{{ route('timetables.show', $timetable) }}">
                                        <i class="bi-card-list"></i>
                                        <span>Info</span>
                                    </a>
                                    <a class="flex flex-col items-center justify-center pt-[5px] pb-[5px] pl-[10px] pr-[10px] hover:bg-[#cecece] hover:rounded-[10px]"
                                       href="{{ route('timetables.edit', $timetable) }}">
                                        <i class="bi bi-pencil-square"></i>
                                        <span>Edit</span>
                                    </a>
                                    <a class="flex flex-col items-center justify-center pt-[5px] pb-[5px] pl-[10px] pr-[10px] hover:bg-[#cecece] hover:rounded-[10px]"
                                       href="{{ route('timetables.copy', $timetable) }}">
                                        <i class="bi bi-files"></i>
                                        <span>Copy</span>
                                    </a>
                                    <livewire:buttons.delete
                                        action="timetables.destroy"
                                        :params="$timetable"
                                        item_name="timetable"
                                        btnType="iconWithText"
                                    />
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif

        @endif
    </div>
@endsection
