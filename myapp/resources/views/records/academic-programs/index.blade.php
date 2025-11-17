@extends('app')

@section('title', 'Academic Programs')

@section('content')
    <div class="w-full p-4">

        <!-- Header: Title + Search + Create Button -->
        <div class="flex justify-between items-center mb-6">
            <div class="flex gap-20 items-center justify-between">
                <h1 class="text-xl font-bold mb-0 text-white">Academic Programs</h1>
                <x-search-bar.search-bar
                    :action="route('academic-programs.index')"
                    placeholder="Search by program name or abbreviation..."
                    name="search"
                />
            </div>

            <a href="{{ route('academic-programs.create') }}"
               class="bg-yellow-500 text-[#5e0b0b] px-4 py-2 rounded-lg font-semibold shadow hover:bg-yellow-600 active:bg-yellow-700 transition-all duration-150">
                Create
            </a>
        </div>

        <!-- Table -->
        <table class="w-full text-left border-separate border-spacing-0 bg-white rounded-lg shadow-md overflow-hidden">
            <thead class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
            <tr>
                <th class="px-6 py-3 font-semibold">Program Name</th>
                <th class="px-6 py-3 font-semibold">Program Abbreviation</th>
                <th class="px-6 py-3 font-semibold text-center">Action</th>
            </tr>
            </thead>
            <tbody class="text-gray-700">
            @forelse($academicPrograms as $academicProgram)
                <tr class="border-t border-gray-200 hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-3">{{ $academicProgram->program_name }}</td>
                    <td class="px-6 py-3">{{ $academicProgram->program_abbreviation }}</td>
                    <td class="px-6 py-3 text-center">
                        <div class="flex flex-row gap-2 justify-center items-center">
                            <!-- Show Button -->
                            <a href="{{ route('academic-programs.show', $academicProgram) }}"
                               class="text-gray-600 px-3 py-2 rounded-lg hover:bg-gray-200 hover:text-gray-800 active:bg-gray-300 transition-all duration-150 flex items-center justify-center w-10 h-10">
                                <i class="bi-card-list"></i>
                            </a>

                            <!-- Edit Button -->
                            <a href="{{ route('academic-programs.edit', $academicProgram) }}"
                               class="text-gray-600 px-3 py-2 rounded-lg hover:bg-gray-200 hover:text-gray-800 active:bg-gray-300 transition-all duration-150 flex items-center justify-center w-10 h-10">
                                <i class="bi bi-pencil-square"></i>
                            </a>

                            <!-- Delete Button -->
                            <livewire:buttons.delete
                                action="academic-programs.destroy"
                                :params="$academicProgram"
                                item_name="academic program"
                                btnType="icon"
                                class="text-gray-600 px-3 py-2 rounded-lg hover:bg-gray-200 hover:text-gray-800 active:bg-gray-300 transition-all duration-150 flex items-center justify-center w-10 h-10"
                            />
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center py-6 text-gray-500">
                        No academic programs found.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
