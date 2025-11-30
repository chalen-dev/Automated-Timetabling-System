<?php

namespace App\Http\Controllers\Timetabling;

use App\Helpers\Logger;
use App\Http\Controllers\Controller;
use App\Models\Records\Timetable;
use App\Models\Timetabling\SessionGroup;
use App\Models\Timetabling\CourseSession;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;


class TimetableEditingPaneController extends Controller
{
    private function normalizeLabelLine(string $s): string
    {
        $s = strtolower(trim($s));
        // collapse multiple spaces into one
        $s = preg_replace('/\s+/', ' ', $s);
        return $s;
    }

    private function calculateVerticalRowspan(array $tableData): array
    {
        $rowspanData = [];
        $rowCount = count($tableData);
        if ($rowCount < 2) return $rowspanData;

        $colCount = count($tableData[0] ?? []);

        for ($col = 0; $col < $colCount; $col++) {
            $rowspanData[$col] = [];
            $currentValue = null;
            $startRow = 1;
            $spanCount = 0;

            for ($row = 1; $row < $rowCount; $row++) {
                $cell = trim($tableData[$row][$col] ?? '');
                $cellLower = strtolower($cell);

                if ($cellLower === 'vacant') {
                    $rowspanData[$col][$row] = 1;
                    $currentValue = null;
                    $spanCount = 0;
                    continue;
                }

                if ($cell === $currentValue) {
                    $spanCount++;
                    $rowspanData[$col][$row] = 0;
                    $rowspanData[$col][$startRow] = $spanCount + 1;
                } else {
                    $currentValue = $cell;
                    $startRow = $row;
                    $spanCount = 0;
                    $rowspanData[$col][$row] = 1;
                }
            }
        }

        return $rowspanData;
    }

    private function parseSheetNameToViewKey(string $sheetName): ?string
    {
        // Example sheet names: "1st_Mon", "1st_Tue", "2nd_Fri", etc.
        $parts = explode('_', $sheetName);
        if (count($parts) !== 2) {
            return null;
        }

        [$termPart, $dayPart] = $parts;

        $termIndexMap = [
            '1st' => 0,
            '2nd' => 1,
        ];

        $dayIndexMap = [
            'Mon' => 0,
            'Tue' => 1,
            'Wed' => 2,
            'Thu' => 3,
            'Fri' => 4,
            'Sat' => 5,
        ];

        if (!array_key_exists($termPart, $termIndexMap) ||
            !array_key_exists($dayPart, $dayIndexMap)) {
            return null;
        }

        $termIndex = $termIndexMap[$termPart];
        $dayIndex  = $dayIndexMap[$dayPart];

        // This matches the editor.js key format: "termIndex-dayIndex"
        return "{$termIndex}-{$dayIndex}";
    }

    /**
     * Build map: "groupLine||courseLine" (normalized) => session_id
     */
    private function buildCellLabelToSessionIdMap($sessionGroups): array
    {
        $map = [];

        foreach ($sessionGroups as $group) {
            $programAbbrev = $group->academicProgram->program_abbreviation ?? 'Unknown';
            $sessionName   = $group->session_name ?? '';
            $yearLevel     = $group->year_level;

            // Mirror the editor.js logic: "CS A 3rd Year"
            $groupTitle = $programAbbrev;
            if ($sessionName !== '') {
                $groupTitle .= ' ' . $sessionName;
            }
            if ($yearLevel !== null && $yearLevel !== '') {
                $groupTitle .= ' ' . $yearLevel . ' Year';
            }
            $groupTitleFull = trim($groupTitle);

            foreach ($group->courseSessions as $session) {
                $course      = $session->course;
                $courseLabel = $course->course_title
                    ?? $course->course_name
                    ?? ('Course #' . $session->id);

                $groupKey  = $this->normalizeLabelLine($groupTitleFull);
                $courseKey = $this->normalizeLabelLine($courseLabel);

                $compositeKey = $groupKey . '||' . $courseKey;
                $map[$compositeKey] = (string) $session->id;
            }
        }

        return $map;
    }



