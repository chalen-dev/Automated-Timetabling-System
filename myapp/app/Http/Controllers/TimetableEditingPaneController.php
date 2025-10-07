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
                    // Vacant cells never merge
                    $rowspanData[$col][$row] = 1;
                    $currentValue = null;
                    $spanCount = 0;
                    continue;
                }

                if ($cell === $currentValue) {
                    // Same as previous → merge vertically
                    $spanCount++;
                    $rowspanData[$col][$row] = 0; // hide repeated cell
                    $rowspanData[$col][$startRow] = $spanCount + 1; // update rowspan of first cell
                } else {
                    // New value → start new block
                    $currentValue = $cell;
                    $startRow = $row;
                    $spanCount = 0;
                    $rowspanData[$col][$row] = 1; // visible
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
                $sheet = $spreadsheet->getSheet(0); // first sheet only
                $tableData = $sheet->toArray(null, true, true, false); // numeric keys
            } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
                $error = "Error reading spreadsheet: " . $e->getMessage();
            }
        } else {
            $error = "Timetable file not found.";
        }

        // Calculate rowspan
        $rowspanData = $this->calculateVerticalRowspan($tableData);

        return view('timetabling.timetable-editing-pane.index', compact('timetable', 'tableData', 'rowspanData', 'error'));
    }
}
