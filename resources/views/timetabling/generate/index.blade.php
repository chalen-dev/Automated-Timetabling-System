@extends('app')

@section('title', 'Generate Timetable')

@section('content')
    <div class="flex flex-col gap-5 pt-10 pb-10 left-40 px-10 justify-center items-center bg-white rounded-2xl shadow-2xl w-270 h-fit mx-auto">

        <!-- Header -->
        <h1 class="text-2xl font-bold mb-6">Generate</h1>

        <!-- Form -->
        <form id="generateTimetableForm"
              action="{{ route('timetables.generate.post', $timetable) }}"
              method="POST"
              class="w-full flex justify-center mt-4">
            @csrf
            <div class="flex gap-10 w-full">
                <div class="w-full flex flex-col gap-2 items-center justify-center border-2 border-gray-300 rounded-lg p-4">
                    <h1 class="font-bold">Options</h1>
                    <livewire:input.single-checkbox
                        name="confineLaboratorySubjects"
                        label="Confine Laboratory Subjects"
                        class="flex gap-3 items-center justify-between align-center border-2 border-gray-300 rounded-lg p-4 w-full"
                        checkboxStyle="w-5 h-5"
                    />
                    <div class="flex flex-col">
                        <p class="text-xs">
                            Laboratory Subjects will be assigned to certain timeslots depending on their time.
                        </p>
                        <p class="text-xs text-center">
                            <br>
                            Morning: 8:00 AM - 10:00 AM, 10:00 AM - 12:00 PM.
                            <br>
                            Afternoon: 1:30 PM - 3:30 PM, 3:30 PM - 5:30 PM.
                            <br>
                            Evening: 5:30 PM - 7:30 PM, 7:30 PM - 9:30 PM.
                        </p>
                    </div>
                </div>

                <div class="w-full flex flex-col items-center justify-center border-2 border-gray-300 rounded-lg p-4">
                    <h3 class="mb-3">Automatically plot courses to rooms</h3>

                    @php
                        /** @var bool $canGenerate */
                        $missingReasons = [];
                        if (!$hasSessionGroups ?? false) {
                            $missingReasons[] = 'at least one Class Session';
                        }
                        if (!$hasCourseSessions ?? false) {
                            $missingReasons[] = 'at least one Course assigned to a particular session';
                        }
                        if (!$hasTimetableRooms ?? false) {
                            $missingReasons[] = 'at least one Room';
                        }
                    @endphp

                    <button type="{{ $canGenerate ? 'submit' : 'button' }}"
                            @class([
                                'px-6 py-3 rounded-lg font-semibold shadow transition-all duration-150',
                                'bg-yellow-500 text-[#5e0b0b] hover:bg-yellow-600 active:bg-yellow-700' => $canGenerate,
                                'bg-gray-300 text-gray-500 cursor-not-allowed' => !$canGenerate,
                            ])
                            @if(!$canGenerate) disabled @endif>
                        Generate Timetable
                    </button>

                    @if(!$canGenerate && !empty($missingReasons))
                        <p class="mt-3 text-xs text-center text-red-600">
                            To generate a timetable, you need {{ implode(', ', $missingReasons) }}.
                        </p>
                    @endif
                </div>

            </div>

        </form>
        <div class="w-full flex justify-center mt-4 h-20">
            <!-- Success Message -->
            @if(session('success'))
                <div class="w-full p-3 bg-green-50 border border-green-400 text-green-800 rounded break-words text-center flex items-center justify-center">
                    {!! session('success') !!}
                </div>
            @endif

            <!-- Error Message -->
            @if(session('error'))
                <div class="w-full p-3 bg-red-50 border border-red-400 text-red-800 rounded overflow-auto max-h-64">
                    {!! session('error') !!}
                </div>
            @endif
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('generateTimetableForm');
            if (!form) return;

            const CAN_GENERATE = @json($canGenerate);

            if (!CAN_GENERATE) {
                // Button is disabled in HTML; just prevent any JS confirmation
                return;
            }

            form.addEventListener('submit', function (e) {
                e.preventDefault();

                // If SweetAlert2 is not available, just submit normally
                if (typeof Swal === 'undefined') {
                    form.submit();
                    return;
                }

                Swal.fire({
                    title: 'Generate timetable?',
                    text: 'This will run the scheduling script and regenerate the timetable file.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, generate',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#16a34a',
                    cancelButtonColor: '#d1d5db',
                    reverseButtons: false,
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endsection
