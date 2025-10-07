@extends('app')

@section('title', 'Generate Timetable')

@section('content')
    <div class="flex flex-col gap-[20px] pt-[40px] pb-[40px] px-[40px] justify-center items-center bg-white rounded-2xl shadow-2xl max-w-[600px] mx-auto">

        <!-- Header -->
        <div class="flex justify-between items-center w-full mb-4">
            <h1 class="text-xl font-bold mb-0">Generate Timetable</h1>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="w-full mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded break-words">
                {{ session('success') }}
            </div>
        @endif

        <!-- Form -->
        <form action="{{ route('timetables.generate.post', $timetable) }}" method="POST" class="w-full flex justify-center">
            @csrf
            <button type="submit"
                    class="bg-yellow-500 text-[#5e0b0b] px-6 py-3 rounded-lg font-semibold shadow hover:bg-yellow-600 active:bg-yellow-700 transition-all duration-150">
                Generate
            </button>
        </form>

    </div>
@endsection
