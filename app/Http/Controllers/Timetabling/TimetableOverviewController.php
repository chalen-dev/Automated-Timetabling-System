<?php

namespace App\Http\Controllers\Timetabling;

use App\Http\Controllers\Controller;
use App\Models\Records\Timetable;
use App\Models\Timetabling\CourseSession;
use App\Models\Timetabling\SessionGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TimetableOverviewController extends Controller
{
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

        // NEW: build filter data for tray (Programs list)
        $sessionGroups = SessionGroup::where('timetable_id', $timetable->id)
            ->with(['academicProgram'])
            ->get();

        $sessionGroupsByProgram = $sessionGroups
            ->groupBy('academic_program_id')
            ->map(function ($groups) {
                // stable ordering: 1st->4th
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
                'groups' => [],
                'sessionGroupsByProgram' => $sessionGroupsByProgram,
                'error' => 'Timetable file not found.',
            ]);
        }

        $tempFileToCleanup = str_contains($xlsxPath, sys_get_temp_dir()) ? $xlsxPath : null;

        try {
            $spreadsheet = IOFactory::load($xlsxPath);
            $placements = $this->buildPlacementsFromTimetableSheets($spreadsheet);

            $dayNames = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

            $sessions = CourseSession::whereHas('sessionGroup', function ($q) use ($timetable) {
                $q->where('timetable_id', $timetable->id);
            })
                ->with(['course', 'sessionGroup.academicProgram'])
                ->get();

            $groupsById = [];

            foreach ($sessions as $cs) {
                if (!$this->sessionBelongsToTerm($cs, $termIndex)) {
                    continue;
                }

                $sg = $cs->sessionGroup;
                $sgId = $sg ? (int) $sg->id : 0;

                $programId = $sg ? (int) $sg->academic_program_id : 0;
                $prog = $sg && $sg->academicProgram ? $sg->academicProgram->program_abbreviation : 'Unknown';
                $sessionName = $sg ? (string) $sg->session_name : '';
                $yearLevel = $sg ? (string) $sg->year_level : '';
                $sessionTime = $sg ? (string) $sg->session_time : '';

                $groupLabel = trim(
                    $prog
                    . ($sessionName !== '' ? ' ' . $sessionName : '')
                    . ($yearLevel !== '' ? ' ' . $yearLevel . ' Year' : '')
                    . ($sessionTime !== '' ? ' (' . ucfirst($sessionTime) . ')' : '')
                );

                if (!isset($groupsById[$sgId])) {
                    $groupsById[$sgId] = [
                        'session_group_id' => $sgId,
                        'group_label' => $groupLabel !== '' ? $groupLabel : ($sgId ? ('Session Group #' . $sgId) : 'Unknown Session Group'),
                        'items' => [],

                        // NEW: for filtering in Blade/JS
                        'academic_program_id' => $programId,
                        'year_level' => $yearLevel,
                        'session_time' => $sessionTime,

                        // existing sort keys
                        'sort_session_time' => strtolower(trim($sessionTime)),
                        'sort_year_level' => strtolower(trim($yearLevel)),
                        'sort_program_abbr' => strtolower(trim($prog)),
                        'sort_session_name' => strtolower(trim($sessionName)),
                    ];
                }

                $courseTitle = '';
                if ($cs->course) {
                    $courseTitle = (string) ($cs->course->course_title ?: $cs->course->course_name ?: '');
                }
                if ($courseTitle === '') {
                    $courseTitle = 'Course Session #' . $cs->id;
                }

                $sid = (string) $cs->id;

                $item = [
                    'course_session_id' => $cs->id,
                    'course_title' => $courseTitle,
                    'days' => [],
                ];

                foreach ($dayNames as $dayIdx => $dayName) {
                    $entries = $placements[$termIndex][$sid][$dayIdx] ?? [];
                    usort($entries, function ($a, $b) {
                        return strcmp((string)($a['start'] ?? ''), (string)($b['start'] ?? ''));
                    });
                    $item['days'][$dayName] = $entries;
                }

                $groupsById[$sgId]['items'][] = $item;
            }

            foreach ($groupsById as $gid => $g) {
                usort($groupsById[$gid]['items'], function ($a, $b) {
                    return strcmp((string) $a['course_title'], (string) $b['course_title']);
                });
            }

            $groups = array_values($groupsById);

            $sessionTimeOrder = ['morning' => 0, 'afternoon' => 1, 'evening' => 2];
            $yearLevelOrder = ['1st' => 1, '2nd' => 2, '3rd' => 3, '4th' => 4];

            usort($groups, function ($a, $b) use ($sessionTimeOrder, $yearLevelOrder) {
                $aId = (int) ($a['session_group_id'] ?? 0);
                $bId = (int) ($b['session_group_id'] ?? 0);

                if ($aId === 0 && $bId !== 0) return 1;
                if ($bId === 0 && $aId !== 0) return -1;

                $aTimeRank = $sessionTimeOrder[(string)($a['sort_session_time'] ?? '')] ?? 99;
                $bTimeRank = $sessionTimeOrder[(string)($b['sort_session_time'] ?? '')] ?? 99;
                if ($aTimeRank !== $bTimeRank) return $aTimeRank <=> $bTimeRank;

                $aYearRank = $yearLevelOrder[(string)($a['sort_year_level'] ?? '')] ?? 99;
                $bYearRank = $yearLevelOrder[(string)($b['sort_year_level'] ?? '')] ?? 99;
                if ($aYearRank !== $bYearRank) return $aYearRank <=> $bYearRank;

                $p = strcmp((string)($a['sort_program_abbr'] ?? ''), (string)($b['sort_program_abbr'] ?? ''));
                if ($p !== 0) return $p;

                return strcmp((string)($a['sort_session_name'] ?? ''), (string)($b['sort_session_name'] ?? ''));
            });

            return view('timetabling.timetable-overview.index', [
                'timetable' => $timetable,
                'termIndex' => $termIndex,
                'groups' => $groups,
                'sessionGroupsByProgram' => $sessionGroupsByProgram,
                'error' => null,
            ]);
        } finally {
            if ($tempFileToCleanup && file_exists($tempFileToCleanup)) {
                @unlink($tempFileToCleanup);
            }
        }
    }

}
