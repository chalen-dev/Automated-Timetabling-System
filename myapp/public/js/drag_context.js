//drag_context.js

// ----- Preview helpers -----
function clearPreview() {
  previewCells.forEach(td => {
    td.classList.remove("preview-place", "preview-swap", "preview-invalid");
  });
  previewCells = [];
  document.querySelectorAll(".session-table.session-return-preview")
    .forEach(tbl => tbl.classList.remove("session-return-preview"));
}

function showBandPreview(tableId, col, top, height, mode) {
  clearPreview();
  const cls =
    mode === "place" ? "preview-place" :
    mode === "swap" ? "preview-swap" :
    "preview-invalid";

  const board = getBoardById(tableId);
  const rowsCount = (board === boardA ? ROWS : B_USED_ROWS);

  for (let k = 0; k < height; k++) {
    const logicalRow = top + k;
    if (logicalRow < 0 || logicalRow >= rowsCount) continue;

    const cellsInCol = document.querySelectorAll(
      `td[data-table="${tableId}"][data-col="${col}"]`
    );
    cellsInCol.forEach(cell => {
      const cellRow = parseInt(cell.dataset.row, 10);
      const cellSpan = parseInt(cell.dataset.rowspan || "1", 10);
      if (logicalRow >= cellRow && logicalRow < cellRow + cellSpan) {
        if (!previewCells.includes(cell)) {
          cell.classList.add(cls);
          previewCells.push(cell);
        }
      }
    });
  }
}

function showSwapPreview(targetCellEl) {
  clearPreview();
  if (!draggedCell) return;

  const tRow = parseInt(targetCellEl.dataset.row, 10);
  const tCol = parseInt(targetCellEl.dataset.col, 10);
  const tTable = targetCellEl.dataset.table;
  const tRowspan = parseInt(targetCellEl.dataset.rowspan || "1", 10);

  const targetCell = { table: tTable, row: tRow, col: tCol, rowspan: tRowspan };
  const evalRes = evaluateSwapBand(draggedCell, targetCell);
  if (!evalRes.hasBand) return;

  const { tTop, Hswap, isValid, isEmptyBand } = evalRes;
  let mode;
  if (!isValid) mode = "invalid";
  else if (isEmptyBand) mode = "place";
  else mode = "swap";

  showBandPreview(tTable, tCol, tTop, Hswap, mode);
}

// ----- Context menu -----
function hideContextMenu() {
  contextMenu.classList.add("hidden");
  contextValue = null;
  contextCell = null;
}

function showContextMenuCanvas(x, y, value) {
  contextValue = value;
  const isLocked = lockedValues.has(value);

  ctxDelete.style.display = "block";
  ctxLock.style.display = isLocked ? "none" : "block";
  ctxUnlock.style.display = isLocked ? "block" : "none";
  ctxReturn.style.display = "none";

  contextMenu.style.left = x + "px";
  contextMenu.style.top = y + "px";
  contextMenu.classList.remove("hidden");
}

// tray-used origin: lock/unlock + return
function showContextMenuTrayUsed(x, y, value) {
  contextValue = value;
  const isLocked = lockedValues.has(value);

  ctxDelete.style.display = "none";
  ctxLock.style.display = isLocked ? "none" : "block";
  ctxUnlock.style.display = isLocked ? "block" : "none";
  ctxReturn.style.display = "block";

  contextMenu.style.left = x + "px";
  contextMenu.style.top = y + "px";
  contextMenu.classList.remove("hidden");
}

