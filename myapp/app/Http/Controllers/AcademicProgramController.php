<?php

namespace App\Http\Controllers;

use App\Models\AcademicProgram;
use Illuminate\Http\Request;

class AcademicProgramController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $academic_programs = AcademicProgram::all();
        return view('academic-programs.index', compact('academic_programs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('academic-programs.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'program_name' => 'required|string',
            'program_abbreviation' => 'required|string',
            'program_description' => 'nullable|string',
        ]);

        AcademicProgram::create($validatedData);
        return redirect()->route('academic-programs.index')
            ->with('success', 'Academic Program created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(AcademicProgram $academic_program)
    {
        return view('academic-program.show', compact('academic_program'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AcademicProgram $academicProgram)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AcademicProgram $academicProgram)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AcademicProgram $academicProgram)
    {
        //
    }
}