    /**
     * Build initial placementsByView from the timetable's XLSX file.
     *
     * Structure:
     * [
     *   "0-0" => [ "33" => ["col" => 4, "topRow" => 3, "blocks" => 4], ... ],
     *   "0-1" => [ ... ],
     *   ...
     * ]
     *
     * where array keys like "33" are CourseSession IDs.
     */
    private function buildInitialPlacementsFromXlsx(Timetable $timetable): array
    {
        $xlsxPath = storage_path("app/exports/timetables/{$timetable->id}.xlsx");
        if (!file_exists($xlsxPath)) {
            return [];
        }

        $spreadsheet = IOFactory::load($xlsxPath);
        $sheetCount  = $spreadsheet->getSheetCount();

        $placementsByView = [];

        for ($sheetIndex = 0; $sheetIndex < $sheetCount; $sheetIndex++) {
            // Map sheet index to (termIndex, dayIndex):
            //   0..5  => 1st Term Mon..Sat
            //   6..11 => 2nd Term Mon..Sat
            $termIndex = $sheetIndex < 6 ? 0 : 1;
            $dayIndex  = $sheetIndex % 6;
            $viewKey   = $termIndex . '-' . $dayIndex;

            $sheet = $spreadsheet->getSheet($sheetIndex);
            $table = $sheet->toArray(null, true, true, false);

            $rowCount = count($table);
            if ($rowCount < 2) {
                continue; // no data rows
            }

            // Column 0 is the "Time" column; rooms start from column 1.
            $colCount       = count($table[0] ?? []);
            $viewPlacements = [];

            for ($col = 1; $col < $colCount; $col++) {
                $row = 1; // skip header row 0 (room names)

                while ($row < $rowCount) {
                    $rawCell = $table[$row][$col] ?? '';
                    $cell    = trim((string) $rawCell);
                    $lower   = strtolower($cell);

                    // Skip blanks and "Vacant"
                    if ($cell === '' || $lower === 'vacant') {
                        $row++;
                        continue;
                    }

                    // Pattern: {program_abbreviation}_{year_level}_{session_group_id}_{course_session_id}
                    // Example: CS_3rd_5_33
                    if (!preg_match('/^[A-Za-z]+_[^_]+_(\d+)_(\d+)$/', $cell, $matches)) {
                        // Not a code we understand; skip this block.
                        $row++;
                        continue;
                    }

                    // We don't actually need session_group_id here, but it's captured as $matches[1].
                    $sessionId = (string) ((int) $matches[2]); // CourseSession.id

                    // Determine the full vertical span (contiguous identical cells)
                    $startRow = $row;
                    $span     = 1;

                    $r = $row + 1;
                    while ($r < $rowCount) {
                        $next = trim((string) ($table[$r][$col] ?? ''));
                        if ($next === $cell) {
                            $span++;
                            $r++;
                        } else {
                            break;
                        }
                    }

                    // Editor rows are 0-based timeslot indices:
                    //   spreadsheet row 1 => topRow 0, row 2 => topRow 1, etc.
                    $topRow = $startRow - 1;
                    $blocks = $span;

                    // In your design, a given CourseSession appears at most once per (term,day),
                    // so it's safe to store one placement per sessionId per viewKey.
                    $viewPlacements[$sessionId] = [
                        'col'    => $col - 1, // remove the time column offset
                        'topRow' => $topRow,
                        'blocks' => $blocks,
                    ];

                    $row = $startRow + $span;
                }
            }

            if (!empty($viewPlacements)) {
                $placementsByView[$viewKey] = $viewPlacements;
            }
        }

        return $placementsByView;
    }



