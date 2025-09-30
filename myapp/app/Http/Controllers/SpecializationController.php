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
        $professor->load('courses');
        return view('specializations.index', compact('professor', 'courses'));
    }

    public function create(Professor $professor)
    {
        $courses = Course::all();
        return view('specializations.create', compact('professor', 'courses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Professor $professor)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
        ]);

        $professor->courses()->syncWithoutDetaching($request->course_id);

        return redirect()->route('records.professors.specializations.edit', $professor)
            ->with('success', 'Course added.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Professor $professor, Specialization $specialization)
    {
        $specialization->delete();
        return redirect()->route('records.professors.specializations.edit', $professor)
            ->with('success', 'Course removed.');
    }
}
