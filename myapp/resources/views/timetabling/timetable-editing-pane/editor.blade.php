{{-- resources/views/timetabling/timetable-editing-pane/editor.blade.php --}}
@extends('app')

@section('title', $timetable->timetable_name)

@section('content')
    <div class="w-full">

        <livewire:timetabling.editor.legend/>

        <livewire:timetabling.editor.tools/>

        <livewire:timetabling.editor.courses-tray/>

        <livewire:timetabling.editor.timetable-canvas :timetable="$timetable" />



    </div>
@endsection

<script>
    window.sessionGroupsData = @json($sessionGroups);
    window.initialPlacementsByView = @json($initialPlacementsByView ?? []);
    const CSRF_TOKEN = '{{ csrf_token() }}';
</script>

<style>
    /* Make sure cells can show the corner icons */
    .timetable-editor td {
        position: relative;
    }

    /* Draw a full rectangular border inside the merged course cell */
    .timetable-editor td.course-cell::after {
        content: "";
        position: absolute;
        inset: 0; /* top:0; right:0; bottom:0; left:0 */
        border: 1px solid #4b5563; /* darker gray */
        pointer-events: none;      /* don't block clicks/drags */
    }
    /* conflict icon */
    .timetable-editor td .conflict-warning-icon {
        position: absolute;
        top: 2px;
        left: 2px;
        font-size: 20px;
        color: #f59e0b; /* amber */
        pointer-events: none;
    }

    /* term badge at the bottom of each course cell (canvas + tray) */
    .timetable-editor td .term-badge,
    #coursesTray td .term-badge {
        position: absolute;
        left: 50%;
        bottom: 4px;
        transform: translateX(-50%);
        font-size: 11px;
        line-height: 1;
        padding: 3px 14px;   /* widened */
        border-radius: 9999px;
        background-color: #f9fafb;
        border: 1px solid #e5e7eb;
        color: #374151;
        text-transform: uppercase;
        white-space: nowrap;
        pointer-events: none;
    }

    .term-badge-1st { border-color: #60a5fa; }
    .term-badge-2nd { border-color: #f97316; }
    .term-badge-sem { border-color: #a855f7; }



    /* Blue band = valid placement (tray -> canvas, slide) */
    .timetable-editor td.preview-place {
        box-shadow: inset 0 0 0 2px rgba(0, 123, 255, 0.6);
    }

    /* Green band = valid swap */
    .timetable-editor td.preview-swap {
        box-shadow: inset 0 0 0 2px rgba(40, 167, 69, 0.6);
    }

    /* Red band = invalid placement */
    .timetable-editor td.preview-invalid {
        box-shadow: inset 0 0 0 2px rgba(220, 53, 69, 0.6);
    }

    /* Alignment row highlight (when hovering a misaligned course cell) */
    .timetable-editor td.alignment-row-highlight {
        box-shadow: inset 0 0 0 2px rgba(234, 179, 8, 0.9); /* yellow-ish */
    }


    /* While hovering a swap target (single-cell dashed outline) */
    .timetable-editor td.drag-over {
        outline: 2px dashed #007bff;
    }

    /* Floating icons in the top-right corner (✔ / ✖ / 🔒) */
    .timetable-editor td.locked::before,
    .timetable-editor td.preview-place::before,
    .timetable-editor td.preview-swap::before,
    .timetable-editor td.preview-invalid::before {
        position: absolute;
        top: 1px;
        right: 2px;
        font-size: 11px;
        line-height: 1;
        pointer-events: none;
        text-shadow: 0 0 2px #fff;
    }

    .timetable-editor td.preview-place::before,
    .timetable-editor td.preview-swap::before {
        content: "✔";
        color: #28a745;
    }

    .timetable-editor td.preview-invalid::before {
        content: "✖";
        color: #dc3545;
    }

    .timetable-editor td.locked::before {
        content: "🔒";
        color: #ffc107;
        font-size: 12px;
    }

    /* When dragging a canvas course over its tray group, highlight that table */
    #coursesTray .session-table.session-return-preview {
        box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.8);
        background-color: rgba(0, 123, 255, 0.06);
    }

    #coursesTray td {
        position: relative;
    }

    #coursesTray td.tray-cell {
        padding-bottom: 1.75rem; /* extra height so text doesn't collide with badge */
    }

    /* Tray cells that are "used" in the current term/day view */
    #coursesTray td.tray-used {
        background-color: #e5e7eb; /* gray-200 */
        color: #9ca3af;            /* gray-400-ish text */
    }

    /* Tray cells that are "used" in the current term/day view */
    #coursesTray td.tray-used {
        background-color: #e5e7eb; /* gray-200 */
        color: #9ca3af;            /* gray-400-ish text */
    }

    /* Tray cells that cannot be placed because of academic term mismatch */
    #coursesTray td.tray-term-disabled {
        background-color: #d1d5db; /* gray-300, darker than tray-used */
        color: #6b7280;            /* gray-500-ish text */
        cursor: not-allowed;
    }

    /* Optional: keep the term badge readable on disabled cells */
    #coursesTray td.tray-term-disabled .term-badge {
        background-color: #f9fafb;
        color: #4b5563;
    }

    /* Tray cells that have reached their max class days in this term */
    #coursesTray td.tray-days-exhausted {
        background-color: #9ca3af; /* darker gray than tray-term-disabled */
        color: #111827;            /* gray-900 for contrast */
        cursor: not-allowed;
    }

    #coursesTray td.tray-days-exhausted .term-badge {
        background-color: #f9fafb;
        color: #1f2937;
    }
    /* Show drag cursor on any draggable timetable/tray cell */
    .timetable-editor td[draggable="true"],
    #coursesTray td[draggable="true"] {
        cursor: grab;
    }
    .timetable-editor td[draggable="true"]:active,
    #coursesTray td[draggable="true"]:active {
        cursor: grabbing;
    }

    /* Top badge in tray cells for placed/required class days (x/y) */
    #coursesTray td .classdays-badge {
        position: absolute;
        top: 4px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 11px;
        line-height: 1;
        padding: 2px 8px;
        border-radius: 9999px;
        background-color: #f9fafb;
        border: 1px solid #e5e7eb;
        color: #111827; /* default text color (not complete) */
        white-space: nowrap;
        pointer-events: none;
    }

    /* When x == y, text becomes green */
    #coursesTray td .classdays-badge.completed {
        color: #16a34a; /* green-600 */
    }

    /* Make tray cells a bit taller so top badge doesn't overlap text */
    #coursesTray td.tray-cell {
        padding-top: 1.5rem;  /* extra for top badge */
        padding-bottom: 1.75rem; /* you already had this; keeping it */
    }





    #timetableContextMenu {
        position: fixed;
        z-index: 9999;
        background: #ffffff;
        border-radius: 0.25rem;
        box-shadow:
            0 10px 15px -3px rgba(0, 0, 0, 0.1),
            0 4px 6px -4px rgba(0, 0, 0, 0.1);
        font-size: 0.875rem;
        color: #111827;
        padding: 0.25rem 0;
        min-width: 160px;
        display: none;
    }

    #timetableContextMenu ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    #timetableContextMenu li {
        padding: 0.35rem 0.75rem;
        cursor: pointer;
        white-space: nowrap;
    }

    #timetableContextMenu li:hover {
        background-color: #f3f4f6;
    }

    #timetableContextMenu hr {
        border: none;
        border-top: 1px solid #e5e7eb;
        margin: 0.25rem 0;
    }


</style>

