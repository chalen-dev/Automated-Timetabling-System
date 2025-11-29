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
        const sessionGroups = window.sessionGroupsData || [];

        // meta for each CourseSession
        const courseMetaById = {}; // sessionId -> { label, blocks, groupIndex }
        // placements on the canvas: sessionId -> { col, topRow, blocks }
        const placements = {};
        // locked courses (by CourseSession id)
        const lockedSessions = new Set();

        // canvas dimensions & references
        let canvasRows = 0;
        let canvasCols = 0;
        let canvasBody = null;

        // drag state
        let dragState = null;

        // context menu
        let contextMenuEl = null;
        let contextTarget = null; // { sessionId, from: 'tray'|'canvas' }

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

                                if (lockedSessions.has(String(sess.id))) {
                                    td.classList.add('locked');
                                    td.draggable = false;
                                } else {
                                    td.draggable = true;
                                    td.addEventListener('dragstart', handleTrayDragStart);
                                    td.addEventListener('dragend', handleDragEnd);
                                }

                                // right-click menu on tray
                                td.addEventListener('contextmenu', handleCellContextMenu);

                                // allow canvas->tray drop (reset)
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
                    td.classList.remove('merged', 'preview-place', 'preview-swap', 'preview-invalid', 'locked');
                    td.removeEventListener('dragstart', handleCanvasDragStart);
                    td.removeEventListener('dragend', handleDragEnd);
                    td.removeEventListener('contextmenu', handleCellContextMenu);
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
            const isLocked = lockedSessions.has(sessionId);

            // Lock / Unlock item
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

            // Canvas-only: Remove from timetable (also unlock)
            if (target.from === 'canvas') {
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
                menu.style.top = (rect.top + dy) + 'px';
            }
        }

        function handleCellContextMenu(e) {
            e.preventDefault();
            hideContextMenu();

            const td = e.currentTarget;
            const sessionId = td.dataset.sessionId;
            if (!sessionId) return;

            const from = td.closest('.timetable-editor') ? 'canvas' : 'tray';
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











