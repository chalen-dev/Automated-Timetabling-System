<?php

namespace App\Http\Controllers\Records;

use App\Helpers\Logger;
use App\Http\Controllers\Controller;
use App\Models\Records\Course;
use App\Models\Records\Professor;
use App\Models\Records\Specialization;
use App\Models\Users\UserLog;
use Illuminate\Http\Request;

class SpecializationController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Professor $professor)
    {
        $courses = Course::all();
        $specializations = $professor->specializations()->with('course')->get();

       Logger::log('index', 'specializations', [
           'professor_id' => $professor->id,
           'professor_name' => $professor->full_name,
       ]);

        return view('records.specializations.index', compact('specializations', 'professor', 'courses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request, Professor $professor)
    {
        $assignedCourseIds = $professor->specializations()->pluck('course_id')->toArray();

        $query = Course::whereNotIn('id', $assignedCourseIds);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('course_name', 'like', "%{$search}%")
                    ->orWhere('course_title', 'like', "%{$search}%");
            });
        }

        $courses = $query->get();

        Logger::log('create', 'specializations', [
            'professor_id' => $professor->id,
            'professor_name' => $professor->full_name,
        ]);

        $coursesCount = $courses->count();

        return view('records.specializations.create', compact('professor', 'courses', 'coursesCount'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Professor $professor)
    {
        $validatedData = $request->validate([
            'courses'   => 'required|array',
            'courses.*' => 'exists:courses,id'
        ]);

        if (empty($validatedData['courses'])) {
            return view('records.specializations.create', [
                'professor' => $professor,
                'courses'   => Course::all(),
                'message'   => 'No courses were selected for this professor.'
            ]);
        }

        $addedCourses = [];

        foreach ($validatedData['courses'] as $courseId) {
            $specialization = $professor->specializations()->create([
                'course_id' => $courseId,
            ]);

            //Add course titles to be logged
            $course = Course::find($courseId);
            $addedCourses[] = $course->course_title ?? 'Unknown';
        }

        // Log each course added
        Logger::log('store', 'specializations', [
            'professor_id' => $professor->id,
            'professor_name' => $professor->full_name,
            'added_courses' => $addedCourses,
        ]);

        return redirect()->route('professors.specializations.index', $professor)
            ->with('success', 'Specializations added successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Professor $professor, Specialization $specialization)
    {
        $specializationData = [
            'professor_id' => $professor->id,
            'professor_name' => $professor->full_name,
            'course_id' => $specialization->course_id,
            'specialization_id' => $specialization->id
        ];

        $courseTitle = $specialization->course->course_name ?? 'Unknown';


        $specialization->delete();

        Logger::log('delete', 'specializations', [
            'professor_id' => $professor->id,
            'professor_name' => $professor->full_name,
            'course_title' => $courseTitle,
        ]);

        return redirect()->route('professors.specializations.index', $professor)
            ->with('success', 'Specialization deleted successfully.');
    }
}
