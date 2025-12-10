<?php

// TIMETABLE (FAST GREEDY, LAB-CONFINED VERSION) — PHP
//
// Usage:
//   php scripts/process_timetable_lab_confined.php "/path/to/input-csvs" "/path/to/output" 1
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
//
// Rules specific to this script:
//
// 1. Any course with total_laboratory_class_days > 0 MUST have class_hours == 2.0 (±1e-6).
//    If not, the script prints an error and exits with non-zero status.
// 2. Any course with total_laboratory_class_days > 0 may ONLY start at:
//      - morning:   08:00, 10:00
//      - afternoon: 13:30, 15:30
//      - evening:   17:30, 19:30
//    depending on its session_group.session_time.
//    If it can’t be scheduled in those windows, it is left unassigned.
// 3. Lab courses are scheduled before non-lab courses.
// 4. All other constraints still apply:
//    - lunch block 12:00–12:30
//    - session_group no overlap
//    - semestral duplication
//    - room_type and course_type_exclusive_to
//    - room exclusive_days
//    - session_time windows for NON-lab courses (coarse bounds).
//

require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

// ---- CONFIG / ARGS ----
if ($argc < 4) {
    fwrite(STDERR, "Usage: php {$argv[0]} <input_dir> <output_dir> <timetable_id>\n");
    exit(1);
}

$inputDir    = rtrim($argv[1], "/");
$outputDir   = rtrim($argv[2], "/");
$timetableId = $argv[3];

$OUTPUT_XLSX = $outputDir . "/" . $timetableId . ".xlsx";

$COURSE_SESSIONS_CSV = $inputDir . "/course-sessions.csv";
$SESSION_GROUPS_CSV  = $inputDir . "/session-groups.csv";
$TEMPLATE_CSV        = $inputDir . "/timetable_template.csv";
$ROOMS_CSV           = $inputDir . "/timetable-rooms.csv";
$PROFESSORS_CSV      = $inputDir . "/timetable-professors.csv"; // optional

$LUNCH_START = "12:00";
$LUNCH_END   = "12:30";
$DAYS        = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];

// Globals
$timesDt               = [];
$slotMinutes           = 30;
$totalSlots            = 0;
$roomCols              = [];
$roomsMeta             = [];
$availability          = [];
$sessionGroupOccupancy = [];
$assignments           = [];
$lunchStartDt          = null;
$lunchEndDt            = null;
$sg_df                 = [];

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
        'g:i A',  // "1:30 PM"
        'g:ia',   // "1:30pm"
        'g:i a',  // "1:30 pm"
        'H:i',    // "13:30"
        'g:i A ', // "1:30 PM "
    ];

    foreach ($formats as $fmt) {
        $dt = DateTime::createFromFormat($fmt, $s);
        if ($dt instanceof DateTime) {
            return $dt;
        }
    }

    return new DateTime($s);
}

function median(array $values)
{
    if (empty($values)) return 0;
    sort($values);
    $count = count($values);
    $mid   = intdiv($count, 2);
    if ($count % 2) {
        return $values[$mid];
    }
    return ($values[$mid - 1] + $values[$mid]) / 2.0;
}

function pretty_code(array $row)
{
    $pa   = str_replace(' ', '', (string)($row['program_abbreviation'] ?? $row['academic_program'] ?? 'UNK'));
    $year = str_replace(' ', '', (string)($row['year_level'] ?? 'UNK'));
    $sg   = (string)($row['session_group_id'] ?? '');
    $csid = (int)($row['course_session_id'] ?? 0);
    return $pa . "_" . $year . "_" . $sg . "_" . $csid;
}

function slot_dt($idx)
{
    global $timesDt;
    return $timesDt[$idx];
}

function get_slot_minutes()
{
    global $slotMinutes;
    return $slotMinutes;
}

function spans_lunch($start, $nSlots)
{
    global $timesDt, $lunchStartDt, $lunchEndDt, $totalSlots;

    $sdt      = slot_dt($start);
    $endIndex = min($start + $nSlots, $totalSlots) - 1;
    $edt      = clone $timesDt[$endIndex];
    $edt->modify("+" . get_slot_minutes() . " minutes");

    return !($edt <= $lunchStartDt || $sdt >= $lunchEndDt);
}

