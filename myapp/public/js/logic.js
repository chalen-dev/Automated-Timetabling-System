// ----- No-cut & lock rules -----
function isBandAligned(board, col, top, height) {
  const rowsCount = (board === boardA ? ROWS : B_USED_ROWS);
  const end = top + height - 1;

  let r = top;
  while (r <= end) {
    if (!isNonEmpty(board, r, col)) {
      r++;
      continue;
    }

    const v = board[r][col];
    let r0 = r;
    while (r0 > 0 && board[r0 - 1][col] === v) r0--;
    let r1 = r;
    while (r1 + 1 < rowsCount && board[r1 + 1][col] === v) r1++;

    if (r0 < top || r1 > end) return false;

    r = r1 + 1;
  }
  return true;
}

function isBandUnlocked(board, col, top, height) {
  const rowsCount = (board === boardA ? ROWS : B_USED_ROWS);
  const end = top + height - 1;
  for (let r = top; r <= end && r < rowsCount; r++) {
    const v = board[r][col];
    if (isLockedValue(v)) return false;
  }
  return true;
}

// ----- Spans / merging -----
function computeSpans(board) {
  if (board === boardA) {
    const spanInfo = Array.from({ length: ROWS }, () =>
      Array.from({ length: COLS }, () => ({ render: true, rowspan: 1 }))
    );

    for (let c = 0; c < COLS; c++) {
      let r = 0;
      while (r < ROWS) {
        if (!isNonEmpty(board, r, c)) {
          r++;
          continue;
        }
        const value = board[r][c];
        let end = r + 1;
        while (end < ROWS && board[end][c] === value) end++;
        const runLength = end - r;
        if (runLength > 1) {
          spanInfo[r][c].rowspan = runLength;
          for (let rr = r + 1; rr < end; rr++) {
            spanInfo[rr][c].render = false;
          }
        }
        r = end;
      }
    }
    return spanInfo;
  } else {
    const spanInfo = Array.from({ length: B_USED_ROWS }, () =>
      Array.from({ length: COLS }, () => ({ render: true, rowspan: 1 }))
    );

    const groupHeights = computeGroupHeights(board);

    for (let c = 0; c < COLS; c++) {
      for (let g = 0; g < SESSION_GROUP_COUNT; g++) {
        const startRow = g * GROUP_ROWS_MAX;
        const limit = startRow + groupHeights[g];
        let r = startRow;

        while (r < limit) {
          if (!isNonEmpty(board, r, c)) {
            r++;
            continue;
          }
          const value = board[r][c];
          let end = r + 1;
          while (end < limit && board[end][c] === value) end++;
          const runLength = end - r;
          if (runLength > 1) {
            spanInfo[r][c].rowspan = runLength;
            for (let rr = r + 1; rr < end; rr++) {
              spanInfo[rr][c].render = false;
            }
          }
          r = end;
        }
      }
    }
    return spanInfo;
  }
}

// ----- Utility: swap -----
function swapCells(board1, r1, c1, board2, r2, c2) {
  const tmp = board1[r1][c1];
  board1[r1][c1] = board2[r2][c2];
  board2[r2][c2] = tmp;
}

// ----- Slide -----
function computeSlideEval(drag, targetRow) {
  const board = getBoardById(drag.table);
  const rowsCount = (board === boardA ? ROWS : B_USED_ROWS);
  const col = drag.col;
  const height = drag.rowspan;
  const origStart = drag.row;
  const origEnd = origStart + height - 1;

  let newTop;
  if (targetRow > origEnd) newTop = targetRow - (height - 1);
  else newTop = targetRow;

  if (newTop < 0) newTop = 0;
  if (newTop + height > rowsCount) newTop = rowsCount - height;
  const newEnd = newTop + height - 1;

  const overlapStart = Math.max(origStart, newTop);
  const overlapEnd = Math.min(origEnd, newEnd);
  const hasOverlap = overlapEnd >= overlapStart;

  let ok = true;
  if (!hasOverlap) {
    ok = false;
  } else {
    for (let r = newTop; r <= newEnd; r++) {
      const inOriginal = r >= origStart && r <= origEnd;
      if (!inOriginal && isNonEmpty(board, r, col)) {
        ok = false;
        break;
      }
    }
  }

  return { newTop, height, isValid: ok };
}

