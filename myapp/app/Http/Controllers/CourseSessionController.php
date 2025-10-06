<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseSession;
use App\Models\SessionGroup;
use App\Models\Timetable;
use Illuminate\Http\Request;

class CourseSessionController extends Controller
{

    /**
     * Show the form for creating a new resource.
     */
    public function create(Timetable $timetable, SessionGroup $sessionGroup, Request $request)
    {
        // Ensure the session group belongs to this timetable
        if ($sessionGroup->timetable_id !== $timetable->id) {
            abort(404);
        }

        // Get IDs of courses already assigned to this SessionGroup
        $assignedCourseIds = $sessionGroup->courseSessions->pluck('course_id');

        // Start query for courses not yet assigned
        $query = Course::whereNotIn('id', $assignedCourseIds);

        // Apply search filter
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

        // Keep track of selected checkboxes from query string
        $selected = $request->input('courses', []);

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

        // Loop through each selected course
        foreach ($validatedData['courses'] as $courseId) {
            // Optional safety check to avoid duplicates
            if (!$sessionGroup->courseSessions()->where('course_id', $courseId)->exists()) {
                $sessionGroup->courseSessions()->create([
                    'course_id' => $courseId,
                    // add other fields here if your CourseSession has more (e.g., start_time, end_time)
                ]);
            }
        }

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

        return redirect()->route('timetables.session-groups.index', $timetable)
            ->with('success', 'Academic term updated!');
    }



    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CourseSession $courseSession)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CourseSession $courseSession)
    {
        //
    }

    /**
     * Remove the specified course session from storage.
     */
    public function destroy(Timetable $timetable, SessionGroup $sessionGroup, CourseSession $courseSession)
    {
        // Optional safety check
        if ($courseSession->session_group_id !== $sessionGroup->id || $sessionGroup->timetable_id !== $timetable->id) {
            abort(404);
        }

        $courseSession->delete();

        return redirect()
            ->route('timetables.session-groups.index', $timetable)
            ->with('success', 'Course session deleted successfully!');
    }
}
