let alignmentHoverCells = [];

function clearAlignmentHoverPreview() {
  for (const cell of alignmentHoverCells) {
    if (cell && cell.classList) {
      cell.classList.remove("alignment-hover-preview");
    }
  }
  alignmentHoverCells = [];
}

function showAlignmentHoverPreview(startRow, endRow, col) {
  clearAlignmentHoverPreview();

  if (!tableAEl) return;

  // Highlight the entire row(s) for that timeframe across all rooms,
  // not just a single column.
  for (let row = startRow; row <= endRow; row++) {
    const cells = tableAEl.querySelectorAll(
      `td[data-table="A"][data-row="${row}"]`
    );
    cells.forEach((cell) => {
      cell.classList.add("alignment-hover-preview");
      alignmentHoverCells.push(cell);
    });
  }
}



function handleAlignmentHoverEnter(event, row, col, value) {
  const vNum = Number(value);
  if (Number.isNaN(vNum)) return;
  if (typeof findAlignmentReferenceBandForValue !== "function") return;

  const refRun = findAlignmentReferenceBandForValue(activeTimetableIndex, vNum);
  if (!refRun) {
    clearAlignmentHoverPreview();
    return;
  }

  // Highlight where this course is placed in its reference timetable
  showAlignmentHoverPreview(refRun.start, refRun.end, refRun.col);
}

function handleAlignmentHoverLeave() {
  clearAlignmentHoverPreview();
}


function createCell(tableId, board, r, c, info) {
  const td = document.createElement("td");
  const raw = board[r][c];
  const value = (raw === 0 ? "" : raw);

  td.textContent = value;
  td.dataset.table = tableId;
  td.dataset.row = r;
  td.dataset.col = c;
  td.dataset.rowspan = info.rowspan;
  td.dataset.value = value; 

  const locked = isLockedValue(value);

  let used = false;
  if (tableId === "B") {
    if (info.rowspan > 1) {
      for (let rr = r; rr < r + info.rowspan; rr++) {
        if (isTrayCellUsed(rr, c)) {
          used = true;
          break;
        }
      }
    } else {
      used = isTrayCellUsed(r, c);
    }
  }

  let groupIndex = null;
  if (value !== "") {
    groupIndex = getSessionGroupForValue(value);
    if (groupIndex !== null) td.dataset.groupIndex = String(groupIndex);
  }

  // --- term-based disabling (tray only) ---
  let termDisabled = false;
  if (tableId === "B" && value) {
    if (typeof getCourseTerm === "function" && typeof getActiveTermIndex === "function") {
      const term = getCourseTerm(value);      // "T1", "T2", "S"
      const activeTerm = getActiveTermIndex(); // 0 = 1st, 1 = 2nd

      // In 1st term view: disable T2 courses
      // In 2nd term view: disable T1 courses
      if ((term === "T1" && activeTerm === 1) || (term === "T2" && activeTerm === 0)) {
        termDisabled = true;
        td.classList.add("tray-term-disabled");
      }
    }
  }

  if (!value || locked || used || termDisabled) {
    // real blanks always look blank
    if (!value) td.classList.add("blank");

    // keep existing styles for locked + used
    if (locked) {
      td.classList.add("blank");
      td.classList.add("locked");
    }
    if (used) {
      td.classList.add("blank");
      td.classList.add("tray-used");
    }

    // termDisabled uses its own visual class above
    td.draggable = false;
  } else {
    td.draggable = true;
  }


  if (info.rowspan > 1) {
    td.rowSpan = info.rowspan;
    td.classList.add("merged");
    td.style.setProperty("--span", info.rowspan);
  }

  // group color always applied if value has a group
  // (but not for term-disabled tray cells – they are grey)
  if (
    value &&
    groupIndex !== null &&
    !(tableId === "B" && typeof termDisabled !== "undefined" && termDisabled)
  ) {
    const color = sessionGroupColors[groupIndex];
    if (color) td.style.backgroundColor = color;
  }

  // tray-used overrides with grey
  if (tableId === "B" && used) {
    td.style.backgroundColor = "#e0e0e0";
    td.style.color = "#555";
  }


  // course term tag (1st / 2nd / SEM) on both tray and canvas
  if (value) {
    const term = typeof getCourseTerm === "function" ? getCourseTerm(value) : "S";
    if (term === "T1" || term === "T2" || term === "S") {
      const tag = document.createElement("span");
      tag.className = "course-term-tag";
      tag.textContent = term === "S" ? "SEM" : (term === "T1" ? "1st" : "2nd");
      td.appendChild(tag);
    }
  }



  // row conflict warning on canvas: same session group appears in this timeslot row
  if (
    tableId === "A" &&
    value &&
    canvasConflictCells &&
    canvasConflictCells.has(`${r},${c}`)
  ) {
    const warn = document.createElement("span");
    warn.className = "conflict-icon";
    warn.textContent = "⚠";

    const tooltipText =
      "Conflict warning\nCannot have cells from the same session group at the same timeframe.";

    // Show tooltip when hovering the icon or anywhere on the cell
    warn.title = tooltipText;
    td.title = tooltipText;

    td.appendChild(warn);
  }

  // cross-timetable alignment warning: same course at different times in other timetables
  if (
    tableId === "A" &&
    value &&
    canvasTimeMisalignmentCells &&
    canvasTimeMisalignmentCells.has(`${r},${c}`)
  ) {
    const warn2 = document.createElement("span");
    warn2.className = "alignment-icon";
    warn2.textContent = "⚠";

    const tooltipText2 =
      "Alignment warning\nThis course is scheduled at different times in other timetables.\n" +
      "It is recommended to keep the same timeframe across days/terms.";

    // Tooltip on the icon itself
    warn2.title = tooltipText2;

    // If there is already a conflict tooltip, append; otherwise set fresh.
    if (td.title) {
      td.title += "\n\n" + tooltipText2;
    } else {
      td.title = tooltipText2;
    }

    td.appendChild(warn2);

    // Also wire hover on the whole cell (not just the icon)
    const vForHover = value;
    td.addEventListener("mouseenter", (e) =>
      handleAlignmentHoverEnter(e, r, c, vForHover)
    );
    td.addEventListener("mouseleave", handleAlignmentHoverLeave);
  }

  return td;
}


