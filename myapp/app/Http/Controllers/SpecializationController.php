<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Professor;
use App\Models\Specialization;
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
        return view('records.professors.specializations.index', compact('specializations', 'professor', 'courses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Professor $professor)
    {
        // get all course IDs already assigned to the professor
        $assignedCourseIds = $professor->specializations()->pluck('course_id')->toArray();

        // only get unassigned courses
        $courses = Course::whereNotIn('id', $assignedCourseIds)->get();

        return view('records.professors.specializations.create', compact('professor', 'courses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Professor $professor)
    {
        $validatedData = $request->validate([
            'courses'   => 'array',
            'courses.*' => 'exists:courses,id'
        ]);

        // No selection
        if (empty($validatedData['courses'])) {
            return view('records.professors.specializations.create', [
                'professor' => $professor,
                'courses'   => Course::all(),
                'message'   => 'No courses were selected for this professor.'
            ]);
        }

        foreach ($validatedData['courses'] as $courseId) {
            $professor->specializations()->create([
                'course_id' => $courseId,
            ]);
        }

        return redirect()->route('records.professors.specializations.index', $professor);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Professor $professor, Specialization $specialization)
    {
        $specialization->delete();

        return redirect()->route('records.professors.specializations.index', $professor)
            ->with('success', 'Specialization deleted successfully.');
    }
}
