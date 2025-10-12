@extends('app')

@section('title', 'Specializations')

@section('content')
    <div class="w-full p-4">

        <!-- Header: Title + Add + Back Button -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-xl font-bold text-white">
                {{ $professor->last_name }}, {{ $professor->first_name }}'s Specializations
            </h1>
            <div class="flex gap-4">
                <!-- Add button: yellow -->
                <a href="{{ route('professors.specializations.create', $professor) }}"
                   class="bg-yellow-500 text-[#5e0b0b] px-4 py-2 rounded-lg font-semibold shadow hover:bg-yellow-600 active:bg-yellow-700 transition-all duration-150">
                    Add
                </a>

                <!-- Back button: gray -->
                <a href="{{ route('professors.index') }}"
                   class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg font-semibold shadow hover:bg-gray-300 hover:text-gray-900 active:bg-gray-400 transition-all duration-150">
                    Back
                </a>
            </div>
        </div>


        <!-- Table -->
        <table class="w-full text-left border-separate border-spacing-0 bg-white rounded-lg shadow-md overflow-hidden">
            <thead class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
            <tr>
                <th class="px-6 py-3 font-semibold">Course Title</th>
                <th class="px-6 py-3 font-semibold">Course Name</th>
                <th class="px-6 py-3 font-semibold text-center">Action</th>
            </tr>
            </thead>
            <tbody class="text-gray-700">
            @forelse($specializations as $specialization)
                <tr class="border-t border-gray-200 hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-3">{{ $specialization->course->course_title }}</td>
                    <td class="px-6 py-3">{{ $specialization->course->course_name }}</td>
                    <td class="px-6 py-3 text-center">
                        <x-buttons.delete
                            action="professors.specializations.destroy"
                            :params="[$professor, $specialization]"
                            item_name="specialization"
                            btnType="icon"
                            class="text-gray-600 px-3 py-2 rounded-lg hover:bg-gray-200 hover:text-gray-800 active:bg-gray-300 transition-all duration-150 flex items-center justify-center w-10 h-10"
                        />
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center py-6 text-gray-500">
                        No specializations found for this professor.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
