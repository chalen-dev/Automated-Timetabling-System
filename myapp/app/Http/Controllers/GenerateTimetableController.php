<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GenerateTimetableController extends Controller
{
    public function index($timetable)
    {
        return view('timetabling.generate-timetable.index', compact('timetable'));
    }

    public function generate(Request $request, $timetable)
    {
        $timetableId = $timetable;
        $exportDir = storage_path('app/exports/input-csvs');

        // Ensure export folder exists
        $this->ensureDirectoryExists($exportDir);

        // Generate query-based CSVs
        $this->generateQueryCSVs($timetableId, $exportDir);

        // Generate timetable_template.csv
        $this->generateTimetableTemplate($timetableId, $exportDir);

        // Run Python script
        $pythonScript = base_path("scripts/process_timetable.py");
        $exportDir = storage_path('app/exports/input-csvs');
        $command = escapeshellcmd("python $pythonScript $exportDir");
        $output = shell_exec($command);

        // Redirect back with success message
        return redirect()->back()->with('success', "CSV files generated and Python script executed. Output: " . $output);
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
        $queries = [
            'timetable-professors' => "SELECT tp.id AS timetable_professor_id, t.id AS timetable_id, t.timetable_name,
                                               p.id AS professor_id, CONCAT(p.first_name, ' ', p.last_name) AS professor_name,
                                               p.professor_type, p.position, ap.program_name AS academic_program
                                        FROM timetable_professors tp
                                        JOIN timetables t ON t.id = tp.timetable_id
                                        JOIN professors p ON p.id = tp.professor_id
                                        JOIN academic_programs ap ON ap.id = p.academic_program_id
                                        WHERE t.id = :timetableId",

            'timetable-rooms' => "SELECT tr.id AS timetable_room_id, t.id AS timetable_id, t.timetable_name, r.id AS room_id,
                                         r.room_name, r.room_type, r.room_capacity, r.course_type_exclusive_to,
                                         GROUP_CONCAT(red.exclusive_day ORDER BY FIELD(red.exclusive_day, 'mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun')) AS exclusive_days
                                  FROM timetable_rooms tr
                                  JOIN timetables t ON t.id = tr.timetable_id
                                  JOIN rooms r ON r.id = tr.room_id
                                  LEFT JOIN room_exclusive_days red ON red.room_id = r.id
                                  WHERE t.id = :timetableId
                                  GROUP BY tr.id, t.id, t.timetable_name, r.id, r.room_name, r.room_type, r.room_capacity, r.course_type_exclusive_to",

            'session-groups' => "SELECT sg.id AS session_group_id, t.id AS timetable_id, t.timetable_name,
                                        ap.program_name AS academic_program, sg.session_name, sg.year_level
                                 FROM session_groups sg
                                 JOIN academic_programs ap ON ap.id = sg.academic_program_id
                                 JOIN timetables t ON t.id = sg.timetable_id
                                 WHERE t.id = :timetableId",

            'course-sessions' => "SELECT sg.id AS session_group_id, sg.session_name, sg.year_level, ap.program_name AS academic_program,
                                        ap.program_abbreviation, cs.id AS course_session_id, cs.academic_term,
                                        c.id AS course_id, c.course_title, c.course_name, c.course_type, c.class_hours,
                                        c.total_lecture_class_days, c.total_laboratory_class_days, c.unit_load, c.duration_type
                                 FROM session_groups sg
                                 JOIN academic_programs ap ON ap.id = sg.academic_program_id
                                 JOIN course_sessions cs ON cs.session_group_id = sg.id
                                 JOIN courses c ON c.id = cs.course_id
                                 JOIN timetables t ON t.id = sg.timetable_id
                                 WHERE t.id = :timetableId
                                 ORDER BY ap.program_name, sg.year_level, sg.session_name, c.course_name",
        ];

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
            ->orderByRaw("FIELD(r.room_type, 'lecture', 'comlab', 'gym', 'main')")
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
