@extends('app')

@section('title', 'Generate Timetable')

@section('content')
    <div class="flex flex-col gap-5 pt-10 pb-10 px-10 justify-center items-center bg-white rounded-2xl shadow-2xl w-200 mx-auto">

        <!-- Header -->
        <h1 class="text-2xl font-bold mb-6">Generate</h1>

        <!-- Success Message -->
        @if(session('success'))
            <div class="w-full p-3 bg-green-50 border border-green-400 text-green-800 rounded break-words">
                {!! session('success') !!}
            </div>
        @endif

        <!-- Error Message -->
        @if(session('error'))
            <div class="w-full p-3 bg-red-50 border border-red-400 text-red-800 rounded overflow-auto max-h-64">
                {!! session('error') !!}
            </div>
        @endif

        <!-- Form -->
        <form action="{{ route('timetables.generate.post', $timetable) }}" method="POST" class="w-full flex justify-center mt-4">
            @csrf
            <div class="flex gap-10 w-full">
                <div class="w-full flex flex-col gap-2 items-center justify-center border-2 border-gray-300 rounded-lg p-4">
                    <h1 class="font-bold">Options</h1>
                    <livewire:input.single-checkbox
                        name="confineLaboratorySubjects"
                        label="Confine Laboratory Subjects"
                        class="flex gap-3 items-center justify-center align-center border-2 border-gray-300 rounded-lg p-4"
                        checkboxStyle="w-5 h-5"
                    />
                    <p class="text-xs">
                        Laboratory Subjects will be assigned to certain timeslots depending on their time.
                    </p>
                </div>

                <div class="w-full flex flex-col items-center justify-center border-2 border-gray-300 rounded-lg p-4">
                    <button type="submit"
                            class="bg-yellow-500 text-[#5e0b0b] px-6 py-3 rounded-lg font-semibold shadow hover:bg-yellow-600 active:bg-yellow-700 transition-all duration-150">
                        Generate
                    </button>
                </div>

            </div>

        </form>

    </div>
@endsection
