<?php

// TIMETABLE (FAST GREEDY) — PHP VERSION
//
// Usage (similar to Python version):
//   php scripts/process_timetable.php "/path/to/input-csvs" "/path/to/output" 1
//
// Required CSVs in input_dir:
//   - course-sessions.csv
//   - session-groups.csv
//   - timetable_template.csv
//   - timetable-rooms.csv
// Optional:
//   - timetable-professors.csv
//
// Output: {output_dir}/{timetable_id}.xlsx

require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

// ---- CONFIG / ARGS ----
if ($argc < 4) {
    fwrite(STDERR,
        "Usage: php {$argv[0]} <input_dir> <output_dir> <timetable_id> [--confine-labs=1]\n"
    );
    exit(1);

}
$confineLabs = false;

foreach ($argv as $arg) {
    if ($arg === '--confine-labs=1') {
        $confineLabs = true;
    }
}

$inputDir = rtrim($argv[1], "/");
$outputDir = rtrim($argv[2], "/");
$timetableId = $argv[3];

$OUTPUT_XLSX = $outputDir . "/" . $timetableId . ".xlsx";

$COURSE_SESSIONS_CSV = $inputDir . "/course-sessions.csv";
$SESSION_GROUPS_CSV = $inputDir . "/session-groups.csv";
$TEMPLATE_CSV = $inputDir . "/timetable_template.csv";
$ROOMS_CSV = $inputDir . "/timetable-rooms.csv";
$PROFESSORS_CSV = $inputDir . "/timetable-professors.csv"; // optional

$LUNCH_START = "12:00";
$LUNCH_END = "12:30";
$DAYS = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];

// Globals used by helper functions (to mirror Python structure)
$timesDt = [];
$slotMinutes = 30;
$totalSlots = 0;
$roomCols = [];
$roomsMeta = [];
$availability = [];
$sessionGroupOccupancy = [];
$assignments = [];
$lunchStartDt = null;
$lunchEndDt = null;

// -------- Helpers --------

function read_csv_assoc($path)
{
    if (!file_exists($path)) {
        throw new RuntimeException("CSV file not found: {$path}");
    }
    $fh = fopen($path, "r");
    if (!$fh) {
        throw new RuntimeException("Cannot open CSV file: {$path}");
    }
    $header = fgetcsv($fh);
    if ($header === false) {
        fclose($fh);
        return [];
    }
    $header = array_map('trim', $header);

    $rows = [];
    while (($row = fgetcsv($fh)) !== false) {
        $assoc = [];
        foreach ($header as $i => $col) {
            $assoc[$col] = $row[$i] ?? null;
        }
        $rows[] = $assoc;
    }
    fclose($fh);
    return $rows;
}

function parse_time_label($lbl)
{
    $s = trim((string)$lbl);
    $formats = [
        'g:i A',   // "1:30 PM"
        'g:ia',    // "1:30pm"
        'H:i',     // "13:30"
        'g:i A '   // "1:30 PM "
    ];

    foreach ($formats as $fmt) {
        $dt = DateTime::createFromFormat($fmt, $s);
        if ($dt instanceof DateTime) {
            return $dt;
        }
    }

    // Fallback: let PHP try
    try {
        $dt = new DateTime($s);
        return $dt;
    } catch (Exception $e) {
        throw new RuntimeException("Cannot parse time label: {$lbl} -> " . $e->getMessage());
    }
}

function median(array $values)
{
    if (empty($values)) {
        return 0;
    }
    sort($values);
    $count = count($values);
    $mid = intdiv($count, 2);
    if ($count % 2) {
        return $values[$mid];
    }
    return ($values[$mid - 1] + $values[$mid]) / 2.0;
}

function pretty_code(array $row)
{
    $pa = str_replace(' ', '', (string)($row['program_abbreviation'] ?? $row['academic_program'] ?? 'UNK'));
    $year = str_replace(' ', '', (string)($row['year_level'] ?? 'UNK'));
    $sg = (string)($row['session_group_id'] ?? '');
    $csidRaw = $row['course_session_id'] ?? '';
    $csid = (int)$csidRaw;
    return $pa . "_" . $year . "_" . $sg . "_" . $csid;
}

function slot_dt($idx)
{
    global $timesDt;
    return $timesDt[$idx];
}

function spans_lunch($start, $nSlots)
{
    global $slotMinutes, $timesDt, $lunchStartDt, $lunchEndDt, $totalSlots;
    $sdt = slot_dt($start);
    $endIndex = min($start + $nSlots, $totalSlots) - 1;
    $edt = clone $timesDt[$endIndex];
    $edt->modify("+" . $slotMinutes . " minutes");

    // check if [sdt, edt) overlaps lunch [LUNCH_START, LUNCH_END)
    if ($edt <= $lunchStartDt || $sdt >= $lunchEndDt) {
        return false;
    }
    return true;
}

