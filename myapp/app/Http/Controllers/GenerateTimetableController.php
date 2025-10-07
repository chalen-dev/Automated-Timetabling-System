<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GenerateTimetableController extends Controller
{
    public function index($timetable)
    {
        $this->logAction('accessed_generate_timetable_page', ['timetable_id' => $timetable]);

        return view('timetabling.generate-timetable.index', compact('timetable'));
    }

    public function generate(Request $request, $timetable)
    {
        $timetableId = $timetable;
        $exportDir = storage_path('app/exports/input-csvs');

        $this->logAction('started_generate_timetable', ['timetable_id' => $timetableId]);

        // Ensure export folder exists
        $this->ensureDirectoryExists($exportDir);

        // Generate query-based CSVs
        $this->generateQueryCSVs($timetableId, $exportDir);

        // Generate timetable_template.csv
        $this->generateTimetableTemplate($timetableId, $exportDir);

        // Run Python script
        $pythonScript = base_path("scripts/process_timetable.py");
        $command = escapeshellcmd("python $pythonScript $exportDir");
        $output = shell_exec($command);

        $this->logAction('finished_generate_timetable', [
            'timetable_id' => $timetableId,
            'python_output' => $output
        ]);

        return redirect()->back()->with('success', "CSV files generated and Python script executed. Output: " . $output);
    }

    private function ensureDirectoryExists($dir)
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    private function generateQueryCSVs($timetableId, $exportDir)
    {
        $queries = [
            'timetable-professors' => "...", // existing queries
            'timetable-rooms' => "...",
            'session-groups' => "...",
            'course-sessions' => "..."
        ];

        foreach ($queries as $name => $query) {
            $results = DB::select($query, ['timetableId' => $timetableId]);
            $this->writeCSV($results, "$exportDir/$name.csv");

            $this->logAction('generated_csv', [
                'timetable_id' => $timetableId,
                'csv_name' => "$name.csv",
                'row_count' => count($results)
            ]);
        }
    }

    private function generateTimetableTemplate($timetableId, $exportDir)
    {
        $rooms = DB::table('timetable_rooms as tr')
            ->join('rooms as r', 'r.id', '=', 'tr.room_id')
            ->where('tr.timetable_id', $timetableId)
            ->orderByRaw("FIELD(r.room_type, 'lecture', 'comlab', 'gym', 'main')")
            ->pluck('r.room_name')
            ->toArray();

        $timeSlots = $this->generateTimeSlots('07:00', '21:30', 30);

        $csvData = ',' . implode(',', $rooms) . "\n";

        foreach ($timeSlots as $time) {
            $row = [$time];
            foreach ($rooms as $room) {
                $row[] = 'vacant';
            }
            $csvData .= implode(',', $row) . "\n";
        }

        file_put_contents("$exportDir/timetable_template.csv", $csvData);

        $this->logAction('generated_csv', [
            'timetable_id' => $timetableId,
            'csv_name' => 'timetable_template.csv',
            'row_count' => count($timeSlots)
        ]);
    }

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

    /**
     * Logs user actions
     */
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
