<?php

namespace App\Http\Controllers;

use App\Models\Timetable;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TimetableEditingPaneController extends Controller
{
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

                // Automatically generate friendly display name
                // Example: 1stTerm_Monday => 1st Term - Monday
                $sheetDisplayName = preg_replace_callback(
                    '/(\d)(st|nd|rd|th)(Term)_([A-Za-z]+)/',
                    function ($matches) {
                        return $matches[1] . $matches[2] . ' Term - ' . $matches[4];
                    },
                    $sheetName
                );

                // Fallback: replace underscores with spaces if pattern doesn't match
                if (!$sheetDisplayName) {
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

        $this->logAction('viewed_timetable', [
            'timetable_id' => $timetable->id,
            'file_exists' => file_exists($xlsxPath),
            'sheet_index' => $sheetIndex,
            'error' => $error,
        ]);

        return view('timetabling.timetable-editing-pane.index', compact(
            'timetable', 'tableData', 'rowspanData', 'error',
            'colors', 'cellColors', 'sheetIndex', 'totalSheets', 'sheetName', 'sheetDisplayName'
        ));
    }


    protected function logAction(string $action, array $details = [])
    {
        if (auth()->check()) {
            \App\Models\UserLog::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'description' => json_encode($details),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }
    }
}