// Map session_time label to start/end HH:MM bounds
function session_time_bounds($label) {
    if (empty($label)) return null;
    $l = strtolower(trim((string)$label));
    $map = [
        'morning'   => ['start' => '07:00', 'end' => '12:00'],
        'afternoon' => ['start' => '12:30', 'end' => '17:30'],
        'evening'   => ['start' => '17:30', 'end' => '21:30'],
    ];
    return isset($map[$l]) ? $map[$l] : null;
}

/**
 * Return slot index for the first slot whose time >= HH:MM
 */
function slot_index_at_or_after($hhmm) {
    global $timesDt, $totalSlots;

    foreach ($timesDt as $i => $dt) {
        if ($dt->format('H:i') >= $hhmm) {
            return $i;
        }
    }
    return null;
}

/**
 * Get primary window slot range for a session_time
 * Returns [startIndex, endIndexExclusive] or null
 */
function session_primary_slot_range($sessionTime) {
    if (!$sessionTime) return null;

    $bounds = session_time_bounds($sessionTime);
    if (!$bounds) return null;

    $start = slot_index_at_or_after($bounds['start']);
    $end   = slot_index_at_or_after($bounds['end']);

    return ($start !== null && $end !== null)
        ? [$start, $end]
        : null;
}

/**
 * Get overflow start slot index (immediate next session)
 */
function session_overflow_start($sessionTime) {
    $map = [
        'morning'   => '12:30',
        'afternoon' => '17:30',
        'evening'   => null, // no overflow
    ];

    $sessionTime = strtolower((string)$sessionTime);
    if (!isset($map[$sessionTime]) || !$map[$sessionTime]) {
        return null;
    }

    return slot_index_at_or_after($map[$sessionTime]);
}

/**
 * GLOBAL exhaustion check (Strategy A)
 * Returns true if NO room on NO day has ANY free slot in the primary window
 */
function is_primary_window_globally_exhausted($term, $sessionTime) {
    global $availability, $roomCols, $DAYS;

    $range = session_primary_slot_range($sessionTime);
    if (!$range) return false;

    [$start, $end] = $range;

    foreach ($DAYS as $day) {
        foreach ($roomCols as $rm) {
            for ($s = $start; $s < $end; $s++) {
                if (!empty($availability[$term][$day][$rm][$s])) {
                    return false; // found at least one free slot
                }
            }
        }
    }

    return true; // absolutely no capacity left
}

function compute_candidate_starts_with_overflow(array $row, string $term)
{
    global $totalSlots;

    $nSlots = (int)$row['required_slots'];
    $sgid   = $row['session_group_id'] ?? null;
    $sg     = get_session_group_row($sgid);
    $st     = strtolower((string)($sg['session_time'] ?? ''));

    $starts = [];

    // ---- PRIMARY WINDOW ----
    $range = session_primary_slot_range($st);
    if ($range) {
        [$ws, $we] = $range;
        for ($s = $ws; $s + $nSlots <= $we; $s++) {
            $starts[] = $s;
        }
    }

    // ---- STOP if NOT exhausted ----
    if (!is_primary_window_globally_exhausted($term, $st)) {
        return $starts;
    }

    // ---- FORWARD OVERFLOW (morning → afternoon, afternoon → evening) ----
    if ($st === 'morning' || $st === 'afternoon') {
        $overflowStart = session_overflow_start($st);
        if ($overflowStart !== null) {
            for ($s = $overflowStart; $s <= $totalSlots - $nSlots; $s++) {
                $starts[] = $s;
            }
        }
    }

    // ---- BACKWARD OVERFLOW (evening → afternoon → morning) ----
    if ($st === 'evening') {
        $prevEnd = slot_index_at_or_after('17:30');
        if ($prevEnd !== null) {
            for ($s = $prevEnd - $nSlots; $s >= 0; $s--) {
                $starts[] = $s;
            }
        }
    }

    return $starts;
}



// Look up the session_group row by session_group_id
function get_session_group_row($sgid) {
    global $sg_df;
    if (empty($sgid) || !is_array($sg_df)) return null;
    foreach ($sg_df as $r) {
        if (isset($r['session_group_id']) && (string)$r['session_group_id'] === (string)$sgid) {
            return $r;
        }
    }
    return null;
}

function room_can_host($rm, $courseType, $roomTypeNeeded, $day, $program)
{
    global $roomsMeta;

    $md = $roomsMeta[$rm] ?? [];
    $rt = strtolower($md['room_type'] ?? '');
    if ($rt !== strtolower($roomTypeNeeded)) {
        return false;
    }

    $ex = strtolower($md['course_type_exclusive_to'] ?? 'none');
    $ct = strtolower($courseType ?? '');

    // PE/NSTP special
    if ($ct === 'pe' || $ct === 'nstp') {
        if ($ex !== $ct) {
            return false;
        }
    } else {
        if ($ex !== 'none') {
            return false;
        }
    }

    $exPrograms = $md['exclusive_programs'] ?? [];
    if (!empty($exPrograms)) {
        $prog = strtoupper(trim((string)$program));
        if ($prog === '' || !in_array($prog, $exPrograms, true)) {
            return false;
        }
    }

    $exday = $md['exclusive_days'] ?? '';
    if ($exday !== '' && strtolower($exday) !== 'nan') {
        if (strtolower($exday) !== strtolower($day)) {
            return false;
        }
    }

    return true;
}