// ----- Swap -----
function evaluateSwapBand(drag, target) {
  const tBoard = getBoardById(target.table);
  const rowsCount = (tBoard === boardA ? ROWS : B_USED_ROWS);

  const Hd = drag.rowspan;
  const Ht = target.rowspan;
  const Hswap = Math.max(Hd, Ht);
  if (Hswap > rowsCount) return { hasBand: false };

  const real = computeSwapParams(drag, target);
  if (real) {
    const { tTop, Hswap: H } = real;
    let empty = true;
    for (let k = 0; k < H; k++) {
      if (isNonEmpty(tBoard, tTop + k, target.col)) {
        empty = false;
        break;
      }
    }
    return { hasBand: true, tTop, Hswap: H, isValid: true, isEmptyBand: empty };
  }

  let tTop = target.row;
  if (tTop + Hswap > rowsCount) tTop = rowsCount - Hswap;
  if (tTop < 0) tTop = 0;
  if (tTop + Hswap > rowsCount) return { hasBand: false };

  return { hasBand: true, tTop, Hswap, isValid: false, isEmptyBand: false };
}

function computeSwapParams(drag, target) {
  const dBoard = getBoardById(drag.table);
  const tBoard = getBoardById(target.table);
  const rowsD = (dBoard === boardA ? ROWS : B_USED_ROWS);
  const rowsT = (tBoard === boardA ? ROWS : B_USED_ROWS);

  const dRow = drag.row;
  const dCol = drag.col;
  const Hd   = drag.rowspan;

  const tRow = target.row;
  const tCol = target.col;
  const Ht   = target.rowspan;

  const Hswap = Math.max(Hd, Ht);
  if (Hswap > rowsD || Hswap > rowsT) return null;

  if (drag.table === target.table && dCol === tCol) {
    const dTop = dRow;
    const tTop = tRow;

    if (dTop + Hswap > rowsD || tTop + Hswap > rowsT) return null;

    const aStart = dTop;
    const aEnd   = dTop + Hswap - 1;
    const bStart = tTop;
    const bEnd   = tTop + Hswap - 1;

    const overlapStart = Math.max(aStart, bStart);
    const overlapEnd   = Math.min(aEnd, bEnd);
    const overlapLen   = overlapEnd - overlapStart + 1;

    const bandsIdentical = aStart === bStart && aEnd === bEnd;
    if (overlapLen > 0 && !bandsIdentical) return null;

    if (!isBandAligned(dBoard, dCol, dTop, Hswap)) return null;
    if (!isBandAligned(tBoard, tCol, tTop, Hswap)) return null;
    if (!isBandUnlocked(dBoard, dCol, dTop, Hswap)) return null;
    if (!isBandUnlocked(tBoard, tCol, tTop, Hswap)) return null;

    return { dTop, tTop, Hswap };
  }

  let dTop = dRow;
  let tTop = tRow;

  const dragOverflow   = dRow + Hswap - rowsD;
  const targetOverflow = tRow + Hswap - rowsT;

  if (dragOverflow > 0 || targetOverflow > 0) {
    const shiftD = Math.max(0, dragOverflow);
    const shiftT = Math.max(0, targetOverflow);

    dTop = dRow - shiftD;
    tTop = tRow - shiftT;

    if (dTop < 0 || tTop < 0) return null;

    if (shiftD > 0) {
      for (let r = dTop; r < dRow; r++) {
        if (isNonEmpty(dBoard, r, dCol)) return null;
      }
    }

    if (shiftT > 0) {
      for (let r = tTop; r < tRow; r++) {
        if (isNonEmpty(tBoard, r, tCol)) return null;
      }
    }

    if (dTop + Hswap > rowsD || tTop + Hswap > rowsT) return null;
  }

  if (!isBandAligned(dBoard, dCol, dTop, Hswap)) return null;
  if (!isBandAligned(tBoard, tCol, tTop, Hswap)) return null;
  if (!isBandUnlocked(dBoard, dCol, dTop, Hswap)) return null;
  if (!isBandUnlocked(tBoard, tCol, tTop, Hswap)) return null;

  return { dTop, tTop, Hswap };
}