function renderCanvasTable() {
  const board = boardA;

  // compute row conflicts (same session group placed in same row across rooms)
  canvasConflictCells = computeCanvasConflicts();

  // compute cross-timetable misalignment warnings for the active timetable
  canvasTimeMisalignmentCells = computeCrossTimetableMisalignmentForActive();

  const spanInfo = computeSpans(board);

  tableAEl.innerHTML = "";

  const headerTr = document.createElement("tr");
  const cornerTh = document.createElement("th");
  cornerTh.className = "corner";
  cornerTh.textContent = "";
  headerTr.appendChild(cornerTh);

  for (let c = 0; c < rooms.length; c++) {
    const th = document.createElement("th");
    th.className = "room-header";
    th.textContent = rooms[c];
    headerTr.appendChild(th);
  }
  tableAEl.appendChild(headerTr);

  for (let r = 0; r < ROWS; r++) {
    const tr = document.createElement("tr");
    tr.dataset.row = String(r);
    const rowHeader = document.createElement("th");
    rowHeader.className = "row-header";
    rowHeader.textContent = TIMESLOTS[r];
    tr.appendChild(rowHeader);

    for (let c = 0; c < rooms.length; c++) {
      const info = spanInfo[r][c];
      if (!info.render) continue;
      const td = createCell("A", board, r, c, info);
      tr.appendChild(td);
    }
    tableAEl.appendChild(tr);
  }
}

