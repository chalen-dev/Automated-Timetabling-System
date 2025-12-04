function toggleTray(event) {
  if (event) event.stopPropagation();
  coursesTrayEl.classList.toggle("collapsed");
}

function addRoom() {
  let name = roomNameInput.value.trim();
  if (!name) name = `Room ${rooms.length + 1}`;
  if (rooms.length >= COLS) {
    alert("Maximum number of rooms reached for this prototype.");
    return;
  }
  rooms.push(name);
  roomNameInput.value = "";
  renderAll();
}

function downloadXLSX() {
  const wb = XLSX.utils.book_new();
  const dataA = boardA.map(row => row.slice());
  const dataB = boardB.map(row => row.slice());
  const sheetA = XLSX.utils.aoa_to_sheet(dataA);
  const sheetB = XLSX.utils.aoa_to_sheet(dataB);
  XLSX.utils.book_append_sheet(wb, sheetA, "Table A");
  XLSX.utils.book_append_sheet(wb, sheetB, "Table B");
  XLSX.writeFile(wb, "tables.xlsx");
}

function prevTimetable() {
  setActiveTimetable(activeTimetableIndex - 1);
  renderAll();
}

function nextTimetable() {
  setActiveTimetable(activeTimetableIndex + 1);
  renderAll();
}
