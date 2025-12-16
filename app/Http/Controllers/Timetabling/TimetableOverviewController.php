<?php

namespace App\Http\Controllers\Timetabling;

use App\Http\Controllers\Controller;
use App\Models\Records\Room;
use App\Models\Records\Timetable;
use App\Models\Timetabling\CourseSession;
use App\Models\Timetabling\SessionGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TimetableOverviewController extends Controller
{
    private function buildSessionGroupLabel(?SessionGroup $sg): string
    {
        if (!$sg) {
            return 'Unknown Session Group';
        }

        $prog = $sg->academicProgram ? (string) $sg->academicProgram->program_abbreviation : 'Unknown';
        $sessionName = (string) ($sg->session_name ?? '');
        $yearLevel = (string) ($sg->year_level ?? '');
        $sessionTime = (string) ($sg->session_time ?? '');

        $label = trim(
            $prog
            . ($sessionName !== '' ? ' ' . $sessionName : '')
            . ($yearLevel !== '' ? ' ' . $yearLevel . ' Year' : '')
            . ($sessionTime !== '' ? ' (' . ucfirst($sessionTime) . ')' : '')
        );

        return $label !== '' ? $label : ('Session Group #' . $sg->id);
    }

    private function buildCourseTitle(?CourseSession $cs): string
    {
        if (!$cs) {
            return 'Unknown Course';
        }

        if ($cs->course) {
            $t = (string) ($cs->course->course_title ?: $cs->course->course_name ?: '');
            if ($t !== '') {
                return $t;
            }
        }

        return 'Course Session #' . $cs->id;
    }

    /**
     * Build a renderable grid:
     * - rooms: ordered list of room names from XLSX header
     * - timeLabels: ordered list of times from XLSX first column
     * - grid[timeIndex][roomName][dayIndex] = null | ['render'=>bool,'rowspan'=>int,'text'=>string,'meta'=>array]
     */
    private function buildRoomGridForTerm($spreadsheet, int $termIndex, array $courseSessionsById, array $sessionGroupsById): array
    {
        $dayNames = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

        $rooms = [];
        $timeLabels = [];

        // Raw tokens by [dayIndex][roomName][timeIndex] = string token
        $tokens = [];

        for ($dayIndex = 0; $dayIndex < 6; $dayIndex++) {
            $sheetIndex = $termIndex * 6 + $dayIndex;
            if ($sheetIndex >= $spreadsheet->getSheetCount()) {
                continue;
            }

            $sheet = $spreadsheet->getSheet($sheetIndex);
            $table = $sheet->toArray(null, true, true, false);

            if (empty($table) || !isset($table[0]) || !is_array($table[0])) {
                continue;
            }

            $header = $table[0];
            $colCount = count($header);
            $rowCount = count($table);

            // Rooms from header (col 1..N)
            $roomByCol = [];
            for ($c = 1; $c < $colCount; $c++) {
                $roomName = trim((string) ($header[$c] ?? ''));
                if ($roomName === '') {
                    continue;
                }
                $roomByCol[$c] = $roomName;

                if (!in_array($roomName, $rooms, true)) {
                    $rooms[] = $roomName;
                }
            }

            // Time labels (row 1..end, col 0)
            $localTimeLabels = [];
            for ($r = 1; $r < $rowCount; $r++) {
                $t = trim((string) ($table[$r][0] ?? ''));
                if ($t === '') {
                    continue;
                }
                $localTimeLabels[] = $t;
            }

            // Use the first non-empty timeLabels as canonical
            if (empty($timeLabels) && !empty($localTimeLabels)) {
                $timeLabels = $localTimeLabels;
            }

            // Map cells into tokens aligned to timeLabels indexes
            // We assume XLSX uses consistent time rows across days/sheets.
            for ($c = 1; $c < $colCount; $c++) {
                if (!isset($roomByCol[$c])) {
                    continue;
                }
                $roomName = $roomByCol[$c];
                if (!isset($tokens[$dayIndex])) {
                    $tokens[$dayIndex] = [];
                }
                if (!isset($tokens[$dayIndex][$roomName])) {
                    $tokens[$dayIndex][$roomName] = [];
                }

                $ti = 0;
                for ($r = 1; $r < $rowCount; $r++) {
                    $timeText = trim((string) ($table[$r][0] ?? ''));
                    if ($timeText === '') {
                        continue;
                    }

                    $cell = trim((string) ($table[$r][$c] ?? ''));
                    $tokens[$dayIndex][$roomName][$ti] = $cell;
                    $ti++;
                }
            }
        }

        // Build 30-min grid initialized to null (vacant)
        $grid30 = [];
        $timeCount30 = count($timeLabels);

        for ($ti = 0; $ti < $timeCount30; $ti++) {
            $grid30[$ti] = [];
            foreach ($rooms as $roomName) {
                $grid30[$ti][$roomName] = [];
                for ($dayIndex = 0; $dayIndex < 6; $dayIndex++) {
                    $grid30[$ti][$roomName][$dayIndex] = null;
                }
            }
        }

        // Fill 30-min grid with blocks + skip markers
        foreach ($rooms as $roomName) {
            for ($dayIndex = 0; $dayIndex < 6; $dayIndex++) {
                $colTokens = $tokens[$dayIndex][$roomName] ?? [];

                $ti = 0;
                while ($ti < $timeCount30) {
                    $token = trim((string) ($colTokens[$ti] ?? ''));

                    if ($token === '' || strtolower($token) === 'vacant') {
                        $ti++;
                        continue;
                    }

                    if (!preg_match('/_(\d+)_(\d+)$/', $token, $m)) {
                        $grid30[$ti][$roomName][$dayIndex] = [
                            'render' => true,
                            'rowspan' => 1,
                            'text' => $token,
                            'meta' => [
                                'day' => $dayNames[$dayIndex] ?? (string) $dayIndex,
                                'room' => $roomName,
                                'session_group_id' => null,
                                'course_session_id' => null,
                                'academic_program_id' => 0,
                                'year_level' => '',
                                'session_time' => '',
                                'session_color' => '',
                            ],
                        ];
                        $ti++;
                        continue;
                    }

                    $sessionGroupId = (int) $m[1];
                    $courseSessionId = (int) $m[2];

                    // contiguous vertical span (30-min blocks)
                    $span = 1;
                    $tj = $ti + 1;
                    while ($tj < $timeCount30) {
                        $next = trim((string) ($colTokens[$tj] ?? ''));
                        if ($next === $token) {
                            $span++;
                            $tj++;
                            continue;
                        }
                        break;
                    }

                    $cs = $courseSessionsById[$courseSessionId] ?? null;
                    $sg = $sessionGroupsById[$sessionGroupId] ?? ($cs ? $cs->sessionGroup : null);

                    $text = $this->buildSessionGroupLabel($sg) . "\n" . $this->buildCourseTitle($cs);

                    $programId = $sg ? (int) $sg->academic_program_id : 0;
                    $yearLevel = $sg ? (string) ($sg->year_level ?? '') : '';
                    $sessionTime = $sg ? (string) ($sg->session_time ?? '') : '';
                    $sessionColor = $sg ? (string) ($sg->session_color ?? '') : '';

                    $grid30[$ti][$roomName][$dayIndex] = [
                        'render' => true,
                        'rowspan' => $span,
                        'text' => $text,
                        'meta' => [
                            'day' => $dayNames[$dayIndex] ?? (string) $dayIndex,
                            'room' => $roomName,
                            'session_group_id' => $sessionGroupId,
                            'course_session_id' => $courseSessionId,
                            'academic_program_id' => $programId,
                            'year_level' => $yearLevel,
                            'session_time' => $sessionTime,
                            'session_color' => $sessionColor,
                        ],
                    ];

                    for ($k = 1; $k < $span; $k++) {
                        if (($ti + $k) >= $timeCount30) {
                            break;
                        }
                        $grid30[$ti + $k][$roomName][$dayIndex] = ['render' => false];
                    }

                    $ti += $span;
                }
            }
        }
        // ---- Build HOURLY grid directly ----

// Build hour labels from 30-min labels
        $timeLabelsHourly = [];
        for ($i = 0; $i < count($timeLabels); $i += 2) {
            $timeLabelsHourly[] = $timeLabels[$i];
        }

        $hourCount = count($timeLabelsHourly);

// Initialize hourly grid
        $gridH = [];
        for ($h = 0; $h < $hourCount; $h++) {
            foreach ($rooms as $roomName) {
                for ($d = 0; $d < 6; $d++) {
                    $gridH[$h][$roomName][$d] = null;
                }
            }
        }

// Convert 30-min blocks → hour blocks
        foreach ($rooms as $roomName) {
            for ($dayIndex = 0; $dayIndex < 6; $dayIndex++) {

                $ti = 0;
                while ($ti < count($grid30)) {
                    $cell = $grid30[$ti][$roomName][$dayIndex] ?? null;

                    if (!is_array($cell) || ($cell['render'] ?? false) !== true) {
                        $ti++;
                        continue;
                    }

                    $span30 = max(1, (int) $cell['rowspan']);
                    $startHour = intdiv($ti, 2);
                    $spanHours = (int) ceil($span30 / 2);

                    if ($startHour >= $hourCount) {
                        $ti += $span30;
                        continue;
                    }

                    $gridH[$startHour][$roomName][$dayIndex] = [
                        'render'  => true,
                        'rowspan' => $spanHours,
                        'text'    => $cell['text'],
                        'meta'    => $cell['meta'],
                    ];

                    for ($k = 1; $k < $spanHours; $k++) {
                        if (($startHour + $k) >= $hourCount) break;
                        $gridH[$startHour + $k][$roomName][$dayIndex] = ['render' => false];
                    }

                    $ti += $span30;
                }
            }
        }

        return [
            'rooms' => $rooms,
            'timeLabels' => $timeLabelsHourly,
            'grid' => $gridH,
        ];

    }


    private function loadTimetableXlsxPath(Timetable $timetable): ?string
    {
        $bucketPath = "timetables/{$timetable->id}.xlsx";
        $tempFile = null;

        if (Storage::disk('facultime')->exists($bucketPath)) {
            $tempFile = tempnam(sys_get_temp_dir(), 'tt_');
            file_put_contents($tempFile, Storage::disk('facultime')->get($bucketPath));
            return $tempFile;
        }

        $legacyPath = storage_path("app/exports/timetables/{$timetable->id}.xlsx");
        if (file_exists($legacyPath)) {
            return $legacyPath;
        }

        return null;
    }

    private function normalize(string $s): string
    {
        $s = strtolower(trim($s));
        $s = preg_replace('/\s+/', ' ', $s);
        return $s;
    }

    private function parseTimeLabelToMinutes(string $label): ?int
    {
        $s = strtolower(trim($label));
        $s = str_replace(['a.m.', 'p.m.'], ['am', 'pm'], $s);

        if (!preg_match('/^(\d{1,2}):(\d{2})\s*(am|pm)$/i', $s, $m)) {
            return null;
        }

        $h = (int) $m[1];
        $min = (int) $m[2];
        $ampm = strtolower($m[3]);

        if ($h === 12) {
            $h = 0;
        }

        $total = $h * 60 + $min;
        if ($ampm === 'pm') {
            $total += 12 * 60;
        }

        return $total;
    }

    private function formatMinutesToTimeLabel(int $minutes): string
    {
        $minutes = $minutes % (24 * 60);
        if ($minutes < 0) {
            $minutes += 24 * 60;
        }

        $h24 = intdiv($minutes, 60);
        $m = $minutes % 60;

        $ampm = $h24 >= 12 ? 'PM' : 'AM';
        $h12 = $h24 % 12;
        if ($h12 === 0) {
            $h12 = 12;
        }

        return $h12 . ':' . str_pad((string) $m, 2, '0', STR_PAD_LEFT) . ' ' . $ampm;
    }

    private function computeEndTimeLabel(string $startLabel, int $blocks): string
    {
        // 1 block = 30 minutes
        $startMinutes = $this->parseTimeLabelToMinutes($startLabel);
        if ($startMinutes === null) {
            return $startLabel;
        }

        $endMinutes = $startMinutes + max(1, $blocks) * 30;
        return $this->formatMinutesToTimeLabel($endMinutes);
    }

    /**
     * Build placements:
     * placements[termIndex][sessionId][dayIndex] = [
     *   ['start' => '7:30 AM', 'end' => '9:00 AM', 'room' => 'RM301'],
     *   ...
     * ]
     */
    private function buildPlacementsFromTimetableSheets($spreadsheet): array
    {
        $placements = [
            0 => [],
            1 => [],
        ];

        $sheetCount = $spreadsheet->getSheetCount();
        $maxSheetsToScan = min(12, $sheetCount);

        for ($sheetIndex = 0; $sheetIndex < $maxSheetsToScan; $sheetIndex++) {
            $termIndex = $sheetIndex < 6 ? 0 : 1;
            $dayIndex  = $sheetIndex % 6;

            $sheet = $spreadsheet->getSheet($sheetIndex);
            $table = $sheet->toArray(null, true, true, false);

            $rowCount = count($table);
            if ($rowCount < 2) {
                continue;
            }

            $header = $table[0] ?? [];
            $colCount = count($header);

            // Build colIndex => roomName (skip col 0 = Time)
            $roomByCol = [];
            for ($c = 1; $c < $colCount; $c++) {
                $roomName = trim((string) ($header[$c] ?? ''));
                if ($roomName === '') continue;
                $roomByCol[$c] = $roomName;
            }

            // Build rowIndex => timeLabel (skip header row 0)
            $timeByRow = [];
            for ($r = 1; $r < $rowCount; $r++) {
                $timeLabel = trim((string) ($table[$r][0] ?? ''));
                if ($timeLabel === '') continue;
                $timeByRow[$r] = $timeLabel;
            }

            foreach ($roomByCol as $c => $roomName) {
                $r = 1;
                while ($r < $rowCount) {
                    $cell = trim((string) ($table[$r][$c] ?? ''));

                    if ($cell === '' || strtolower($cell) === 'vacant') {
                        $r++;
                        continue;
                    }

                    // Expect encoded with last 2 parts being session_group_id and course_session_id
                    // e.g. PROG_1st_12_98
                    if (!preg_match('/_(\d+)_(\d+)$/', $cell, $m)) {
                        $r++;
                        continue;
                    }

                    $sessionId = (string) ((int) $m[2]);

                    // Find vertical span (contiguous same encoded value)
                    $startRow = $r;
                    $span = 1;
                    $rr = $r + 1;
                    while ($rr < $rowCount) {
                        $next = trim((string) ($table[$rr][$c] ?? ''));
                        if ($next === $cell) {
                            $span++;
                            $rr++;
                            continue;
                        }
                        break;
                    }

                    $startTime = $timeByRow[$startRow] ?? '';
                    $endTime   = $startTime !== '' ? $this->computeEndTimeLabel($startTime, $span) : '';

                    if (!isset($placements[$termIndex][$sessionId])) {
                        $placements[$termIndex][$sessionId] = [];
                    }
                    if (!isset($placements[$termIndex][$sessionId][$dayIndex])) {
                        $placements[$termIndex][$sessionId][$dayIndex] = [];
                    }

                    $placements[$termIndex][$sessionId][$dayIndex][] = [
                        'start' => $startTime,
                        'end'   => $endTime,
                        'room'  => $roomName,
                    ];

                    $r = $startRow + $span;
                }
            }
        }

        return $placements;
    }

    private function sessionBelongsToTerm(CourseSession $cs, int $termIndex): bool
    {
        $termRaw = '';
        if (!empty($cs->academic_term)) {
            $termRaw = (string) $cs->academic_term;
        } elseif ($cs->course && !empty($cs->course->academic_term)) {
            $termRaw = (string) $cs->course->academic_term;
        }

        $termNorm = strtolower(trim($termRaw));

        // semestral/blank => show in both
        if ($termNorm === '' || $termNorm === 'semestral' || $termNorm === 'sem') {
            return true;
        }

        if ($termIndex === 0) {
            return $termNorm === '1st' || $termNorm === '1' || $termNorm === 'first';
        }

        return $termNorm === '2nd' || $termNorm === '2' || $termNorm === 'second';
    }

    public function index(Timetable $timetable, Request $request)
    {
        $termIndex = (int) $request->query('term', 0);
        if ($termIndex !== 0 && $termIndex !== 1) {
            $termIndex = 0;
        }

        $sessionGroups = SessionGroup::where('timetable_id', $timetable->id)
            ->with(['academicProgram'])
            ->get();

        $sessionGroupsByProgram = $sessionGroups
            ->groupBy('academic_program_id')
            ->map(function ($groups) {
                return $groups->sortBy(function ($g) {
                    $map = ['1st' => 1, '2nd' => 2, '3rd' => 3, '4th' => 4];
                    return $map[$g->year_level] ?? 99;
                });
            });

        $xlsxPath = $this->loadTimetableXlsxPath($timetable);

        if (!$xlsxPath || !file_exists($xlsxPath)) {
            return view('timetabling.timetable-overview.index', [
                'timetable' => $timetable,
                'termIndex' => $termIndex,
                'sessionGroupsByProgram' => $sessionGroupsByProgram,
                'rooms' => [],
                'roomsByType' => [],
                'timeLabels' => [],
                'grid' => [],
                'error' => 'Timetable file not found.',
            ]);
        }

        $tempFileToCleanup = str_contains($xlsxPath, sys_get_temp_dir()) ? $xlsxPath : null;

        try {
            $spreadsheet = IOFactory::load($xlsxPath);

            $courseSessions = CourseSession::whereHas('sessionGroup', function ($q) use ($timetable) {
                $q->where('timetable_id', $timetable->id);
            })
                ->with(['course', 'sessionGroup.academicProgram'])
                ->get();

            $courseSessionsById = [];
            foreach ($courseSessions as $cs) {
                $courseSessionsById[(int) $cs->id] = $cs;
            }

            $sessionGroupsById = [];
            foreach ($sessionGroups as $sg) {
                $sessionGroupsById[(int) $sg->id] = $sg;
            }

            $gridData = $this->buildRoomGridForTerm(
                $spreadsheet,
                $termIndex,
                $courseSessionsById,
                $sessionGroupsById
            );

            // Build roomsByType (single query, then group)
            $roomModelsByName = Room::whereIn('room_name', $gridData['rooms'])
                ->get()
                ->keyBy('room_name');

            $roomsByType = [];
            foreach ($gridData['rooms'] as $roomName) {
                $type = $roomModelsByName[$roomName]->room_type ?? 'Unknown';
                $roomsByType[$type][] = [
                    'name' => $roomName,
                    'type' => $type,
                ];
            }

            // Optional: stable type ordering (comlab, lecture, then others)
            $typeOrder = ['comlab' => 0, 'lecture' => 1, 'gym' => 2, 'main' => 3, 'Unknown' => 99];
            uksort($roomsByType, function ($a, $b) use ($typeOrder) {
                $ra = $typeOrder[strtolower((string) $a)] ?? 50;
                $rb = $typeOrder[strtolower((string) $b)] ?? 50;
                if ($ra !== $rb) return $ra <=> $rb;
                return strcmp((string) $a, (string) $b);
            });

            return view('timetabling.timetable-overview.index', [
                'timetable' => $timetable,
                'termIndex' => $termIndex,
                'sessionGroupsByProgram' => $sessionGroupsByProgram,
                'rooms' => $gridData['rooms'],
                'roomsByType' => $roomsByType,
                'timeLabels' => $gridData['timeLabels'],
                'grid' => $gridData['grid'],
                'error' => null,
            ]);
        } finally {
            if ($tempFileToCleanup && file_exists($tempFileToCleanup)) {
                @unlink($tempFileToCleanup);
            }
        }
    }

}
