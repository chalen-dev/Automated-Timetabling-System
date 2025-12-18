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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

class CourseSessionController extends Controller
{
    protected function clearCourseSessionFromTimetableXlsx(Timetable $timetable, int $courseSessionId): void {
        $disk = Storage::disk('facultime');
        $remotePath = "timetables/{$timetable->id}.xlsx";

        // Load XLSX (bucket first, local fallback)
        if ($disk->exists($remotePath)) {
            $tempPath = tempnam(sys_get_temp_dir(), 'tt_') . '.xlsx';
            file_put_contents($tempPath, $disk->get($remotePath));
            $writeBackToBucket = true;
        } else {
            $tempPath = storage_path("app/exports/timetables/{$timetable->id}.xlsx");
            $writeBackToBucket = false;

            if (!file_exists($tempPath)) {
                return;
            }
        }

        $spreadsheet = IOFactory::load($tempPath);
        $needle = '_' . $courseSessionId;
        $changed = false;

        foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
            $sheetName = $sheet->getTitle();

            /*
            |----------------------------------------------------------
            | A. OVERVIEW + UNASSIGNED → DELETE ROWS
            |----------------------------------------------------------
            */
            if (in_array($sheetName, ['Overview_1st', 'Overview_2nd', 'Unassigned'])) {

                $highestRow = $sheet->getHighestRow();
                $rowsToDelete = [];

                // Column A = course_session_id
                for ($row = 2; $row <= $highestRow; $row++) {
                    $idCell = trim((string) $sheet->getCell("A{$row}")->getValue());

                    if ((string) $idCell === (string) $courseSessionId) {
                        $rowsToDelete[] = $row;
                    }
                }

                rsort($rowsToDelete);

                foreach ($rowsToDelete as $row) {
                    $sheet->removeRow($row, 1);
                    $changed = true;
                }

                continue;
            }

            /*
            |----------------------------------------------------------
            | B. TIMETABLE SHEETS → CLEAR CELLS
            |----------------------------------------------------------
            */
            $highestRow = $sheet->getHighestRow();
            $highestCol = Coordinate::columnIndexFromString($sheet->getHighestColumn());

            for ($row = 2; $row <= $highestRow; $row++) {
                for ($col = 2; $col <= $highestCol; $col++) {

                    $cellAddress = Coordinate::stringFromColumnIndex($col) . $row;
                    $value = trim((string) $sheet->getCell($cellAddress)->getValue());

                    if ($value !== '' && str_ends_with($value, $needle)) {
                        $sheet->setCellValue($cellAddress, 'vacant');
                        $changed = true;
                    }
                }
            }
        }