    public function index(Timetable $timetable, Request $request)
    {
        $sheetIndex = (int) $request->query('sheet', 0);
        $xlsxPath = storage_path("app/exports/timetables/{$timetable->id}.xlsx");
        $tableData = [];
        $error = null;
        $totalSheets = 0;
        $sheetName = null;
        $sheetDisplayName = null;

        if (file_exists($xlsxPath)) {
            try {
                $spreadsheet = IOFactory::load($xlsxPath);
                $totalSheets = $spreadsheet->getSheetCount();
                $sheetIndex = max(0, min($sheetIndex, $totalSheets - 1));
                $sheet = $spreadsheet->getSheet($sheetIndex);
                $sheetName = $sheet->getTitle();

                $termMapping = [
                    '1st' => '1st Term',
                    '2nd' => '2nd Term',
                    '3rd' => '3rd Term',
                    '4th' => '4th Term',
                ];

                $weekdayMapping = [
                    'Mon' => 'Monday',
                    'Tue' => 'Tuesday',
                    'Wed' => 'Wednesday',
                    'Thu' => 'Thursday',
                    'Fri' => 'Friday',
                    'Sat' => 'Saturday',
                    'Sun' => 'Sunday',
                ];

                $parts = explode('_', $sheetName);
                if (count($parts) === 2) {
                    [$termPart, $dayPart] = $parts;
                    $termName = $termMapping[$termPart] ?? $termPart;
                    $dayName = $weekdayMapping[$dayPart] ?? $dayPart;
                    $sheetDisplayName = "{$dayName} {$termName}";
                } else {
                    $sheetDisplayName = str_replace('_', ' ', $sheetName);
                }

                $tableData = $sheet->toArray(null, true, true, false);
            } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
                $error = "Error reading spreadsheet: " . $e->getMessage();
            }
        } else {
            $error = "Timetable file not found.";
        }

        $rowspanData = $this->calculateVerticalRowspan($tableData);

        $colors = [
            '#A8D5BA', '#F6C1C1', '#FFD9A8', '#C1E0F6', '#E3C1F6',
            '#FFF1A8', '#F6E0C1', '#C1F6E3', '#F6C1E0', '#D9C1F6',
        ];

        $cellColors = [];

        // NEW: map of session_group_id => session_color
        $sessionColorsByGroupId = SessionGroup::where('timetable_id', $timetable->id)
            ->pluck('session_color', 'id')
            ->toArray();

        Logger::log('timetable_edit', 'timetable editing pane', [
            'timetable_id'   => $timetable->id,
            'timetable_name' => $timetable->timetable_name,
        ]);

        return view('timetabling.timetable-editing-pane.index', compact(
            'timetable',
            'tableData',
            'rowspanData',
            'error',
            'colors',
            'cellColors',
            'sheetIndex',
            'totalSheets',
            'sheetName',
            'sheetDisplayName',
            'sessionColorsByGroupId' // <-- pass to Blade
        ));
    }

    public function editor(Timetable $timetable)
    {
        $sessionGroups = SessionGroup::where('timetable_id', $timetable->id)
            ->with([
                'academicProgram',       // uses AcademicProgram model you just showed
                'courseSessions.course',
            ])
            ->get();

        $sessionGroups->each(function ($group) use ($timetable) {
            $group->update_color_url = route(
                'timetables.session-groups.update-color',
                [$timetable, $group]
            );
        });

        // Build placements from XLSX using encoded course_session_id
        $initialPlacementsByView = $this->buildInitialPlacementsFromXlsx($timetable);

        Logger::log(
            'timetable_editor_open',
            'timetable prototype editor opened',
            [
                'timetable_id'   => $timetable->id,
                'timetable_name' => $timetable->timetable_name,
            ]
        );

        return view('timetabling.timetable-editing-pane.editor', [
            'timetable'               => $timetable,
            'sessionGroups'           => $sessionGroups,
            'initialPlacementsByView' => $initialPlacementsByView,
        ]);
    }





}
