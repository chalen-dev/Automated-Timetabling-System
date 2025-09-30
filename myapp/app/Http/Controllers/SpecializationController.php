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
        $courses = Course::all();
        return view('records.specializations.create', compact('professor', 'courses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Professor $professor)
    {
        $validatedData = $request->validate([
            'course_ids'   => 'required|array',
            'course_ids.*' => 'exists:courses,id'
        ]);

        // clear old ones
        $professor->specializations()->delete();

        foreach ($validatedData['course_ids'] as $courseId) {
            $professor->specializations()->create([
                'course_id' => $courseId,
            ]);
        }

        return redirect()->route('records.specializations.index', $professor);
    }

    /**
     * Display the specified resource.
     */
    public function show(Professor $professor, Specialization $specialization)
    {
        return view('records.specializations.show', compact('professor', 'specialization'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Professor $professor, Specialization $specialization)
    {
        $courses = Course::all();
        return view('records.specializations.edit', compact('professor', 'specialization', 'courses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Professor $professor, Specialization $specialization)
    {
        $validatedData = $request->validate([
            'course_ids'   => 'required|array',
            'course_ids.*' => 'exists:courses,id',
        ]);

        // Clear old ones
        $professor->specializations()->delete();

        // Insert new ones
        foreach ($validatedData['course_ids'] as $courseId) {
            $professor->specializations()->create([
                'course_id' => $courseId,
            ]);
        }

        return redirect()->route('records.specializations.index', $professor)
            ->with('success', 'Specializations updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Professor $professor, Specialization $specialization)
    {
        $specialization->delete();

        return redirect()->route('records.specializations.index', $professor)
            ->with('success', 'Specialization deleted successfully.');
    }
}
