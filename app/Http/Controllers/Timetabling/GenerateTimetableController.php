<?php

namespace App\Http\Controllers\Timetabling;

use App\Helpers\AlgorithmQueries;
use App\Helpers\FilePath;
use App\Http\Controllers\Controller;
use App\Models\Records\Timetable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GenerateTimetableController extends Controller
{
    public function index(Timetable $timetable)
    {
        return view('timetabling.generate-timetable.index', compact('timetable'));
    }

    public function generate(Request $request, Timetable $timetable)
    {
        $timetableId = $timetable->id;

        // Directories in storage
        $exportDir = storage_path('app/exports/input-csvs');       // CSVs
        $outputDir = storage_path('app/exports/timetables');       // XLSX output

        // Ensure directories exist
        $this->ensureDirectoryExists($exportDir);
        $this->ensureDirectoryExists($outputDir);

        // Generate CSVs
        $this->generateQueryCSVs($timetableId, $exportDir);
        $this->generateTimetableTemplate($timetableId, $exportDir);

        // Run Python script using venv (Windows or Linux)
        //Change to .php for testing
        // Decide which script and interpreter to use
        $scriptPath = base_path('scripts/process_timetable.php'); // for PHP testing

        // Use the current PHP binary
        $interpreter = PHP_BINARY;

        // Build the command safely for both Windows and Linux
        $command = escapeshellarg($interpreter) . ' '
            . escapeshellarg($scriptPath) . ' '
            . escapeshellarg($exportDir) . ' '
            . escapeshellarg($outputDir) . ' '
            . escapeshellarg($timetableId) . ' 2>&1';

        $output  = [];
        $status  = 0;
        exec($command, $output, $status);
        $outputText = implode("\n", $output);

        if ($status !== 0) {
            return redirect()->back()->with('error', "<pre>" . e($outputText) . "</pre>");
        }


        // XLSX file path in storage
        $outputFile = $outputDir . DIRECTORY_SEPARATOR . "{$timetableId}.xlsx";

        if (!file_exists($outputFile)) {
            return redirect()->back()->with('error', "Timetable XLSX not found after generation.");
        }

        $outputUrl = asset("storage/exports/timetables/{$timetableId}.xlsx");

        return redirect()->back()->with('success', "Timetable generated successfully!");
    }

    /**
     * Ensure the export directory exists
     */
    private function ensureDirectoryExists($dir)
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
    /**
     * Generate CSVs based on the predefined queries
     */
    private function generateQueryCSVs($timetableId, $exportDir)
    {
        $queries = AlgorithmQueries::get();

        foreach ($queries as $name => $query) {
            $results = DB::select($query, ['timetableId' => $timetableId]);
            $this->writeCSV($results, "$exportDir/$name.csv");
        }
    }

    /**
     * Generate the timetable_template CSV
     */
    private function generateTimetableTemplate($timetableId, $exportDir)
    {
        // Fetch room names ordered by type
        $rooms = DB::table('timetable_rooms as tr')
            ->join('rooms as r', 'r.id', '=', 'tr.room_id')
            ->where('tr.timetable_id', $timetableId)
            ->orderByRaw("
                CASE r.room_type
                    WHEN 'comlab' THEN 1
                    WHEN 'lecture' THEN 2
                    WHEN 'gym' THEN 3
                    WHEN 'main' THEN 4
                    ELSE 5
                END
            ")
            ->orderBy('r.room_name', 'ASC')
            ->pluck('r.room_name')
            ->toArray();

        $timeSlots = $this->generateTimeSlots('07:00', '21:30', 30);

        // First row = rooms
        $csvData = ',' . implode(',', $rooms) . "\n";

        // Rows = time slots + vacant
        foreach ($timeSlots as $time) {
            $row = [$time];
            foreach ($rooms as $room) {
                $row[] = 'vacant';
            }
            $csvData .= implode(',', $row) . "\n";
        }

        file_put_contents("$exportDir/timetable_template.csv", $csvData);
    }

    /**
     * Generate time slots array in 30-minute intervals
     */
    private function generateTimeSlots($startTime, $endTime, $intervalMinutes = 30)
    {
        $start = strtotime($startTime);
        $end = strtotime($endTime);
        $slots = [];

        for ($time = $start; $time <= $end; $time += $intervalMinutes * 60) {
            $slots[] = date('g:i a', $time);
        }

        return $slots;
    }

    /**
     * Write CSV from DB results
     */
    private function writeCSV($results, $filePath)
    {
        $csvData = '';
        if (!empty($results)) {
            $csvData .= implode(',', array_keys((array) $results[0])) . "\n";
            foreach ($results as $row) {
                $csvData .= implode(',', array_map(function($value){
                        return '"' . str_replace('"', '""', $value) . '"';
                    }, (array) $row)) . "\n";
            }
        }
        file_put_contents($filePath, $csvData);
    }
}
