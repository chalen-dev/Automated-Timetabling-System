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
    // Raw session groups + their courseSessions from Laravel
    window.sessionGroupsData = @json($sessionGroups);
</script>
<style>
    /* Make sure cells can show the corner icons */
    .timetable-editor td {
        position: relative;
    }

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
</style>

<script>
    (function () {
        const sessionGroups = window.sessionGroupsData || [];

        // meta for each CourseSession
        const courseMetaById = {}; // sessionId -> { label, blocks, groupIndex }
        // placements on the canvas: sessionId -> { col, topRow, blocks }
        const placements = {};

        // canvas dimensions & references
        let canvasRows = 0;
        let canvasCols = 0;
        let canvasBody = null;

        // drag state
        let dragState = null;

        document.addEventListener('DOMContentLoaded', function () {
            buildTray();
            initCanvas();
            renderCanvas();
        });

        // ---------- TRAY RENDERING ----------

        function buildTray() {
            const container = document.getElementById('sessionGroupsContainer');
            if (!container) return;

            container.innerHTML = '';

            sessionGroups.forEach((group, groupIndex) => {
                const wrapper = document.createElement('div');
                wrapper.className = 'border rounded-lg shadow-sm bg-gray-50 tray-group';

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

                title.textContent = groupTitle.trim();
                title.className = 'font-semibold text-gray-700';
                header.appendChild(title);

                const controls = document.createElement('div');
                controls.className = 'group-color-controls flex items-center space-x-2';

                const colorDisplay = document.createElement('div');
                colorDisplay.className = 'group-color-display w-4 h-4 rounded border border-gray-400';
                controls.appendChild(colorDisplay);

                const colorBtn = document.createElement('button');
                colorBtn.type = 'button';
                colorBtn.className = 'group-color-open-btn text-xs px-2 py-1 rounded border border-gray-300 bg-white';
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

                    // Step 3: compute span info like prototype
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
                    tbl.style.width = (cols * 80) + 'px'; // similar to prototype

                    for (let r = 0; r < rowsCount; r++) {
                        const tr = document.createElement('tr');

                        for (let c = 0; c < cols; c++) {
                            const info = spanInfo[r][c];
                            if (!info.render) continue;

                            const td = document.createElement('td');
                            td.className = 'tray-cell border px-3 py-2 text-sm text-gray-700 bg-white';
                            td.dataset.groupIndex = groupIndex;
                            td.dataset.col = c;
                            td.dataset.row = r;

                            const sess = info.session;
                            if (sess) {
                                td.dataset.sessionId = sess.id;

                                const course = sess.course || {};
                                let label = 'Course #' + sess.id;
                                if (course.course_title || course.course_name) {
                                    label = course.course_title || course.course_name;
                                }

                                td.textContent = label;

                                // store meta for this sessionId (if not already)
                                if (!courseMetaById[sess.id]) {
                                    let hours = parseFloat(course.class_hours);
                                    if (!Number.isFinite(hours) || hours <= 0) {
                                        hours = 1;
                                    }
                                    const blocks = Math.max(1, Math.round(hours * 2));
                                    courseMetaById[sess.id] = {
                                        label,
                                        blocks,
                                        groupIndex
                                    };
                                }

                                // make tray cell draggable
                                td.draggable = true;
                                td.addEventListener('dragstart', handleTrayDragStart);
                                td.addEventListener('dragend', handleDragEnd);
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
                container.appendChild(wrapper);
            });
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

            // reset all room cells: clear content, show them, remove rowspans & previews
            for (let r = 0; r < canvasRows; r++) {
                const tr = canvasBody.rows[r];
                for (let c = 0; c < canvasCols; c++) {
                    const td = tr.cells[c + 1]; // skip Time col
                    td.textContent = '';
                    td.rowSpan = 1;
                    td.style.display = '';
                    td.draggable = false;
                    td.classList.remove('merged', 'preview-place', 'preview-swap', 'preview-invalid');
                    td.removeEventListener('dragstart', handleCanvasDragStart);
                    td.removeEventListener('dragend', handleDragEnd);
                    delete td.dataset.sessionId;
                    delete td.dataset.topRow;
                    delete td.dataset.blocks;
                }
            }

            // draw each placement as a vertical merged cell
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

                // hide underlying cells
                for (let r = topRow + 1; r < Math.min(canvasRows, topRow + blocks); r++) {
                    const tr = canvasBody.rows[r];
                    const td = tr.cells[col + 1];
                    td.style.display = 'none';
                }

                topTd.rowSpan = Math.min(blocks, canvasRows - topRow);
                topTd.textContent = meta.label;
                topTd.classList.add('merged');
                topTd.draggable = true;
                topTd.dataset.sessionId = sessionId;
                topTd.dataset.topRow = topRow;
                topTd.dataset.blocks = blocks;
                // keep col info on the merged cell itself
                topTd.dataset.col = col;
                topTd.addEventListener('dragstart', handleCanvasDragStart);
                topTd.addEventListener('dragend', handleDragEnd);
            });
        }

        // ---------- PREVIEW HELPERS ----------

        function clearCanvasPreviews() {
            if (!canvasBody) return;
            for (let r = 0; r < canvasRows; r++) {
                const tr = canvasBody.rows[r];
                for (let c = 0; c < canvasCols; c++) {
                    const td = tr.cells[c + 1];
                    td.classList.remove('preview-place', 'preview-swap', 'preview-invalid');
                }
            }
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

        // ---------- ROW PICKING (from mouse Y, like prototype) ----------

        function getCanvasRowFromEvent(e) {
            if (!canvasBody) return null;

            const tbodyRect = canvasBody.getBoundingClientRect();
            const y = e.clientY - tbodyRect.top; // distance from top of tbody

            let accumulated = 0;
            for (let r = 0; r < canvasRows; r++) {
                const tr = canvasBody.rows[r];
                const h = tr.getBoundingClientRect().height;
                if (y < accumulated + h) {
                    return r;
                }
                accumulated += h;
            }

            // If somehow beyond, clamp to last row
            return canvasRows > 0 ? canvasRows - 1 : null;
        }


        // ---------- DRAG HANDLERS ----------

        function handleTrayDragStart(e) {
            const td = e.currentTarget;
            const sessionId = td.dataset.sessionId;
            if (!sessionId || !courseMetaById[sessionId]) return;

            dragState = {
                source: 'tray',
                sessionId: sessionId,
                blocks: courseMetaById[sessionId].blocks
            };

            e.dataTransfer.effectAllowed = 'move';
            // Firefox requires some data
            e.dataTransfer.setData('text/plain', sessionId);
        }

        function handleCanvasDragStart(e) {
            const td = e.currentTarget;
            const sessionId = td.dataset.sessionId;
            if (!sessionId || !placements[sessionId]) return;

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

        function handleCanvasDragOver(e) {
            if (!dragState) return;
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';

            const td = e.currentTarget.closest('td');
            if (!td) return;

            const row = getCanvasRowFromEvent(e);
            const col = parseInt(td.dataset.col, 10);
            if (!Number.isFinite(row) || !Number.isFinite(col)) return;

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
                        cls = 'preview-swap'; // displacing existing blocks
                    } else {
                        cls = 'preview-place'; // clean place
                    }
                    // align preview to the actual topRow used by evaluation
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

                let ok = false;
                if (col === origCol) {
                    const result = evaluateSlide(sessionId, row);
                    ok = result.ok;
                    if (result.ok && Number.isFinite(result.topRow)) {
                        previewTop = result.topRow;
                    }
                } else {
                    const result = evaluateMoveToOtherColumn(sessionId, row, col);
                    ok = result.ok;
                    if (result.ok && Number.isFinite(result.topRow)) {
                        previewTop = result.topRow;
                    }
                }

                const cls = ok ? 'preview-place' : 'preview-invalid';
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
            if (!Number.isFinite(row) || !Number.isFinite(col)) return;

            clearCanvasPreviews();

            if (dragState.source === 'tray') {
                handleDropFromTray(row, col);
            } else if (dragState.source === 'canvas') {
                handleDropFromCanvas(row, col);
            }

            dragState = null;
            renderCanvas();
        }

        // ---------- DROP LOGIC ----------

        function handleDropFromTray(targetRow, targetCol) {
            const sessionId = dragState.sessionId;
            const blocks = dragState.blocks;
            if (!sessionId) return;

            const evalResult = evaluateTrayPlacement(targetRow, targetCol, blocks, sessionId);
            if (!evalResult.ok) return;

            // remove displaced placements
            evalResult.displaced.forEach(id => {
                delete placements[id];
            });

            // place (or move) this session
            placements[sessionId] = {
                col: targetCol,
                topRow: evalResult.topRow,
                blocks: blocks
            };
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

            Object.keys(placements).forEach(id => {
                const p = placements[id];
                if (!p || p.col !== targetCol) return;

                const pStart = p.topRow;
                const pEnd = p.topRow + p.blocks;

                // disjoint -> ok
                if (pEnd <= bandStart || pStart >= bandEnd) return;

                // allow displacement only if fully inside band (no-cut)
                if (pStart < bandStart || pEnd > bandEnd) {
                    // would slice a block -> invalid
                    displaced.clear();
                    topRow = null;
                } else {
                    displaced.add(id);
                }
            });

            if (topRow === null) {
                return { ok: false };
            }

            return { ok: true, topRow, displaced: Array.from(displaced) };
        }

        function handleDropFromCanvas(targetRow, targetCol) {
            const { sessionId, col, topRow, blocks } = dragState;
            if (!sessionId || !placements[sessionId]) return;

            // same column -> slide up/down
            if (targetCol === col) {
                const result = evaluateSlide(sessionId, targetRow);
                if (!result.ok) return;
                placements[sessionId].topRow = result.topRow;
                return;
            }

            // different column -> simple move (no overlaps)
            const result = evaluateMoveToOtherColumn(sessionId, targetRow, targetCol);
            if (!result.ok) return;

            placements[sessionId].col = targetCol;
            placements[sessionId].topRow = result.topRow;
        }

        function evaluateSlide(sessionId, targetRow) {
            const place = placements[sessionId];
            if (!place) return { ok: false };

            const rowsCount = canvasRows;
            let newTop = targetRow;

            newTop = Math.max(0, Math.min(newTop, rowsCount - place.blocks));
            const bandStart = newTop;
            const bandEnd = newTop + place.blocks;

            // can't overlap other placements in same column
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

            // no overlaps in new column
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









