@extends('app')

@section('title', 'Professors')

@section('content')
    <div class="w-full p-4">
        <div class="flex justify-between items-center mb-6">
            <div class="flex gap-20 items-center justify-center">
                <h1 class="text-xl font-bold text-white">List of Professors</h1>
                <livewire:input.search-bar
                    :action="route('professors.index')"
                    placeholder="Search by name or course..."
                />
            </div>
            @if($academicProgramsCount > 0)
                <a href="{{ route('professors.create') }}"
                   class="bg-yellow-500 text-[#5e0b0b] px-4 py-2 rounded-lg font-semibold shadow hover:bg-yellow-600 active:bg-yellow-700 transition-all duration-150">
                    Create
                </a>
            @else
                <a href="{{route('academic-programs.index')}}" class="bg-yellow-500 text-[#5e0b0b] px-4 py-2 rounded-lg font-semibold shadow hover:bg-yellow-600 active:bg-yellow-700 transition-all duration-150">
                    Create Academic Program
                </a>
            @endif

        </div>

        <table class="w-full text-left border-separate border-spacing-0 bg-white rounded-lg shadow-md overflow-hidden">
            <thead class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
            <tr>
                <th class="px-6 py-3 font-semibold">First Name</th>
                <th class="px-6 py-3 font-semibold">Last Name</th>
                <th class="px-6 py-3 font-semibold">Regular/Non-Regular</th>
                <th class="px-6 py-3 font-semibold">Academic Program</th>
                <th class="px-6 py-3 font-semibold">Max Unit Load</th>
                <th class="px-6 py-3 font-semibold">Course Specializations</th>
                <th class="px-6 py-3 font-semibold text-center">Action</th>
            </tr>
            </thead>
            <tbody class="text-gray-700">
            @forelse($professors as $professor)
                <tr class="border-t border-gray-200 hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-3 whitespace-nowrap">{{ $professor->first_name }}</td>
                    <td class="px-6 py-3 whitespace-nowrap">{{ $professor->last_name }}</td>
                    <td class="px-6 py-3">{{ $professor->professor_type }}</td>
                    <td class="px-6 py-3">{{ $professor->academicProgram?->program_abbreviation ?? 'N/A' }}</td>
                    <td class="px-6 py-3">{{ $professor->max_unit_load }}</td>
                    <td class="px-6 py-3">
                        {{ $professor->courses->pluck('course_title')->implode(', ') ?: 'N/A' }}
                    </td>
                    <td class="px-6 py-3 text-center">
                        <div class="flex flex-row gap-2 justify-center items-center">
                            <!-- Specializations Button -->
                            <a href="{{ route('professors.specializations.index', $professor) }}"
                               class="bg-gray-200 text-gray-800 px-3 py-2 rounded-lg font-semibold shadow hover:bg-gray-300 hover:text-gray-900 active:bg-gray-400 transition-all duration-150">
                                Specializations
                            </a>

                            <!-- Show Button -->
                            <livewire:buttons.show :route="'professors.show'" :params="$professor"/>

                            <!-- Edit Button -->
                            <livewire:buttons.edit :route="'professors.edit'" :params="$professor"/>

                            <!-- Delete Button -->
                            <livewire:buttons.delete
                                action="professors.destroy"
                                :params="$professor"
                                item_name="professor"
                                btnType="icon"
                                class="text-gray-600 px-3 py-2 rounded-lg hover:bg-gray-200 hover:text-gray-800 active:bg-gray-300 transition-all duration-150 flex items-center justify-center w-10 h-10"
                            />
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-6 text-gray-500">
                        @if($academicProgramsCount == 0)
                            No academic programs found. Please create an academic program first.
                        @else
                            No professors found.
                        @endif
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