function available_rooms($term, $day, $roomTypeNeeded, $courseType, $start, $nSlots, $program)

{
    global $roomCols, $availability;
    $rlist = [];
    foreach ($roomCols as $rm) {
        if (!room_can_host($rm, $courseType, $roomTypeNeeded, $day, $program)) {
            continue;
        }
        $ok = true;
        for ($s = $start; $s < $start + $nSlots; $s++) {
            if (empty($availability[$term][$day][$rm][$s])) {
                $ok = false;
                break;
            }
        }
        if ($ok) {
            $rlist[] = $rm;
        }
    }
    return $rlist;
}
function sg_has_conflict($term, $sgid, $day, $start, $nSlots)
{
    global $sessionGroupOccupancy, $totalSlots;

    if (!isset($sessionGroupOccupancy[$term][$day][$sgid])) {
        $sessionGroupOccupancy[$term][$day][$sgid] = array_fill(0, $totalSlots, false);
    }
    $occ = $sessionGroupOccupancy[$term][$day][$sgid];

    for ($s = $start; $s < $start + $nSlots; $s++) {
        if (!empty($occ[$s])) {
            return true;
        }
    }
    return false;
}

// combinations of size k from $array (like itertools.combinations)
function combinations(array $array, $k)
{
    $results = [];
    $n = count($array);
    if ($k === 0) {
        return [[]];
    }
    if ($k > $n) {
        return [];
    }

    $indices = range(0, $k - 1);
    while (true) {
        $combo = [];
        foreach ($indices as $i) {
            $combo[] = $array[$i];
        }
        $results[] = $combo;

        // generate next
        $i = $k - 1;
        while ($i >= 0 && $indices[$i] === $i + $n - $k) {
            $i--;
        }
        if ($i < 0) {
            break;
        }
        $indices[$i]++;
        for ($j = $i + 1; $j < $k; $j++) {
            $indices[$j] = $indices[$j - 1] + 1;
        }
    }
    return $results;
}

function build_day_grid($term, $day, $template, $timeCol)
{
    global $roomCols, $assignments;

    // Initialize grid with "vacant" for all room cols, keep time column
    $grid = [];
    foreach ($template as $row) {
        $newRow = [];
        $newRow[$timeCol] = $row[$timeCol];
        foreach ($roomCols as $rm) {
            $newRow[$rm] = "vacant";
        }
        $grid[] = $newRow;
    }

    // Fill from assignments
    foreach ($assignments as $a) {
        if ($a['term'] !== $term || $a['day'] !== $day) {
            continue;
        }
        $rm = $a['room'];
        $start = $a['start_slot'];
        $n = $a['n_slots'];
        for ($s = $start; $s < $start + $n; $s++) {
            if ($grid[$s][$rm] === "vacant") {
                $grid[$s][$rm] = $a['code'];
            }
        }
    }
    return $grid;
}

function build_overview_filtered($term, array $subset, array $assignments, array $DAYS)
{
    $rows = [];
    foreach ($subset as $r) {
        $csidRaw = $r['course_session_id'] ?? 0;
        $csid = (int)$csidRaw;
        $code = pretty_code($r);
        $title = $r['course_title'] ?? '';

        $rec = [
            "course_session" => $code,
            "course_session_id" => $csid,
            "course_title" => $title
        ];

        foreach ($DAYS as $d) {
            $matchedRooms = [];
            foreach ($assignments as $a) {
                if ($a['term'] === $term && $a['course_session_id'] === $csid && $a['day'] === $d) {
                    $matchedRooms[] = $a['room'];
                }
            }
            if (empty($matchedRooms)) {
                $rec[$d] = "vacant";
            } else {
                $matchedRooms = array_values(array_unique($matchedRooms));
                sort($matchedRooms);
                $rec[$d] = implode(";", $matchedRooms);
            }
        }
        $rows[] = $rec;
    }
    return $rows;
}

function write_sheet_from_rows(Spreadsheet $spreadsheet, $sheetName, array $rows): void
{
    $sheet = new Worksheet($spreadsheet, $sheetName);
    $spreadsheet->addSheet($sheet);

    if (empty($rows)) {
        return;
    }

    $headers = array_keys($rows[0]);

    // Header row
    $rowIndex = 1;
    $colIndex = 1;
    foreach ($headers as $h) {
        // Convert numeric column index → letter (1 → A, 2 → B, etc.)
        $columnLetter = Coordinate::stringFromColumnIndex($colIndex);
        $cellRef = $columnLetter . $rowIndex;

        $sheet->setCellValue($cellRef, $h);
        $colIndex++;
    }

    // Data rows
    foreach ($rows as $row) {
        $rowIndex++;
        $colIndex = 1;

        foreach ($headers as $h) {
            $val = $row[$h] ?? '';

            $columnLetter = Coordinate::stringFromColumnIndex($colIndex);
            $cellRef = $columnLetter . $rowIndex;

            $sheet->setCellValue($cellRef, $val);
            $colIndex++;
        }
    }
}


