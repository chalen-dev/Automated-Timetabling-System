//data.js

// ----- Time & room configuration -----
function generateTimeSlots() {
  const slots = [];
  let hour = 7;
  let minute = 0;
  while (hour < 21 || (hour === 21 && minute <= 30)) {
    const suffix = hour < 12 ? "AM" : "PM";
    let displayHour = hour % 12;
    if (displayHour === 0) displayHour = 12;
    const displayMin = minute.toString().padStart(2, "0");
    slots.push(`${displayHour}:${displayMin} ${suffix}`);
    minute += 30;
    if (minute >= 60) {
      minute = 0;
      hour++;
    }
  }
  return slots;
}

const TIMESLOTS = generateTimeSlots();
const ROWS = TIMESLOTS.length;
const COLS = 15;

let rooms = ["Room 1"];

// ----- Tray grouping -----
const GROUP_ROWS_MAX = 6;         // backing grid rows per session group
const SESSION_GROUP_COUNT = 2;

function buildBaseTray() {
  const trayRows = SESSION_GROUP_COUNT * GROUP_ROWS_MAX;
  const tray = Array.from({ length: trayRows }, () =>
    Array.from({ length: COLS }, () => 0)
  );

  let valueCounter = 1;

  for (let g = 0; g < SESSION_GROUP_COUNT; g++) {
    const baseRow = g * GROUP_ROWS_MAX;
    for (let c = 0; c < COLS; c++) {
      const v = valueCounter++;
      const durationBlocks = 1 + ((g + c) % 3); // 1–3 blocks (0.5–1.5h)
      for (let offset = 0; offset < durationBlocks; offset++) {
        const r = baseRow + offset;
        if (r < baseRow + GROUP_ROWS_MAX) {
          tray[r][c] = v;
        }
      }
    }
  }
  return tray;
}

const baseTray = buildBaseTray();
const B_USED_ROWS = baseTray.length;

// ----- Group colors -----
// 50 light, non-gray preset colors
const GROUP_COLOR_PRESETS = [
  // pastel reds / warm
  "#ffe0e0",
  "#ffc4c4",
  "#ffb3b3",
  "#ffd0c8",
  "#ffebe0",

  // pastel oranges
  "#ffe8cc",
  "#ffdcb3",
  "#ffcf99",
  "#ffe6b8",
  "#ffefcc",

  // pastel yellows
  "#fff6cc",
  "#fff2b3",
  "#fff0a6",
  "#fff9cc",
  "#fff7b8",

  // pastel greens
  "#e0ffe0",
  "#ccf5d5",
  "#bff0cc",
  "#d6ffe6",
  "#c8ffd9",

  // pastel teals
  "#e0fff7",
  "#ccf5f0",
  "#b8efe8",
  "#ccfffb",
  "#d6fffa",

  // pastel blues
  "#e0eaff",
  "#ccdfff",
  "#b8d4ff",
  "#d6e8ff",
  "#c3ddff",

  // pastel indigos
  "#e3e0ff",
  "#d5d0ff",
  "#c6c0ff",
  "#d9d4ff",
  "#cbc9ff",

  // pastel purples
  "#f0e0ff",
  "#ebd0ff",
  "#e6c2ff",
  "#f2ddff",
  "#f5e6ff",

  // pastel pinks / mints
  "#ffe0f0",
  "#ffcce8",
  "#ffb8e0",
  "#ffd6f0",
  "#ffe6f5",
  "#e6ffea",
  "#e0ffe8",
  "#f0ffea",
  "#e8ffe0",
  "#f2ffe6"
];

// initial defaults: first few presets
const defaultGroupColors = GROUP_COLOR_PRESETS.slice(0, 6);

let sessionGroupColors = Array.from(
  { length: SESSION_GROUP_COUNT },
  (_, i) => defaultGroupColors[i % defaultGroupColors.length]
);

// kept for compatibility; we’ll set it alongside sessionGroupColors
let sessionGroupPendingColors = sessionGroupColors.slice();

