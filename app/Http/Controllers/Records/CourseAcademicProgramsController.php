<?php

namespace App\Http\Controllers\Records;

use App\Helpers\Logger;
use App\Http\Controllers\Controller;
use App\Models\Records\AcademicProgram;
use App\Models\Records\Course;
use App\Models\Records\CourseAcademicPrograms;
use Illuminate\Http\Request;

class CourseAcademicProgramsController extends Controller
{
    public function index(Course $course)
    {
        $assignedPrograms = CourseAcademicPrograms::with('academicProgram')
            ->where('course_id', $course->id)
            ->get();

        Logger::log('index', 'course programs', [
            'course_id' => $course->id,
        ]);

        return view('records.course-academic-programs.index', [
            'course' => $course,
            'assignedPrograms' => $assignedPrograms,
        ]);
    }

    public function create(Course $course)
    {
        $assignedProgramIds = CourseAcademicPrograms::where('course_id', $course->id)
            ->pluck('academic_program_id')
            ->toArray();

        $unassignedPrograms = AcademicProgram::query()
            ->when(!empty($assignedProgramIds), function ($q) use ($assignedProgramIds) {
                $q->whereNotIn('id', $assignedProgramIds);
            })
            ->orderBy('program_name')
            ->get();

        Logger::log('create', 'course programs', [
            'course_id' => $course->id,
        ]);

        return view('records.course-academic-programs.create', compact('course', 'unassignedPrograms'));
    }

    public function store(Request $request, Course $course)
    {
        $validated = $request->validate([
            'academic_program_ids'   => 'required|array',
            'academic_program_ids.*' => 'exists:academic_programs,id',
        ]);

        $course->academicPrograms()->syncWithoutDetaching($validated['academic_program_ids']);

        Logger::log('store', 'course programs', [
            'course_id' => $course->id,
            'added_program_ids' => $validated['academic_program_ids'],
        ]);

        return redirect()
            ->route('courses.course-academic-programs.index', $course)
            ->with('success', 'Academic programs assigned successfully.');
    }

    public function destroy(Course $course, CourseAcademicPrograms $courseAcademicProgram)
    {
        if ($courseAcademicProgram->course_id !== $course->id) {
            abort(404);
        }

        $courseAcademicProgram->delete();

        Logger::log('delete', 'course programs', [
            'course_id' => $course->id,
            'academic_program_id' => $courseAcademicProgram->academic_program_id,
        ]);

        return redirect()
            ->route('courses.course-academic-programs.index', $course)
            ->with('success', 'Academic program removed successfully.');
    }
}