// ---- Load CSVs ----
echo "Loading CSVs from runtime folder...\n";

$cs_df = read_csv_assoc($COURSE_SESSIONS_CSV);
$sg_df = read_csv_assoc($SESSION_GROUPS_CSV);
$template_df = read_csv_assoc($TEMPLATE_CSV);
$rooms_df = read_csv_assoc($ROOMS_CSV);

// Optional professors CSV (like the Python try/except with undefined const)
$prof_df = null;
if (file_exists($PROFESSORS_CSV)) {
    try {
        $prof_df = read_csv_assoc($PROFESSORS_CSV);
    } catch (Exception $e) {
        $prof_df = null;
    }
}

// ---- Template / timeslots ----
if (empty($template_df)) {
    throw new RuntimeException("Template CSV is empty.");
}
$firstRow = $template_df[0];
$columns = array_keys($firstRow);
$timeCol = $columns[0];

$timeLabels = [];
foreach ($template_df as $row) {
    $timeLabels[] = (string)$row[$timeCol];
}

$timesDt = [];
foreach ($timeLabels as $t) {
    $timesDt[] = parse_time_label($t);
}

// Compute slot length
$deltasMin = [];
for ($i = 0; $i < count($timesDt) - 1; $i++) {
    $a = $timesDt[$i];
    $b = $timesDt[$i + 1];
    $deltaSec = $b->getTimestamp() - $a->getTimestamp();
    $deltasMin[] = $deltaSec / 60.0;
}
if (!empty($deltasMin)) {
    $slotMinutes = (int)round(median($deltasMin));
} else {
    $slotMinutes = 30;
}
$slotHours = $slotMinutes / 60.0;
$totalSlots = count($timeLabels);

echo "Detected {$totalSlots} slots/day, slot length {$slotMinutes} minutes\n";

// ---- Rooms metadata ----
$room_name_col = null;
foreach (["room_name", "room_id", "name", "room"] as $cand) {
    if (array_key_exists($cand, $rooms_df[0] ?? [])) {
        $room_name_col = $cand;
        break;
    }
}
if ($room_name_col === null) {
    throw new RuntimeException("Could not find a room name column in timetable-rooms.csv. Expected one of 'room_name','room_id','name','room'.");
}

// Build map room_key => row
$roomKeyMap = [];
foreach ($rooms_df as $row) {
    $key = trim((string)($row[$room_name_col] ?? ''));
    $row['_room_key'] = $key;
    $roomKeyMap[$key] = $row;
}

// Room columns from template (all non-time columns)
$roomCols = [];
foreach ($columns as $col) {
    if ($col !== $timeCol) {
        $roomCols[] = $col;
    }
}

// rooms_meta
$roomsMeta = [];
foreach ($roomCols as $rm) {
    $key = trim((string)$rm);
    $m = $roomKeyMap[$key] ?? null;
    if ($m !== null) {
        $rtype = strtolower(trim((string)($m['room_type'] ?? 'lecture')));
        $ctex = strtolower(trim((string)($m['course_type_exclusive_to'] ?? 'none')));
        $exd = strtolower(trim((string)($m['exclusive_days'] ?? '')));
        $exProgramsRaw = trim((string)($m['exclusive_programs'] ?? ''));
        $exPrograms = [];

        if ($exProgramsRaw !== '') {
            foreach (preg_split('/[,;]+/', $exProgramsRaw) as $p) {
                $p = strtoupper(trim($p));
                if ($p !== '') {
                    $exPrograms[] = $p;
                }
            }
        }

        $roomsMeta[$rm] = [
            "room_type" => $rtype ?: "lecture",
            "course_type_exclusive_to" => $ctex ?: "none",
            "exclusive_days" => $exd,
            "exclusive_programs" => $exPrograms, // NEW
        ];
    } else {
        $roomsMeta[$rm] = [
            "room_type" => "lecture",
            "course_type_exclusive_to" => "none",
            "exclusive_days" => "",
        ];
    }
}

// Small breakdown print (like Python Counter)
$typeCounts = [];
foreach ($roomCols as $rm) {
    $rt = $roomsMeta[$rm]["room_type"] ?? "lecture";
    $typeCounts[$rt] = ($typeCounts[$rt] ?? 0) + 1;
}
echo "Rooms loaded: " . count($roomCols) . " lecture/comlab breakdown: " . json_encode($typeCounts) . "\n";

// ---- Course sessions preprocessing ----
$cs = $cs_df; // copy

