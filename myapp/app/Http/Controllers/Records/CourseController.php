<?php

namespace App\Http\Controllers\Records;

use App\Helpers\Logger;
use App\Http\Controllers\Controller;
use App\Models\Records\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    private $courseTypeOptions = [
        'major' => 'Major',
        'minor' => 'Minor',
        'pe' => 'PE',
        'nstp' => 'NSTP',
        'others' => 'Others',
    ];

    private $durationTypeOptions = [
        'semestral' => 'Semestral',
        'term' => 'Term'
    ];

    private function total_days_exceed_6($data){
        return ($data->total_lecture_class_days + $data->total_laboratory_class_days) > 6;
    }

    public function index(Request $request)
    {
        $search = $request->input('search');

        $courses = Course::when($search, function($query, $search) {
            $query->where('course_title', 'like', "%{$search}%")
                ->orWhere('course_name', 'like', "%{$search}%")
                ->orWhere('course_type', 'like', "%{$search}%");
        })->get();

        Logger::log('index', 'course', null);

        return view('records.courses.index', compact('courses', 'search'));
    }

    public function show(Course $course){

        //Log
        Logger::log('show', 'course', [
            'course_id' => $course->id,
            'course_title' => $course->course_title,
            'course_name' => $course->course_name,
        ]);
        return view('records.courses.show', compact('course'));
    }

    public function create(){
        $courseTypeOptions = $this->courseTypeOptions;
        $durationTypeOptions = $this->durationTypeOptions;

        //Log
        Logger::log('create', 'course', null);

        return view('records.courses.create', compact('courseTypeOptions', 'durationTypeOptions'));
    }

    public function store(Request $request){
        if ($this->total_days_exceed_6($request)){
            return back()->withErrors([
                'total_days' => 'The total of lecture and laboratory days cannot exceed 6.'
            ])->withInput();
        }

        $validatedData = $request->validate([
            'course_title' => 'required|string',
            'course_name' => 'required|string',
            'course_type' => 'required|string',
            'class_hours' => 'required|numeric|min:1|max:9',
            'total_lecture_class_days' => 'required|numeric|min:0|max:6',
            'total_laboratory_class_days' => 'required|numeric|min:0|max:6',
            'unit_load' => 'required|numeric|min:0.0|max:10.0',
            'duration_type' => 'required|string',
        ]);

        $course = Course::create($validatedData);

        //Log
        Logger::log('store', 'course', [
            'course_id' => $course->id,
            'course_title' => $course->course_title,
            'course_name' => $course->course_name,
            'course_type' => $course->course_type,
            'class_hours' => $course->class_hours,
            'total_lecture_class_days' => $course->total_lecture_class_days,
            'total_laboratory_class_days' => $course->total_laboratory_class_days,
            'unit_load' => $course->unit_load,
            'duration_type' => $course->duration_type,
        ]);

        return redirect()->route('courses.index')
            ->with('success', 'Course created successfully.');
    }

    public function edit(Course $course){
        $courseTypeOptions = $this->courseTypeOptions;
        $durationTypeOptions = $this->durationTypeOptions;

        //Log
        Logger::log('edit', 'course', [
            'course_id' => $course->id,
            'course_title' => $course->course_title,
            'course_name' => $course->course_name,
        ]);

        return view('records.courses.edit', compact('course', 'courseTypeOptions', 'durationTypeOptions'));
    }

    public function update(Request $request, Course $course){
        if ($this->total_days_exceed_6($request)){
            return back()->withErrors([
                'total_days' => 'The total of lecture and laboratory days cannot exceed 6.'
            ])->withInput();
        }

        $validatedData = $request->validate([
            'course_title' => 'required|string',
            'course_name' => 'required|string',
            'course_type' => 'required|string',
            'class_hours' => 'required|numeric|min:1|max:9',
            'total_lecture_class_days' => 'required|numeric|min:0|max:6',
            'total_laboratory_class_days' => 'required|numeric|min:0|max:6',
            'unit_load' => 'required|numeric|min:0.0|max:10.0',
            'duration_type' => 'required|string',
        ]);

        $course->update($validatedData);

        //Log
        Logger::log('update', 'course', $validatedData);

        return redirect()->route('courses.index')
            ->with('success', 'Course updated successfully');
    }

    public function destroy(Course $course)
    {
        // Collect metadata for logging
        $courseData = [
            'course_id'    => $course->id,
            'course_title' => $course->course_title,
        ];

        // Fetch all CourseSessions of this Course (across ALL SessionGroups / Timetables)
        $sessions = $course->courseSessions()
            ->with(['sessionGroup.academicProgram', 'sessionGroup.timetable'])
            ->get();

        // Loop each CourseSession and clean all XLSX references
        foreach ($sessions as $session) {
            $group = $session->sessionGroup;
            if (!$group || !$group->timetable) {
                continue;
            }

            $timetable = $group->timetable;

            // Build encoded string used in XLSX sheets
            $programAbbr = $group->academicProgram?->program_abbreviation ?? 'UNK';
            $yearLevel   = $group->year_level ?? '';
            $groupId     = $group->id;
            $sessionId   = $session->id;

            $encoded = "{$programAbbr}_{$yearLevel}_{$groupId}_{$sessionId}";

            $xlsxPath = storage_path("app/exports/timetables/{$timetable->id}.xlsx");

            if (!file_exists($xlsxPath)) {
                \Log::info("Course destroy: XLSX not found for timetable {$timetable->id}");
                continue;
            }

            if (!is_writable($xlsxPath) && !is_writable(dirname($xlsxPath))) {
                \Log::warning("Course destroy: XLSX not writable for timetable {$timetable->id}");
                continue;
            }

            try {
                // Load spreadsheet
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($xlsxPath);
                $sheetCount  = $spreadsheet->getSheetCount();
                $madeChange  = false;

                for ($si = 0; $si < $sheetCount; $si++) {
                    $sheet = $spreadsheet->getSheet($si);
                    $table = $sheet->toArray(null, true, true, false);

                    if (empty($table) || !is_array($table[0])) continue;

                    $rowCount = count($table);
                    $colCount = count($table[0] ?? []);

                    // skip col 0 (time), skip row 0 (room header)
                    for ($r = 1; $r < $rowCount; $r++) {
                        for ($c = 1; $c < $colCount; $c++) {
                            $cellVal = trim((string)($table[$r][$c] ?? ''));

                            if ($cellVal === $encoded) {
                                // convert array index → Excel notation
                                $excelRowIndex = $r + 1;
                                $excelColIndex = $c + 1;
                                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($excelColIndex);
                                $cellAddress = $colLetter . $excelRowIndex;

                                $sheet->setCellValue($cellAddress, 'Vacant');
                                $madeChange = true;
                            }
                        }
                    }
                }

                if ($madeChange) {
                    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
                    $writer->save($xlsxPath);
                    \Log::info("Course destroy: Removed encoded '{$encoded}' from timetable {$timetable->id}");
                }

            } catch (\Throwable $e) {
                \Log::error("Error cleaning XLSX for Course destroy: " . $e->getMessage(), [
                    'course_id'      => $course->id,
                    'session_id'     => $sessionId,
                    'timetable_id'   => $timetable->id,
                ]);
            }

            // Finally delete the CourseSession record
            $session->delete();
        }

        // Now delete the Course itself
        $course->delete();

        // Log final deletion
        Logger::log('delete', 'course', $courseData);

        return redirect()
            ->route('courses.index')
            ->with('success', 'Course deleted successfully');
    }

}
