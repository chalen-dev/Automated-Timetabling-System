<?php

namespace App\Http\Controllers\records;

use App\Http\Controllers\Controller;
use App\Models\AcademicProgram;
use App\Models\UserLog;
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

        // Log the view action
        $this->logAction('viewed academic programs list', ['search' => $search]);

        return view('records.academic-programs.index', compact('academicPrograms'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->logAction('accessed academic program creation form');

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

        $this->logAction('created academic program', [
            'program_id' => $academicProgram->id,
            'program_name' => $academicProgram->program_name
        ]);

        return redirect()->route('academic-programs.index')
            ->with('success', 'Academic Program created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(AcademicProgram $academicProgram)
    {
        $this->logAction('viewed academic program', [
            'program_id' => $academicProgram->id,
            'program_name' => $academicProgram->program_name
        ]);

        return view('records.academic-programs.show', compact('academicProgram'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AcademicProgram $academicProgram)
    {
        $this->logAction('accessed academic program edit form', [
            'program_id' => $academicProgram->id,
            'program_name' => $academicProgram->program_name
        ]);

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

        $this->logAction('updated academic program', [
            'program_id' => $academicProgram->id,
            'program_name' => $academicProgram->program_name
        ]);

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

        $this->logAction('deleted academic program', $programData);

        return redirect()->route('academic-programs.index')
            ->with('success', 'Academic Program deleted successfully');
    }

    /**
     * Log user actions.
     */
    protected function logAction(string $action, array $details = [])
    {
        if(auth()->check()) {
            UserLog::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'description' => json_encode($details),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }
    }
}