// ----- Group color validation (only light, non-gray colors allowed) -----
function hexToRgb(hex) {
  if (typeof hex !== "string") return null;
  if (hex[0] === "#") hex = hex.slice(1);
  if (hex.length !== 6) return null;
  const r = parseInt(hex.slice(0, 2), 16);
  const g = parseInt(hex.slice(2, 4), 16);
  const b = parseInt(hex.slice(4, 6), 16);
  if (Number.isNaN(r) || Number.isNaN(g) || Number.isNaN(b)) return null;
  return { r, g, b };
}

function isLightNonGrayColor(hex) {
  const rgb = hexToRgb(hex);
  if (!rgb) return false;
  const { r, g, b } = rgb;

  const max = Math.max(r, g, b);
  const min = Math.min(r, g, b);
  const lightness = (max + min) / 2 / 255; // 0..1

  const drg = Math.abs(r - g);
  const dgb = Math.abs(g - b);
  const drb = Math.abs(r - b);
  const isGrayish = drg < 10 && dgb < 10 && drb < 10; // channels almost equal → gray-ish

  // Require fairly light & not gray-ish
  return lightness >= 0.7 && !isGrayish;
}

function ensureAllowedGroupColor(candidate, fallback) {
  if (isLightNonGrayColor(candidate)) {
    return candidate;
  }
  return fallback;
}


// ----- Course term classification -----
// "T1" = 1st term only, "T2" = 2nd term only, "S" = semestral (both terms).
const courseTermMap = new Map();

function initCourseTermsFromBaseTray() {
  courseTermMap.clear();

  const t1Count = 3;
  const t2Count = 3;
  const sCount = 3;

  // Do term assignment independently inside each session group block
  for (let g = 0; g < SESSION_GROUP_COUNT; g++) {
    const startRow = g * GROUP_ROWS_MAX;
    const endRow = startRow + GROUP_ROWS_MAX;

    const seen = [];

    for (let r = startRow; r < endRow; r++) {
      const row = baseTray[r];
      for (let c = 0; c < row.length; c++) {
        const v = row[c];
        if (v && !seen.includes(v)) {
          seen.push(v);
        }
      }
    }

    for (let i = 0; i < seen.length; i++) {
      const v = seen[i];
      if (i < t1Count) {
        // first 3 distinct courses in this group -> 1st term
        courseTermMap.set(v, "T1");
      } else if (i < t1Count + t2Count) {
        // next 3 -> 2nd term
        courseTermMap.set(v, "T2");
      } else if (i < t1Count + t2Count + sCount) {
        // next 3 -> semestral
        courseTermMap.set(v, "S");
      } else {
        // any extra courses default to semestral
        courseTermMap.set(v, "S");
      }
    }
  }
}


function getCourseTerm(value) {
  const v = Number(value);
  if (Number.isNaN(v)) return "S";
  return courseTermMap.get(v) || "S";
}


initCourseTermsFromBaseTray();

// ----- Boards -----
const TERMS = 2;
const DAYS_PER_TERM = 6;
const TIMETABLE_COUNT = TERMS * DAYS_PER_TERM;


let activeTimetableIndex = 0;

// One canvas (boardA) per timetable
let boardAList = Array.from({ length: TIMETABLE_COUNT }, () =>
  Array.from({ length: ROWS }, () =>
    Array.from({ length: COLS }, () => "")
  )
);

// One tray (boardB) per timetable
// Each timetable starts with the full base tray; term rules are enforced at render/drag time.
let boardBList = Array.from({ length: TIMETABLE_COUNT }, () =>
  Array.from({ length: ROWS }, (_, r) =>
    r < B_USED_ROWS ? baseTray[r].slice() : Array.from({ length: COLS }, () => "")
  )
);



// boardA / boardB always point to the currently active timetable
let boardA = boardAList[activeTimetableIndex];
let boardB = boardBList[activeTimetableIndex];

// Original tray pattern (same for all timetables)
const initialBoardB = baseTray.map(row => row.slice());



const TERM_LABELS = ["1st Term", "2nd Term"];
const DAY_LABELS = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];

function getTimetableTermIndex(tIndex) {
  return Math.floor(tIndex / DAYS_PER_TERM);
}

