<?php

namespace App\Http\Controllers;

use App\Models\Timetable;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TimetableEditingPaneController extends Controller
{
    /**
     * Calculate vertical rowspan for a timetable table
     */
    private function calculateVerticalRowspan(array $tableData): array
    {
        $rowspanData = [];
        $rowCount = count($tableData);
        if ($rowCount < 2) return $rowspanData; // no data

        $colCount = count($tableData[0] ?? []);

        for ($col = 0; $col < $colCount; $col++) {
            $rowspanData[$col] = [];
            $currentValue = null;
            $startRow = 1; // first row after header
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

    /**
     * Show timetable view
     */
    public function index(Timetable $timetable)
    {
        $xlsxPath = base_path("scripts/public/exports/timetables/{$timetable->id}.xlsx");
        $tableData = [];
        $error = null;

        if (file_exists($xlsxPath)) {
            try {
                $spreadsheet = IOFactory::load($xlsxPath);
                $sheet = $spreadsheet->getSheet(0);
                $tableData = $sheet->toArray(null, true, true, false);
            } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
                $error = "Error reading spreadsheet: " . $e->getMessage();
            }
        } else {
            $error = "Timetable file not found.";
        }

        // Calculate rowspan
        $rowspanData = $this->calculateVerticalRowspan($tableData);

        // --- Logging ---
        $this->logAction('viewed_timetable', [
            'timetable_id' => $timetable->id,
            'file_exists' => file_exists($xlsxPath),
            'error' => $error,
        ]);

        return view('timetabling.timetable-editing-pane.index', compact('timetable', 'tableData', 'rowspanData', 'error'));
    }

    /**
     * Log user actions
     */
    protected function logAction(string $action, array $details = [])
    {
        if(auth()->check()) {
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