// Coarse session_time bounds (for NON-lab courses)
function session_time_bounds($label)
{
    if (empty($label)) return null;
    $l = strtolower(trim((string)$label));
    $map = [
        'morning'   => ['start' => '07:00', 'end' => '12:00'],
        'afternoon' => ['start' => '12:30', 'end' => '17:30'],
        'evening'   => ['start' => '17:30', 'end' => '19:30'], // must finish by 19:30
    ];
    return $map[$l] ?? null;
}

// Discrete lab 2-hour start times by session_time
function lab_allowed_start_times_strings($label)
{
    $l = strtolower(trim((string)$label));
    switch ($l) {
        case 'morning':
            return ['08:00', '10:00'];
        case 'afternoon':
            return ['13:30', '15:30'];
        case 'evening':
            return ['17:30', '19:30'];
        default:
            return [];
    }
}

// Map lab HH:MM starts to slot indices for 2-hour blocks
function lab_allowed_start_slots($label, $nSlots)
{
    global $timesDt, $totalSlots;

    $allowedHms = lab_allowed_start_times_strings($label);
    if (empty($allowedHms)) return [];

    $indices = [];
    foreach ($allowedHms as $hm) {
        for ($i = 0; $i < $totalSlots; $i++) {
            $dt = $timesDt[$i];
            if (!$dt instanceof DateTime) continue;
            if ($dt->format('H:i') === $hm) {
                if ($i + $nSlots <= $totalSlots) {
                    $indices[] = $i;
                }
                break;
            }
        }
    }
    $indices = array_values(array_unique($indices));
    sort($indices);
    return $indices;
}

function get_session_group_row($sgid)
{
    global $sg_df;
    if (empty($sgid) || !is_array($sg_df)) return null;
    foreach ($sg_df as $r) {
        if ((string)($r['session_group_id'] ?? '') === (string)$sgid) {
            return $r;
        }
    }
    return null;
}

// Parse exclusive_days like "mon,tue" → ["monday","tuesday"]
function parse_exclusive_days($str)
{
    $str = trim((string)$str);
    if ($str === '' || strtolower($str) === 'nan') return [];
    $parts = preg_split('/[,;]+/', strtolower($str));
    $days  = [];
    foreach ($parts as $p) {
        $p = trim($p);
        if ($p === '') continue;
        switch ($p) {
            case 'mon':
            case 'monday':
                $days[] = 'monday'; break;
            case 'tue':
            case 'tues':
            case 'tuesday':
                $days[] = 'tuesday'; break;
            case 'wed':
            case 'weds':
            case 'wednesday':
                $days[] = 'wednesday'; break;
            case 'thu':
            case 'thur':
            case 'thurs':
            case 'thursday':
                $days[] = 'thursday'; break;
            case 'fri':
            case 'friday':
                $days[] = 'friday'; break;
            case 'sat':
            case 'saturday':
                $days[] = 'saturday'; break;
            case 'sun':
            case 'sunday':
                $days[] = 'sunday'; break;
        }
    }
    return array_values(array_unique($days));
}

function room_can_host($rm, $courseType, $roomTypeNeeded, $day)
{
    global $roomsMeta;

    $md = $roomsMeta[$rm] ?? [];
    $rt = strtolower($md['room_type'] ?? '');
    if ($rt !== strtolower($roomTypeNeeded)) {
        return false;
    }

    $ex = strtolower($md['course_type_exclusive_to'] ?? 'none');
    $ct = strtolower($courseType ?? '');

    if ($ct === 'pe' || $ct === 'nstp') {
        if ($ex !== $ct) return false;
    } else {
        if ($ex !== 'none') return false;
    }

    $exdays = $md['exclusive_days'] ?? [];
    if (!empty($exdays)) {
        if (!in_array(strtolower($day), $exdays, true)) {
            return false;
        }
    }

    return true;
}