        if ($changed) {
            IOFactory::createWriter($spreadsheet, 'Xlsx')->save($tempPath);

            if ($writeBackToBucket) {
                $disk->put($remotePath, fopen($tempPath, 'r'));
                unlink($tempPath);
            }
        }
    }


    /**
     * Display a listing of the course sessions for a single session group.
     */
    public function index(Timetable $timetable, SessionGroup $sessionGroup, Request $request)
    {
        $this->authorize('editRecords', $timetable);

        // Safety: ensure session group belongs to timetable
        if ((int) $sessionGroup->timetable_id !== (int) $timetable->id) {
            abort(404);
        }

        // Load course sessions with course relation
        $courseSessions = CourseSession::query()
            ->where('session_group_id', $sessionGroup->id)
            ->with('course')
            ->orderBy('id') // change ordering as needed
            ->get();

        // Optionally allow simple searching (by course_title / course_name)
        if ($search = $request->input('search')) {
            $courseSessions = $courseSessions->filter(function($cs) use ($search) {
                $title = $cs->course->course_title ?? '';
                $name  = $cs->course->course_name ?? '';
                return stripos($title, $search) !== false || stripos($name, $search) !== false;
            })->values();
        }

        return view(
            'timetabling.timetable-course-sessions.index',
            compact('timetable', 'sessionGroup', 'courseSessions')
        );
    }


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
            return redirect()
                ->route(
                    'timetables.session-groups.course-sessions.create',
                    [$timetable, $sessionGroup]
                )
                ->with('error', 'Must select at least one course.');
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

        $newTerm = $validated['academic_term'][$courseSession->id];

        // Clear timetable placements (XLSX only)
        $this->clearCourseSessionFromTimetableXlsx($timetable, $courseSession->id);

        // Update DB term
        $courseSession->update([
            'academic_term' => $newTerm,
        ]);

        Logger::log('update_academic_term', 'course sessions', [
            'timetable_id' => $timetable->id,
            'session_group_id' => $sessionGroup->id,
            'course_session_id' => $courseSession->id,
            'academic_term' => $newTerm,
        ]);

        return redirect()
            ->route('timetables.session-groups.index', $timetable)
            ->with('success', 'Academic term updated and timetable placement cleared.');
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
                ->route('timetables.session-groups.index', [$timetable, $sessionGroup])
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
            ->route('timetables.session-groups.index', [$timetable, $sessionGroup])
            ->with('success', 'Academic terms updated successfully.');
    }

    public function destroy(Timetable $timetable, SessionGroup $sessionGroup, CourseSession $courseSession)
    {
        // Safety check
        if (
            $courseSession->session_group_id !== $sessionGroup->id ||
            $sessionGroup->timetable_id !== $timetable->id
        ) {
            abort(404);
        }

        $courseSessionId = $courseSession->id;

        /*
        |--------------------------------------------------------------------------
        | 1. LOAD XLSX (bucket first, local fallback) — SAME AS WORKING CODE
        |--------------------------------------------------------------------------
        */
        $disk = Storage::disk('facultime');
        $remotePath = "timetables/{$timetable->id}.xlsx";

        if ($disk->exists($remotePath)) {
            $tempPath = tempnam(sys_get_temp_dir(), 'tt_') . '.xlsx';
            file_put_contents($tempPath, $disk->get($remotePath));

            if (filesize($tempPath) < 1024) {
                throw new \Exception('Downloaded XLSX is invalid');
            }

            $writeBackToBucket = true;
        } else {
            $tempPath = storage_path("app/exports/timetables/{$timetable->id}.xlsx");
            $writeBackToBucket = false;

            if (!file_exists($tempPath)) {
                return redirect()
                    ->route('timetables.session-groups.index', $timetable)
                    ->with('success', 'Course session deleted (no XLSX found).');
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 2. CLEAR ONLY THIS COURSE SESSION (ALL SHEETS)
        |--------------------------------------------------------------------------
        */
        try {
            $spreadsheet = IOFactory::load($tempPath);
            $needle = '_' . $courseSessionId; // always last segment
            $changed = false;

            foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
                $sheetName = $sheet->getTitle();

                /*
                |----------------------------------------------------------
                | A. OVERVIEW + UNASSIGNED → DELETE ROWS BY course_session_id
                |----------------------------------------------------------
                */
                if (in_array($sheetName, ['Overview_1st', 'Overview_2nd', 'Unassigned'])) {

                    $highestRow = $sheet->getHighestRow();
                    $rowsToDelete = [];

                    // Column A = course_session_id
                    for ($row = 2; $row <= $highestRow; $row++) {
                        $idCell = trim((string) $sheet->getCell("A{$row}")->getValue());

                        if ((string) $idCell === (string) $courseSessionId) {
                            $rowsToDelete[] = $row;
                        }
                    }

                    // Delete bottom-up to avoid shifting
                    rsort($rowsToDelete);

                    foreach ($rowsToDelete as $row) {
                        $sheet->removeRow($row, 1);
                        $changed = true;
                    }

                    continue;
                }

                /*
                |----------------------------------------------------------
                | B. TIMETABLE SHEETS → CLEAR CELLS (EXISTING BEHAVIOR)
                |----------------------------------------------------------
                */
                $highestRow = $sheet->getHighestRow();
                $highestCol = Coordinate::columnIndexFromString($sheet->getHighestColumn());

                for ($row = 2; $row <= $highestRow; $row++) {
                    for ($col = 2; $col <= $highestCol; $col++) {

                        $cellAddress = Coordinate::stringFromColumnIndex($col) . $row;
                        $value = trim((string) $sheet->getCell($cellAddress)->getValue());

                        if ($value !== '' && str_ends_with($value, $needle)) {
                            $sheet->setCellValue($cellAddress, 'vacant');
                            $changed = true;
                        }
                    }
                }
            }

            /*
            |----------------------------------------------------------
            | SAVE XLSX BACK
            |----------------------------------------------------------
            */
            if ($changed) {
                IOFactory::createWriter($spreadsheet, 'Xlsx')->save($tempPath);

                if ($writeBackToBucket) {
                    $disk->put($remotePath, fopen($tempPath, 'r'));
                    unlink($tempPath);
                }
            }

        } catch (Throwable $e) {
            return redirect()
                ->route('timetables.session-groups.index', $timetable)
                ->with('error', 'Failed to update timetable file. Course session was NOT deleted.');
        }


        /*
        |--------------------------------------------------------------------------
        | 4. DELETE DB RECORD LAST (CANONICAL)
        |--------------------------------------------------------------------------
        */
        $courseSession->delete();

        Logger::log('delete', 'course sessions', [
            'timetable_id'      => $timetable->id,
            'session_group_id'  => $sessionGroup->id,
            'course_session_id'=> $courseSessionId,
        ]);

        return redirect()
            ->route('timetables.session-groups.index', $timetable)
            ->with('success', 'Course session deleted and timetable cleared.');
    }

    public function delete(Timetable $timetable, SessionGroup $sessionGroup)
    {
        if ((int) $sessionGroup->timetable_id !== (int) $timetable->id) {
            abort(404);
        }

        $courseSessions = CourseSession::query()
            ->where('session_group_id', $sessionGroup->id)
            ->with('course')
            ->orderBy('id')
            ->get();

        return view(
            'timetabling.timetable-course-sessions.delete',
            compact('timetable', 'sessionGroup', 'courseSessions')
        );
    }

    public function bulkDestroy(Request $request, Timetable $timetable, SessionGroup $sessionGroup)
    {
        // Safety check
        if ((int) $sessionGroup->timetable_id !== (int) $timetable->id) {
            abort(404);
        }

        $validated = $request->validate([
            'course_sessions'   => ['required', 'array'],
            'course_sessions.*' => ['integer', 'exists:course_sessions,id'],
        ]);

        $ids = array_map('intval', $validated['course_sessions']);

        try {
            DB::transaction(function () use ($ids, $timetable, $sessionGroup) {

                $sessions = CourseSession::query()
                    ->where('session_group_id', $sessionGroup->id)
                    ->whereIn('id', $ids)
                    ->get();

                foreach ($sessions as $courseSession) {

                    // 1️⃣ Clear occurrences from XLSX (same logic as destroy)
                    $this->clearCourseSessionFromTimetableXlsx(
                        $timetable,
                        $courseSession->id
                    );

                    // 2️⃣ Delete DB record LAST
                    $courseSession->delete();
                }
            });

        } catch (\Throwable $e) {
            // XLSX or DB failure → nothing partially committed
            return redirect()
                ->route('timetables.session-groups.course-sessions.delete', [$timetable, $sessionGroup])
                ->with('error', 'Bulk delete failed. No course sessions were deleted.');
        }

        Logger::log('bulk_delete', 'course sessions', [
            'timetable_id'     => $timetable->id,
            'session_group_id' => $sessionGroup->id,
            'deleted_ids'      => $ids,
        ]);

        return redirect()
            ->route('timetables.session-groups.course-sessions.index', [$timetable, $sessionGroup])
            ->with('success', count($ids) . ' course session(s) deleted and cleared from timetable.');
    }






}
