<?php

namespace App\Http\Controllers\Timetabling;

use App\Helpers\Logger;
use App\Http\Controllers\Controller;
use App\Models\Records\Timetable;
use App\Models\Timetabling\SessionGroup;
use App\Models\Timetabling\CourseSession;
use App\Models\Timetabling\TimetableRoom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Throwable;


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
            $programAbbrev = $group->academicProgram?->program_abbreviation ?? 'Unknown';
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
     * where array keys like "33" are CourseSession IDs. This version maps sheet columns
     * to the controller's canonical room ordering (so room deletions won't shift columns).
     */
    private function buildInitialPlacementsFromXlsx(Timetable $timetable): array
    {
        // 1. Try reading from the bucket
        $bucketPath = "timetables/{$timetable->id}.xlsx";
        $tempFile = null;

        if (Storage::disk('facultime')->exists($bucketPath)) {
            try {
                $tempFile = tempnam(sys_get_temp_dir(), 'tt_');
                file_put_contents($tempFile, Storage::disk('facultime')->get($bucketPath));
                $xlsxPath = $tempFile;
            } catch (\Throwable $e) {
                $xlsxPath = null;
            }
        } else {
            $xlsxPath = null;
        }

        // 2. Fallback to local legacy path
        if (!$xlsxPath) {
            $legacyPath = storage_path("app/exports/timetables/{$timetable->id}.xlsx");
            if (file_exists($legacyPath)) {
                $xlsxPath = $legacyPath;
            } else {
                return []; // Nothing we can load
            }
        }

        // 3. Load spreadsheet normally
        $spreadsheet = IOFactory::load($xlsxPath);

        // Cleanup
        if ($tempFile && file_exists($tempFile)) {
            @unlink($tempFile);
        }

        $sheetCount  = $spreadsheet->getSheetCount();

        $placementsByView = [];

        // canonical rooms order for this timetable (same ordering used by the editor)
        $rooms = DB::table('timetable_rooms as tr')
            ->join('rooms as r', 'r.id', '=', 'tr.room_id')
            ->where('tr.timetable_id', $timetable->id)
            ->orderByRaw("
            CASE
                WHEN LOWER(r.room_type) IN ('comlab','com lab','com-lab','lab','computer lab','computer_lab') THEN 1
                WHEN LOWER(r.room_type) IN ('lecture','lec','lecture_hall') THEN 2
                WHEN LOWER(r.room_type) IN ('avr','av','audio-visual','av_room') THEN 3
                WHEN LOWER(r.room_type) IN ('gym','gymnasium') THEN 4
                WHEN LOWER(r.room_type) IN ('main','main_hall') THEN 5
                ELSE 10
            END
        ")
            ->orderBy('r.room_name')
            ->pluck('room_name')
            ->toArray();

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

            // Column 0 is Time header; header row is $table[0]; sheet columns are array indices 0..N-1
            $colCount = count($table[0] ?? []);

            // Build normalized header mapping: normalized room name => sheet column index (0-based)
            $header = $table[0] ?? [];
            $sheetNameToColIndex = [];
            for ($c = 1; $c < $colCount; $c++) {
                $raw = trim((string) ($header[$c] ?? ''));
                if ($raw === '') continue;
                $norm = strtolower(preg_replace('/\s+/', ' ', $raw));
                $sheetNameToColIndex[$norm] = $c;
            }

            // Map canonical room index (0-based) => sheet column index (0-based)
            $canonicalIndexToSheetCol = [];
            foreach ($rooms as $idx => $roomName) {
                $norm = strtolower(preg_replace('/\s+/', ' ', trim($roomName)));
                $canonicalIndexToSheetCol[$idx] = $sheetNameToColIndex[$norm] ?? null;
            }

            $viewPlacements = [];

            // Iterate canonical room indices (so the editor's col index = canonical room index)
            $totalCanonicalRooms = count($rooms);
            for ($canonicalCol = 0; $canonicalCol < $totalCanonicalRooms; $canonicalCol++) {
                $sheetCol = $canonicalIndexToSheetCol[$canonicalCol] ?? null;
                if ($sheetCol === null) {
                    // Room not present in this sheet header (deleted/renamed); skip safely.
                    continue;
                }

                $row = 1; // skip header row 0 (room names)
                while ($row < $rowCount) {
                    $rawCell = $table[$row][$sheetCol] ?? '';
                    $cell    = trim((string) $rawCell);
                    $lower   = strtolower($cell);

                    // Skip blanks and "Vacant"
                    if ($cell === '' || $lower === 'vacant') {
                        $row++;
                        continue;
                    }

                    // Pattern expected: {program_abbreviation}_{year_level}_{session_group_id}_{course_session_id}
                    if (!preg_match('/^[A-Za-z]+_[^_]+_(\d+)_(\d+)$/', $cell, $matches)) {
                        // not a known encoded value; skip it
                        $row++;
                        continue;
                    }

                    // CourseSession.id is captured as last group
                    $sessionId = (string) ((int) $matches[2]);

                    // Determine vertical span of identical contiguous cells
                    $startRow = $row;
                    $span     = 1;
                    $r = $row + 1;
                    while ($r < $rowCount) {
                        $next = trim((string) ($table[$r][$sheetCol] ?? ''));
                        if ($next === $cell) {
                            $span++;
                            $r++;
                        } else {
                            break;
                        }
                    }

                    // Editor rows are 0-based timeslot indices:
                    // spreadsheet row 1 => topRow 0, row 2 => topRow 1, etc.
                    $topRow = $startRow - 1;
                    $blocks = $span;

                    // Store placement: col is canonical (editor) index
                    $viewPlacements[$sessionId] = [
                        'col'    => $canonicalCol,
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

        // NEW: we will try to load from the facultime bucket first,
        // and fall back to the old local path if needed.
        $diskPath = "timetables/{$timetable->id}.xlsx";
        $xlsxPath = null;
        $tempFile = null;

        if (Storage::disk('facultime')->exists($diskPath)) {
            $tempFile = tempnam(sys_get_temp_dir(), 'tt_');
            file_put_contents($tempFile, Storage::disk('facultime')->get($diskPath));
            $xlsxPath = $tempFile;
        } else {
            // fallback to legacy local path
            $legacyPath = storage_path("app/exports/timetables/{$timetable->id}.xlsx");
            if (file_exists($legacyPath)) {
                $xlsxPath = $legacyPath;
            }
        }


        $tableData = [];
        $error = null;
        $totalSheets = 0;
        $sheetName = null;
        $sheetDisplayName = null;

        if ($xlsxPath && file_exists($xlsxPath)) {
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

        // Clean up temp file if we created one
        if ($tempFile && file_exists($tempFile)) {
            @unlink($tempFile);
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
            'sessionColorsByGroupId'
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


        $rooms = TimetableRoom::where('timetable_id', $timetable->id)
            ->join('rooms as r', 'r.id', '=', 'timetable_rooms.room_id')
            ->orderByRaw("
                CASE
                    WHEN LOWER(r.room_type) = 'comlab' THEN 0
                    WHEN LOWER(r.room_type) = 'lecture' THEN 1
                    ELSE 2
                END
            ")
            ->orderBy('r.room_name', 'asc')
            ->select('timetable_rooms.*', 'r.room_name', 'r.room_type')
            ->get();


        Logger::log(
            'timetable_editor_open',
            'timetable prototype editor opened',
            [
                'timetable_id'   => $timetable->id,
                'timetable_name' => $timetable->timetable_name,
            ]
        );


        return view('timetabling.timetable-editing-pane.editor', [
            'timetable' => $timetable,
            'sessionGroups' => $sessionGroups,
            'initialPlacementsByView' => $initialPlacementsByView,
            'rooms' => $rooms,
        ]);
    }

    public function saveFromEditor(Timetable $timetable, Request $request)
    {
        try {
            // 1) Get placements from request (be permissive, don't rely on validate's redirect)
            $placementsByView = $request->input('placementsByView', []);
            if (!is_array($placementsByView)) {
                throw new \RuntimeException('placementsByView must be an array.');
            }

            // 2) Load existing XLSX (same path pattern as index/editor)
            $xlsxPath = storage_path("app/exports/timetables/{$timetable->id}.xlsx");
            if (!file_exists($xlsxPath)) {
                Log::error('Timetable XLSX not found for saveFromEditor', [
                    'timetable_id' => $timetable->id,
                    'path'         => $xlsxPath,
                ]);

                return response()->json([
                    'status'  => 'error',
                    'message' => 'Timetable XLSX not found.',
                ], 404);
            }

            $spreadsheet = IOFactory::load($xlsxPath);
            $sheetCount  = $spreadsheet->getSheetCount();

            // 3) Build map: sessionId => "CS_3rd_5_33"
            $sessionGroups = SessionGroup::where('timetable_id', $timetable->id)
                ->with(['academicProgram', 'courseSessions'])
                ->get();

            $codeBySessionId = [];
            foreach ($sessionGroups as $group) {
                $programAbbr = $group->academicProgram?->program_abbreviation ?? 'UNK';
                $yearLevel   = $group->year_level ?? '';

                foreach ($group->courseSessions as $session) {
                    $sessionId = (string) $session->id;
                    $codeBySessionId[$sessionId] =
                        "{$programAbbr}_{$yearLevel}_{$group->id}_{$session->id}";
                }
            }

            // 3b) canonical room list for this timetable (must match editor's order)
            $rooms = DB::table('timetable_rooms as tr')
                ->join('rooms as r', 'r.id', '=', 'tr.room_id')
                ->where('tr.timetable_id', $timetable->id)
                ->orderByRaw("
                    CASE
                        WHEN LOWER(r.room_type) = 'comlab' THEN 0
                        WHEN LOWER(r.room_type) = 'lecture' THEN 1
                        ELSE 2
                    END
                ")
                ->orderBy('r.room_name')
                ->pluck('room_name')
                ->toArray();


            // 4) Overwrite each sheet's data cells from placementsByView
            for ($sheetIndex = 0; $sheetIndex < $sheetCount; $sheetIndex++) {
                // Map sheet index to (termIndex, dayIndex): 0..5 = 1st term, 6..11 = 2nd term
                $termIndex = $sheetIndex < 6 ? 0 : 1;
                $dayIndex  = $sheetIndex % 6;
                $viewKey   = $termIndex . '-' . $dayIndex;

                $sheet = $spreadsheet->getSheet($sheetIndex);
                $table = $sheet->toArray(null, true, true, false);

                $rowCount = count($table);
                if ($rowCount < 2) {
                    continue; // no data rows
                }
                $colCount = count($table[0] ?? []);

                // ---- Build header map: normalized room name => Excel column index (1-based) ----
                $header = $table[0];
                $sheetRoomNameToCol = []; // normalized => excelColIndex (1-based)
                for ($c = 1; $c < $colCount; $c++) {
                    $raw = trim((string) ($header[$c] ?? ''));
                    if ($raw === '') continue;
                    $norm = strtolower(preg_replace('/\s+/', ' ', $raw));
                    // PhpSpreadsheet 1-based columns: add 1 to array index
                    $sheetRoomNameToCol[$norm] = $c + 1;
                }

                // Map canonical room name (editor order) => excel column index (1-based)
                $canonicalRoomToCol = [];
                foreach ($rooms as $idx => $roomName) {
                    $norm = strtolower(preg_replace('/\s+/', ' ', trim($roomName)));
                    if (isset($sheetRoomNameToCol[$norm])) {
                        $canonicalRoomToCol[$norm] = $sheetRoomNameToCol[$norm];
                    }
                }
                // -------------------------------------------------------------------------------

                // 4a) Clear data area to "Vacant"
                for ($row = 1; $row < $rowCount; $row++) {
                    for ($col = 1; $col < $colCount; $col++) {
                        $excelRowIndex = $row + 1;        // PhpSpreadsheet rows are 1-based
                        $excelColIndex = $col + 1;        // array index -> excel col
                        $colLetter     = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($excelColIndex);
                        $cellAddress   = $colLetter . $excelRowIndex; // e.g. "B2"
                        $sheet->setCellValue($cellAddress, 'Vacant');
                    }
                }

                // 4b) Fill from placements for this view, if any
                if (
                    !isset($placementsByView[$viewKey]) ||
                    !is_array($placementsByView[$viewKey]) ||
                    empty($placementsByView[$viewKey])
                ) {
                    continue;
                }

                foreach ($placementsByView[$viewKey] as $sessionId => $placement) {
                    $sessionId = (string) $sessionId;
                    if (!isset($codeBySessionId[$sessionId])) {
                        // Session not part of this timetable (defensive)
                        Log::warning('saveFromEditor: sessionId not found in codeBySessionId', [
                            'timetable_id' => $timetable->id,
                            'session_id'   => $sessionId,
                        ]);
                        continue;
                    }

                    $code   = $codeBySessionId[$sessionId];
                    $col    = (int) ($placement['col'] ?? 0);     // 0-based among rooms (editor canonical index)
                    $topRow = (int) ($placement['topRow'] ?? 0);  // 0-based among timeslots
                    $blocks = max(1, (int) ($placement['blocks'] ?? 1));

                    // Editor's col is index into $rooms array
                    if (!isset($rooms[$col])) {
                        Log::warning('saveFromEditor: room index out of range for placement', [
                            'timetable_id' => $timetable->id,
                            'session_id' => $sessionId,
                            'col_index' => $col,
                        ]);
                        continue;
                    }

                    $roomName = $rooms[$col];
                    $roomNorm = strtolower(preg_replace('/\s+/', ' ', trim($roomName)));

                    if (!isset($canonicalRoomToCol[$roomNorm])) {
                        // The room in the editor isn't present in the sheet header (deleted/renamed) — skip writing.
                        Log::warning('saveFromEditor: canonical room not found in sheet header', [
                            'timetable_id' => $timetable->id,
                            'session_id' => $sessionId,
                            'room' => $roomName,
                        ]);
                        continue;
                    }

                    $excelColIndex = $canonicalRoomToCol[$roomNorm]; // 1-based excel column index
                    $excelRowTop   = $topRow + 2; // topRow 0 -> excel row 2

                    for ($offset = 0; $offset < $blocks; $offset++) {
                        $excelRowIndex = $excelRowTop + $offset;
                        $colLetter     = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($excelColIndex);
                        $cellAddress   = $colLetter . $excelRowIndex;
                        $sheet->setCellValue($cellAddress, $code);
                    }
                }
            }

            // 5) Save XLSX back to disk
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

            // ensure writable (explicit check will give clearer error)
            if (!is_writable(dirname($xlsxPath))) {
                Log::error('saveFromEditor: output directory not writable', ['path' => dirname($xlsxPath)]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot write timetable file: directory is not writable.'
                ], 500);
            }

            $writer->save($xlsxPath);

            return response()->json([
                'status'  => 'ok',
                'message' => 'Timetable changes saved.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Error in saveFromEditor', [
                'timetable_id' => $timetable->id ?? null,
                'error'        => $e->getMessage(),
                'trace'        => $e->getTraceAsString(),
            ]);

            $msg = $e->getMessage();

            if (str_contains($msg, 'Resource temporarily unavailable') || str_contains($msg, 'Permission denied')) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Cannot save the Excel file because it is locked or not writable. Close the XLSX in Excel / any viewer and make sure the file is writable, then try again.',
                ], 500);
            }

            return response()->json([
                'status'  => 'error',
                'message' => $msg,
            ], 500);
        }
    }

    public function exportFormattedSpreadsheet(Timetable $timetable)
    {
        $inputPath = storage_path("app/exports/timetables/{$timetable->id}.xlsx");

        if (!file_exists($inputPath)) {
            return redirect()->back()->with('error', 'Source timetable XLSX not found for id: ' . $timetable->id);
        }

        try {
            $reader = IOFactory::createReaderForFile($inputPath);
            $reader->setReadDataOnly(true);
            $src = $reader->load($inputPath);

            $target = new Spreadsheet();
            while ($target->getSheetCount() > 0) {
                $target->removeSheetByIndex(0);
            }

            $sheetCount = $src->getSheetCount();

            for ($s = 0; $s < $sheetCount; $s++) {
                $srcSheet = $src->getSheet($s);
                $sheetTitle = $srcSheet->getTitle() ?: ('Sheet' . ($s + 1));

                $dstSheet = new Worksheet($target, $sheetTitle);
                $target->addSheet($dstSheet, $s);

                $highestRow = (int)$srcSheet->getHighestRow();
                $highestColIndex = Coordinate::columnIndexFromString($srcSheet->getHighestColumn());
                $lastColLetter = Coordinate::stringFromColumnIndex($highestColIndex);

                // Top headers
                $titleRange = "A1:{$lastColLetter}1";
                $subtitleRange = "A2:{$lastColLetter}2";

                $mainTitle = trim($timetable->timetable_name . ' ' . $timetable->semester . ' semester (' . $timetable->academic_year . ')');
                $dstSheet->setCellValue('A1', $mainTitle);
                $dstSheet->mergeCells($titleRange);
                $dstSheet->getStyle($titleRange)->getFont()->setBold(true)->setSize(16);
                $dstSheet->getStyle($titleRange)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $dstSheet->setCellValue('A2', $sheetTitle);
                $dstSheet->mergeCells($subtitleRange);
                $dstSheet->getStyle($subtitleRange)->getFont()->setBold(true)->setSize(12);
                $dstSheet->getStyle($subtitleRange)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                // Copy source values starting at dst row 3 (so src row 1 -> dst row 3)
                $dstRowOffset = 2;
                for ($r = 1; $r <= $highestRow; $r++) {
                    for ($c = 1; $c <= $highestColIndex; $c++) {
                        $colLetter = Coordinate::stringFromColumnIndex($c);
                        $srcCell = $srcSheet->getCell($colLetter . $r);
                        $val = $srcCell ? $srcCell->getValue() : null;

                        // ** CHANGE: write blank instead of the string "Vacant" **
                        if ($val === null || trim((string)$val) === '') {
                            $val = ''; // blank cell
                        }

                        $dstCellCoord = $colLetter . ($r + $dstRowOffset);
                        $dstSheet->setCellValue($dstCellCoord, (string)$val);
                    }
                }

                // set timeslots header (first column header row in copied table)
                $dstSheet->setCellValue('A' . (1 + $dstRowOffset), 'timeslots');
                $dstSheet->getStyle('A' . (1 + $dstRowOffset))->getFont()->setBold(true);

                $allRange = 'A' . (1 + $dstRowOffset) . ':' . $lastColLetter . ($highestRow + $dstRowOffset);
                $dstSheet->getStyle($allRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $dstSheet->getStyle($allRange)->getAlignment()->setWrapText(true);
                $dstSheet->getStyle($allRange)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);

                // Merge only course-session blocks (vertical contiguous identical values that are "course-like")
                for ($c = 1; $c <= $highestColIndex; $c++) {
                    $colLetter = Coordinate::stringFromColumnIndex($c);

                    $runValue = null;
                    $runStart = 1 + $dstRowOffset;

                    for ($dstR = (1 + $dstRowOffset); $dstR <= ($highestRow + $dstRowOffset + 1); $dstR++) {
                        if ($dstR <= $highestRow + $dstRowOffset) {
                            $cellVal = $dstSheet->getCell($colLetter . $dstR)->getValue();
                            $cellValStr = $cellVal === null ? null : trim((string)$cellVal);
                        } else {
                            $cellValStr = null; // flush run
                        }

                        if ($cellValStr !== null && $cellValStr === '') $cellValStr = null;

                        // is this cell a "course-like" encoded string? (non-empty and has underscores / parts)
                        $isCourseLike = false;
                        if ($cellValStr !== null) {
                            $partsCheck = explode('_', $cellValStr);
                            if (count($partsCheck) >= 3) {
                                $isCourseLike = true;
                            }
                        }

                        if ($runValue === null) {
                            if ($cellValStr !== null && $isCourseLike) {
                                $runValue = $cellValStr;
                                $runStart = $dstR;
                            } else {
                                $runValue = null;
                                $runStart = $dstR + 1;
                            }
                            continue;
                        }

                        // continuing run?
                        if ($cellValStr === $runValue) {
                            continue;
                        }

                        // run ended at dstR - 1
                        $runEnd = $dstR - 1;
                        $runLen = $runEnd - $runStart + 1;

                        if ($runValue !== null && $runLen > 1) {
                            $mergeRange = "{$colLetter}{$runStart}:{$colLetter}{$runEnd}";
                            $dstSheet->mergeCells($mergeRange);

                            // parse encoded value to locate sessionGroupId and sessionId (robust)
                            $parts = explode('_', $runValue);
                            $sessionGroupId = null;
                            $sessionIdPart = null;
                            if (count($parts) >= 2) {
                                $sessionGroupId = $parts[count($parts) - 2] ?? null;
                                $sessionIdPart = $parts[count($parts) - 1] ?? null;
                            }

                            // build display text: top line (sessionGroup title) and bottom (course title)
                            $displayMain = $runValue;  // fallback plain encoded string
                            $displayTop = null;
                            $displayBottom = null;

                            if ($sessionIdPart) {
                                // attempt to load CourseSession and related models
                                $cs = \App\Models\Timetabling\CourseSession::with(['course', 'sessionGroup.academicProgram'])->find($sessionIdPart);
                                if ($cs) {
                                    $sg = $cs->sessionGroup;
                                    $prog = $sg && $sg->academicProgram ? $sg->academicProgram->program_abbreviation : null;
                                    $sessionName = $sg->session_name ?? '';
                                    $yearLevel = $sg->year_level !== null ? $sg->year_level : '';
                                    $displayTop = trim(($prog ?: 'Unknown') . ' ' . $sessionName . ' ' . ($yearLevel !== '' ? $yearLevel . ' Year' : ''));
                                    $course = $cs->course;
                                    $displayBottom = $course ? ($course->course_title ?: $course->course_name ?: 'Course #' . $cs->course_id) : ('Course #' . $cs->course_id);
                                    $displayMain = $displayTop . "\n" . $displayBottom;
                                } elseif ($sessionGroupId) {
                                    // fallback: try to fetch session group only
                                    $sg = \App\Models\Timetabling\SessionGroup::with('academicProgram')->find($sessionGroupId);
                                    if ($sg) {
                                        $prog = $sg->academicProgram ? $sg->academicProgram->program_abbreviation : null;
                                        $displayTop = trim(($prog ?: 'Unknown') . ' ' . ($sg->session_name ?? '') . ' ' . ($sg->year_level !== null ? $sg->year_level . ' Year' : ''));
                                        $displayMain = $displayTop . "\n" . ($runValue);
                                    }
                                }
                            }

                            // apply background color for session group if available (try sessionGroupId or cs->sessionGroup)
                            $hex = null;
                            if (!empty($sessionGroupId)) {
                                $sg2 = \App\Models\Timetabling\SessionGroup::find($sessionGroupId);
                                if ($sg2 && !empty($sg2->session_color)) {
                                    $hex = ltrim($sg2->session_color, '#');
                                }
                            }
                            // if still null, try using cs->sessionGroup
                            if (empty($hex) && isset($cs) && $cs && $cs->sessionGroup && !empty($cs->sessionGroup->session_color)) {
                                $hex = ltrim($cs->sessionGroup->session_color, '#');
                            }

                            if (!empty($hex) && strlen($hex) === 6) {
                                $excelColor = 'FF' . strtoupper($hex);
                                $dstSheet->getStyle($mergeRange)->getFill()
                                    ->setFillType(Fill::FILL_SOLID)
                                    ->getStartColor()->setARGB($excelColor);
                            }

                            // Use RichText to set top bold + bottom italic on the merged cell
                            $rich = new \PhpOffice\PhpSpreadsheet\RichText\RichText();
                            if ($displayTop !== null) {
                                $runTop = $rich->createTextRun($displayTop . "\n");
                                $runTop->getFont()->setBold(true);
                                $runTop->getFont()->setSize(11);
                            } else {
                                // fallback to runValue as plain bold top
                                $rTop = $rich->createTextRun($runValue . "\n");
                                $rTop->getFont()->setBold(true);
                                $rTop->getFont()->setSize(11);
                            }
                            if ($displayBottom !== null) {
                                $runBottom = $rich->createTextRun($displayBottom);
                                $runBottom->getFont()->setItalic(true);
                                $runBottom->getFont()->setSize(10);
                            } else {
                                // if no course title found, append the encoded
                                $runBottom = $rich->createTextRun($runValue);
                                $runBottom->getFont()->setItalic(true);
                                $runBottom->getFont()->setSize(10);
                            }

                            // write rich text into the top-left cell of the merged range
                            $dstSheet->getCell($colLetter . $runStart)->setValue($rich);

                            // center & wrap
                            $dstSheet->getStyle($mergeRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                            $dstSheet->getStyle($mergeRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                            $dstSheet->getStyle($mergeRange)->getFont()->setBold(false); // per-run formatting already set
                            $dstSheet->getStyle($mergeRange)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
                        } elseif ($runValue !== null && $runLen === 1) {
                            // single course-like cell (no merge) -> style similarly and set rich text
                            $cellCoord = $colLetter . $runStart;

                            $parts = explode('_', $runValue);
                            $sessionGroupId = count($parts) >= 2 ? $parts[count($parts) - 2] : null;
                            $sessionIdPart = count($parts) >= 1 ? $parts[count($parts) - 1] : null;

                            $displayTop = null;
                            $displayBottom = null;
                            if ($sessionIdPart) {
                                $csSingle = \App\Models\Timetabling\CourseSession::with(['course', 'sessionGroup.academicProgram'])->find($sessionIdPart);
                                if ($csSingle) {
                                    $sg = $csSingle->sessionGroup;
                                    $prog = $sg && $sg->academicProgram ? $sg->academicProgram->program_abbreviation : null;
                                    $displayTop = trim(($prog ?: 'Unknown') . ' ' . ($sg->session_name ?? '') . ' ' . ($sg->year_level !== null ? $sg->year_level . ' Year' : ''));
                                    $displayBottom = $csSingle->course ? ($csSingle->course->course_title ?: $csSingle->course->course_name) : ('Course #' . $csSingle->course_id);
                                }
                            }

                            $rich = new \PhpOffice\PhpSpreadsheet\RichText\RichText();
                            if ($displayTop !== null) {
                                $rt = $rich->createTextRun($displayTop . "\n");
                                $rt->getFont()->setBold(true)->setSize(11);
                            } else {
                                $rt = $rich->createTextRun($runValue . "\n");
                                $rt->getFont()->setBold(true)->setSize(11);
                            }
                            if ($displayBottom !== null) {
                                $rb = $rich->createTextRun($displayBottom);
                                $rb->getFont()->setItalic(true)->setSize(10);
                            } else {
                                $rb = $rich->createTextRun($runValue);
                                $rb->getFont()->setItalic(true)->setSize(10);
                            }

                            $dstSheet->getCell($cellCoord)->setValue($rich);

                            // color if available
                            $hex = null;
                            if (!empty($sessionGroupId)) {
                                $sg2 = \App\Models\Timetabling\SessionGroup::find($sessionGroupId);
                                if ($sg2 && !empty($sg2->session_color)) {
                                    $hex = ltrim($sg2->session_color, '#');
                                }
                            }
                            if (!empty($hex) && strlen($hex) === 6) {
                                $excelColor = 'FF' . strtoupper($hex);
                                $dstSheet->getStyle($cellCoord)->getFill()
                                    ->setFillType(Fill::FILL_SOLID)
                                    ->getStartColor()->setARGB($excelColor);
                            }

                            $dstSheet->getStyle($cellCoord)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                            $dstSheet->getStyle($cellCoord)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                            $dstSheet->getStyle($cellCoord)->getFont()->setBold(false);
                        }

                        // reset run
                        if ($cellValStr !== null && count(explode('_', $cellValStr)) >= 3) {
                            $runValue = $cellValStr;
                            $runStart = $dstR;
                        } else {
                            $runValue = null;
                            $runStart = $dstR + 1;
                        }
                    } // dstR
                } // col loop

                // autosize
                for ($c = 1; $c <= $highestColIndex; $c++) {
                    $dstSheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setAutoSize(true);
                }
                $dstSheet->getColumnDimension('A')->setWidth(14);
            } // sheets loop

            // ensure output folder exists
            $outputDir = storage_path('app/exports/formatted-spreadsheets');
            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0755, true);
            }

            $outputPath = $outputDir . DIRECTORY_SEPARATOR . $timetable->id . '-formatted.xlsx';
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($target);
            $writer->save($outputPath);

            return response()->download($outputPath, "{$timetable->timetable_name}-formatted.xlsx", [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            ]);
        } catch (Throwable $e) {
            return redirect()->back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }















}
