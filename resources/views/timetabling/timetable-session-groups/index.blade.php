@extends('app')

@section('title', $timetable->timetable_name . ' Class Sessions')

@php
    $sessionGroupTopSpacingValue = 1; // Spacing for the top part per session group/class session
    $programTypeBottomSpacingValue = 3; // Spacing for the bottom part
@endphp

@section('content')
    <div class="w-full pl-39 p-4">
        <div class="flex flex-row mb-7 justify-between items-center">
            {{-- Search bar for Session Groups --}}
            <div class="flex flex-col text-[#5e0b0b]">
                <h1 class="text-[18px] text-white">{{ $timetable->timetable_name }} Class Sessions</h1>
                <livewire:input.search-bar :action="route('timetables.session-groups.index', $timetable)" />
            </div>

            <a href="{{ route('timetables.session-groups.create', $timetable) }}" class="flex align-center box-border pt-[10px] pb-[10px] pl-[20px] pr-[20px] rounded-[12px] text-[16px] bg-yellow-500 text-[#5e0b0b] cursor-pointer shadow-2xl font-[600]">
                Add
            </a>
        </div>

        {{-- Right-side filters tray --}}
        <livewire:trays.session-group-filters :sessionGroupsByProgram="$sessionGroupsByProgram" />


        @foreach($sessionGroupsByProgram as $programId => $groups)
                <div class="program-section mb-4" data-program-id="{{ $programId }}">
                    @foreach($groups as $sessionGroup)

                        <div
                            class="sg-group mb-2 bg-white rounded-[12px] shadow-md overflow-hidden"
                            data-session-group-id="{{ $sessionGroup->id }}"
                            data-year-level="{{ $sessionGroup->year_level }}"
                            data-session-time="{{ $sessionGroup->session_time }}"
                            x-data="{ open: false }"
                        >
                            <!-- Header row -->
                            <div class="pt-4 pb-2 flex flex-row justify-between w-full bg-gray-100">
                                <div class="pl-6 flex items-center gap-3">
                                    <!-- Collapse / expand button -->
                                    <button
                                        type="button"
                                        class="sg-toggle flex items-center justify-center w-7 h-7 rounded-full border border-gray-300 bg-white text-gray-700 text-sm"
                                        @click="open = !open"
                                        :aria-expanded="open"
                                        aria-controls="sg-details-{{ $sessionGroup->id }}"
                                    >
                                        <i
                                            class="bi"
                                            :class="open ? 'bi-chevron-up' : 'bi-chevron-down'"
                                        ></i>
                                    </button>

                                    <p class="font-bold">
                                        {{ $sessionGroup->academicProgram->program_abbreviation ?? 'Unknown' }}
                                        {{ $sessionGroup->session_name }}
                                        {{ $sessionGroup->year_level }} Year
                                        @if($sessionGroup->session_time)
                                            ({{ ucfirst($sessionGroup->session_time) }})
                                        @endif
                                    </p>
                                </div>

                                <div class="pr-6">
                                    <div class="flex gap-3 items-center">
                                        <!-- Session group color picker -->
                                        <div
                                            class="mt-1 flex items-center gap-2"
                                            data-session-group-id="{{ $sessionGroup->id }}"
                                            data-update-url="{{ route('timetables.session-groups.update-color', [$timetable, $sessionGroup]) }}"
                                            data-current-color="{{ $sessionGroup->session_color ?? '' }}"
                                        >
                                            <span class="text-xs text-gray-600">Tray color:</span>
                                            {{-- Preview square showing current DB color --}}
                                            <div
                                                class="w-4 h-4 rounded border border-gray-400 sg-color-display"
                                                style="background-color: {{ $sessionGroup->session_color ?? '#ffffff' }};"
                                            ></div>
                                            {{-- Button to open palette --}}
                                            <button
                                                type="button"
                                                class="group-color-open-btn sg-color-btn text-xs px-2 py-1 rounded border border-gray-300 bg-white"
                                            >
                                                Color
                                            </button>
                                        </div>

                                        <!-- Add Sessions Button -->
                                        <a href="{{ route('timetables.session-groups.course-sessions.create', [$timetable, $sessionGroup]) }}"
                                           class="bg-[#800000] text-white px-4 py-2 rounded-lg font-semibold shadow hover:bg-[#660000] active:bg-[#4d0000] transition-all duration-150">
                                            Add Sessions
                                        </a>

                                        <!-- Edit Academic Terms Button -->
                                        <a href="{{ route('timetables.session-groups.edit-terms', [$timetable, $sessionGroup]) }}"
                                           class="bg-white text-[#800000] px-4 py-2 rounded-lg font-semibold shadow border border-[#800000] hover:bg-gray-50 active:bg-gray-100 transition-all duration-150">
                                            Edit Academic Terms
                                        </a>

                                        <!-- Copy Button -->
                                        <a href="{{ route('timetables.session-groups.copy', [$timetable, $sessionGroup]) }}"
                                           class="text-gray-600 px-3 py-2 rounded-lg hover:bg-gray-200 hover:text-gray-800 active:bg-gray-300 transition-all duration-150"
                                           title="Copy this Class Session (including its course sessions)">
                                            <i class="bi bi-files"></i>
                                        </a>

                                        <!-- Show Button -->
                                        <a href="{{ route('timetables.session-groups.show', [$timetable, $sessionGroup]) }}"
                                           class="text-gray-600 px-3 py-2 rounded-lg hover:bg-gray-200 hover:text-gray-800 active:bg-gray-300 transition-all duration-150">
                                            <i class="bi-card-list"></i>
                                        </a>

                                        <!-- Edit Button -->
                                        <a href="{{ route('timetables.session-groups.edit', [$timetable, $sessionGroup]) }}"
                                           class="text-gray-600 px-3 py-2 rounded-lg hover:bg-gray-200 hover:text-gray-800 active:bg-gray-300 transition-all duration-150">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>

                                        <!-- Delete Button -->
                                        <livewire:buttons.delete
                                            action="timetables.session-groups.destroy"
                                            :params="[$timetable, $sessionGroup]"
                                            item_name="session"
                                            btnType="icon"
                                            class="text-gray-600 px-3 py-2 rounded-lg hover:bg-gray-200 hover:text-gray-800 active:bg-gray-300 transition-all duration-150"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Collapsible details (course sessions table) -->
                            <div
                                id="sg-details-{{ $sessionGroup->id }}"
                                class="sg-details border-t border-gray-200"
                                x-cloak
                                x-show="open"
                                x-transition
                            >
                                <table class="w-full text-left border-separate border-spacing-0 bg-white">
                                    <thead class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                                    <tr>
                                        <th class="px-6 py-3 font-semibold">Course Title</th>
                                        <th class="px-6 py-3 font-semibold">Course Name</th>
                                        <th class="px-6 py-3 font-semibold">Units</th>
                                        <th class="px-6 py-3 font-semibold">Type</th>
                                        <th class="px-6 py-3 font-semibold">Academic Term</th>
                                        <th class="px-6 py-3 font-semibold text-center">Action</th>
                                    </tr>
                                    </thead>
                                    <tbody class="text-gray-700">
                                    @foreach($courseSessionsBySessionGroup[$sessionGroup->id] ?? [] as $courseSession)
                                        <tr class="border-t border-gray-200 hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-3">{{ $courseSession->course->course_title ?? 'Unknown Course' }}</td>
                                            <td class="px-6 py-3">{{ $courseSession->course->course_name }}</td>
                                            <td class="px-6 py-3">{{ $courseSession->course->unit_load }}</td>
                                            <td class="px-6 py-3">{{ $courseSession->course->course_type }}</td>
                                            <td class="px-6 py-3">
                                                <form method="POST"
                                                      action="{{ route('timetables.session-groups.course-sessions.update-term', [$timetable, $sessionGroup, $courseSession]) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <select
                                                        name="academic_term[{{ $courseSession->id }}]"
                                                        onchange="this.form.submit()"
                                                        @if($courseSession->course->duration_type === 'semestral') disabled @endif
                                                        class="border border-gray-300 rounded-md text-sm px-2 py-1 focus:ring-2 focus:ring-maroon-600 focus:outline-none"
                                                    >
                                                        @if($courseSession->course->duration_type === 'semestral')
                                                            <option value="semestral" selected>semestral</option>
                                                        @else
                                                            <option value="" {{ is_null($courseSession->academic_term) ? 'selected' : '' }}>-- Select Term --</option>
                                                            <option value="1st" {{ $courseSession->academic_term == '1st' ? 'selected' : '' }}>1st</option>
                                                            <option value="2nd" {{ $courseSession->academic_term == '2nd' ? 'selected' : '' }}>2nd</option>
                                                        @endif
                                                    </select>
                                                </form>
                                            </td>
                                            <td class="px-6 py-3 text-center">
                                                <livewire:buttons.delete
                                                    action="timetables.session-groups.course-sessions.destroy"
                                                    :params="[$timetable, $sessionGroup, $courseSession]"
                                                    item_name="course session"
                                                    btnType="icon"
                                                />
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    @endforeach

                </div>
            @endforeach
    </div>