function getActiveTermIndex() {
  return getTimetableTermIndex(activeTimetableIndex);
}


function getActiveDayIndex() {
  return activeTimetableIndex % DAYS_PER_TERM;
}

function getCurrentTimetableLabel() {
  const termIdx = getActiveTermIndex();
  const dayIdx = getActiveDayIndex();
  const term =
    TERM_LABELS[termIdx] != null ? TERM_LABELS[termIdx] : `Term ${termIdx + 1}`;
  const day =
    DAY_LABELS[dayIdx] != null ? DAY_LABELS[dayIdx] : `Day ${dayIdx + 1}`;
  return term + " – " + day;
}

function setActiveTimetable(index) {
  if (index < 0) {
    index = 0;
  }
  if (index >= TIMETABLE_COUNT) {
    index = TIMETABLE_COUNT - 1;
  }
  activeTimetableIndex = index;

  // Point globals at this timetable's boards + tray-used map
  boardA = boardAList[activeTimetableIndex];
  boardB = boardBList[activeTimetableIndex];
  usedTrayCells = usedTrayCellsList[activeTimetableIndex];
}



// ----- DOM references -----
const tableAEl = document.getElementById("gridA");
const trayContainerEl = document.getElementById("gridB");
const coursesTrayEl = document.getElementById("coursesTray");

const contextMenu = document.getElementById("contextMenu");
const ctxDelete = document.getElementById("ctxDelete");
const ctxLock = document.getElementById("ctxLock");
const ctxUnlock = document.getElementById("ctxUnlock");
const ctxReturn = document.getElementById("ctxReturn");

const timetableLabelEl = document.getElementById("timetableLabel");
const roomNameInput = document.getElementById("newRoomName");

// ----- Global interaction state -----
let draggedCell = null;
let previewCells = [];
let lockedValues = new Set();
let contextValue = null;
let contextCell = null;

// tray origin tracking for placed values
// One map per timetable; each tracks its own greyed-out tray origins.
let usedTrayCellsList = Array.from({ length: TIMETABLE_COUNT }, () => new Map());
let usedTrayCells = usedTrayCellsList[activeTimetableIndex]; // key "row,col" -> value

// canvas conflicts: keys "row,col" where a block conflicts with same session group
let canvasConflictCells = new Set();

// cross-timetable time misalignment: keys "row,col" where same value is at different times in other timetables
let canvasTimeMisalignmentCells = new Set();


// ----- Shared helpers -----
function getBoardById(id) {
  return id === "A" ? boardA : boardB;
}

function isNonEmpty(board, r, c) {
  const v = board[r][c];
  return v !== "" && v != null && v !== 0;
}

function isLockedValue(val) {
  return val !== "" && val != null && val !== 0 && lockedValues.has(val);
}

function isTrayCellUsed(row, col) {
  return usedTrayCells.has(`${row},${col}`);
}

function getSessionGroupForValue(val) {
  if (val == null || val === "" || val === 0) return null;
  for (let r = 0; r < B_USED_ROWS; r++) {
    for (let c = 0; c < COLS; c++) {
      if (initialBoardB[r][c] === val) {
        return Math.floor(r / GROUP_ROWS_MAX);
      }
    }
  }
  return null;
}

// compute visible height (rows) for each session group based on longest run
function computeGroupHeights(board) {
  const heights = new Array(SESSION_GROUP_COUNT).fill(1);
  for (let g = 0; g < SESSION_GROUP_COUNT; g++) {
    const startRow = g * GROUP_ROWS_MAX;
    const limitRow = startRow + GROUP_ROWS_MAX;
    let maxLen = 1;

    for (let c = 0; c < COLS; c++) {
      let r = startRow;
      while (r < limitRow) {
        if (!isNonEmpty(board, r, c)) {
          r++;
          continue;
        }
        const v = board[r][c];
        let end = r + 1;
        while (end < limitRow && board[end][c] === v) end++;
        const runLen = end - r;
        if (runLen > maxLen) maxLen = runLen;
        r = end;
      }
    }
    heights[g] = maxLen;
  }
  return heights;
}