// ----- Reset / tray-return -----
function resetValueToTray(value) {
  const v = Number(value);
  if (Number.isNaN(v)) return;

  // Clear this value from all canvas boards
  if (typeof boardAList !== "undefined" && Array.isArray(boardAList)) {
    for (let t = 0; t < TIMETABLE_COUNT; t++) {
      const board = boardAList[t];
      if (!board) continue;
      for (let r = 0; r < ROWS; r++) {
        for (let c = 0; c < COLS; c++) {
          if (board[r][c] === v) {
            board[r][c] = "";
          }
        }
      }
    }
  }

  // Clear from all trays and usedTrayCells maps
  if (
    typeof boardBList !== "undefined" &&
    Array.isArray(boardBList) &&
    typeof usedTrayCellsList !== "undefined" &&
    Array.isArray(usedTrayCellsList)
  ) {
    for (let t = 0; t < TIMETABLE_COUNT; t++) {
      const tray = boardBList[t];
      const usedMap = usedTrayCellsList[t];
      if (!tray || !usedMap) continue;
      for (let r = 0; r < ROWS; r++) {
        for (let c = 0; c < COLS; c++) {
          if (tray[r][c] === v) {
            tray[r][c] = "";
          }
          const key = `${r},${c}`;
          if (usedMap.get(key) === v) {
            usedMap.delete(key);
          }
        }
      }
    }
  }

  // Unlock this course globally
  lockedValues.delete(v);

  // Restore original pattern into all trays based on initialBoardB
  for (let r = 0; r < initialBoardB.length; r++) {
    const row = initialBoardB[r];
    for (let c = 0; c < row.length; c++) {
      if (row[c] !== v) continue;

      for (let t = 0; t < TIMETABLE_COUNT; t++) {
        const tray = boardBList[t];
        if (!tray) continue;

        if (tray[r][c] === "") {
          tray[r][c] = v;
        }
      }
    }
  }
}

function applyTrayToCanvas(drag, target) {
  const h = drag.rowspan;
  const col = target.col;

  let top = target.row;
  if (top + h > ROWS) top = ROWS - h;
  if (top < 0) top = 0;

  if (!isBandAligned(boardA, col, top, h)) return false;
  if (!isBandUnlocked(boardA, col, top, h)) return false;

  const displacedValues = new Set();

  for (let k = 0; k < h; k++) {
    const dstRow = top + k;
    const dstVal = boardA[dstRow][col];
    if (dstVal !== "" && dstVal != null && dstVal !== 0 && !isLockedValue(dstVal)) {
      displacedValues.add(dstVal);
    }
  }

  for (let k = 0; k < h; k++) {
    const srcRow = drag.row + k;
    const dstRow = top + k;
    const val = boardB[srcRow][drag.col];

    boardA[dstRow][col] = val;
    usedTrayCells.set(`${srcRow},${drag.col}`, val);
  }

  displacedValues.forEach(v => resetValueToTray(v));

  return true;
}

function evaluateTrayPlacementBand(drag, targetRow, targetCol) {
  const h = drag.rowspan;
  let top = targetRow;
  if (top + h > ROWS) top = ROWS - h;
  if (top < 0) top = 0;
  if (top < 0 || top + h > ROWS) return { hasBand: false };

  let valid = isBandAligned(boardA, targetCol, top, h) &&
              isBandUnlocked(boardA, targetCol, top, h);

  let empty = true;
  if (valid) {
    for (let k = 0; k < h; k++) {
      if (isNonEmpty(boardA, top + k, targetCol)) {
        empty = false;
        break;
      }
    }
  }

  return { hasBand: true, top, height: h, isValid: valid, isEmpty: empty };
}

// ----- Block apply (slide/swap/placement) -----
function blockApply(drag, target, slideEval = null) {
  const dBoard = getBoardById(drag.table);

  if (slideEval && slideEval.isValid) {
    const col = drag.col;
    const height = drag.rowspan;
    const origStart = drag.row;
    const val = dBoard[origStart][col];
    const newTop = slideEval.newTop;

    for (let k = 0; k < height; k++) {
      dBoard[origStart + k][col] = "";
    }
    for (let k = 0; k < height; k++) {
      dBoard[newTop + k][col] = val;
    }
    return true;
  }

  if (!target) return false;

  if (drag.table === "B" && target.table === "A") {
    return applyTrayToCanvas(drag, target);
  }

  const params = computeSwapParams(drag, target);
  if (!params) return false;

  const tBoard = getBoardById(target.table);
  const dCol = drag.col;
  const tCol = target.col;
  const { dTop, tTop, Hswap } = params;

  const rowsD = (dBoard === boardA ? ROWS : B_USED_ROWS);
  const rowsT = (tBoard === boardA ? ROWS : B_USED_ROWS);

  for (let k = 0; k < Hswap; k++) {
    if (dTop + k < rowsD && tTop + k < rowsT) {
      swapCells(dBoard, dTop + k, dCol, tBoard, tTop + k, tCol);
    }
  }
  return true;
}

