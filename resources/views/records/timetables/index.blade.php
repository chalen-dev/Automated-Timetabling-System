@extends('app')

@section('title', 'Timetables')

@section('content')
    <div class="flex flex-col h-[calc(100vh-55px)] w-full">
        <h1 class="text-16px text-[#ffffff] font-semibold space-1px p-3 ml-2">
            Timetables
        </h1>

        {{-- CREATE NEW TIMETABLE --}}
        <ul class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-10 mb-10">

            <li>
                <a class="flex flex-col items-center justify-center
                          p-6 min-h-[230px]
                          bg-white shadow-2xl rounded-2xl
                          hover:-translate-y-2
                          hover:shadow-[0_12px_24px_rgba(0,0,0,0.12)]
                          transition-all duration-300 ease-in-out"
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
                <ul class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-10 mb-10">

                    @foreach($private as $timetable)
                        <li class="flex flex-col justify-between
                           p-6 min-h-[230px]
                           bg-white shadow-2xl rounded-2xl
                           hover:-translate-y-2
                           hover:shadow-[0_12px_24px_rgba(0,0,0,0.12)]
                           transition-all duration-300 ease-in-out">

                            <a href="{{ route('timetables.timetable-editing-pane.index', $timetable) }}">
                                <div class="flex flex-col items-center pt-3 pb-3">
                                    <p class="font-bold">{{ $timetable->timetable_name }}</p>

                                    <p>
                                        {{ $timetable->semester }} semester ({{ $timetable->academic_year }})
                                    </p>

                                    @if($timetable->user)
                                        <p class="text-sm mt-1
                                        {{ $timetable->user_id === auth()->id()
                                            ? 'text-green-600 font-semibold'
                                            : 'text-gray-500' }}">
                                            by {{ $timetable->user->name }}
                                        </p>
                                    @endif
                                </div>
                            </a>
                            <div class="flex flex-row justify-evenly">
                                @include('includes.timetable-actions', [
                                    'rowTimetable' => $timetable
                                ])
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif

            {{-- SHARED --}}
            @if($shared->isNotEmpty())
                <h2 class="text-[#ffffff] font-semibold mb-2 ml-2">Shared Timetables</h2>
                <ul class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-10 mb-10">

                    @foreach($shared as $timetable)
                        <li class="flex flex-col justify-between
                           p-6 min-h-[230px]
                           bg-white shadow-2xl rounded-2xl
                           hover:-translate-y-2
                           hover:shadow-[0_12px_24px_rgba(0,0,0,0.12)]
                           transition-all duration-300 ease-in-out">

                            <a href="{{ route('timetables.timetable-editing-pane.index', $timetable) }}">
                                <div class="flex flex-col items-center pt-3 pb-3">
                                    <p class="font-bold">{{ $timetable->timetable_name }}</p>

                                    <p>
                                        {{ $timetable->semester }} semester ({{ $timetable->academic_year }})
                                    </p>

                                    @if($timetable->user)
                                        <p class="text-sm mt-1
                                        {{ $timetable->user_id === auth()->id()
                                            ? 'text-green-600 font-semibold'
                                            : 'text-gray-500' }}">
                                            by {{ $timetable->user->name }}
                                        </p>
                                    @endif
                                </div>
                            </a>
                            <div class="flex flex-row justify-evenly">
                                @include('includes.timetable-actions', [
                                    'rowTimetable' => $timetable
                                ])
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif

            {{-- PUBLIC --}}
            @if($public->isNotEmpty())
                <h2 class="text-[#ffffff] font-semibold mb-2 ml-2">Public Timetables</h2>
                <ul class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-10 mb-10">

                    @foreach($public as $timetable)
                        <li class="flex flex-col justify-between
                           p-6 min-h-[230px]
                           bg-white shadow-2xl rounded-2xl
                           hover:-translate-y-2
                           hover:shadow-[0_12px_24px_rgba(0,0,0,0.12)]
                           transition-all duration-300 ease-in-out">

                            <a href="{{ route('timetables.timetable-editing-pane.index', $timetable) }}">
                                <div class="flex flex-col items-center pt-3 pb-3">
                                    <p class="font-bold">{{ $timetable->timetable_name }}</p>

                                    <p>
                                        {{ $timetable->semester }} semester ({{ $timetable->academic_year }})
                                    </p>

                                    @if($timetable->user)
                                        <p class="text-sm mt-1
                                        {{ $timetable->user_id === auth()->id()
                                            ? 'text-green-600 font-semibold'
                                            : 'text-gray-500' }}">
                                            by {{ $timetable->user->name }}
                                        </p>
                                    @endif
                                </div>
                            </a>
                            <div class="flex flex-row justify-evenly">
                                @include('includes.timetable-actions', [
                                    'rowTimetable' => $timetable
                                ])
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
                <ul class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-10 mb-10">

                    @foreach($owned as $timetable)
                        <li class="flex flex-col justify-between
                           p-6 min-h-[230px]
                           bg-white shadow-2xl rounded-2xl
                           hover:-translate-y-2
                           hover:shadow-[0_12px_24px_rgba(0,0,0,0.12)]
                           transition-all duration-300 ease-in-out">

                            <a href="{{ route('timetables.timetable-editing-pane.index', $timetable) }}">
                                <div class="flex flex-col items-center pt-3 pb-3">
                                    <p class="font-bold">{{ $timetable->timetable_name }}</p>

                                    <p>
                                        {{ $timetable->semester }} semester ({{ $timetable->academic_year }})
                                    </p>

                                    @if($timetable->user)
                                        <p class="text-sm mt-1
                                        {{ $timetable->user_id === auth()->id()
                                            ? 'text-green-600 font-semibold'
                                            : 'text-gray-500' }}">
                                            by {{ $timetable->user->name }}
                                        </p>
                                    @endif
                                </div>
                            </a>
                            <div class="flex flex-row justify-evenly">
                                @include('includes.timetable-actions', [
                                    'rowTimetable' => $timetable
                                ])
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif

            {{-- PUBLIC --}}
            @if($public->isNotEmpty())
                <h2 class="text-[#ffffff] font-semibold mb-2 ml-2">Public Timetables</h2>
                <ul class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-10 mb-10">

                    @foreach($public as $timetable)
                        <li class="flex flex-col justify-between
                           p-6 min-h-[230px]
                           bg-white shadow-2xl rounded-2xl
                           hover:-translate-y-2
                           hover:shadow-[0_12px_24px_rgba(0,0,0,0.12)]
                           transition-all duration-300 ease-in-out">

                            <a href="{{ route('timetables.timetable-editing-pane.index', $timetable) }}">
                                <div class="flex flex-col items-center pt-3 pb-3">
                                    <p class="font-bold">{{ $timetable->timetable_name }}</p>

                                    <p>
                                        {{ $timetable->semester }} semester ({{ $timetable->academic_year }})
                                    </p>

                                    @if($timetable->user)
                                        <p class="text-sm mt-1
                                        {{ $timetable->user_id === auth()->id()
                                            ? 'text-green-600 font-semibold'
                                            : 'text-gray-500' }}">
                                            by {{ $timetable->user->name }}
                                        </p>
                                    @endif
                                </div>
                            </a>
                            <div class="flex flex-row justify-evenly">
                                @include('includes.timetable-actions', [
                                    'rowTimetable' => $timetable
                                ])
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif

            {{-- OTHER USERS --}}
            @if($others->isNotEmpty())
                <h2 class="text-[#ffffff] font-semibold mb-2 ml-2">Other Users’ Timetables</h2>
                <ul class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-10 mb-10">

                    @foreach($others as $timetable)
                        <li class="flex flex-col justify-between
                               p-6 min-h-[230px]
                               bg-white shadow-2xl rounded-2xl
                               hover:-translate-y-2
                               hover:shadow-[0_12px_24px_rgba(0,0,0,0.12)]
                               transition-all duration-300 ease-in-out">

                            <a href="{{ route('timetables.timetable-editing-pane.index', $timetable) }}">
                                <div class="flex flex-col items-center pt-3 pb-3">
                                    <p class="font-bold">{{ $timetable->timetable_name }}</p>

                                    <p>
                                        {{ $timetable->semester }} semester ({{ $timetable->academic_year }})
                                    </p>

                                    @if($timetable->user)
                                        <p class="text-sm mt-1
                                        {{ $timetable->user_id === auth()->id()
                                            ? 'text-green-600 font-semibold'
                                            : 'text-gray-500' }}">
                                            by {{ $timetable->user->name }}
                                        </p>
                                    @endif
                                </div>
                            </a>
                            <div class="flex flex-row justify-evenly">
                                @include('includes.timetable-actions', [
                                    'rowTimetable' => $timetable
                                ])
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif

        @endif
    </div>
@endsection