// ----- Drag & drop wiring -----
function attachDragAndDrop() {
  const cells = document.querySelectorAll("td");

  cells.forEach((cell) => {
    cell.addEventListener("dragstart", (e) => {
      draggedCell = {
        table: e.target.dataset.table,
        row: parseInt(e.target.dataset.row, 10),
        col: parseInt(e.target.dataset.col, 10),
        rowspan: parseInt(e.target.dataset.rowspan || "1", 10),
      };
      e.dataTransfer.effectAllowed = "move";
      clearPreview();

      const origin = e.target;
      origin.classList.add("drag-origin");
    });

    cell.addEventListener("dragend", (e) => {
      const origin = e.target;
      origin.classList.remove("drag-origin");


      document
        .querySelectorAll("td.drag-over")
        .forEach((td) => td.classList.remove("drag-over"));
      clearPreview();
      draggedCell = null;
    });

    cell.addEventListener("contextmenu", (e) => {
      const target = e.currentTarget;
      const tableId = target.dataset.table;
      const trayUsed = tableId === "B" && target.classList.contains("tray-used");

      const rawVal = target.dataset.value;
      if (rawVal === undefined || rawVal === "") {
        hideContextMenu();
        return;
      }

      const numVal = Number(rawVal);
      if (Number.isNaN(numVal)) {
        hideContextMenu();
        return;
      }

      const row = parseInt(target.dataset.row, 10);
      const col = parseInt(target.dataset.col, 10);
      contextCell = { table: tableId, row, col, value: numVal };

      if (tableId === "A") {
        e.preventDefault();
        showContextMenuCanvas(e.pageX, e.pageY, numVal);
        return;
      }

      if (trayUsed) {
        e.preventDefault();
        showContextMenuTrayUsed(e.pageX, e.pageY, numVal);
        return;
      }

      hideContextMenu();
    });

    cell.addEventListener("dragover", (e) => {
      e.preventDefault();
      if (!draggedCell) return;

      const target = e.currentTarget;
      const tRow = parseInt(target.dataset.row, 10);
      const tCol = parseInt(target.dataset.col, 10);
      const tTable = target.dataset.table;

      if (draggedCell.table === "B" && tTable === "B") {
        clearPreview();
        e.dataTransfer.dropEffect = "none";
        return;
      }

      if (tTable === draggedCell.table && tCol === draggedCell.col) {
        const slideEval = computeSlideEval(draggedCell, tRow);
        if (slideEval && slideEval.isValid) {
          showBandPreview(
            tTable,
            tCol,
            slideEval.newTop,
            slideEval.height,
            "place"
          );
          e.dataTransfer.dropEffect = "move";
          return;
        }
      }

      if (draggedCell.table === "B" && tTable === "A") {
        const evalRes = evaluateTrayPlacementBand(draggedCell, tRow, tCol);
        if (!evalRes.hasBand) return;
        const mode = !evalRes.isValid
          ? "invalid"
          : (evalRes.isEmpty ? "place" : "swap");
        showBandPreview("A", tCol, evalRes.top, evalRes.height, mode);
        e.dataTransfer.dropEffect = "move";
        return;
      }

      if (draggedCell.table === "A" && tTable === "B") {
        clearPreview();
        const dBoard = getBoardById(draggedCell.table);
        const val = dBoard[draggedCell.row][draggedCell.col];
        const groupIndex = getSessionGroupForValue(val);
        if (groupIndex !== null) {
          const tables = trayContainerEl.querySelectorAll("table.session-table");
          if (groupIndex >= 0 && groupIndex < tables.length) {
            tables[groupIndex].classList.add("session-return-preview");
          }
        }
        e.dataTransfer.dropEffect = "move";
        return;
      }

      target.classList.add("drag-over");
      showSwapPreview(target);
      e.dataTransfer.dropEffect = "move";
    });

    cell.addEventListener("dragleave", (e) => {
      e.currentTarget.classList.remove("drag-over");
    });

    cell.addEventListener("drop", (e) => {
      e.preventDefault();
      const target = e.currentTarget;
      target.classList.remove("drag-over");
      clearPreview();

      if (!draggedCell) return;

      const tRow = parseInt(target.dataset.row, 10);
      const tCol = parseInt(target.dataset.col, 10);
      const tTable = target.dataset.table;
      const tRowspan = parseInt(target.dataset.rowspan || "1", 10);

      if (draggedCell.table === "B" && tTable === "B") {
        draggedCell = null;
        return;
      }

      let didSomething = false;

      if (tTable === draggedCell.table && tCol === draggedCell.col) {
        const slideEval = computeSlideEval(draggedCell, tRow);
        if (slideEval && slideEval.isValid) {
          blockApply(draggedCell, null, slideEval);
          didSomething = true;
        }
      }

      if (!didSomething && draggedCell.table === "B" && tTable === "A") {
        didSomething = applyTrayToCanvas(draggedCell, { row: tRow, col: tCol });
      }

      if (!didSomething && draggedCell.table === "A" && tTable === "B") {
        const dBoard = getBoardById(draggedCell.table);
        const val = dBoard[draggedCell.row][draggedCell.col];
        if (val != null && val !== "" && val !== 0) {
          resetValueToTray(val);
          didSomething = true;
        }
      }

      if (!didSomething) {
        const targetCell = {
          table: tTable,
          row: tRow,
          col: tCol,
          rowspan: tRowspan,
        };
        blockApply(draggedCell, targetCell);
      }

      draggedCell = null;
      renderAll();
    });
  });

  // context menu button handlers (only wire once)
  ctxDelete.onclick = () => {
    if (contextValue == null) return;
    resetValueToTray(contextValue);
    hideContextMenu();
    renderAll();
  };

  ctxLock.onclick = () => {
    if (contextValue == null) return;
    lockedValues.add(contextValue);
    hideContextMenu();
    renderAll();
  };

  ctxUnlock.onclick = () => {
    if (contextValue == null) return;
    lockedValues.delete(contextValue);
    hideContextMenu();
    renderAll();
  };

  ctxReturn.onclick = () => {
    if (!contextCell || contextValue == null) return;
    if (contextCell.table === "B" && isTrayCellUsed(contextCell.row, contextCell.col)) {
      resetValueToTray(contextValue);
    }
    hideContextMenu();
    renderAll();
  };

  document.addEventListener("click", (e) => {
    if (!contextMenu.contains(e.target)) {
      hideContextMenu();
    }
  }, { once: true });
}
