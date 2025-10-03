<?php

namespace App\Http\Controllers;

use App\Models\AcademicProgram;
use App\Models\Course;
use App\Models\Professor;
use Illuminate\Http\Request;

class ProfessorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    private $genderOptions = [
        'male' => 'Male',
        'female' => 'Female',
        'none' => 'Not Specified'
    ];

    private $professorTypeOptions = [
        'regular' => 'Regular',
        'non-regular' => 'Non-Regular',
        'none' => 'None'
    ];

    public function index()
    {
        $professors = Professor::with('specializations.course')->get();
        return view('records.professors.index', compact('professors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $genderOptions = $this->genderOptions;
        $professorTypeOptions = $this->professorTypeOptions;
        $academic_program_options = AcademicProgram::all()->pluck('program_abbreviation', 'id')->toArray();
        return view('records.professors.create', compact('academic_program_options', 'genderOptions', 'professorTypeOptions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request -> validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'professor_type' => 'required|string',
            'max_unit_load' => 'required|numeric|min:1.0',
            'gender' => 'required|string',
            'professor_age' => 'nullable|numeric',
            'position' => 'nullable|string',
            'academic_program_id' => 'required|exists:academic_programs,id',
        ]);



        Professor::create($validatedData);
        return redirect()->route('professors.index')
            ->with('success', 'Professor created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Professor $professor)
    {
        $academic_program_options = AcademicProgram::all()->pluck('program_abbreviation', 'id')->toArray();
        return view('records.professors.show', compact('professor', 'academic_program_options'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Professor $professor)
    {
        $courses = Course::all();
        $academic_program_options = AcademicProgram::all()->pluck('program_abbreviation', 'id')->toArray();
        $genderOptions = $this->genderOptions;
        $professorTypeOptions = $this->professorTypeOptions;
        return view('records.professors.edit', compact('professor', 'academic_program_options', 'genderOptions', 'professorTypeOptions', 'courses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Professor $professor)
    {
        $validatedData = $request -> validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'professor_type' => 'required|string',
            'max_unit_load' => 'required|numeric|min:1.0',
            'gender' => 'required|string',
            'professor_age' => 'nullable|numeric',
            'position' => 'nullable|string',
            'academic_program_id' => 'required|exists:academic_programs,id',
        ]);
        $professor->update($validatedData);
        return redirect()->route('professors.index')
            ->with('success', 'Professor updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Professor $professor)
    {
        $professor->delete();
        return redirect()->route('professors.index')
            ->with('success', 'Professor deleted successfully.');
    }
}
