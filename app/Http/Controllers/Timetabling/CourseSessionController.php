<?php

namespace App\Http\Controllers\Timetabling;

use App\Helpers\Logger;
use App\Http\Controllers\Controller;
use App\Models\Records\Course;
use App\Models\Records\Timetable;
use App\Models\Timetabling\CourseSession;
use App\Models\Timetabling\SessionGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CourseSessionController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create(Timetable $timetable, SessionGroup $sessionGroup, Request $request)
    {
        if ($sessionGroup->timetable_id !== $timetable->id) {
            abort(404);
        }

        $assignedCourseIds = CourseSession::where('session_group_id', $sessionGroup->id)
            ->pluck('course_id')
            ->toArray();
        $query = Course::whereNotIn('id', $assignedCourseIds);

        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('course_title', 'like', "%{$search}%")
                    ->orWhere('course_name', 'like', "%{$search}%")
                    ->orWhere('course_type', 'like', "%{$search}%")
                    ->orWhere('unit_load', 'like', "%{$search}%")
                    ->orWhere('duration_type', 'like', "%{$search}%");
            });
        }

        $courses = $query->get();
        $selected = $request->input('courses', []);

        Logger::log('create', 'course sessions', [
            'timetable_id' => $timetable->id,
            'session_group_id' => $sessionGroup->id,
        ]);

        return view(
            'timetabling.timetable-course-sessions.create',
            compact('timetable', 'sessionGroup', 'courses', 'selected')
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Timetable $timetable, SessionGroup $sessionGroup)
    {
        $validatedData = $request->validate([
            'courses' => 'array',
            'courses.*' => 'exists:courses,id'
        ]);

        $assignedCourseIds = $sessionGroup->courseSessions->pluck('course_id');
        $courses = Course::whereNotIn('id', $assignedCourseIds)->get();

        if(empty($validatedData['courses'])) {
            return view('timetabling.timetable-course-sessions.create', [
                'timetable' => $timetable,
                'sessionGroup' => $sessionGroup,
                'courses' => $courses,
                'message' => 'Must select a course.'
            ]);
        }

        $addedCourses = [];

        foreach ($validatedData['courses'] as $courseId) {
            if (!$sessionGroup->courseSessions()->where('course_id', $courseId)->exists()) {
                $courseSession = $sessionGroup->courseSessions()->create([
                    'course_id' => $courseId,
                ]);

                $course = Course::find($courseId);
                $addedCourses[] = $course->course_title;
            }
        }

        Logger::log('store', 'course sessions', [
            'timetable_id' => $timetable->id,
            'session_group_id' => $sessionGroup->id,
            'added_courses' => $addedCourses,
        ]);

        return redirect()
            ->route('timetables.session-groups.index', $timetable)
            ->with('success', 'CourseSession created successfully!');
    }

    public function updateTerm(Request $request, Timetable $timetable, SessionGroup $sessionGroup, CourseSession $courseSession)
    {
        $validated = $request->validate([
            'academic_term' => 'required|array',
            'academic_term.*' => 'required|in:1st,2nd,semestral',
        ]);

        $courseSession->update([
            'academic_term' => $validated['academic_term'][$courseSession->id],
        ]);

        Logger::log('update_academic_term', 'course sessions', [
            'timetable_id' => $timetable->id,
            'session_group_id' => $sessionGroup->id,
            'course_session_id' => $courseSession->id,
            'academic_term' => $validated['academic_term'][$courseSession->id],
        ]);

        return redirect()->route('timetables.session-groups.index', $timetable)
            ->with('success', 'Academic term updated!');
    }

    public function editTerms(Timetable $timetable, SessionGroup $sessionGroup)
    {
        if ((int) $sessionGroup->timetable_id !== (int) $timetable->id) {
            abort(404);
        }

        $sessionGroup->load(['academicProgram']);

        $courseSessions = CourseSession::query()
            ->where('session_group_id', $sessionGroup->id)
            ->whereHas('sessionGroup', function ($q) use ($timetable) {
                $q->where('timetable_id', $timetable->id);
            })
            ->with(['course'])
            ->get();

        return view('timetabling.timetable-course-sessions.edit-terms', compact(
            'timetable',
            'sessionGroup',
            'courseSessions'
        ));
    }

    public function bulkUpdateTerms(Request $request, Timetable $timetable, SessionGroup $sessionGroup)
    {
        if ((int) $sessionGroup->timetable_id !== (int) $timetable->id) {
            abort(404);
        }

        $validated = $request->validate([
            'academic_term' => ['required', 'array'],
            'academic_term.*' => [
                'nullable',
                'string',
                Rule::in(['', '1st', '2nd', 'semestral']),
            ],
        ]);

        $termMap = $validated['academic_term'] ?? [];
        if (!is_array($termMap) || empty($termMap)) {
            return redirect()
                ->route('timetables.session-groups.edit-terms', [$timetable, $sessionGroup])
                ->with('error', 'No changes submitted.');
        }

        $ids = array_map('intval', array_keys($termMap));

        DB::transaction(function () use ($timetable, $sessionGroup, $termMap, $ids) {
            $sessions = CourseSession::query()
                ->where('session_group_id', $sessionGroup->id)
                ->whereIn('id', $ids)
                ->whereHas('sessionGroup', function ($q) use ($timetable) {
                    $q->where('timetable_id', $timetable->id);
                })
                ->with(['course'])
                ->get();

            foreach ($sessions as $courseSession) {
                $incoming = $termMap[$courseSession->id] ?? null;

                // Normalize empty string to null
                if ($incoming === '') {
                    $incoming = null;
                }

                // If the course is semestral, enforce semestral
                if (($courseSession->course->duration_type ?? null) === 'semestral') {
                    $incoming = 'semestral';
                }

                $courseSession->academic_term = $incoming;
                $courseSession->save();
            }
        });

        return redirect()
            ->route('timetables.session-groups.edit-terms', [$timetable, $sessionGroup])
            ->with('success', 'Academic terms updated successfully.');
    }

    /**
     * Remove the specified course session from storage.
     */
    public function destroy(Timetable $timetable, SessionGroup $sessionGroup, CourseSession $courseSession)
    {
        if ($courseSession->session_group_id !== $sessionGroup->id || $sessionGroup->timetable_id !== $timetable->id) {
            abort(404);
        }

        // Build encoded code exactly like saveFromEditor() expects:
        // "{$programAbbr}_{$yearLevel}_{$sessionGroupId}_{$sessionId}"
        try {
            $programAbbr = $sessionGroup->academicProgram?->program_abbreviation ?? 'UNK';
            $yearLevel   = $sessionGroup->year_level ?? '';
            $sessionGroupId = $sessionGroup->id;
            $sessionId = $courseSession->id;

            $encoded = "{$programAbbr}_{$yearLevel}_{$sessionGroupId}_{$sessionId}";

            $xlsxPath = storage_path("app/exports/timetables/{$timetable->id}.xlsx");

            if (file_exists($xlsxPath)) {
                // Quick writable check (attempt to check dir too)
                if (!is_writable($xlsxPath) && !is_writable(dirname($xlsxPath))) {
                    \Illuminate\Support\Facades\Log::warning('CourseSession destroy: XLSX file or directory not writable — skipping sheet update', [
                        'timetable_id' => $timetable->id,
                        'path' => $xlsxPath,
                    ]);
                } else {
                    // Load spreadsheet and scan all sheets for exact matches to $encoded
                    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($xlsxPath);
                    $sheetCount = $spreadsheet->getSheetCount();
                    $madeChange = false;

                    for ($si = 0; $si < $sheetCount; $si++) {
                        $sheet = $spreadsheet->getSheet($si);
                        // Convert to PHP array (0-based cols)
                        $table = $sheet->toArray(null, true, true, false);
                        if (empty($table) || !isset($table[0]) || !is_array($table[0])) {
                            continue;
                        }

                        $rowCount = count($table);
                        $colCount = count($table[0] ?? []);

                        // Iterate data area rows (skip header row 0). Use the same indexing pattern your other code uses.
                        for ($r = 1; $r < $rowCount; $r++) {
                            for ($c = 1; $c < $colCount; $c++) {
                                $cellVal = trim((string) ($table[$r][$c] ?? ''));
                                if ($cellVal === $encoded) {
                                    // Convert array indices to Excel (1-based)
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
                        // Save back (only if changed)
                        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
                        $writer->save($xlsxPath);

                        \Illuminate\Support\Facades\Log::info('CourseSession destroy: cleared encoded placements in XLSX', [
                            'timetable_id' => $timetable->id,
                            'session_group_id' => $sessionGroupId,
                            'course_session_id' => $sessionId,
                            'encoded' => $encoded,
                            'path' => $xlsxPath,
                        ]);
                    } else {
                        \Illuminate\Support\Facades\Log::info('CourseSession destroy: no matching encoded cells found in XLSX', [
                            'timetable_id' => $timetable->id,
                            'session_group_id' => $sessionGroupId,
                            'course_session_id' => $sessionId,
                            'encoded' => $encoded,
                            'path' => $xlsxPath,
                        ]);
                    }
                }
            } else {
                \Illuminate\Support\Facades\Log::info('CourseSession destroy: timetable XLSX not found — skipping sheet update', [
                    'timetable_id' => $timetable->id,
                    'path' => $xlsxPath,
                ]);
            }
        } catch (\Throwable $e) {
            // Don't block the deletion if sheet update fails; log the error for investigation.
            \Illuminate\Support\Facades\Log::error('Error while clearing CourseSession encoded values in XLSX during destroy()', [
                'timetable_id' => $timetable->id ?? null,
                'session_group_id' => $sessionGroup->id ?? null,
                'course_session_id' => $courseSession->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }


        $courseSession->delete();

        Logger::log('delete', 'course sessions', [
            'timetable_id' => $timetable->id,
            'session_group_id' => $sessionGroup->id,
            'course_session_id' => $courseSession->id
        ]);

        return redirect()
            ->route('timetables.session-groups.index', $timetable)
            ->with('success', 'Course session deleted successfully!');
    }

}