function openGroupColorPicker(groupIndex) {
  let overlay = document.getElementById("groupColorOverlay");
  if (!overlay) {
    overlay = document.createElement("div");
    overlay.id = "groupColorOverlay";
    overlay.className = "group-color-overlay hidden";

    const modal = document.createElement("div");
    modal.className = "group-color-modal";

    const header = document.createElement("div");
    header.className = "group-color-modal-header";

    const title = document.createElement("span");
    title.textContent =
      "Choose color for Session Group " +
      String.fromCharCode(65 + groupIndex);

    const closeBtn = document.createElement("button");
    closeBtn.type = "button";
    closeBtn.textContent = "Close";
    closeBtn.addEventListener("click", () => {
      overlay.classList.add("hidden");
    });

    header.appendChild(title);
    header.appendChild(closeBtn);

    const grid = document.createElement("div");
    grid.className = "group-color-grid";

    modal.appendChild(header);
    modal.appendChild(grid);
    overlay.appendChild(modal);

    overlay.addEventListener("click", (e) => {
      if (e.target === overlay) {
        overlay.classList.add("hidden");
      }
    });

    document.body.appendChild(overlay);
  }

  const grid = overlay.querySelector(".group-color-grid");
  grid.innerHTML = "";

  GROUP_COLOR_PRESETS.forEach((hex) => {
    const swatch = document.createElement("div");
    swatch.className = "group-color-swatch";
    swatch.style.backgroundColor = hex;

    if (sessionGroupColors[groupIndex] === hex) {
      swatch.classList.add("selected");
    }

    swatch.addEventListener("click", () => {
      sessionGroupColors[groupIndex] = hex;
      sessionGroupPendingColors[groupIndex] = hex;
      overlay.classList.add("hidden");
      renderAll();
    });

    grid.appendChild(swatch);
  });

  const titleEl = overlay.querySelector(".group-color-modal-header span");
  if (titleEl) {
    titleEl.textContent =
      "Choose color for Session Group " +
      String.fromCharCode(65 + groupIndex);
  }

  overlay.classList.remove("hidden");
}


function renderTrayTable() {
  const board = boardB;
  const spanInfo = computeSpans(board);
  const groupHeights = computeGroupHeights(initialBoardB);
  trayContainerEl.innerHTML = "";

  for (let g = 0; g < SESSION_GROUP_COUNT; g++) {
    const wrapper = document.createElement("div");

    const label = document.createElement("div");
    label.className = "tray-group-header";

    const groupLetter = String.fromCharCode("A".charCodeAt(0) + g);
    const labelText = document.createElement("span");
    labelText.textContent = `Session Group ${groupLetter}`;
    label.appendChild(labelText);

    const controls = document.createElement("div");
    controls.className = "group-color-controls";

    const colorDisplay = document.createElement("div");
    colorDisplay.className = "group-color-display";
    colorDisplay.style.backgroundColor = sessionGroupColors[g];
    controls.appendChild(colorDisplay);

    const colorBtn = document.createElement("button");
    colorBtn.type = "button";
    colorBtn.className = "group-color-open-btn";
    colorBtn.textContent = "Color";
    colorBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      openGroupColorPicker(g);
    });
    controls.appendChild(colorBtn);

    label.appendChild(controls);


    wrapper.appendChild(label);

    const scrollWrapper = document.createElement("div");
    scrollWrapper.className = "session-table-wrapper";

    const tbl = document.createElement("table");
    tbl.className = "session-table";
    tbl.style.width = (COLS * 80) + "px";

    const groupStart = g * GROUP_ROWS_MAX;
    const groupEnd = groupStart + groupHeights[g];

    for (let r = groupStart; r < groupEnd; r++) {
      const tr = document.createElement("tr");
      for (let c = 0; c < COLS; c++) {
        const info = spanInfo[r][c];
        if (!info.render) continue;
        const td = createCell("B", board, r, c, info);
        tr.appendChild(td);
      }
      tbl.appendChild(tr);
    }

    scrollWrapper.appendChild(tbl);
    wrapper.appendChild(scrollWrapper);
    trayContainerEl.appendChild(wrapper);
  }
}

function updateTimetableLabel() {
  if (!timetableLabelEl || typeof getCurrentTimetableLabel !== "function") {
    return;
  }
  timetableLabelEl.textContent = getCurrentTimetableLabel();
}

function renderAll() {
  if (sessionGroupPendingColors.length !== sessionGroupColors.length) {
    sessionGroupPendingColors = sessionGroupColors.slice();
  }
  updateTimetableLabel();
  renderCanvasTable();
  renderTrayTable();
  attachDragAndDrop();
}