$allExact = true;
for ($i = 0; $i < count($cs); $i++) {
    $row = $cs[$i];

    $lab_days = (int)($row['total_laboratory_class_days'] ?? 0);
    $lect_days = (int)($row['total_lecture_class_days'] ?? 0);
    $total_days = $lab_days + $lect_days;

    $class_hours = (float)($row['class_hours'] ?? 0.0);

    if ($confineLabs && $lab_days > 0) {
        if (abs($class_hours - 2.0) > 1e-6) {
            $unassigned[] = [
                'course_session_id' => (int)($row['course_session_id'] ?? 0),
                'code'              => pretty_code($row),
                'term'              => 'both',
                'reason'            => 'lab_course_must_be_2_hours',
            ];
            continue;
        }
    }

    $required_slots_float = ($slotHours > 0) ? ($class_hours / $slotHours) : 0.0;
    $required_slots_round = (int)round($required_slots_float);
    $exact = (abs($required_slots_float - $required_slots_round) < 1e-6);
    if (!$exact) {
        $allExact = false;
    }

    $course_type_norm = strtolower(trim((string)($row['course_type'] ?? '')));
    $prog = $row['program_abbreviation'] ?? $row['academic_program'] ?? null;

    $cs[$i]['total_laboratory_class_days'] = $lab_days;
    $cs[$i]['total_lecture_class_days'] = $lect_days;
    $cs[$i]['total_days'] = $total_days;
    $cs[$i]['class_hours'] = $class_hours;
    $cs[$i]['required_slots_float'] = $required_slots_float;
    $cs[$i]['required_slots_round'] = $required_slots_round;
    $cs[$i]['required_slots_exact'] = $exact;
    $cs[$i]['required_slots'] = $required_slots_round;
    $cs[$i]['course_type_norm'] = $course_type_norm;
    $cs[$i]['prog'] = $prog;
}

if (!$allExact) {
    echo "Warning: some class_hours not exact multiples of slot; rounding to nearest slot.\n";
}

// ordering: program, year, session_group, then larger classes/days first
usort($cs, function ($a, $b) {
    $ka = [
        strtolower((string)($a['prog'] ?? '')),
        (int)($a['year_level'] ?? 0),
        (int)($a['session_group_id'] ?? 0),
        -(int)($a['required_slots'] ?? 0),
        -(int)($a['total_days'] ?? 0),
    ];
    $kb = [
        strtolower((string)($b['prog'] ?? '')),
        (int)($b['year_level'] ?? 0),
        (int)($b['session_group_id'] ?? 0),
        -(int)($b['required_slots'] ?? 0),
        -(int)($b['total_days'] ?? 0),
    ];
    return $ka <=> $kb;
});

// ---- Availability and occupancy ----
$availability = [];
foreach (["1st", "2nd"] as $term) {
    $availability[$term] = [];
    foreach ($DAYS as $day) {
        $availability[$term][$day] = [];
        foreach ($roomCols as $rm) {
            $availability[$term][$day][$rm] = array_fill(0, $totalSlots, true);
        }
    }
}

// --- Reorder sessions: schedule non-PE/NSTP first, then PE/NSTP last ---
// This ensures PE and NSTP course types are assigned after other subjects.
$cs_non_pe = [];
$cs_pe = [];

foreach ($cs as $r) {
    $ct = strtolower(trim((string)($r['course_type_norm'] ?? $r['course_type'] ?? '')));
    if ($ct === 'pe' || $ct === 'nstp') {
        $cs_pe[] = $r;
    } else {
        $cs_non_pe[] = $r;
    }
}

$cs_ordered = array_merge($cs_non_pe, $cs_pe);
// Use $cs_ordered in scheduling below instead of $cs


// apply exclusive_days: room only allowed on that day
foreach ($roomsMeta as $rm => $md) {
    $ex = $md['exclusive_days'] ?? '';
    if ($ex !== '' && strtolower($ex) !== 'nan') {
        foreach (["1st", "2nd"] as $term) {
            foreach ($DAYS as $d) {
                if (strtolower($d) !== strtolower($ex)) {
                    $availability[$term][$d][$rm] = array_fill(0, $totalSlots, false);
                }
            }
        }
    }
}

// session_group_occupancy
$sessionGroupOccupancy = [];
foreach (["1st", "2nd"] as $term) {
    $sessionGroupOccupancy[$term] = [];
    foreach ($DAYS as $day) {
        $sessionGroupOccupancy[$term][$day] = []; // lazy init per sgid
    }
}

$assignments = [];
$unassigned = [];

$lunchStartDt = DateTime::createFromFormat('H:i', $LUNCH_START);
$lunchEndDt = DateTime::createFromFormat('H:i', $LUNCH_END);

echo "Running fast greedy scheduler...\n";

