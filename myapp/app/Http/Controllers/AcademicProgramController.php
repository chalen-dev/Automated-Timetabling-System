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
        $academicPrograms = AcademicProgram::all();
        return view('records.academic-programs.index', compact('academicPrograms'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('records.academic-programs.create');
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
    public function show(AcademicProgram $academicProgram)
    {
        return view('records.academic-programs.show', compact('academicProgram'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AcademicProgram $academicProgram)
    {
        return view('records.academic-programs.edit', compact('academicProgram'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AcademicProgram $academicProgram)
    {
        $validatedData = $request -> validate([
            'program_name' => 'required|string',
            'program_abbreviation' => 'required|string',
            'program_description' => 'nullable|string',
        ]);

        $academicProgram->update($validatedData);
        return redirect()->route('academic-programs.index')
            ->with('success', 'Academic Program updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AcademicProgram $academicProgram)
    {
        $academicProgram->delete();
        return redirect()->route('academic-programs.index')
            ->with('success', 'Academic Program deleted successfully');
    }
}