<script>
    (function () {
        // --- Auto-retract tray when dragging a cell out of it ---
        let isDraggingFromTray = false;

        // Track where the drag started
        document.addEventListener('dragstart', function (e) {
            const cell = e.target.closest('td');
            if (!cell) return;

            // If the dragged cell lives inside the tray, mark it
            const trayEl = document.getElementById('coursesTray');
            if (trayEl && trayEl.contains(cell)) {
                isDraggingFromTray = true;
            } else {
                isDraggingFromTray = false;
            }
        });

        // Clear state when drag ends (anywhere)
        document.addEventListener('dragend', function () {
            isDraggingFromTray = false;
        });

        // When a drag that started in the tray actually leaves the tray root,
        // tell Alpine to close the tray.
        document.addEventListener('DOMContentLoaded', function () {
            const trayEl = document.getElementById('coursesTray');
            if (!trayEl) return;

            trayEl.addEventListener('dragleave', function (e) {
                if (!isDraggingFromTray) return;

                const related = e.relatedTarget;
                // If we're still going to another element INSIDE the tray, ignore.
                if (related && trayEl.contains(related)) return;

                // We left the tray area during a tray-origin drag: retract.
                window.dispatchEvent(new CustomEvent('courses-tray:retract'));
            });
        });


        const sessionGroups = window.sessionGroupsData || [];

        // --- Academic term helpers for courses/sessions ---
        // Accepts a CourseSession object (preferred) but also works
        // if you accidentally pass just the Course.
        // Returns { raw, badgeLabel, termIndex }
        // termIndex: 0 => 1st term, 1 => 2nd term, null => semestral / always allowed
        function getCourseTermInfo(sessionOrCourse) {
            if (!sessionOrCourse) {
                return { raw: '', badgeLabel: '', termIndex: null };
            }

            // If it's a CourseSession, pull from session.academic_term first,
            // then fall back to session.course.academic_term.
            let rawVal;
            if ('course' in sessionOrCourse || 'academic_term' in sessionOrCourse) {
                const session = sessionOrCourse;
                const course  = session.course || {};
                rawVal = (session.academic_term || course.academic_term || '');
            } else {
                // Fallback: plain Course object passed in
                const course = sessionOrCourse;
                rawVal = (course.academic_term || '');
            }

            rawVal = rawVal.toString().toLowerCase().trim();

            let badgeLabel = '';
            let termIndex  = null; // semestral / always allowed by default

            if (rawVal.startsWith('1')) {
                badgeLabel = '1ST';
                termIndex  = 0;
            } else if (rawVal.startsWith('2')) {
                badgeLabel = '2ND';
                termIndex  = 1;
            } else if (rawVal.includes('sem')) {
                badgeLabel = 'SEM';
                termIndex  = null;
            }

            return { raw: rawVal, badgeLabel, termIndex };
        }


        function getSessionAcademicTerm(session) {
            if (!session) return '';

            // prefer CourseSession.academic_term; fall back to Course.academic_term if present
            let term = session.academic_term;
            if (!term && session.course) {
                term = session.course.academic_term;
            }
            if (!term) return '';

            return String(term).toLowerCase(); // e.g. "1st", "2nd", "semestral"
        }

        function isSessionAllowedInActiveTerm(session) {
            const term = getSessionAcademicTerm(session);

            // empty / unknown / semestral => allowed in both
            if (!term || term === 'semestral' || term === 'sem') {
                return true;
            }

            // 1st-term only
            if (term === '1st' || term === 'first' || term === '1') {
                return activeTermIndex === 0;
            }

            // 2nd-term only
            if (term === '2nd' || term === 'second' || term === '2') {
                return activeTermIndex === 1;
            }

            // anything else, be permissive
            return true;
        }

        // ----- SESSION GROUP COLOR PICKER (same palette as session-group index) -----

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

        const CSRF_TOKEN = @json(csrf_token());

        let colorOverlay = null;
        let colorOverlayGrid = null;
        let colorActiveWrapper = null;

        function createColorOverlay() {
            colorOverlay = document.createElement('div');
            colorOverlay.style.position = 'fixed';
            colorOverlay.style.inset = '0';
            colorOverlay.style.background = 'rgba(0, 0, 0, 0.35)';
            colorOverlay.style.display = 'none';
            colorOverlay.style.alignItems = 'center';
            colorOverlay.style.justifyContent = 'center';
            colorOverlay.style.zIndex = '9999';

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

            const title = document.createElement('div');
            title.textContent = 'Choose session group color';
            title.style.fontSize = '0.875rem';
            title.style.fontWeight = '600';

            const closeBtn = document.createElement('button');
            closeBtn.type = 'button';
            closeBtn.textContent = 'Close';
            closeBtn.style.fontSize = '0.75rem';
            closeBtn.style.padding = '0.25rem 0.5rem';
            closeBtn.style.borderRadius = '0.25rem';
            closeBtn.style.border = '1px solid #e5e7eb';
            closeBtn.style.background = '#f3f4f6';
            closeBtn.addEventListener('click', hideColorOverlay);

            header.appendChild(title);
            header.appendChild(closeBtn);

            colorOverlayGrid = document.createElement('div');
            colorOverlayGrid.style.display = 'grid';
            colorOverlayGrid.style.gridTemplateColumns = 'repeat(8, minmax(0, 1fr))';
            colorOverlayGrid.style.gap = '0.25rem';

            modal.appendChild(header);
            modal.appendChild(colorOverlayGrid);
            colorOverlay.appendChild(modal);

            colorOverlay.addEventListener('click', function (e) {
                if (e.target === colorOverlay) {
                    hideColorOverlay();
                }
            });

            document.body.appendChild(colorOverlay);
        }

        function ensureColorOverlay() {
            if (!colorOverlay) {
                createColorOverlay();
            }
            return colorOverlay;
        }

        function hideColorOverlay() {
            if (!colorOverlay) return;
            colorOverlay.style.display = 'none';
            colorActiveWrapper = null;
        }

        function showColorOverlay() {
            ensureColorOverlay();
            colorOverlay.style.display = 'flex';
        }

        function setGroupColorOnWrapper(wrapperEl, hex) {
            wrapperEl.dataset.currentColor = hex;

            const preview = wrapperEl.querySelector('.sg-color-display');
            if (preview) {
                preview.style.backgroundColor = hex;
            }
        }

        function applyGroupColor(groupIndex, hex) {
            // update in-memory sessionGroups
            const group = sessionGroups[groupIndex];
            if (group) {
                group.session_color = hex;
            }

            // update courseMetaById colors for this group
            Object.keys(courseMetaById).forEach(function (id) {
                const meta = courseMetaById[id];
                if (meta && meta.groupIndex === groupIndex) {
                    meta.color = hex;
                }
            });

            // rebuild tray + repaint canvas with new colors
            buildTray();
            renderCanvas();
        }

        function saveGroupColor(wrapperEl, hex) {
            const updateUrl = wrapperEl.dataset.updateUrl;
            const groupIndex = parseInt(wrapperEl.dataset.groupIndex, 10);

            if (!updateUrl || Number.isNaN(groupIndex)) {
                console.error('Missing updateUrl or groupIndex on wrapperEl');
                return;
            }
            if (!CSRF_TOKEN) {
                console.error('Missing CSRF token meta tag');
                return;
            }

            // Laravel-friendly form payload
            const formData = new URLSearchParams();
            formData.append('_token', CSRF_TOKEN);
            formData.append('_method', 'PATCH');
            formData.append('session_color', hex);

            fetch(updateUrl, {
                method: 'POST', // use POST + _method=PATCH for Laravel
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                },
                body: formData.toString(),
                credentials: 'same-origin'
            })
                .then(function (res) {
                    if (!res.ok) {
                        console.error('Failed to save session_color, status:', res.status);
                    }
                    return res.json().catch(function () { return null; });
                })
                .then(function (data) {
                    // Backend confirmed; update UI + in-memory structures
                    const newColor = (data && data.session_color) ? data.session_color : hex;
                    setGroupColorOnWrapper(wrapperEl, newColor);
                    applyGroupColor(groupIndex, newColor);
                })
                .catch(function (err) {
                    console.error('Error saving session_color', err);
                });
        }



        function openGroupColorPicker(wrapperEl) {
            colorActiveWrapper = wrapperEl;
            ensureColorOverlay();
            colorOverlayGrid.innerHTML = '';

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
                    // let backend confirm, then UI is updated in saveGroupColor()
                    saveGroupColor(wrapperEl, hex);
                    hideColorOverlay();
                });

                colorOverlayGrid.appendChild(swatch);
            });

            showColorOverlay();
        }

        function attachColorPickerToWrapper(wrapperEl) {
            const btn = wrapperEl.querySelector('.sg-color-btn');
            if (!btn) return;

            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                openGroupColorPicker(wrapperEl);
            });
        }


        // meta for each CourseSession
        const courseMetaById = {}; // sessionId -> { label, blocks, groupIndex }

        // placements per (term, day) view:
        // key = `${termIndex}-${dayIndex}` -> { sessionId -> { col, topRow, blocks } }
        const placementsByView = {};
        let placements = {}; // points to placementsByView for the active view

        // total class-day usage per CourseSession:
        // classDayUsageBySessionId[sessionId] = { 0: Set(dayIndex), 1: Set(dayIndex) }
        let classDayUsageBySessionId = {};

        // locked courses (by CourseSession id) - global across all views
        const lockedSessions = new Set();

        // sessions that are in conflict in the current view (same group, same timeframe)
        let conflictSessionIds = new Set();

        // sessions that are misaligned across timetables (same group+course, different timeslots)
        let alignmentIssueSessionIds = new Set();
        // alignmentTargetRowByViewAndSession[viewKey][sessionId] = targetRow
        let alignmentTargetRowByViewAndSession = {};
        const alignmentTargetBlocksBySessionId = {};

        // active term/day (term: 0 = 1st, 1 = 2nd; day: 0..5 = Mon..Sat)
        let activeTermIndex = 0;
        let activeDayIndex  = 0;

        // canvas dimensions & references
        let canvasRows = 0;
        let canvasCols = 0;
        let canvasBody = null;

        // drag state
        let dragState = null;

        // context menu
        let contextMenuEl = null;
        let contextTarget = null; // { sessionId, from: 'tray'|'canvas' }

        // ---------- TERM/DAY VIEW HELPERS ----------

        function getCurrentViewKey() {
            return activeTermIndex + '-' + activeDayIndex;
        }

        function ensureCurrentViewPlacements() {
            const key = getCurrentViewKey();
            if (!placementsByView[key]) {
                placementsByView[key] = {};
            }
            placements = placementsByView[key];
        }

        // Count how many *days* this session is placed in a given term (0 = 1st, 1 = 2nd)
        // x in "x / y" badge.
        function getPlacementCountForSessionInTerm(sessionId, termIndex) {
            const sid = String(sessionId);
            let count = 0;

            // We have dayIndex = 0..5 (Mon..Sat)
            for (let day = 0; day <= 5; day++) {
                const key  = termIndex + '-' + day;
                const view = placementsByView[key];
                if (view && view[sid]) {
                    count++;
                }
            }
            return count;
        }


        function updateTermButtonsUI() {
            const termButtons = document.querySelectorAll('.timetable-editor .term-button');
            termButtons.forEach(btn => {
                const termIdx = parseInt(btn.dataset.termIndex, 10);
                if (Number.isNaN(termIdx)) return;

                const isActive = termIdx === activeTermIndex;

                // remove color-specific classes only
                btn.classList.remove('bg-red-700', 'text-white', 'hover:bg-red-800');
                btn.classList.remove('bg-gray-200', 'text-gray-700', 'hover:bg-gray-300');

                if (isActive) {
                    btn.classList.add('bg-red-700', 'text-white', 'hover:bg-red-800');
                } else {
                    btn.classList.add('bg-gray-200', 'text-gray-700', 'hover:bg-gray-300');
                }
            });
        }

        function updateDayButtonsUI() {
            const dayButtons = document.querySelectorAll('.timetable-editor .day-button');
            dayButtons.forEach(btn => {
                const dayIdx = parseInt(btn.dataset.dayIndex, 10);
                if (Number.isNaN(dayIdx)) return;

                const isActive = dayIdx === activeDayIndex;

                // only toggle base bg/text colors; keep hover/shadow classes as-is
                btn.classList.remove('bg-red-700', 'text-white');
                btn.classList.remove('bg-gray-200', 'text-gray-700');

                if (isActive) {
                    btn.classList.add('bg-red-700', 'text-white');
                } else {
                    btn.classList.add('bg-gray-200', 'text-gray-700');
                }
            });
        }

        function switchToView(termIndex, dayIndex) {
            activeTermIndex = termIndex;
            activeDayIndex  = dayIndex;

            ensureCurrentViewPlacements();
            updateTermButtonsUI();
            updateDayButtonsUI();

            // tray grey-out state depends on the active (term, day)
            buildTray();

            clearCanvasPreviews();
            renderCanvas();
        }



        function initTermDayControls() {
            // term buttons
            const termButtons = document.querySelectorAll('.timetable-editor .term-button');
            termButtons.forEach(btn => {
                btn.addEventListener('click', function () {
                    const idx = parseInt(btn.dataset.termIndex, 10);
                    if (Number.isNaN(idx)) return;
                    if (idx === activeTermIndex) return;
                    switchToView(idx, activeDayIndex);
                });
            });

            // day buttons
            const dayButtons = document.querySelectorAll('.timetable-editor .day-button');
            dayButtons.forEach(btn => {
                btn.addEventListener('click', function () {
                    const idx = parseInt(btn.dataset.dayIndex, 10);
                    if (Number.isNaN(idx)) return;
                    if (idx === activeDayIndex) return;
                    switchToView(activeTermIndex, idx);
                });
            });

            // initial visual state
            updateTermButtonsUI();
            updateDayButtonsUI();
        }

        // ---------- CONFLICT COMPUTATION ----------

        function recomputeConflicts() {
            conflictSessionIds = new Set();

            const rowsCount = canvasRows;
            if (!rowsCount || !placements) return;

            // For each row (timeframe), see if any session group appears in more than one room
            for (let r = 0; r < rowsCount; r++) {
                const groupToSessions = new Map(); // groupIndex -> Set(sessionId)

                for (const sessionId of Object.keys(placements)) {
                    const p = placements[sessionId];
                    const meta = courseMetaById[sessionId];
                    if (!p || !meta) continue;

                    const g = meta.groupIndex;
                    if (g === undefined || g === null) continue;

                    if (r < p.topRow || r >= p.topRow + p.blocks) continue;

                    let set = groupToSessions.get(g);
                    if (!set) {
                        set = new Set();
                        groupToSessions.set(g, set);
                    }
                    set.add(sessionId);
                }

                // any group with more than 1 session in this row = conflict
                for (const set of groupToSessions.values()) {
                    if (set.size > 1) {
                        set.forEach(id => conflictSessionIds.add(id));
                    }
                }
            }
        }

        // ---------- ALIGNMENT COMPUTATION (ACROSS ALL TERM/DAY VIEWS) ----------

        function recomputeAlignmentIssues() {
            alignmentIssueSessionIds = new Set();
            alignmentTargetRowByViewAndSession = {};

            function setTargetRow(viewKey, sessionId, targetRow) {
                if (!alignmentTargetRowByViewAndSession[viewKey]) {
                    alignmentTargetRowByViewAndSession[viewKey] = {};
                }
                alignmentTargetRowByViewAndSession[viewKey][sessionId] = targetRow;
            }

            // Group placements by (groupIndex + courseLabel)
            const groupCoursePlacements = new Map();

            Object.keys(placementsByView).forEach(function (viewKey) {
                const viewPlacements = placementsByView[viewKey];
                if (!viewPlacements) return;

                const [termStr, dayStr] = viewKey.split('-');
                const termIndex = parseInt(termStr, 10) || 0;
                const dayIndex  = parseInt(dayStr, 10) || 0;

                Object.keys(viewPlacements).forEach(function (sessionId) {
                    const p = viewPlacements[sessionId];
                    const meta = courseMetaById[sessionId];
                    if (!p || !meta) return;

                    const gIdx = meta.groupIndex;
                    const courseLabel = meta.courseLabel || '';
                    if (gIdx === undefined || gIdx === null) return;

                    const key = gIdx + '|' + courseLabel;

                    let arr = groupCoursePlacements.get(key);
                    if (!arr) {
                        arr = [];
                        groupCoursePlacements.set(key, arr);
                    }
                    arr.push({
                        sessionId,
                        viewKey,
                        topRow: p.topRow,
                        termIndex,
                        dayIndex
                    });
                });
            });

            // Helper: nearest-day ordering
            function viewDistance(a, b) {
                const termDiff = Math.abs(a.termIndex - b.termIndex);
                const dayDiff  = Math.abs(a.dayIndex  - b.dayIndex);
                return termDiff * 10 + dayDiff; // prioritize term difference heavily
            }

            // Analyze misalignment for each course
            groupCoursePlacements.forEach(function (arr) {
                if (!arr || arr.length <= 1) return;

                // Sort chronologically (earliest term, then day)
                const sorted = arr.slice().sort(function (a, b) {
                    if (a.termIndex !== b.termIndex) return a.termIndex - b.termIndex;
                    return a.dayIndex - b.dayIndex;
                });

                const earliest = sorted[0];

                // Check if ANY day has different row
                const anyDifferentRow = sorted.some(function (entry) {
                    return entry.topRow !== earliest.topRow;
                });
                if (!anyDifferentRow) return; // all aligned

                // For non-earliest days: target = earliest.topRow
                for (let i = 1; i < sorted.length; i++) {
                    const entry = sorted[i];
                    if (entry.topRow !== earliest.topRow) {
                        alignmentIssueSessionIds.add(entry.sessionId);
                        setTargetRow(entry.viewKey, entry.sessionId, earliest.topRow);
                    }
                }

                // For the earliest placement itself:
                // highlight the closest misaligned neighbor
                const candidates = sorted.slice(1).filter(e => e.topRow !== earliest.topRow);
                if (candidates.length > 0) {
                    let best = candidates[0];
                    let bestDist = viewDistance(earliest, best);

                    for (let i = 1; i < candidates.length; i++) {
                        const cand = candidates[i];
                        const d = viewDistance(earliest, cand);
                        if (d < bestDist) {
                            bestDist = d;
                            best = cand;
                        }
                    }

                    alignmentIssueSessionIds.add(earliest.sessionId);
                    setTargetRow(earliest.viewKey, earliest.sessionId, best.topRow);
                }
            });
        }

        // ---------- CLASS-DAY USAGE COMPUTATION (across all term/day views) ----------

        function recomputeClassDayUsage() {
            classDayUsageBySessionId = {};

            Object.keys(placementsByView).forEach(function (viewKey) {
                const viewPlacements = placementsByView[viewKey];
                if (!viewPlacements) return;

                const parts    = viewKey.split('-');
                const termIndex = parseInt(parts[0], 10);
                const dayIndex  = parseInt(parts[1], 10);

                if (!Number.isFinite(termIndex) || !Number.isFinite(dayIndex)) {
                    return;
                }

                Object.keys(viewPlacements).forEach(function (sessionId) {
                    let perSession = classDayUsageBySessionId[sessionId];
                    if (!perSession) {
                        perSession = classDayUsageBySessionId[sessionId] = {
                            0: new Set(),
                            1: new Set()
                        };
                    }

                    if (termIndex === 0 || termIndex === 1) {
                        perSession[termIndex].add(dayIndex);
                    }
                });
            });
        }

        function getUsedClassDaysInTerm(sessionId, termIndex) {
            const perSession = classDayUsageBySessionId[sessionId];
            if (!perSession) return 0;
            const set = perSession[termIndex];
            return set ? set.size : 0;
        }


        // ---------- ALIGNMENT ROW HIGHLIGHT HELPERS ----------

        function clearAlignmentRowHighlight() {
            if (!canvasBody) return;
            for (let r = 0; r < canvasRows; r++) {
                const tr = canvasBody.rows[r];
                if (!tr) continue;
                for (let c = 0; c < tr.cells.length; c++) {
                    tr.cells[c].classList.remove('alignment-row-highlight');
                }
            }
        }

        function applyAlignmentRowHighlight(startRow, blocks) {
            if (!canvasBody) return;

            const rowsCount = canvasRows;
            const bandStart = Math.max(0, startRow);
            const bandEnd   = Math.min(rowsCount, startRow + (blocks || 1));

            for (let r = bandStart; r < bandEnd; r++) {
                const tr = canvasBody.rows[r];
                if (!tr) continue;
                for (let c = 0; c < tr.cells.length; c++) {
                    tr.cells[c].classList.add('alignment-row-highlight');
                }
            }
        }



        function handleAlignmentMouseEnter(e) {
            const td = e.currentTarget;
            const sessionId = td.dataset.sessionId;
            if (!sessionId) return;

            const viewKey   = getCurrentViewKey();
            const mapForView = alignmentTargetRowByViewAndSession[viewKey] || {};
            const targetRow  = mapForView[sessionId];

            const meta   = courseMetaById[sessionId];
            const blocks = meta ? meta.blocks : 1;

            if (typeof targetRow === 'number' && targetRow >= 0 && targetRow < canvasRows) {
                clearAlignmentRowHighlight();
                applyAlignmentRowHighlight(targetRow, blocks);
            }
        }




        function handleAlignmentMouseLeave() {
            clearAlignmentRowHighlight();
        }


        document.addEventListener('DOMContentLoaded', function () {
            // 1) Accept initial placements from backend, if present
            if (window.initialPlacementsByView) {
                Object.keys(window.initialPlacementsByView).forEach(function (viewKey) {
                    placementsByView[viewKey] = window.initialPlacementsByView[viewKey] || {};
                });
            }

            // 2) Existing initialization
            buildTray();
            initCanvas();

            // Ensure current view has a placements object
            ensureCurrentViewPlacements();

            // 3) Default view: 1st Term, Monday
            switchToView(0, 0);
        });


        // ---------- TRAY RENDERING ----------

        function buildTray() {
            const container = document.getElementById('sessionGroupsContainer');
            if (!container) return;

            // --- remember vertical scroll of the whole tray ---
            const trayRoot = document.getElementById('coursesTray');
            const previousTrayScrollTop = trayRoot ? trayRoot.scrollTop : 0;
            // --------------------------------------------------

            // --- remember horizontal scroll per session group before rebuild ---
            const previousScrollLeftByGroupId = {};
            container.querySelectorAll('.tray-group').forEach(groupEl => {
                const groupId = groupEl.dataset.sessionGroupId;
                if (!groupId) return;
                const scrollWrapper = groupEl.querySelector('.session-table-wrapper');
                if (scrollWrapper) {
                    previousScrollLeftByGroupId[groupId] = scrollWrapper.scrollLeft;
                }
            });
            // -------------------------------------------------------------------

            container.innerHTML = '';

            sessionGroups.forEach((group, groupIndex) => {
                const wrapper = document.createElement('div');
                wrapper.className = 'border rounded-lg shadow-sm bg-gray-50 tray-group';

                // identify session group + current color (for color picker)
                wrapper.dataset.sessionGroupId = group.id;
                wrapper.dataset.groupIndex = groupIndex;
                wrapper.dataset.currentColor = group.session_color || '';
                wrapper.dataset.updateUrl = group.update_color_url || '';

                // header: PROGRAM_ABBR SESSION_NAME YEAR Year
                const header = document.createElement('div');
                header.className = 'tray-group-header flex items-center justify-between px-4 py-2 bg-gray-100 border-b';

                const title = document.createElement('span');

                const program = group.academic_program || {};
                const programAbbr = program.program_abbreviation || 'Unknown';
                const sessionName = group.session_name || '';
                const yearLevel = group.year_level != null ? String(group.year_level) : '';

                let groupTitle = programAbbr;
                if (sessionName) {
                    groupTitle += ' ' + sessionName;
                }
                if (yearLevel) {
                    groupTitle += ' ' + yearLevel + ' Year';
                }

                const groupTitleFull = groupTitle.trim();
                const groupColor = group.session_color || '';

                title.textContent = groupTitleFull;
                title.className = 'font-semibold text-gray-700';
                header.appendChild(title);

                const controls = document.createElement('div');
                controls.className = 'group-color-controls flex items-center space-x-2';

                const colorDisplay = document.createElement('div');
                colorDisplay.className = 'group-color-display sg-color-display w-4 h-4 rounded border border-gray-400';

                if (groupColor) {
                    colorDisplay.style.backgroundColor = groupColor;
                }

                controls.appendChild(colorDisplay);

                const colorBtn = document.createElement('button');
                colorBtn.type = 'button';
                colorBtn.className = 'group-color-open-btn sg-color-btn text-xs px-2 py-1 rounded border border-gray-300 bg-white';
                colorBtn.textContent = 'Color';

                controls.appendChild(colorBtn);

                header.appendChild(controls);
                wrapper.appendChild(header);

                // body: mini grid with vertical spans based on class_hours
                const scrollWrapper = document.createElement('div');
                scrollWrapper.className = 'session-table-wrapper overflow-x-auto';

                const tbl = document.createElement('table');
                tbl.className = 'session-table w-full table-fixed border-collapse';

                const tbody = document.createElement('tbody');

                const rawSessions = group.course_sessions || group.courseSessions || [];

                if (!rawSessions.length) {
                    const tr = document.createElement('tr');
                    const td = document.createElement('td');
                    td.className = 'border px-3 py-2 text-xs text-gray-400 italic';
                    td.textContent = 'No sessions';
                    tr.appendChild(td);
                    tbody.appendChild(tr);
                } else {
                    // Step 1: compute blocks (height) for each session
                    const sessions = rawSessions.map((session) => {
                        const course = session.course || {};
                        let hours = parseFloat(course.class_hours);
                        if (!Number.isFinite(hours) || hours <= 0) {
                            hours = 1; // default 1 hour
                        }
                        const blocks = Math.max(1, Math.round(hours * 2)); // 1 hour = 2 x 30-min
                        return { session, blocks };
                    });

                    const cols = sessions.length;
                    let rowsCount = 1;
                    sessions.forEach(sb => {
                        if (sb.blocks > rowsCount) rowsCount = sb.blocks;
                    });

                    // Step 2: backing grid [row][col]
                    const grid = Array.from({ length: rowsCount }, () =>
                        Array.from({ length: cols }, () => null)
                    );

                    // place each session as vertical run starting at row 0 of its column
                    sessions.forEach((sb, colIdx) => {
                        for (let r = 0; r < sb.blocks && r < rowsCount; r++) {
                            grid[r][colIdx] = sb.session;
                        }
                    });

                    // Step 3: compute span info
                    const spanInfo = Array.from({ length: rowsCount }, () =>
                        Array.from({ length: cols }, () => ({
                            render: true,
                            rowspan: 1,
                            session: null,
                        }))
                    );

                    for (let c = 0; c < cols; c++) {
                        let r = 0;
                        while (r < rowsCount) {
                            const cellSession = grid[r][c];
                            if (!cellSession) {
                                r++;
                                continue;
                            }

                            const id = cellSession.id;
                            let end = r + 1;
                            while (
                                end < rowsCount &&
                                grid[end][c] &&
                                grid[end][c].id === id
                                ) {
                                end++;
                            }

                            const runLen = end - r;
                            spanInfo[r][c].rowspan = runLen;
                            spanInfo[r][c].session = cellSession;

                            for (let rr = r + 1; rr < end; rr++) {
                                spanInfo[rr][c].render = false;
                                spanInfo[rr][c].session = null;
                            }

                            r = end;
                        }
                    }

                    // Step 4: render rows/cols using spanInfo
                    tbl.style.width = (cols * 80) + 'px';

                    for (let r = 0; r < rowsCount; r++) {
                        const tr = document.createElement('tr');

                        for (let c = 0; c < cols; c++) {
                            const info = spanInfo[r][c];
                            if (!info.render) continue;

                            const td = document.createElement('td');
                            td.className = 'tray-cell border px-3 py-3 text-sm text-gray-700 bg-white';
                            td.dataset.groupIndex = groupIndex;
                            td.dataset.col = c;
                            td.dataset.row = r;

                            const sess = info.session;
                            if (sess) {
                                td.dataset.sessionId = sess.id;

                                const course = sess.course || {};
                                let courseLabel = 'Course #' + sess.id;
                                if (course.course_title || course.course_name) {
                                    courseLabel = course.course_title || course.course_name;
                                }

                                const termInfo = getCourseTermInfo(sess);

                                // --- total class days (y in x/y) ---
                                const lectureDays = parseInt(course.total_lecture_class_days, 10) || 0;
                                const labDays     = parseInt(course.total_laboratory_class_days, 10) || 0;
                                const totalClassDays = lectureDays + labDays;

                                // Store meta only once
                                if (!courseMetaById[sess.id]) {
                                    let hours = parseFloat(course.class_hours);
                                    if (!Number.isFinite(hours) || hours <= 0) {
                                        hours = 1;
                                    }
                                    const blocks = Math.max(1, Math.round(hours * 2));

                                    courseMetaById[sess.id] = {
                                        labelHTML: `
                                            <div class="text-xs font-semibold text-gray-600">${groupTitleFull}</div>
                                            <div class="text-sm text-gray-800">${courseLabel}</div>
                                        `,
                                        blocks,
                                        groupIndex,
                                        groupTitle: groupTitleFull,
                                        courseLabel: courseLabel,
                                        color: groupColor || null,

                                        // term info for both tray + canvas
                                        termIndex: termInfo.termIndex,             // 0 = 1st, 1 = 2nd, null = semestral
                                        termBadgeLabel: termInfo.badgeLabel || '', // e.g. "1ST", "2ND", "SEM"
                                        termKey:
                                            termInfo.termIndex === 0
                                                ? '1st'
                                                : (termInfo.termIndex === 1 ? '2nd' : 'sem'),

                                        // total class days for this course
                                        totalClassDays: totalClassDays
                                    };
                                } else if (courseMetaById[sess.id].totalClassDays == null) {
                                    // in case meta was created earlier without this field
                                    courseMetaById[sess.id].totalClassDays = totalClassDays;
                                }

                                const metaForSession = courseMetaById[sess.id];

                                // --- x in x/y: how many timetables (days) this session is placed in THIS term ---
                                let placedCount = 0;
                                if (metaForSession.totalClassDays > 0) {
                                    placedCount = getPlacementCountForSessionInTerm(sess.id, activeTermIndex);
                                }

                                // top badge: x/y
                                let classdaysBadgeHTML = '';
                                if (metaForSession.totalClassDays > 0) {
                                    const x = placedCount;
                                    const y = metaForSession.totalClassDays;
                                    const completed = x >= y;
                                    const completedClass = completed ? ' completed' : '';
                                    classdaysBadgeHTML = `
                                        <div class="classdays-badge${completedClass}">
                                            ${x}/${y}
                                        </div>
                                    `;
                                }

                                // term badge HTML (bottom)
                                const termBadgeHTML = termInfo.badgeLabel
                                    ? `
                                        <div class="mt-1 inline-flex items-center justify-center px-3 py-0.5 border border-gray-300 rounded-full text-[11px] uppercase tracking-wide bg-white/80 term-badge">
                                            ${termInfo.badgeLabel}
                                        </div>
                                      `
                                    : '';

                                // label inside tray cell (centered text)
                                td.innerHTML = `
                                    ${classdaysBadgeHTML}
                                    <div class="text-xs font-semibold text-gray-600 text-center">${groupTitleFull}</div>
                                    <div class="text-sm text-gray-800 text-center">${courseLabel}</div>
                                    <div class="mt-1 flex justify-center">${termBadgeHTML}</div>
                                `;

                                const currentKey = getCurrentViewKey();
                                const viewPlacements = placementsByView[currentKey] || {};
                                const isPlacedInCurrentView = !!viewPlacements[sess.id];

                                const termIndex = metaForSession.termIndex;
                                const isTermAllowed =
                                    termIndex === null || termIndex === activeTermIndex;

                                // --- state priority: term-disabled > locked > used > normal ---
                                if (!isTermAllowed) {
                                    td.classList.add('tray-term-disabled');
                                    td.draggable = false;
                                    const timetableLabel = activeTermIndex === 0 ? '1st' : '2nd';
                                    const courseLabelTerm = termIndex === 0 ? '1st' : (termIndex === 1 ? '2nd' : 'this');
                                    td.title = `Cannot place ${courseLabelTerm} term subjects on ${timetableLabel} term timetable.`;
                                } else if (lockedSessions.has(String(sess.id))) {
                                    td.classList.add('locked');
                                    td.draggable = false;
                                    if (groupColor) {
                                        td.style.backgroundColor = groupColor;
                                    }
                                } else if (isPlacedInCurrentView) {
                                    td.classList.add('tray-used');
                                    td.draggable = false;
                                } else {
                                    if (groupColor) {
                                        td.style.backgroundColor = groupColor;
                                    }
                                    td.draggable = true;
                                    td.addEventListener('dragstart', handleTrayDragStart);
                                    td.addEventListener('dragend', handleDragEnd);
                                }

                                // tray context menu + drag-over/drop
                                td.addEventListener('contextmenu', handleCellContextMenu);
                                td.addEventListener('dragover', handleTrayDragOver);
                                td.addEventListener('drop', handleTrayDrop);
                            } else {
                                td.textContent = '';
                            }

                            if (info.rowspan > 1) {
                                td.rowSpan = info.rowspan;
                                td.classList.add('merged');
                            }

                            tr.appendChild(td);
                        }

                        tbody.appendChild(tr);
                    }
                }

                tbl.appendChild(tbody);
                scrollWrapper.appendChild(tbl);
                wrapper.appendChild(scrollWrapper);
                attachColorPickerToWrapper(wrapper);
                container.appendChild(wrapper);

                // --- restore saved horizontal scroll for this group, if any ---
                const savedScrollLeft = previousScrollLeftByGroupId[String(group.id)];
                if (typeof savedScrollLeft === 'number') {
                    scrollWrapper.scrollLeft = savedScrollLeft;
                }
                // ---------------------------------------------------------------
            });

            // --- restore vertical scroll of the whole tray ---
            if (trayRoot) {
                trayRoot.scrollTop = previousTrayScrollTop;
            }
            // --------------------------------------------------
        }

        // ---------- CANVAS INIT & RENDERING ----------

        function initCanvas() {
            const table = document.querySelector('.timetable-editor table');
            if (!table) return;

            canvasBody = table.tBodies[0];
            if (!canvasBody) return;

            canvasRows = canvasBody.rows.length;
            const headerRow = table.tHead && table.tHead.rows[0];
            if (headerRow) {
                canvasCols = headerRow.cells.length - 1; // minus Time column
            } else {
                canvasCols = canvasBody.rows[0].cells.length - 1;
            }

            // make canvas cells receptive to drops
            for (let r = 0; r < canvasRows; r++) {
                const tr = canvasBody.rows[r];
                for (let c = 0; c < canvasCols; c++) {
                    const td = tr.cells[c + 1]; // +1 to skip Time col
                    td.dataset.row = r;
                    td.dataset.col = c;

                    td.addEventListener('dragover', handleCanvasDragOver);
                    td.addEventListener('drop', handleCanvasDrop);
                }
            }
        }

        function renderCanvas() {
            if (!canvasBody) return;

            // Recompute alignment (across all term/day views) and conflicts (within current view)
            recomputeAlignmentIssues();
            recomputeConflicts();

            // clear any previous alignment row highlight
            clearAlignmentRowHighlight();

            // Reset all cells
            for (let r = 0; r < canvasRows; r++) {
                const tr = canvasBody.rows[r];
                for (let c = 0; c < canvasCols; c++) {
                    const td = tr.cells[c + 1]; // skip Time col
                    td.textContent = '';
                    td.rowSpan = 1;
                    td.style.display = '';
                    td.style.backgroundColor = '';
                    td.draggable = false;
                    td.classList.remove(
                        'merged',
                        'preview-place',
                        'preview-swap',
                        'preview-invalid',
                        'locked',
                        'course-cell',
                        'alignment-row-highlight'
                    );
                    td.removeAttribute('title');
                    td.removeEventListener('dragstart', handleCanvasDragStart);
                    td.removeEventListener('dragend', handleDragEnd);
                    td.removeEventListener('contextmenu', handleCellContextMenu);
                    td.removeEventListener('mouseenter', handleAlignmentMouseEnter);
                    td.removeEventListener('mouseleave', handleAlignmentMouseLeave);
                    delete td.dataset.sessionId;
                    delete td.dataset.topRow;
                    delete td.dataset.blocks;
                }
            }

            // Draw all placements for the CURRENT view
            Object.keys(placements).forEach(sessionId => {
                const place = placements[sessionId];
                const meta = courseMetaById[sessionId];
                if (!place || !meta) return;

                const col = place.col;
                const topRow = place.topRow;
                const blocks = place.blocks;

                if (topRow < 0 || topRow >= canvasRows) return;

                const topTr = canvasBody.rows[topRow];
                if (!topTr) return;

                const topTd = topTr.cells[col + 1];
                if (!topTd) return;

                // hide underlying rows for merged block
                for (let r = topRow + 1; r < Math.min(canvasRows, topRow + blocks); r++) {
                    const tr = canvasBody.rows[r];
                    const td = tr.cells[col + 1];
                    td.style.display = 'none';
                }

                topTd.rowSpan = Math.min(blocks, canvasRows - topRow);

                // base label
                let contentHTML = meta.labelHTML;

                // separate conflict vs alignment
                const hasConflict  = conflictSessionIds.has(sessionId);
                const hasAlignment = alignmentIssueSessionIds.has(sessionId);

                if (hasConflict || hasAlignment) {
                    contentHTML += `
                        <div class="conflict-warning-icon">⚠</div>
                    `;

                    const messages = [];
                    if (hasConflict) {
                        messages.push(
                            "Conflict: this session group is double-scheduled in this timeframe (same group appearing in multiple rooms)."
                        );
                    }
                    if (hasAlignment) {
                        messages.push(
                            "Alignment: this course is placed at different timeslots across other timetables; consider aligning its time."
                        );
                    }
                    topTd.title = messages.join(" ");
                }


                // term badge at bottom of the cell
                if (meta.termBadgeLabel || meta.termLabel) {
                    const label = meta.termBadgeLabel || meta.termLabel;
                    const key   = meta.termKey ||
                        (meta.termIndex === 0 ? '1st' : (meta.termIndex === 1 ? '2nd' : 'sem'));

                    const termClass = key ? ` term-badge-${key}` : '';
                    contentHTML += `
                        <div class="term-badge${termClass}">${label}</div>
                    `;
                }

                topTd.innerHTML = contentHTML;

                if (meta.color) {
                    topTd.style.backgroundColor = meta.color;
                }

                // apply consistent course cell border
                topTd.classList.add('merged', 'course-cell');

                topTd.dataset.sessionId = sessionId;
                topTd.dataset.topRow = topRow;
                topTd.dataset.blocks = blocks;
                topTd.dataset.col = col;

                if (lockedSessions.has(String(sessionId))) {
                    topTd.classList.add('locked');
                    topTd.draggable = false;
                } else {
                    topTd.draggable = true;
                    topTd.addEventListener('dragstart', handleCanvasDragStart);
                    topTd.addEventListener('dragend', handleDragEnd);
                }

                topTd.addEventListener('contextmenu', handleCellContextMenu);

                // hover alignment highlight only for cells involved in alignment issues
                if (alignmentIssueSessionIds.has(sessionId)) {
                    topTd.addEventListener('mouseenter', handleAlignmentMouseEnter);
                    topTd.addEventListener('mouseleave', handleAlignmentMouseLeave);
                }
            });
        }




        // ---------- PREVIEW HELPERS ----------

        function clearCanvasPreviews() {
            if (canvasBody) {
                for (let r = 0; r < canvasRows; r++) {
                    const tr = canvasBody.rows[r];
                    for (let c = 0; c < canvasCols; c++) {
                        const td = tr.cells[c + 1];
                        td.classList.remove('preview-place', 'preview-swap', 'preview-invalid');
                    }
                }
            }
            // also clear tray "return" preview
            document
                .querySelectorAll('#coursesTray .session-table.session-return-preview')
                .forEach(tbl => tbl.classList.remove('session-return-preview'));
        }

        function applyPreviewBand(topRow, blocks, col, cls) {
            if (!canvasBody) return;
            const rowsCount = canvasRows;
            const start = Math.max(0, topRow);
            const end = Math.min(rowsCount, topRow + blocks);
            for (let r = start; r < end; r++) {
                const tr = canvasBody.rows[r];
                const td = tr.cells[col + 1];
                td.classList.add(cls);
            }
        }

        // ---------- ROW PICKING (from mouse Y) ----------

        function getCanvasRowFromEvent(e) {
            if (!canvasBody) return null;

            const tbodyRect = canvasBody.getBoundingClientRect();
            const y = e.clientY - tbodyRect.top;

            let accumulated = 0;
            for (let r = 0; r < canvasRows; r++) {
                const tr = canvasBody.rows[r];
                const h = tr.getBoundingClientRect().height;
                if (y < accumulated + h) {
                    return r;
                }
                accumulated += h;
            }

            return canvasRows > 0 ? canvasRows - 1 : null;
        }

        // ---------- CONTEXT MENU HELPERS ----------

        function ensureContextMenu() {
            if (contextMenuEl) return contextMenuEl;

            const el = document.createElement('div');
            el.id = 'timetableContextMenu';

            const ul = document.createElement('ul');
            el.appendChild(ul);

            document.body.appendChild(el);
            contextMenuEl = el;

            document.addEventListener('click', hideContextMenu);
            window.addEventListener('resize', hideContextMenu);
            document.addEventListener('scroll', hideContextMenu, true);

            return el;
        }

        function hideContextMenu() {
            if (!contextMenuEl) return;
            contextMenuEl.style.display = 'none';
            contextTarget = null;
        }

        function showContextMenu(x, y, target) {
            const menu = ensureContextMenu();
            const ul = menu.querySelector('ul');
            ul.innerHTML = '';
            contextTarget = target;

            const sessionId = String(target.sessionId);
            const from      = target.from;
            const isLocked  = lockedSessions.has(sessionId);

            const currentKey     = getCurrentViewKey();
            const viewPlacements = placementsByView[currentKey] || {};
            const isPlacedInCurrentView = !!viewPlacements[sessionId];

            // --- Canvas: Lock/Unlock + Remove from timetable ---
            if (from === 'canvas') {
                // Lock / Unlock item (canvas only)
                const lockItem = document.createElement('li');
                lockItem.textContent = isLocked ? 'Unlock course' : 'Lock course';
                lockItem.addEventListener('click', function () {
                    if (lockedSessions.has(sessionId)) {
                        lockedSessions.delete(sessionId);
                    } else {
                        lockedSessions.add(sessionId);
                    }
                    hideContextMenu();
                    buildTray();
                    renderCanvas();
                });
                ul.appendChild(lockItem);

                const hr = document.createElement('hr');
                ul.appendChild(hr);

                const removeItem = document.createElement('li');
                removeItem.textContent = 'Remove from timetable';
                removeItem.addEventListener('click', function () {
                    delete placements[sessionId];
                    lockedSessions.delete(sessionId);
                    hideContextMenu();
                    buildTray();
                    renderCanvas();
                });
                ul.appendChild(removeItem);

            } else if (from === 'tray' && isPlacedInCurrentView) {
                // --- Tray: ONLY "Return to tray" (no lock/unlock here) ---
                const returnItem = document.createElement('li');
                returnItem.textContent = 'Return to tray';
                returnItem.addEventListener('click', function () {
                    delete placements[sessionId];
                    lockedSessions.delete(sessionId);
                    hideContextMenu();
                    buildTray();
                    renderCanvas();
                });
                ul.appendChild(returnItem);
            } else {
                // Safety: nothing to show
                return;
            }

            // position menu
            menu.style.display = 'block';
            menu.style.left = x + 'px';
            menu.style.top = y + 'px';

            const rect = menu.getBoundingClientRect();
            let dx = 0;
            let dy = 0;
            if (rect.right > window.innerWidth) {
                dx = window.innerWidth - rect.right - 8;
            }
            if (rect.bottom > window.innerHeight) {
                dy = window.innerHeight - rect.bottom - 8;
            }
            if (dx || dy) {
                menu.style.left = (rect.left + dx) + 'px';
                menu.style.top  = (rect.top + dy) + 'px';
            }
        }



        function handleCellContextMenu(e) {
            e.preventDefault();
            hideContextMenu();

            const td = e.currentTarget;
            const sessionId = td.dataset.sessionId;
            if (!sessionId) return;

            const from = td.closest('.timetable-editor') ? 'canvas' : 'tray';

            // For TRAY cells: only show a context menu if the session
            // is already placed in the current view. Otherwise, NO menu.
            if (from === 'tray') {
                const currentKey      = getCurrentViewKey();
                const viewPlacements  = placementsByView[currentKey] || {};
                const isPlacedHere    = !!viewPlacements[sessionId];

                if (!isPlacedHere) {
                    // Nothing to do, don't open any menu.
                    return;
                }
            }

            showContextMenu(e.clientX, e.clientY, { sessionId, from });
        }


        // ---------- DRAG HELPERS ----------

        function handleTrayDragStart(e) {
            const td = e.currentTarget;
            const sessionId = td.dataset.sessionId;
            if (!sessionId || !courseMetaById[sessionId]) return;
            if (lockedSessions.has(String(sessionId))) return;

            dragState = {
                source: 'tray',
                sessionId: sessionId,
                blocks: courseMetaById[sessionId].blocks
            };

            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', sessionId);
        }

        function handleCanvasDragStart(e) {
            const td = e.currentTarget;
            const sessionId = td.dataset.sessionId;
            if (!sessionId || !placements[sessionId]) return;
            if (lockedSessions.has(String(sessionId))) return;

            const col = parseInt(td.dataset.col, 10);
            const topRow = parseInt(td.dataset.topRow, 10);
            const blocks = parseInt(td.dataset.blocks, 10);

            dragState = {
                source: 'canvas',
                sessionId,
                col,
                topRow,
                blocks
            };

            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', sessionId);
        }

        function handleDragEnd() {
            dragState = null;
            clearCanvasPreviews();
        }

        // canvas helper: which session occupies (row,col)?
        function getSessionIdAt(row, col) {
            for (const sessionId of Object.keys(placements)) {
                const p = placements[sessionId];
                if (!p) continue;
                if (p.col !== col) continue;
                if (row >= p.topRow && row < p.topRow + p.blocks) {
                    return sessionId;
                }
            }
            return null;
        }

        // evaluate swap between two canvas blocks
        function evaluateCanvasSwap(sessionIdA, sessionIdB) {
            const a = placements[sessionIdA];
            const b = placements[sessionIdB];
            if (!a || !b) return { ok: false };

            const rowsCount = canvasRows;
            const aBlocks = a.blocks;
            const bBlocks = b.blocks;

            const aTop = a.topRow;
            const bTop = b.topRow;
            const aCol = a.col;
            const bCol = b.col;

            if (lockedSessions.has(String(sessionIdA)) || lockedSessions.has(String(sessionIdB))) {
                return { ok: false };
            }

            // Ensure they fit in destination positions
            if (bTop + aBlocks > rowsCount || aTop + bBlocks > rowsCount) {
                return { ok: false };
            }

            // no other occupants in A-destination band on B column
            if (hasOtherOccupants(bCol, bTop, aBlocks, new Set([sessionIdB]))) {
                return { ok: false };
            }
            // no other occupants in B-destination band on A column
            if (hasOtherOccupants(aCol, aTop, bBlocks, new Set([sessionIdA]))) {
                return { ok: false };
            }

            return {
                ok: true,
                aNewTop: bTop,
                bNewTop: aTop
            };
        }

        function hasOtherOccupants(col, top, blocks, allowed) {
            const bandStart = top;
            const bandEnd = top + blocks;
            for (const sessionId of Object.keys(placements)) {
                if (allowed && allowed.has(sessionId)) continue;
                const p = placements[sessionId];
                if (!p || p.col !== col) continue;
                const pStart = p.topRow;
                const pEnd = p.topRow + p.blocks;
                if (pEnd <= bandStart || pStart >= bandEnd) continue;
                return true;
            }
            return false;
        }

        // ---------- TRAY DROP (canvas -> tray = reset/unlock) ----------

        function handleTrayDragOver(e) {
            if (!dragState || dragState.source !== 'canvas') return;
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';

            clearCanvasPreviews();
            const tbl = e.currentTarget.closest('table.session-table');
            if (tbl) {
                tbl.classList.add('session-return-preview');
            }
        }

        function handleTrayDrop(e) {
            if (!dragState || dragState.source !== 'canvas') return;
            e.preventDefault();

            clearCanvasPreviews();

            const sessionId = dragState.sessionId;
            if (sessionId) {
                delete placements[sessionId];
                lockedSessions.delete(String(sessionId));
                buildTray();
                renderCanvas();
            }

            dragState = null;
        }

        // ---------- CANVAS DRAGOVER / DROP ----------

        function handleCanvasDragOver(e) {
            if (!dragState) return;
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';

            const td = e.currentTarget.closest('td');
            if (!td) return;

            const row = getCanvasRowFromEvent(e);
            const col = parseInt(td.dataset.col, 10);
            if (!Number.isFinite(col) || row === null || row === undefined) return;

            clearCanvasPreviews();

            if (dragState.source === 'tray') {
                const blocks = dragState.blocks;
                const rowsCount = canvasRows;
                if (!rowsCount) return;

                let previewTop = row;
                if (previewTop + blocks > rowsCount) {
                    previewTop = rowsCount - blocks;
                }
                if (previewTop < 0) previewTop = 0;

                const evalResult = evaluateTrayPlacement(row, col, blocks, dragState.sessionId);
                let cls = 'preview-invalid';

                if (evalResult.ok) {
                    if (evalResult.displaced && evalResult.displaced.length > 0) {
                        cls = 'preview-swap'; // displacing existing band
                    } else {
                        cls = 'preview-place'; // clean place
                    }
                    if (Number.isFinite(evalResult.topRow)) {
                        previewTop = evalResult.topRow;
                    }
                }

                applyPreviewBand(previewTop, blocks, col, cls);
            } else if (dragState.source === 'canvas') {
                const { sessionId, col: origCol, blocks } = dragState;
                const rowsCount = canvasRows;
                if (!rowsCount) return;

                let previewTop = row;
                if (previewTop + blocks > rowsCount) {
                    previewTop = rowsCount - blocks;
                }
                if (previewTop < 0) previewTop = 0;

                let cls = 'preview-invalid';

                if (col === origCol) {
                    // slide up/down in same column
                    const result = evaluateSlide(sessionId, row);
                    if (result.ok) {
                        cls = 'preview-place';
                        if (Number.isFinite(result.topRow)) {
                            previewTop = result.topRow;
                        }
                    }
                } else {
                    // different column: either swap with target block or move into empty column
                    const targetSessionId = getSessionIdAt(row, col);
                    if (targetSessionId && targetSessionId !== sessionId) {
                        const swapRes = evaluateCanvasSwap(sessionId, targetSessionId);
                        if (swapRes.ok) {
                            cls = 'preview-swap';
                            previewTop = swapRes.aNewTop;
                        } else {
                            cls = 'preview-invalid';
                        }
                    } else {
                        const result = evaluateMoveToOtherColumn(sessionId, row, col);
                        if (result.ok) {
                            cls = 'preview-place';
                            if (Number.isFinite(result.topRow)) {
                                previewTop = result.topRow;
                            }
                        } else {
                            cls = 'preview-invalid';
                        }
                    }
                }

                applyPreviewBand(previewTop, blocks, col, cls);
            }
        }

        function handleCanvasDrop(e) {
            if (!dragState) return;
            e.preventDefault();

            const td = e.currentTarget.closest('td');
            if (!td) return;

            const row = getCanvasRowFromEvent(e);
            const col = parseInt(td.dataset.col, 10);
            if (!Number.isFinite(col) || row === null || row === undefined) return;

            clearCanvasPreviews();

            if (dragState.source === 'tray') {
                handleDropFromTray(row, col);
            } else if (dragState.source === 'canvas') {
                handleDropFromCanvas(row, col);
            }

            dragState = null;
            renderCanvas();
        }

        // ---------- BAND EVALUATION / DROP LOGIC ----------

        function handleDropFromTray(targetRow, targetCol) {
            const sessionId = dragState.sessionId;
            const blocks = dragState.blocks;
            if (!sessionId) return;

            const evalResult = evaluateTrayPlacement(targetRow, targetCol, blocks, sessionId);
            if (!evalResult.ok) return;

            evalResult.displaced.forEach(id => {
                delete placements[id];
            });

            placements[sessionId] = {
                col: targetCol,
                topRow: evalResult.topRow,
                blocks: blocks
            };

            // reflect "used in this timetable" state in the tray
            buildTray();
        }


        function evaluateTrayPlacement(targetRow, targetCol, blocks, sessionId) {
            const rowsCount = canvasRows;
            if (!rowsCount) return { ok: false };

            let topRow = targetRow;
            if (topRow + blocks > rowsCount) {
                topRow = rowsCount - blocks;
            }
            if (topRow < 0) topRow = 0;

            const bandStart = topRow;
            const bandEnd = topRow + blocks;
            const displaced = new Set();

            for (const id of Object.keys(placements)) {
                const p = placements[id];
                if (!p || p.col !== targetCol) continue;

                const pStart = p.topRow;
                const pEnd = p.topRow + p.blocks;

                if (pEnd <= bandStart || pStart >= bandEnd) continue;

                // band intersects this block
                if (lockedSessions.has(String(id))) {
                    // cannot displace/cut locked course
                    displaced.clear();
                    topRow = null;
                    break;
                }

                // only allow if fully inside band (no-cut)
                if (pStart < bandStart || pEnd > bandEnd) {
                    displaced.clear();
                    topRow = null;
                    break;
                } else {
                    displaced.add(id);
                }
            }

            if (topRow === null) {
                return { ok: false };
            }

            return { ok: true, topRow, displaced: Array.from(displaced) };
        }

        function handleDropFromCanvas(targetRow, targetCol) {
            const { sessionId, col, blocks } = dragState;
            if (!sessionId || !placements[sessionId]) return;

            if (targetCol === col) {
                // simple slide
                const result = evaluateSlide(sessionId, targetRow);
                if (!result.ok) return;
                placements[sessionId].topRow = result.topRow;
                return;
            }

            // cross-column: swap if hitting another block, else move
            const targetSessionId = getSessionIdAt(targetRow, targetCol);
            if (targetSessionId && targetSessionId !== sessionId) {
                const swapRes = evaluateCanvasSwap(sessionId, targetSessionId);
                if (!swapRes.ok) return;

                const a = placements[sessionId];
                const b = placements[targetSessionId];

                a.col = targetCol;
                a.topRow = swapRes.aNewTop;

                b.col = col;
                b.topRow = swapRes.bNewTop;
            } else {
                const result = evaluateMoveToOtherColumn(sessionId, targetRow, targetCol);
                if (!result.ok) return;

                placements[sessionId].col = targetCol;
                placements[sessionId].topRow = result.topRow;
            }
        }

        function evaluateSlide(sessionId, targetRow) {
            const place = placements[sessionId];
            if (!place) return { ok: false };

            const rowsCount = canvasRows;
            let newTop = targetRow;

            newTop = Math.max(0, Math.min(newTop, rowsCount - place.blocks));
            const bandStart = newTop;
            const bandEnd = newTop + place.blocks;

            for (const id of Object.keys(placements)) {
                if (id === sessionId) continue;
                const p = placements[id];
                if (!p || p.col !== place.col) continue;

                const pStart = p.topRow;
                const pEnd = p.topRow + p.blocks;

                if (pEnd <= bandStart || pStart >= bandEnd) continue;
                return { ok: false };
            }

            return { ok: true, topRow: newTop };
        }

        function evaluateMoveToOtherColumn(sessionId, targetRow, targetCol) {
            const place = placements[sessionId];
            if (!place) return { ok: false };

            const rowsCount = canvasRows;
            let newTop = targetRow;
            newTop = Math.max(0, Math.min(newTop, rowsCount - place.blocks));

            const bandStart = newTop;
            const bandEnd = newTop + place.blocks;

            for (const id of Object.keys(placements)) {
                if (id === sessionId) continue;
                const p = placements[id];
                if (!p || p.col !== targetCol) continue;

                const pStart = p.topRow;
                const pEnd = p.topRow + p.blocks;

                if (pEnd <= bandStart || pStart >= bandEnd) continue;
                return { ok: false };
            }

            return { ok: true, topRow: newTop };
        }
    })();
</script>