// ----- Canvas row conflicts -----
// Any timeslot row where the same session group appears in multiple rooms
// will mark all involved blocks as "conflict".
function computeCanvasConflicts() {
  const runs = [];
  const rowGroups = Array.from({ length: ROWS }, () => new Map());

  // Build vertical runs per column, like merging logic
  for (let c = 0; c < COLS; c++) {
    let r = 0;
    while (r < ROWS) {
      if (!isNonEmpty(boardA, r, c)) {
        r++;
        continue;
      }

      const v = boardA[r][c];
      const groupIndex = getSessionGroupForValue(v);

      let end = r + 1;
      while (end < ROWS && boardA[end][c] === v) end++;

      const runIndex = runs.length;
      runs.push({ start: r, end: end - 1, col: c, groupIndex });

      // Only group-linked values participate in conflicts
      if (groupIndex != null) {
        for (let rr = r; rr < end; rr++) {
          const map = rowGroups[rr];
          let arr = map.get(groupIndex);
          if (!arr) {
            arr = [];
            map.set(groupIndex, arr);
          }
          arr.push(runIndex);
        }
      }

      r = end;
    }
  }

  // Mark runs that share a row with another run of the same group
  const runConflict = new Array(runs.length).fill(false);

  for (let row = 0; row < ROWS; row++) {
    const map = rowGroups[row];
    for (const [groupIndex, arr] of map.entries()) {
      if (arr.length > 1) {
        // same session group appears multiple times in this row
        for (const idx of arr) {
          runConflict[idx] = true;
        }
      }
    }
  }

  const conflicts = new Set();
  runs.forEach((run, idx) => {
    if (runConflict[idx]) {
      conflicts.add(`${run.start},${run.col}`);
    }
  });

  return conflicts;
}

// ----- Cross-timetable alignment warnings -----
// For each numeric value:
//   If it appears in multiple relevant timetables (per term type) but with different
//   start rows (timeframes), we mark all of its blocks in those timetables as "time-misaligned".
function computeCrossTimetableMisalignmentForActive() {
  // If multi-timetable layout is not present, just no-op safely.
  if (typeof boardAList === "undefined" || !Array.isArray(boardAList)) {
    return new Set();
  }

  const valueRuns = new Map(); // value -> array of { timetableIndex, start, end, col }

  // Scan every timetable's canvas (boardAList[t])
  for (let t = 0; t < TIMETABLE_COUNT; t++) {
    const board = boardAList[t];
    if (!board) continue;

    for (let c = 0; c < COLS; c++) {
      let r = 0;
      while (r < ROWS) {
        if (!isNonEmpty(board, r, c)) {
          r++;
          continue;
        }

        const v = board[r][c];

        // Build vertical run for this value in this column
        let end = r + 1;
        while (end < ROWS && board[end][c] === v) end++;

        let list = valueRuns.get(v);
        if (!list) {
          list = [];
          valueRuns.set(v, list);
        }
        list.push({
          timetableIndex: t,
          start: r,
          end: end - 1,
          col: c
        });

        r = end;
      }
    }
  }

  // Prepare per-timetable result sets
  const perTimetable = Array.from({ length: TIMETABLE_COUNT }, () => new Set());

  // Decide which runs are misaligned across timetables
  for (const [v, runs] of valueRuns.entries()) {
    if (!runs || runs.length === 0) continue;

    const courseTerm = typeof getCourseTerm === "function" ? getCourseTerm(v) : null;
    if (!courseTerm) continue;



    // Filter runs to only timetables relevant for this course's term
    const filteredRuns = runs.filter((run) => {
      const tTerm = typeof getTimetableTermIndex === "function"
        ? getTimetableTermIndex(run.timetableIndex)
        : Math.floor(run.timetableIndex / DAYS_PER_TERM);
      if (courseTerm === "T1") return tTerm === 0;
      if (courseTerm === "T2") return tTerm === 1;
      // semestral applies to all
      return true;
    });

    if (filteredRuns.length === 0) continue;

    // We only care if the value appears in at least 2 different relevant timetables.
    const uniqueTimetables = new Set(filteredRuns.map((run) => run.timetableIndex));
    if (uniqueTimetables.size <= 1) continue;

    // For alignment, we compare one representative run per timetable (by earliest start row)
    const repsByTimetable = new Map(); // tIndex -> representative run
    for (const run of filteredRuns) {
      const prev = repsByTimetable.get(run.timetableIndex);
      if (!prev || run.start < prev.start) {
        repsByTimetable.set(run.timetableIndex, run);
      }
    }

    const repRuns = Array.from(repsByTimetable.values());
    if (repRuns.length <= 1) continue;

    const firstRow = repRuns[0].start;
    let allSameRow = true;
    for (let i = 1; i < repRuns.length; i++) {
      if (repRuns[i].start !== firstRow) {
        allSameRow = false;
        break;
      }
    }

    // If all relevant timetables place this value at the same timeslot, no misalignment.
    if (allSameRow) continue;

    // Otherwise, mark all runs for this value in those relevant timetables as misaligned
    const misalignedTimetables = new Set(
      repRuns.map((run) => run.timetableIndex)
    );

    for (const run of filteredRuns) {
      if (!misalignedTimetables.has(run.timetableIndex)) continue;
      const setForT = perTimetable[run.timetableIndex];
      for (let row = run.start; row <= run.end; row++) {
        setForT.add(`${row},${run.col}`);
      }
    }
  }

  return perTimetable[activeTimetableIndex] || new Set();
}


