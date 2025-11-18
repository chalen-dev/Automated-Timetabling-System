<?php

namespace App\Http\Controllers\Records;

use App\Helpers\Logger;
use App\Http\Controllers\Controller;
use App\Models\Records\AcademicProgram;
use Illuminate\Http\Request;

class AcademicProgramController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $academicPrograms = AcademicProgram::query()
            ->when($search, function ($query, $search) {
                $query->where('program_name', 'like', "%{$search}%")
                    ->orWhere('program_abbreviation', 'like', "%{$search}%");
            })
            ->get();

        Logger::log('index', 'academic program', null);

        return view('records.academic-programs.index', compact('academicPrograms'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Logger::log('create', 'academic program', null);
        return view('records.academic-programs.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'program_name' => 'required|string',
            'program_abbreviation' => 'required|string|unique:academic_programs',
            'program_description' => 'nullable|string',
        ]);

        $academicProgram = AcademicProgram::create($validatedData);

        Logger::log('store', 'academic program', [
            'program_name' => $academicProgram->program_name,
            'program_abbreviation' => $academicProgram->program_abbreviation,
            'program_description' => $academicProgram->program_description,
        ]);

        return redirect()->route('academic-programs.index')
            ->with('success', 'Academic Program created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(AcademicProgram $academicProgram)
    {
        Logger::log('show', 'academic program', $academicProgram);
        return view('records.academic-programs.show', compact('academicProgram'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AcademicProgram $academicProgram)
    {
        Logger::log('edit', 'academic program', $academicProgram);
        return view('records.academic-programs.edit', compact('academicProgram'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AcademicProgram $academicProgram)
    {
        $validatedData = $request -> validate([
            'program_name' => 'required|string',
            'program_abbreviation' => 'required|unique:academic_programs,program_abbreviation,' . $academicProgram->id,
            'program_description' => 'nullable|string',
        ]);

        $academicProgram->update($validatedData);

        Logger::log('update', 'academic program', $validatedData);

        return redirect()->route('academic-programs.index')
            ->with('success', 'Academic Program updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AcademicProgram $academicProgram)
    {
        $programData = [
            'program_id' => $academicProgram->id,
            'program_name' => $academicProgram->program_name
        ];

        $academicProgram->delete();

        Logger::log('delete', 'academic program', $programData);

        return redirect()->route('academic-programs.index')
            ->with('success', 'Academic Program deleted successfully');
    }

    /**
     * Log user actions.
     */

}
