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
            $needle = '_' . $courseSessionId; // course_session_id is ALWAYS the last segment
            $changed = false;

            foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
                $highestRow = $sheet->getHighestRow();
                $highestCol = Coordinate::columnIndexFromString($sheet->getHighestColumn());

                // Skip header row (1) and time column (A)
                for ($row = 2; $row <= $highestRow; $row++) {
                    for ($col = 2; $col <= $highestCol; $col++) {

                        $cellAddress = Coordinate::stringFromColumnIndex($col) . $row;
                        $cell = $sheet->getCell($cellAddress);
                        $value = trim((string) $cell->getValue());

                        if ($value !== '' && str_ends_with($value, $needle)) {
                            $cell->setValue('vacant');
                            $changed = true;
                        }
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | 3. SAVE XLSX BACK (same source)
            |--------------------------------------------------------------------------
            */
            if ($changed) {
                IOFactory::createWriter($spreadsheet, 'Xlsx')->save($tempPath);

                if ($writeBackToBucket) {
                    $disk->put($remotePath, fopen($tempPath, 'r'));
                    unlink($tempPath);
                }
            }

        } catch (Throwable $e) {
            // XLSX failed → DO NOT DELETE DB
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




}
