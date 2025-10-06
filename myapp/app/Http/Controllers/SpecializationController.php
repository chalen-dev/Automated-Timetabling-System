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
        //Get all specializations tied for the professor
        $specializations = $professor->specializations()->with('course')->get();
        return view('records.specializations.index', compact('specializations', 'professor', 'courses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request, Professor $professor)
    {
        $assignedCourseIds = $professor->specializations()->pluck('course_id')->toArray();

        $query = Course::whereNotIn('id', $assignedCourseIds);

        // Apply search filter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('course_name', 'like', "%{$search}%")
                    ->orWhere('course_title', 'like', "%{$search}%");
            });
        }

        $courses = $query->get();

        return view('records.specializations.create', compact('professor', 'courses'));
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

        // No selection
        if (empty($validatedData['courses'])) {
            return view('records.specializations.create', [
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

        return redirect()->route('professors.specializations.index', $professor);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Professor $professor, Specialization $specialization)
    {
        $specialization->delete();

        return redirect()->route('professors.specializations.index', $professor)
            ->with('success', 'Specialization deleted successfully.');
    }
}