// ---- Greedy scheduling ----
foreach (["1st", "2nd"] as $term) {
    foreach ($cs as $row) {
        $academicTermRaw = $row['academic_term'] ?? '';
        $academicTerm = strtolower(trim((string)$academicTermRaw));

        // semestral: only schedule once in 1st term loop
        if ($academicTerm === 'semestral' && $term === '2nd') {
            continue;
        }
        if ($academicTerm !== 'semestral' && $academicTerm !== strtolower($term)) {
            continue;
        }

        $csidRaw = $row['course_session_id'] ?? 0;
        $csid = (int)$csidRaw;
        $code = pretty_code($row);
        $courseType = $row['course_type_norm'] ?? '';
        $neededLect = (int)($row['total_lecture_class_days'] ?? 0);
        $neededLab = (int)($row['total_laboratory_class_days'] ?? 0);
        $nSlots = (int)($row['required_slots'] ?? 0);
        $sgid = $row['session_group_id'] ?? null;

        $sg_row = get_session_group_row($sgid);
        $sg_program = strtoupper(trim((string)($sg_row['academic_program'] ?? '')));

        $courseProgramsRaw = trim((string)($row['course_programs'] ?? ''));

        if ($courseProgramsRaw !== '') {
            $allowed = [];
            foreach (preg_split('/[,;]+/', $courseProgramsRaw) as $p) {
                $p = strtoupper(trim($p));
                if ($p !== '') {
                    $allowed[] = $p;
                }
            }

            if (!in_array($sg_program, $allowed, true)) {
                $unassigned[] = [
                    'course_session_id' => $csid,
                    'code' => $code,
                    'term' => $term,
                    'reason' => 'course_not_allowed_for_session_program',
                ];
                continue;
            }
        }

        if ($nSlots <= 0 || ($neededLect + $neededLab) === 0) {
            $unassigned[] = [
                "course_session_id" => $csid,
                "code" => $code,
                "term" => $term,
                "reason" => "invalid_slots_or_zero_days"
            ];
            continue;
        }

        $placed = false;
        $ctype = strtolower(trim((string)$courseType));
        $isPE   = ($ctype === 'pe');
        $isNSTP = ($ctype === 'nstp');
        $isLabCourse = ($neededLab > 0);


        /*
        |--------------------------------------------------------------------------
        | LAB COURSES — CONFINED START TIMES
        |--------------------------------------------------------------------------
        */
        if ($confineLabs && $isLabCourse) {

            $sg_row = get_session_group_row($sgid);
            $st = strtolower((string)($sg_row['session_time'] ?? ''));

            $allowedTimes = [];

            if ($st === 'morning') {
                $allowedTimes = ['08:00', '10:00'];
            } elseif ($st === 'afternoon') {
                $allowedTimes = ['13:30', '15:30'];
            } elseif ($st === 'evening') {
                $allowedTimes = ['17:30', '19:30'];
            }

            $candidateStarts = [];

            foreach ($allowedTimes as $hm) {
                foreach ($timesDt as $i => $dt) {
                    if ($dt->format('H:i') === $hm) {
                        if ($i + $nSlots <= $totalSlots) {
                            $candidateStarts[] = $i;
                        }
                        break;
                    }
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | PE / NSTP — ASSIGNED LAST, SESSION-OPPOSITE
        |--------------------------------------------------------------------------
        */
        elseif ($isPE || $isNSTP) {

            $sg_row = get_session_group_row($sgid);
            $st = strtolower((string)($sg_row['session_time'] ?? ''));

            if ($st === 'morning') {
                $preferred = ['afternoon', 'evening'];
            } elseif ($st === 'afternoon') {
                $preferred = ['morning', 'evening'];
            } else {
                $preferred = ['afternoon', 'morning'];
            }

            if ($isNSTP) {
                $preferred = array_values(array_filter(
                    $preferred,
                    fn($x) => $x !== 'evening'
                ));
            }

            $candidateStarts = [];

            foreach ($preferred as $p) {
                $range = session_primary_slot_range($p);
                if (!$range) continue;

                [$ws, $we] = $range;
                for ($s = $ws; $s + $nSlots <= $we; $s++) {
                    $candidateStarts[] = $s;
                }
            }

            // absolute fallback
            if (empty($candidateStarts)) {
                for ($s = 0; $s <= $totalSlots - $nSlots; $s++) {
                    $startTime = slot_dt($s)->format('H:i');
                    if ($isNSTP && strtotime($startTime) >= strtotime('17:30')) {
                        continue;
                    }
                    $candidateStarts[] = $s;
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | NORMAL COURSES (INCLUDING LABS WHEN NOT CONFINED)
        |--------------------------------------------------------------------------
        */
        else {
            $candidateStarts = compute_candidate_starts_with_overflow($row, $term);
        }



        foreach ($candidateStarts as $start) {
            if (spans_lunch($start, $nSlots)) {
                continue;
            }


            // candidate lecture days
            $lect_days = [];
            foreach ($DAYS as $d) {
                $r = available_rooms($term, $d, "lecture", $courseType, $start, $nSlots, $sg_program);
                if (!empty($r)) {
                    $lect_days[] = $d;
                }
            }

            // candidate lab days
            $lab_days = [];
            foreach ($DAYS as $d) {
                $r = available_rooms($term, $d, "comlab", $courseType, $start, $nSlots, $sg_program);
                if (empty($r)) {
                    $r = available_rooms($term, $d, "lab", $courseType, $start, $nSlots, $sg_program);
                }
                if (!empty($r)) {
                    $lab_days[] = $d;
                }
            }

            if (count($lect_days) < $neededLect || count($lab_days) < $neededLab) {
                continue;
            }

            $lect_combos = ($neededLect == 0) ? [[]] : combinations($lect_days, $neededLect);
            $lab_combos = ($neededLab == 0) ? [[]] : combinations($lab_days, $neededLab);

            $success_blocks = null;

            foreach ($lect_combos as $lcombo) {
                foreach ($lab_combos as $labcombo) {
                    // ensure no overlap between lecture and lab days
                    if (!empty(array_intersect($lcombo, $labcombo))) {
                        continue;
                    }

                    // check session_group conflicts (current term only)
                    $conflict = false;
                    foreach (array_merge($lcombo, $labcombo) as $d) {
                        if (sg_has_conflict($term, $sgid, $d, $start, $nSlots)) {
                            $conflict = true;
                            break;
                        }
                    }
                    if ($conflict) {
                        continue;
                    }

                    // For semestral, check existence of rooms in BOTH terms for chosen days
                    if ($academicTerm === 'semestral') {
                        $sem_ok = true;
                        // lecture days
                        foreach ($lcombo as $d) {
                            $r1 = available_rooms("1st", $d, "lecture", $courseType, $start, $nSlots, $sg_program);
                            $r2 = available_rooms("2nd", $d, "lecture", $courseType, $start, $nSlots, $sg_program);
                            if (empty($r1) || empty($r2)) {
                                $sem_ok = false;
                                break;
                            }
                        }
                        if (!$sem_ok) {
                            continue;
                        }
                        // lab days
                        foreach ($labcombo as $d) {
                            $r1 = available_rooms("1st", $d, "comlab", $courseType, $start, $nSlots, $sg_program);
                            if (empty($r1)) {
                                $r1 = available_rooms("1st", $d, "lab", $courseType, $start, $nSlots, $sg_program);
                            }
                            $r2 = available_rooms("2nd", $d, "comlab", $courseType, $start, $nSlots, $sg_program);
                            if (empty($r2)) {
                                $r2 = available_rooms("2nd", $d, "lab", $courseType, $start, $nSlots, $sg_program);
                            }
                            if (empty($r1) || empty($r2)) {
                                $sem_ok = false;
                                break;
                            }
                        }
                        if (!$sem_ok) {
                            continue;
                        }
                    }

                    // pick first-fit rooms for this TERM
                    $chosen_blocks = [];
                    $ok = true;

                    // lecture rooms
                    foreach ($lcombo as $d) {
                        $rlist = available_rooms($term, $d, "lecture", $courseType, $start, $nSlots, $sg_program);
                        if (empty($rlist)) {
                            $ok = false;
                            break;
                        }
                        $chosen_blocks[] = [
                            "term" => $term,
                            "day" => $d,
                            "room" => $rlist[0],
                            "is_lab" => false
                        ];
                    }
                    if (!$ok) {
                        continue;
                    }

                    // lab rooms
                    foreach ($labcombo as $d) {
                        $rlist = available_rooms($term, $d, "comlab", $courseType, $start, $nSlots, $sg_program);
                        if (empty($rlist)) {
                            $rlist = available_rooms($term, $d, "lab", $courseType, $start, $nSlots, $sg_program);
                        }
                        if (empty($rlist)) {
                            $ok = false;
                            break;
                        }
                        $chosen_blocks[] = [
                            "term" => $term,
                            "day" => $d,
                            "room" => $rlist[0],
                            "is_lab" => true
                        ];
                    }
                    if (!$ok) {
                        continue;
                    }

                    // For semestral: pick rooms for 2nd term (rooms can differ)
                    $chosen_blocks_2nd = [];
                    if ($academicTerm === 'semestral') {
                        foreach ($chosen_blocks as $blk) {
                            $d = $blk['day'];
                            if ($blk['is_lab']) {
                                $rlist2 = available_rooms("2nd", $d, "comlab", $courseType, $start, $nSlots, $sg_program);
                                if (empty($rlist2)) {
                                    $rlist2 = available_rooms("2nd", $d, "lab", $courseType, $start, $nSlots, $sg_program);
                                }
                            } else {
                                $rlist2 = available_rooms("2nd", $d, "lecture", $courseType, $start, $nSlots, $sg_program);
                            }
                            if (empty($rlist2)) {
                                $ok = false;
                                break;
                            }
                            $chosen_blocks_2nd[] = [
                                "term" => "2nd",
                                "day" => $d,
                                "room" => $rlist2[0],
                                "is_lab" => $blk['is_lab']
                            ];
                        }
                        if (!$ok) {
                            continue;
                        }
                    }

                    // Commit assignments for this term (and for semestral, also 2nd term)
                    $blocks_to_commit = [];

                    foreach ($chosen_blocks as $blk) {
                        $blocks_to_commit[] = [
                            "course_session_id" => $csid,
                            "code" => $code,
                            "term" => $blk['term'],
                            "day" => $blk['day'],
                            "room" => $blk['room'],
                            "start_slot" => $start,
                            "n_slots" => $nSlots,
                            "is_lab" => $blk['is_lab'],
                            "session_group_id" => $sgid
                        ];
                    }

                    if ($academicTerm === 'semestral') {
                        foreach ($chosen_blocks_2nd as $blk) {
                            $blocks_to_commit[] = [
                                "course_session_id" => $csid,
                                "code" => $code,
                                "term" => $blk['term'],
                                "day" => $blk['day'],
                                "room" => $blk['room'],
                                "start_slot" => $start,
                                "n_slots" => $nSlots,
                                "is_lab" => $blk['is_lab'],
                                "session_group_id" => $sgid
                            ];
                        }
                    }

                    // Update availability and session_group_occupancy
                    foreach ($blocks_to_commit as $b) {
                        $assignments[] = $b;
                        $t_term = $b['term'];
                        $t_day = $b['day'];
                        $t_rm = $b['room'];
                        $t_sgid = $b['session_group_id'];

                        if (!isset($sessionGroupOccupancy[$t_term][$t_day][$t_sgid])) {
                            $sessionGroupOccupancy[$t_term][$t_day][$t_sgid] = array_fill(0, $totalSlots, false);
                        }

                        for ($s = $b['start_slot']; $s < $b['start_slot'] + $b['n_slots']; $s++) {
                            $availability[$t_term][$t_day][$t_rm][$s] = false;
                            $sessionGroupOccupancy[$t_term][$t_day][$t_sgid][$s] = true;
                        }
                    }

                    $success_blocks = $blocks_to_commit;
                    break;
                }

                if ($success_blocks !== null) {
                    break;
                }
            }

            if ($success_blocks !== null) {
                $placed = true;
                break;
            }
        }

        if (!$placed) {
            $unassigned[] = [
                "course_session_id" => $csid,
                "code" => $code,
                "term" => $term,
                "reason" => "no_start_day_room_found"
            ];
        }
    }
}

echo "Greedy scheduling complete. Assigned blocks: " . count($assignments) . " Unassigned records: " . count($unassigned) . "\n";

// ---- Build overviews ----
$cs_1st = [];
$cs_2nd = [];
foreach ($cs as $row) {
    $at = strtolower(trim((string)($row['academic_term'] ?? '')));
    if ($at === '1st' || $at === 'semestral') {
        $cs_1st[] = $row;
    }
    if ($at === '2nd' || $at === 'semestral') {
        $cs_2nd[] = $row;
    }
}

$overview_1st = build_overview_filtered("1st", $cs_1st, $assignments, $DAYS);
$overview_2nd = build_overview_filtered("2nd", $cs_2nd, $assignments, $DAYS);

// Compose Unassigned final sheet: collapse unique csids with reasons
$unq = [];
foreach ($unassigned as $u) {
    $cid = (int)$u["course_session_id"];
    $term = $u["term"] ?? "";
    $reason = $u["reason"] ?? "";
    if (!isset($unq[$cid])) {
        $unq[$cid] = [
            "course_session_id" => $cid,
            "code" => $u["code"] ?? "",
            "terms_tried" => [$term],
            "reasons" => [$reason],
        ];
    } else {
        $unq[$cid]["terms_tried"][] = $term;
        $unq[$cid]["reasons"][] = $reason;
    }
}
$unassigned_list = [];
foreach ($unq as $v) {
    $row = [
        "course_session_id" => $v["course_session_id"],
        "code" => $v["code"],
        "terms_tried" => implode(",", array_unique($v["terms_tried"])),
        "reason" => implode(";", array_unique($v["reasons"])),
    ];
    $unassigned_list[] = $row;
}

// ---- Export XLSX ----
echo "Writing workbook to {$OUTPUT_XLSX}\n";

$spreadsheet = new Spreadsheet();
// remove default first sheet
$spreadsheet->removeSheetByIndex(0);

// Day grids per term/day
foreach (["1st", "2nd"] as $term) {
    foreach ($DAYS as $d) {
        $sheetName = $term . "_" . substr($d, 0, 3);
        $grid = build_day_grid($term, $d, $template_df, $timeCol);
        write_sheet_from_rows($spreadsheet, $sheetName, $grid);
    }
}

// Overview sheets
write_sheet_from_rows($spreadsheet, "Overview_1st", $overview_1st);
write_sheet_from_rows($spreadsheet, "Overview_2nd", $overview_2nd);

// Unassigned sheet
if (!empty($unassigned_list)) {
    write_sheet_from_rows($spreadsheet, "Unassigned", $unassigned_list);
} else {
    write_sheet_from_rows($spreadsheet, "Unassigned", [
        ["note" => "No unassigned course_sessions"]
    ]);
}

$writer = new Xlsx($spreadsheet);
$writer->save($OUTPUT_XLSX);

// Summary
$total_cs = count($cs);
$unique_assigned = count(array_unique(array_map(function ($a) {
    return $a['course_session_id'];
}, $assignments)));
$unique_unassigned = count($unassigned_list);

echo "Done. Output file: {$OUTPUT_XLSX}\n";
echo "Summary: total course_sessions: {$total_cs} unique assigned course_session ids: {$unique_assigned} unique unassigned ids: {$unique_unassigned}\n";
