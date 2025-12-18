@extends('app')

@section('title', 'Timetable Settings')

@section('content')

    <div class="w-full p-4 pl-40">

        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-xl font-bold text-white">
                Timetable Visibility Settings
            </h1>
        </div>

        <!-- Form -->
        <form method="POST"
              action="{{ route('timetables.settings.update', $timetable) }}"
              class="bg-white rounded-lg shadow-md p-6 space-y-6">
            @csrf
            @method('PUT')

            <!-- Visibility Options -->
            <div>
                <h2 class="text-lg font-semibold mb-4 text-gray-700">
                    Visibility
                </h2>

                <div class="flex flex-col gap-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="visibility" value="private"
                            {{ $timetable->visibility === 'private' ? 'checked' : '' }}>
                        <span>Private (only me)</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="visibility" value="public"
                            {{ $timetable->visibility === 'public' ? 'checked' : '' }}>
                        <span>Public (all users)</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="visibility" value="restricted"
                            {{ $timetable->visibility === 'restricted' ? 'checked' : '' }}>
                        <span>Restricted</span>
                    </label>
                </div>
            </div>

            <!-- Restricted Settings -->
            <div id="restricted-section" class="border-t pt-6 transition-opacity duration-200">

                <h2 class="text-lg font-semibold mb-4 text-gray-700">
                    Restricted Access
                </h2>

                <p class="text-sm text-gray-500 mb-4">
                    Select users and/or academic programs that can edit this timetable.
                </p>

                <!-- Users -->
                <div class="mb-6">
                    <h3 class="font-semibold mb-2 text-gray-600">Allowed Users</h3>

                    <div class="grid grid-cols-2 gap-2 max-h-60 overflow-y-auto border rounded-lg p-3">
                        @forelse($users as $user)
                            <label
                                class="flex items-start gap-3 cursor-pointer
                                   border border-gray-200 rounded-md
                                   p-3 hover:bg-gray-50 transition">
                                <input type="checkbox"
                                       class="mt-1"
                                       name="user_ids[]"
                                       value="{{ $user->id }}"
                                    {{ $timetable->allowedUsers->contains($user->id) ? 'checked' : '' }}>

                                <div class="flex flex-col leading-tight">
                                    <span class="font-medium text-gray-800">
                                        {{ $user->name }}
                                    </span>

                                    <span class="text-sm text-gray-600">
                                        {{ trim($user->first_name . ' ' . $user->last_name) }}
                                    </span>

                                    <span class="text-xs text-gray-500">
                                        {{ $user->email }}
                                    </span>
                                </div>
                            </label>
                        @empty
                            <div class="col-span-2 text-sm text-gray-500 italic">
                                No users available.
                            </div>
                        @endforelse
                    </div>

                </div>

                <!-- Academic Programs -->
                <div>
                    <h3 class="font-semibold mb-2 text-gray-600">
                        Allowed Academic Programs
                    </h3>
                    <p class="text-sm text-gray-500 mb-4">
                        All users belonging to the selected academic programs can edit this timetable.
                    </p>

                    <div class="grid grid-cols-2 gap-2 max-h-60 overflow-y-auto border rounded-lg p-3">
                        @foreach($programs as $program)
                            <label
                                class="flex items-center gap-3 cursor-pointer
                                   border border-gray-200 rounded-md
                                   p-3 hover:bg-gray-50 transition">
                                <input type="checkbox"
                                       name="program_ids[]"
                                       value="{{ $program->id }}"
                                    {{ $timetable->allowedPrograms->contains($program->id) ? 'checked' : '' }}>

                                <span class="text-gray-800">
                                    {{ $program->program_name }} ({{ $program->program_abbreviation }})
                                </span>
                            </label>
                        @endforeach

                    </div>
                </div>

                <hr class="my-6">

                <h3 class="text-lg font-semibold mb-2">
                    Editing Permissions
                </h3>

                <div class="flex items-center gap-3">
                    <input
                        type="checkbox"
                        id="allow_non_owner_record_edit"
                        name="allow_non_owner_record_edit"
                        value="1"
                        {{ $timetable->allow_non_owner_record_edit ? 'checked' : '' }}
                        class="rounded border-gray-300"
                    >

                    <label for="allow_non_owner_record_edit" class="text-sm text-gray-700">
                        Allow non-owners to edit records (Class Sessions & Rooms)
                    </label>
                </div>
                <div class="flex items-center gap-3 mt-3">
                    <input
                        type="checkbox"
                        id="allow_non_owner_timetable_edit"
                        name="allow_non_owner_timetable_edit"
                        value="1"
                        {{ $timetable->allow_non_owner_timetable_edit ? 'checked' : '' }}
                        class="rounded border-gray-300"
                    >

                    <label for="allow_non_owner_timetable_edit" class="text-sm text-gray-700">
                        Allow non-owners to edit the timetable (Editor)
                    </label>
                </div>


            </div>

            <!-- Actions -->
            <div class="flex gap-4 pt-4">
                <button type="submit"
                        class="bg-yellow-500 text-[#5e0b0b] px-4 py-2 rounded-lg font-semibold shadow
                           hover:bg-yellow-600 active:bg-yellow-700 transition-all duration-150">
                    Save Settings
                </button>
            </div>

        </form>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const restrictedSection = document.getElementById('restricted-section');
            const restrictedInputs = restrictedSection.querySelectorAll('.restricted-input');
            const visibilityRadios = document.querySelectorAll('input[name="visibility"]');

            function updateRestrictedState() {
                const isRestricted = document.querySelector('input[name="visibility"]:checked')?.value === 'restricted';

                // Grey out section
                restrictedSection.classList.toggle('opacity-50', !isRestricted);
                restrictedSection.classList.toggle('pointer-events-none', !isRestricted);

                // Enable / disable inputs
                restrictedInputs.forEach(input => {
                    input.disabled = !isRestricted;
                });
            }

            // Initial state (page load)
            updateRestrictedState();

            // React to radio changes
            visibilityRadios.forEach(radio => {
                radio.addEventListener('change', updateRestrictedState);
            });
        });
    </script>

@endsection
