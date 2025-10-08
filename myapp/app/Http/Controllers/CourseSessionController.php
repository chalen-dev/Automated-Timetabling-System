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

        $this->logAction('accessed_create_course_session_form', [
            'timetable_id' => $timetable->id,
            'session_group_id' => $sessionGroup->id
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

        foreach ($validatedData['courses'] as $courseId) {
            if (!$sessionGroup->courseSessions()->where('course_id', $courseId)->exists()) {
                $courseSession = $sessionGroup->courseSessions()->create([
                    'course_id' => $courseId,
                ]);

                $this->logAction('create_course_session', [
                    'timetable_id' => $timetable->id,
                    'session_group_id' => $sessionGroup->id,
                    'course_id' => $courseId,
                    'course_session_id' => $courseSession->id
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

        $this->logAction('update_course_session_term', [
            'timetable_id' => $timetable->id,
            'session_group_id' => $sessionGroup->id,
            'course_session_id' => $courseSession->id,
            'academic_term' => $validated['academic_term'][$courseSession->id]
        ]);

        return redirect()->route('timetables.session-groups.index', $timetable)
            ->with('success', 'Academic term updated!');
    }

    /**
     * Remove the specified course session from storage.
     */
    public function destroy(Timetable $timetable, SessionGroup $sessionGroup, CourseSession $courseSession)
    {
        if ($courseSession->session_group_id !== $sessionGroup->id || $sessionGroup->timetable_id !== $timetable->id) {
            abort(404);
        }

        $courseSession->delete();

        $this->logAction('delete_course_session', [
            'timetable_id' => $timetable->id,
            'session_group_id' => $sessionGroup->id,
            'course_session_id' => $courseSession->id
        ]);

        return redirect()
            ->route('timetables.session-groups.index', $timetable)
            ->with('success', 'Course session deleted successfully!');
    }

    /**
     * Log user actions.
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