function available_rooms($term, $day, $roomTypeNeeded, $courseType, $start, $nSlots)
{
    global $roomCols, $availability;
    $rlist = [];
    foreach ($roomCols as $rm) {
        if (!room_can_host($rm, $courseType, $roomTypeNeeded, $day)) {
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
        if (!empty($occ[$s])) return true;
    }
    return false;
}

function combinations(array $array, $k)
{
    $results = [];
    $n = count($array);
    if ($k === 0) {
        return [[]];
    }
    if ($k > $n) return [];

    $indices = range(0, $k - 1);
    while (true) {
        $combo = [];
        foreach ($indices as $i) $combo[] = $array[$i];
        $results[] = $combo;

        $i = $k - 1;
        while ($i >= 0 && $indices[$i] === $i + $n - $k) {
            $i--;
        }
        if ($i < 0) break;
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

    $grid = [];
    foreach ($template as $row) {
        $newRow = [];
        $newRow[$timeCol] = $row[$timeCol];
        foreach ($roomCols as $rm) {
            $newRow[$rm] = "vacant";
        }
        $grid[] = $newRow;
    }

    foreach ($assignments as $a) {
        if ($a['term'] !== $term || $a['day'] !== $day) continue;
        $rm    = $a['room'];
        $start = $a['start_slot'];
        $n     = $a['n_slots'];
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
        $csid = (int)($r['course_session_id'] ?? 0);
        $code = pretty_code($r);
        $title = $r['course_title'] ?? '';

        $rec = [
            "course_session"    => $code,
            "course_session_id" => $csid,
            "course_title"      => $title,
        ];

        foreach ($DAYS as $d) {
            $matched = [];
            foreach ($assignments as $a) {
                if ($a['term'] === $term && $a['course_session_id'] === $csid && $a['day'] === $d) {
                    $matched[] = $a['room'];
                }
            }
            if (empty($matched)) {
                $rec[$d] = "vacant";
            } else {
                $matched = array_values(array_unique($matched));
                sort($matched);
                $rec[$d] = implode(";", $matched);
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

    if (empty($rows)) return;

    $headers = array_keys($rows[0]);

    $rowIndex = 1;
    $colIndex = 1;
    foreach ($headers as $h) {
        $colLetter = Coordinate::stringFromColumnIndex($colIndex);
        $sheet->setCellValue($colLetter . $rowIndex, $h);
        $colIndex++;
    }

    foreach ($rows as $row) {
        $rowIndex++;
        $colIndex = 1;
        foreach ($headers as $h) {
            $val = $row[$h] ?? '';
            $colLetter = Coordinate::stringFromColumnIndex($colIndex);
            $sheet->setCellValue($colLetter . $rowIndex, $val);
            $colIndex++;
        }
    }
}

// ---- Load CSVs ----
echo "Loading CSVs (lab-confined)...\n";

$cs_df       = read_csv_assoc($COURSE_SESSIONS_CSV);
$sg_df       = read_csv_assoc($SESSION_GROUPS_CSV);
$template_df = read_csv_assoc($TEMPLATE_CSV);
$rooms_df    = read_csv_assoc($ROOMS_CSV);

// Optional professors
$prof_df = null;
if (file_exists($PROFESSORS_CSV)) {
    try { $prof_df = read_csv_assoc($PROFESSORS_CSV); } catch (Exception $e) { $prof_df = null; }
}

// ---- Template / timeslots ----
if (empty($template_df)) {
    throw new RuntimeException("Template CSV is empty.");
}
$firstRow = $template_df[0];
$columns  = array_keys($firstRow);
$timeCol  = $columns[0];

$timeLabels = [];
foreach ($template_df as $row) {
    $timeLabels[] = (string)$row[$timeCol];
}

$timesDt = [];
foreach ($timeLabels as $t) {
    $timesDt[] = parse_time_label($t);
}

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
    throw new RuntimeException("Could not find room_name column in timetable-rooms.csv.");
}

$roomKeyMap = [];
foreach ($rooms_df as $row) {
    $key = trim((string)($row[$room_name_col] ?? ''));
    $row['_room_key'] = $key;
    $roomKeyMap[$key] = $row;
}

$roomCols = [];
foreach ($columns as $col) {
    if ($col !== $timeCol) $roomCols[] = $col;
}

$roomsMeta = [];
foreach ($roomCols as $rm) {
    $key = trim((string)$rm);
    $m   = $roomKeyMap[$key] ?? null;
    if ($m) {
        $rtype = strtolower(trim((string)($m['room_type'] ?? 'lecture')));
        $ctex  = strtolower(trim((string)($m['course_type_exclusive_to'] ?? 'none')));
        $exd   = $m['exclusive_days'] ?? '';
        $roomsMeta[$rm] = [
            'room_type'              => $rtype ?: 'lecture',
            'course_type_exclusive_to' => $ctex ?: 'none',
            'exclusive_days'         => parse_exclusive_days($exd),
        ];
    } else {
        $roomsMeta[$rm] = [
            'room_type'              => 'lecture',
            'course_type_exclusive_to' => 'none',
            'exclusive_days'         => [],
        ];
    }
}

$typeCounts = [];
foreach ($roomCols as $rm) {
    $rt = $roomsMeta[$rm]['room_type'] ?? 'lecture';
    $typeCounts[$rt] = ($typeCounts[$rt] ?? 0) + 1;
}
echo "Rooms loaded: " . count($roomCols) . " by type: " . json_encode($typeCounts) . "\n";

// ---- Course sessions preprocessing ----
$cs = $cs_df;

$allExact     = true;
$labViolations = [];

for ($i = 0; $i < count($cs); $i++) {
    $row = $cs[$i];

    $lab_days  = (int)($row['total_laboratory_class_days'] ?? 0);
    $lect_days = (int)($row['total_lecture_class_days'] ?? 0);
    $total_days= $lab_days + $lect_days;

    $class_hours = (float)($row['class_hours'] ?? 0.0);
    $slotHours   = $slotMinutes > 0 ? ($slotMinutes / 60.0) : 0.5;
    $required_slots_float = ($slotHours > 0) ? ($class_hours / $slotHours) : 0.0;
    $required_slots_round = (int)round($required_slots_float);
    $exact = (abs($required_slots_float - $required_slots_round) < 1e-6);
    if (!$exact) $allExact = false;

    $course_type_norm = strtolower(trim((string)($row['course_type'] ?? '')));
    $prog             = $row['program_abbreviation'] ?? $row['academic_program'] ?? null;

    if ($lab_days > 0) {
        if (abs($class_hours - 2.0) > 1e-6) {
            $labViolations[] = $row;
        }
    }

    $cs[$i]['total_laboratory_class_days'] = $lab_days;
    $cs[$i]['total_lecture_class_days']    = $lect_days;
    $cs[$i]['total_days']                  = $total_days;
    $cs[$i]['class_hours']                 = $class_hours;
    $cs[$i]['required_slots_float']        = $required_slots_float;
    $cs[$i]['required_slots_round']        = $required_slots_round;
    $cs[$i]['required_slots_exact']        = $exact;
    $cs[$i]['required_slots']              = $required_slots_round;
    $cs[$i]['course_type_norm']            = $course_type_norm;
    $cs[$i]['prog']                        = $prog;
}

if (!$allExact) {
    echo "Warning: some class_hours not exact multiples of slot; rounding to nearest slot.\n";
}

if (!empty($labViolations)) {
    fwrite(STDERR, "ERROR: In lab-confined mode, all courses with laboratory days must have class_hours == 2.0\n");
    foreach ($labViolations as $row) {
        $csid = $row['course_session_id'] ?? 'N/A';
        $code = pretty_code($row);
        $ch   = $row['class_hours'] ?? 'N/A';
        fwrite(STDERR, " - course_session_id={$csid}, code={$code}, class_hours={$ch}\n");
    }
    exit(1);
}

// sort by program/year/sg then larger sessions first
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

// ---- Availability & occupancy ----
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

// room exclusive_days
foreach ($roomsMeta as $rm => $md) {
    $exdays = $md['exclusive_days'] ?? [];
    if (!empty($exdays)) {
        foreach (["1st", "2nd"] as $term) {
            foreach ($DAYS as $d) {
                if (!in_array(strtolower($d), $exdays, true)) {
                    $availability[$term][$d][$rm] = array_fill(0, $totalSlots, false);
                }
            }
        }
    }
}

$sessionGroupOccupancy = [];
foreach (["1st", "2nd"] as $term) {
    $sessionGroupOccupancy[$term] = [];
    foreach ($DAYS as $day) {
        $sessionGroupOccupancy[$term][$day] = [];
    }
}

$assignments = [];
$unassigned  = [];

$lunchStartDt = DateTime::createFromFormat('H:i', $LUNCH_START);
$lunchEndDt   = DateTime::createFromFormat('H:i', $LUNCH_END);

echo "Running lab-confined scheduler...\n";

// reorder: lab first, PE/NSTP last in each group
$cs_lab_non_pe    = [];
$cs_lab_pe        = [];
$cs_nonlab_non_pe = [];
$cs_nonlab_pe     = [];

foreach ($cs as $row) {
    $lab_days = (int)($row['total_laboratory_class_days'] ?? 0);
    $ct       = strtolower(trim((string)($row['course_type_norm'] ?? $row['course_type'] ?? '')));
    $isLab    = $lab_days > 0;
    $isPENSTP = ($ct === 'pe' || $ct === 'nstp');

    if ($isLab) {
        if ($isPENSTP) $cs_lab_pe[] = $row;
        else           $cs_lab_non_pe[] = $row;
    } else {
        if ($isPENSTP) $cs_nonlab_pe[] = $row;
        else           $cs_nonlab_non_pe[] = $row;
    }
}

$cs_ordered = array_merge(
    $cs_lab_non_pe,
    $cs_lab_pe,
    $cs_nonlab_non_pe,
    $cs_nonlab_pe
);

foreach (["1st", "2nd"] as $term) {
    foreach ($cs_ordered as $row) {

        $academicTerm = strtolower(trim((string)($row['academic_term'] ?? '')));

        if ($academicTerm === 'semestral' && $term === '2nd') {
            continue;
        }
        if ($academicTerm !== 'semestral' && $academicTerm !== strtolower($term)) {
            continue;
        }

        $csid        = (int)($row['course_session_id'] ?? 0);
        $code        = pretty_code($row);
        $courseType  = $row['course_type_norm'] ?? '';
        $neededLect  = (int)($row['total_lecture_class_days'] ?? 0);
        $neededLab   = (int)($row['total_laboratory_class_days'] ?? 0);
        $nSlots      = (int)($row['required_slots'] ?? 0);
        $sgid        = $row['session_group_id'] ?? null;
        $isLabCourse = ($neededLab > 0);

        if ($nSlots <= 0 || ($neededLect + $neededLab) === 0) {
            $unassigned[] = [
                'course_session_id' => $csid,
                'code'              => $code,
                'term'              => $term,
                'reason'            => 'invalid_slots_or_zero_days',
            ];
            continue;
        }

        // For lab courses, enforce 2-hour slot count
        if ($isLabCourse) {
            $slotsFor2h = (int)round(120.0 / $slotMinutes);
            if ($nSlots !== $slotsFor2h) {
                $unassigned[] = [
                    'course_session_id' => $csid,
                    'code'              => $code,
                    'term'              => $term,
                    'reason'            => 'lab_course_slots_not_2_hours',
                ];
                continue;
            }
        }

        $placed = false;

        // Candidate starts
        $candidateStarts = [];

        if ($isLabCourse) {
            $sg_row          = get_session_group_row($sgid);
            $sess_time_label = $sg_row['session_time'] ?? '';
            $candidateStarts = lab_allowed_start_slots($sess_time_label, $nSlots);

            // ABSOLUTE: if no allowed start for this session_time, we NEVER try other times.
            if (empty($candidateStarts)) {
                $unassigned[] = [
                    'course_session_id' => $csid,
                    'code'              => $code,
                    'term'              => $term,
                    'reason'            => 'lab_no_allowed_start_for_session_time',
                ];
                continue;
            }
        } else {
            $candidateStarts = range(0, $totalSlots - $nSlots);
        }

        foreach ($candidateStarts as $start) {

            if (spans_lunch($start, $nSlots)) continue;

            // Non-lab: enforce coarse session_time bounds for non-PE/NSTP
            $ctypeNorm = strtolower(trim((string)$courseType));
            if (!$isLabCourse && $ctypeNorm !== 'pe' && $ctypeNorm !== 'nstp') {
                $sg_row = get_session_group_row($sgid);
                if ($sg_row !== null) {
                    $sess_time_label = $sg_row['session_time'] ?? '';
                    $bounds          = session_time_bounds($sess_time_label);
                    if ($bounds !== null) {
                        $slotStartDt = slot_dt($start);
                        $endIndex    = min($start + $nSlots, $totalSlots) - 1;
                        $slotEndBase = slot_dt($endIndex);

                        if ($slotStartDt instanceof DateTime && $slotEndBase instanceof DateTime) {
                            $start_hm = $slotStartDt->format('H:i');
                            $slotEndDt = clone $slotEndBase;
                            $slotEndDt->modify('+' . get_slot_minutes() . ' minutes');
                            $end_hm = $slotEndDt->format('H:i');
                        } else {
                            $start_hm = date('H:i', strtotime((string)$slotStartDt));
                            $end_ts   = strtotime((string)$slotEndBase . ' +' . get_slot_minutes() . ' minutes');
                            $end_hm   = date('H:i', $end_ts);
                        }

                        if (strtotime($start_hm) < strtotime($bounds['start']) ||
                            strtotime($end_hm)   > strtotime($bounds['end'])) {
                            continue;
                        }
                    }
                }
            }

            // candidate lecture days
            $lect_days = [];
            foreach ($DAYS as $d) {
                $r = available_rooms($term, $d, 'lecture', $courseType, $start, $nSlots);
                if (!empty($r)) $lect_days[] = $d;
            }

            // candidate lab days
            $lab_days = [];
            foreach ($DAYS as $d) {
                $r = available_rooms($term, $d, 'comlab', $courseType, $start, $nSlots);
                if (empty($r)) {
                    $r = available_rooms($term, $d, 'lab', $courseType, $start, $nSlots);
                }
                if (!empty($r)) $lab_days[] = $d;
            }

            if (count($lect_days) < $neededLect || count($lab_days) < $neededLab) {
                continue;
            }

            $lect_combos = ($neededLect == 0) ? [[]] : combinations($lect_days, $neededLect);
            $lab_combos  = ($neededLab == 0) ? [[]] : combinations($lab_days, $neededLab);

            $success_blocks = null;

            foreach ($lect_combos as $lcombo) {
                foreach ($lab_combos as $labcombo) {

                    if (!empty(array_intersect($lcombo, $labcombo))) continue;

                    // session_group conflict
                    $conflict = false;
                    foreach (array_merge($lcombo, $labcombo) as $d) {
                        if (sg_has_conflict($term, $sgid, $d, $start, $nSlots)) {
                            $conflict = true; break;
                        }
                    }
                    if ($conflict) continue;

                    // semestral check
                    if ($academicTerm === 'semestral') {
                        $sem_ok = true;
                        foreach ($lcombo as $d) {
                            $r1 = available_rooms('1st', $d, 'lecture', $courseType, $start, $nSlots);
                            $r2 = available_rooms('2nd', $d, 'lecture', $courseType, $start, $nSlots);
                            if (empty($r1) || empty($r2)) { $sem_ok = false; break; }
                        }
                        if (!$sem_ok) continue;
                        foreach ($labcombo as $d) {
                            $r1 = available_rooms('1st', $d, 'comlab', $courseType, $start, $nSlots);
                            if (empty($r1)) $r1 = available_rooms('1st', $d, 'lab', $courseType, $start, $nSlots);
                            $r2 = available_rooms('2nd', $d, 'comlab', $courseType, $start, $nSlots);
                            if (empty($r2)) $r2 = available_rooms('2nd', $d, 'lab', $courseType, $start, $nSlots);
                            if (empty($r1) || empty($r2)) { $sem_ok = false; break; }
                        }
                        if (!$sem_ok) continue;
                    }

                    // pick rooms this term
                    $chosen_blocks = [];
                    $ok = true;

                    foreach ($lcombo as $d) {
                        $rlist = available_rooms($term, $d, 'lecture', $courseType, $start, $nSlots);
                        if (empty($rlist)) { $ok = false; break; }
                        $chosen_blocks[] = [
                            'term'   => $term,
                            'day'    => $d,
                            'room'   => $rlist[0],
                            'is_lab' => false,
                        ];
                    }
                    if (!$ok) continue;

                    foreach ($labcombo as $d) {
                        $rlist = available_rooms($term, $d, 'comlab', $courseType, $start, $nSlots);
                        if (empty($rlist)) {
                            $rlist = available_rooms($term, $d, 'lab', $courseType, $start, $nSlots);
                        }
                        if (empty($rlist)) { $ok = false; break; }
                        $chosen_blocks[] = [
                            'term'   => $term,
                            'day'    => $d,
                            'room'   => $rlist[0],
                            'is_lab' => true,
                        ];
                    }
                    if (!$ok) continue;

                    // semestral rooms for 2nd term
                    $chosen_blocks_2nd = [];
                    if ($academicTerm === 'semestral') {
                        foreach ($chosen_blocks as $blk) {
                            $d = $blk['day'];
                            if ($blk['is_lab']) {
                                $rlist2 = available_rooms('2nd', $d, 'comlab', $courseType, $start, $nSlots);
                                if (empty($rlist2)) $rlist2 = available_rooms('2nd', $d, 'lab', $courseType, $start, $nSlots);
                            } else {
                                $rlist2 = available_rooms('2nd', $d, 'lecture', $courseType, $start, $nSlots);
                            }
                            if (empty($rlist2)) { $ok = false; break; }
                            $chosen_blocks_2nd[] = [
                                'term'   => '2nd',
                                'day'    => $d,
                                'room'   => $rlist2[0],
                                'is_lab' => $blk['is_lab'],
                            ];
                        }
                        if (!$ok) continue;
                    }

                    // commit
                    $blocks_to_commit = [];
                    foreach ($chosen_blocks as $blk) {
                        $blocks_to_commit[] = [
                            'course_session_id' => $csid,
                            'code'              => $code,
                            'term'              => $blk['term'],
                            'day'               => $blk['day'],
                            'room'              => $blk['room'],
                            'start_slot'        => $start,
                            'n_slots'           => $nSlots,
                            'is_lab'            => $blk['is_lab'],
                            'session_group_id'  => $sgid,
                        ];
                    }
                    if ($academicTerm === 'semestral') {
                        foreach ($chosen_blocks_2nd as $blk) {
                            $blocks_to_commit[] = [
                                'course_session_id' => $csid,
                                'code'              => $code,
                                'term'              => $blk['term'],
                                'day'               => $blk['day'],
                                'room'              => $blk['room'],
                                'start_slot'        => $start,
                                'n_slots'           => $nSlots,
                                'is_lab'            => $blk['is_lab'],
                                'session_group_id'  => $sgid,
                            ];
                        }
                    }

                    foreach ($blocks_to_commit as $b) {
                        $assignments[] = $b;
                        $t_term = $b['term'];
                        $t_day  = $b['day'];
                        $t_rm   = $b['room'];
                        $t_sgid = $b['session_group_id'];

                        if (!isset($sessionGroupOccupancy[$t_term][$t_day][$t_sgid])) {
                            $sessionGroupOccupancy[$t_term][$t_day][$t_sgid] =
                                array_fill(0, $totalSlots, false);
                        }
                        for ($s = $b['start_slot']; $s < $b['start_slot'] + $b['n_slots']; $s++) {
                            $availability[$t_term][$t_day][$t_rm][$s] = false;
                            $sessionGroupOccupancy[$t_term][$t_day][$t_sgid][$s] = true;
                        }
                    }

                    $success_blocks = $blocks_to_commit;
                    break;
                }
                if ($success_blocks !== null) break;
            }

            if ($success_blocks !== null) {
                $placed = true;
                break;
            }
        }

        if (!$placed) {
            $unassigned[] = [
                'course_session_id' => $csid,
                'code'              => $code,
                'term'              => $term,
                'reason'            => $isLabCourse
                    ? 'lab_no_start_day_room_found'
                    : 'no_start_day_room_found',
            ];
        }
    }
}

echo "Lab-confined scheduling complete. Assigned blocks: " . count($assignments)
    . " Unassigned records: " . count($unassigned) . "\n";

// ---- Overviews ----
$cs_1st = [];
$cs_2nd = [];
foreach ($cs as $row) {
    $at = strtolower(trim((string)($row['academic_term'] ?? '')));
    if ($at === '1st' || $at === 'semestral') $cs_1st[] = $row;
    if ($at === '2nd' || $at === 'semestral') $cs_2nd[] = $row;
}

$overview_1st = build_overview_filtered('1st', $cs_1st, $assignments, $DAYS);
$overview_2nd = build_overview_filtered('2nd', $cs_2nd, $assignments, $DAYS);

// ---- Unassigned sheet ----
$unq = [];
foreach ($unassigned as $u) {
    $cid    = (int)$u['course_session_id'];
    $term   = $u['term'] ?? '';
    $reason = $u['reason'] ?? '';
    if (!isset($unq[$cid])) {
        $unq[$cid] = [
            'course_session_id' => $cid,
            'code'              => $u['code'] ?? '',
            'terms_tried'       => [$term],
            'reasons'           => [$reason],
        ];
    } else {
        $unq[$cid]['terms_tried'][] = $term;
        $unq[$cid]['reasons'][]     = $reason;
    }
}
$unassigned_list = [];
foreach ($unq as $v) {
    $unassigned_list[] = [
        'course_session_id' => $v['course_session_id'],
        'code'              => $v['code'],
        'terms_tried'       => implode(',', array_unique($v['terms_tried'])),
        'reason'            => implode(';', array_unique($v['reasons'])),
    ];
}

// ---- Export XLSX ----
echo "Writing workbook to {$OUTPUT_XLSX}\n";

$spreadsheet = new Spreadsheet();
$spreadsheet->removeSheetByIndex(0);

foreach (['1st', '2nd'] as $term) {
    foreach ($DAYS as $d) {
        $sheetName = $term . '_' . substr($d, 0, 3);
        $grid      = build_day_grid($term, $d, $template_df, $timeCol);
        write_sheet_from_rows($spreadsheet, $sheetName, $grid);
    }
}

write_sheet_from_rows($spreadsheet, 'Overview_1st', $overview_1st);
write_sheet_from_rows($spreadsheet, 'Overview_2nd', $overview_2nd);

if (!empty($unassigned_list)) {
    write_sheet_from_rows($spreadsheet, 'Unassigned', $unassigned_list);
} else {
    write_sheet_from_rows($spreadsheet, 'Unassigned', [
        ['note' => 'No unassigned course_sessions'],
    ]);
}

$writer = new Xlsx($spreadsheet);
$writer->save($OUTPUT_XLSX);

$total_cs         = count($cs);
$unique_assigned  = count(array_unique(array_map(function ($a) {
    return $a['course_session_id'];
}, $assignments)));
$unique_unassigned= count($unassigned_list);

echo "Done (lab-confined). Output file: {$OUTPUT_XLSX}\n";
echo "Summary: total course_sessions: {$total_cs} unique assigned: {$unique_assigned} unique unassigned: {$unique_unassigned}\n";