@endsection
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Exactly your preset list
        const GROUP_COLOR_PRESETS = [
            "#ffe0e0", "#ffc4c4", "#ffb3b3", "#ffd0c8", "#ffebe0",
            "#ffe8cc", "#ffdcb3", "#ffcf99", "#ffe6b8", "#ffefcc",
            "#fff6cc", "#fff2b3", "#fff0a6", "#fff9cc", "#fff7b8",
            "#e0ffe0", "#ccf5d5", "#bff0cc", "#d6ffe6", "#c8ffd9",
            "#e0fff7", "#ccf5f0", "#b8efe8", "#ccfffb", "#d6fffa",
            "#e0eaff", "#ccdfff", "#b8d4ff", "#d6e8ff", "#c3ddff",
            "#e3e0ff", "#d5d0ff", "#c6c0ff", "#d9d4ff", "#cbc9ff",
            "#f0e0ff", "#ebd0ff", "#e6c2ff", "#f2ddff", "#f5e6ff",
            "#ffe0f0", "#ffcce8", "#ffb8e0", "#ffd6f0", "#ffe6f5",
            "#e6ffea", "#e0ffe8", "#f0ffea", "#e8ffe0", "#f2ffe6"
        ];

        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const CSRF_TOKEN = csrfMeta ? csrfMeta.getAttribute('content') : '';

        let overlay = null;
        let overlayGrid = null;
        let overlayTitle = null;
        let activeWrapper = null; // the current [data-session-group-id] wrapper

        function createOverlay() {
            overlay = document.createElement('div');
            overlay.style.position = 'fixed';
            overlay.style.inset = '0';
            overlay.style.background = 'rgba(0, 0, 0, 0.35)';
            overlay.style.display = 'none';
            overlay.style.alignItems = 'center';
            overlay.style.justifyContent = 'center';
            overlay.style.zIndex = '9999';

            const modal = document.createElement('div');
            modal.style.background = '#ffffff';
            modal.style.borderRadius = '0.5rem';
            modal.style.padding = '1rem';
            modal.style.maxWidth = '420px';
            modal.style.width = '100%';
            modal.style.boxShadow = '0 10px 25px rgba(0,0,0,0.15)';

            const header = document.createElement('div');
            header.style.display = 'flex';
            header.style.alignItems = 'center';
            header.style.justifyContent = 'space-between';
            header.style.marginBottom = '0.75rem';

            overlayTitle = document.createElement('div');
            overlayTitle.textContent = 'Choose session group color';
            overlayTitle.style.fontSize = '0.875rem';
            overlayTitle.style.fontWeight = '600';

            const closeBtn = document.createElement('button');
            closeBtn.type = 'button';
            closeBtn.textContent = 'Close';
            closeBtn.style.fontSize = '0.75rem';
            closeBtn.style.padding = '0.25rem 0.5rem';
            closeBtn.style.borderRadius = '0.25rem';
            closeBtn.style.border = '1px solid #e5e7eb';
            closeBtn.style.background = '#f3f4f6';
            closeBtn.addEventListener('click', hideOverlay);

            header.appendChild(overlayTitle);
            header.appendChild(closeBtn);

            overlayGrid = document.createElement('div');
            overlayGrid.style.display = 'grid';
            overlayGrid.style.gridTemplateColumns = 'repeat(8, minmax(0, 1fr))';
            overlayGrid.style.gap = '0.25rem';

            modal.appendChild(header);
            modal.appendChild(overlayGrid);
            overlay.appendChild(modal);

            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) {
                    hideOverlay();
                }
            });

            document.body.appendChild(overlay);
        }

        function ensureOverlay() {
            if (!overlay) {
                createOverlay();
            }
            return overlay;
        }

        function hideOverlay() {
            if (!overlay) return;
            overlay.style.display = 'none';
            activeWrapper = null;
        }

        function showOverlay() {
            ensureOverlay();
            overlay.style.display = 'flex';
        }

        function setGroupColor(wrapperEl, hex) {
            wrapperEl.dataset.currentColor = hex;

            const preview = wrapperEl.querySelector('.sg-color-display');
            if (preview) {
                preview.style.backgroundColor = hex;
            }
        }

        function saveGroupColor(wrapperEl, hex) {
            const updateUrl = wrapperEl.dataset.updateUrl;
            if (!updateUrl) return;

            fetch(updateUrl, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                body: JSON.stringify({ session_color: hex })
            })
                .then(function (res) {
                    if (!res.ok) {
                        console.error('Failed to save session_color, status:', res.status);
                    }
                    return res.json().catch(function () {});
                })
                .then(function (data) {
                    // If backend returns a color, trust it
                    if (data && data.session_color && wrapperEl) {
                        setGroupColor(wrapperEl, data.session_color);
                    }
                })
                .catch(function (err) {
                    console.error('Error saving session_color', err);
                });
        }

        function openColorPicker(wrapperEl) {
            activeWrapper = wrapperEl;
            ensureOverlay();
            overlayGrid.innerHTML = '';

            const currentColor = (wrapperEl.dataset.currentColor || '').toLowerCase();

            GROUP_COLOR_PRESETS.forEach(function (hex) {
                const swatch = document.createElement('button');
                swatch.type = 'button';
                swatch.style.width = '1.5rem';
                swatch.style.height = '1.5rem';
                swatch.style.borderRadius = '0.25rem';
                swatch.style.border = '1px solid #d1d5db';
                swatch.style.backgroundColor = hex;
                swatch.style.cursor = 'pointer';

                if (currentColor && currentColor === hex.toLowerCase()) {
                    swatch.style.outline = '2px solid #111827';
                    swatch.style.outlineOffset = '1px';
                }

                swatch.addEventListener('click', function (e) {
                    e.stopPropagation();
                    setGroupColor(wrapperEl, hex);
                    saveGroupColor(wrapperEl, hex);
                    hideOverlay();
                });

                overlayGrid.appendChild(swatch);
            });

            showOverlay();
        }

        // Attach click handlers to each Color button
        document.querySelectorAll('.sg-color-btn').forEach(function (btn) {
            const wrapperEl = btn.closest('[data-session-group-id]');
            if (!wrapperEl) return;

            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                openColorPicker(wrapperEl);
            });
        });

        // --- Program / Year / Time filter logic (plain JS) ---
        const programButtons = Array.from(document.querySelectorAll('.program-filter-btn'));
        const yearButtons = Array.from(document.querySelectorAll('.year-filter-btn'));
        const timeButtons = Array.from(document.querySelectorAll('.time-filter-btn'));
        const programSections = Array.from(document.querySelectorAll('.program-section'));

        let currentProgramId = 'all';
        let currentYear = 'all';
        let currentTime = 'all';

        function styleActiveButton(buttons, activeValue, attrName) {
            buttons.forEach(function (btn) {
                const val = btn.getAttribute(attrName) || 'all';
                const isActive = val === activeValue;

                btn.classList.toggle('bg-[#5e0b0b]', isActive);
                btn.classList.toggle('text-white', isActive);
                btn.classList.toggle('border-[#5e0b0b]', isActive);

                if (!isActive) {
                    btn.classList.add('bg-gray-200', 'text-gray-800');
                } else {
                    btn.classList.remove('bg-gray-200', 'text-gray-800');
                }
            });
        }

        function recomputeVisibility() {
            programSections.forEach(function (section) {
                const secProgramId = section.getAttribute('data-program-id');
                const programMatches = (currentProgramId === 'all' || secProgramId === currentProgramId);

                // Show/hide entire program section first
                section.style.display = programMatches ? '' : 'none';
                if (!programMatches) {
                    return;
                }

                const groups = section.querySelectorAll('.sg-group');
                groups.forEach(function (group) {
                    const year = group.getAttribute('data-year-level');
                    const time = group.getAttribute('data-session-time');

                    const yearMatch = (currentYear === 'all' || year === currentYear);
                    const timeMatch = (currentTime === 'all' || time === currentTime);

                    group.style.display = (yearMatch && timeMatch) ? '' : 'none';
                });
            });
        }

        // Default active styles
        styleActiveButton(programButtons, 'all', 'data-program-id');
        styleActiveButton(yearButtons, 'all', 'data-year');
        styleActiveButton(timeButtons, 'all', 'data-time');
        recomputeVisibility();

        // Wire up clicks
        programButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                currentProgramId = btn.getAttribute('data-program-id') || 'all';
                styleActiveButton(programButtons, currentProgramId, 'data-program-id');
                recomputeVisibility();
            });
        });

        yearButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                currentYear = btn.getAttribute('data-year') || 'all';
                styleActiveButton(yearButtons, currentYear, 'data-year');
                recomputeVisibility();
            });
        });

        timeButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                currentTime = btn.getAttribute('data-time') || 'all';
                styleActiveButton(timeButtons, currentTime, 'data-time');
                recomputeVisibility();
            });
        });
    });
</script>