// Find the "reference" band for a value in another timetable,
// used for hover-highlighting alignment positions.
// Rules per value v:
//   - Find all timetables where v appears (tIndices sorted).
//   - Let firstT = earliest timetable index where v appears.
//   - When hovering in firstT, show next timetable's placement (if any).
//   - When hovering in any later timetable, show firstT's placement.
function findAlignmentReferenceBandForValue(myTimetableIndex, value) {
  if (typeof boardAList === "undefined" || !Array.isArray(boardAList)) {
    return null;
  }

  const vNum = Number(value);
  if (Number.isNaN(vNum)) return null;

  const courseTerm = typeof getCourseTerm === "function" ? getCourseTerm(vNum) : null;
  if (!courseTerm) return null;

  const placements = []; // { tIndex, start, end, col }

  for (let t = 0; t < TIMETABLE_COUNT; t++) {
    const board = boardAList[t];
    if (!board) continue;

    const tTerm = typeof getTimetableTermIndex === "function"
      ? getTimetableTermIndex(t)
      : Math.floor(t / DAYS_PER_TERM);

    if (courseTerm === "T1" && tTerm !== 0) continue;
    if (courseTerm === "T2" && tTerm !== 1) continue;
    // "S" = semestral uses all timetables

    for (let c = 0; c < COLS; c++) {
      let r = 0;
      while (r < ROWS) {
        if (board[r][c] !== vNum) {
          r++;
          continue;
        }

        // Found this value; build vertical run
        let end = r + 1;
        while (end < ROWS && board[end][c] === vNum) end++;

        placements.push({
          tIndex: t,
          start: r,
          end: end - 1,
          col: c
        });

        r = end;
      }
    }
  }

  if (placements.length < 2) return null;

  const tIndicesSorted = Array.from(
    new Set(placements.map((p) => p.tIndex))
  ).sort((a, b) => a - b);

  if (!tIndicesSorted.includes(myTimetableIndex)) {
    return null;
  }

  const firstT = tIndicesSorted[0];
  const secondT = tIndicesSorted[1] != null ? tIndicesSorted[1] : null;

  let refT = null;
  if (myTimetableIndex === firstT) {
    // Hovering on earliest timetable: show next placement (if any)
    refT = secondT;
  } else {
    // Hovering on any later timetable: show earliest placement
    refT = firstT;
  }

  if (refT == null) return null;

  // Representative run in refT: earliest start row
  let refRun = null;
  for (const p of placements) {
    if (p.tIndex !== refT) continue;
    if (!refRun || p.start < refRun.start) {
      refRun = p;
    }
  }

  return refRun;
}


