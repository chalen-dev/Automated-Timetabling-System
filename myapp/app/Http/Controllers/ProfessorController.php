<?php

namespace App\Http\Controllers;

use App\Models\AcademicProgram;
use App\Models\Course;
use App\Models\Professor;
use Illuminate\Http\Request;

class ProfessorController extends Controller
{
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

    public function index(Request $request)
    {
        $search = $request->input('search');

        $professors = Professor::with('specializations.course')
            ->when($search, function($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhereHas('specializations.course', function($q2) use ($search) {
                            $q2->where('course_title', 'like', "%{$search}%");
                        });
                });
            })
            ->get();

        // Log the view
        $this->logAction('viewed_professors_list', ['search' => $search]);

        return view('records.professors.index', compact('professors', 'search'));
    }

    public function create()
    {
        $genderOptions = $this->genderOptions;
        $professorTypeOptions = $this->professorTypeOptions;
        $academic_program_options = AcademicProgram::all()->pluck('program_abbreviation', 'id')->toArray();

        // Log access to create form
        $this->logAction('accessed_create_professor_form');

        return view('records.professors.create', compact('academic_program_options', 'genderOptions', 'professorTypeOptions'));
    }

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

        $professor = Professor::create($validatedData);

        // Log creation
        $this->logAction('create_professor', [
            'professor_id' => $professor->id,
            'professor_name' => $professor->first_name . ' ' . $professor->last_name
        ]);

        return redirect()->route('professors.index')
            ->with('success', 'Professor created successfully.');
    }

    public function show(Professor $professor)
    {
        $academic_program_options = AcademicProgram::all()->pluck('program_abbreviation', 'id')->toArray();

        // Log view
        $this->logAction('viewed_professor', [
            'professor_id' => $professor->id,
            'professor_name' => $professor->first_name . ' ' . $professor->last_name
        ]);

        return view('records.professors.show', compact('professor', 'academic_program_options'));
    }

    public function edit(Professor $professor)
    {
        $courses = Course::all();
        $academic_program_options = AcademicProgram::all()->pluck('program_abbreviation', 'id')->toArray();
        $genderOptions = $this->genderOptions;
        $professorTypeOptions = $this->professorTypeOptions;

        // Log access to edit form
        $this->logAction('accessed_edit_professor_form', [
            'professor_id' => $professor->id,
            'professor_name' => $professor->first_name . ' ' . $professor->last_name
        ]);

        return view('records.professors.edit', compact('professor', 'academic_program_options', 'genderOptions', 'professorTypeOptions', 'courses'));
    }

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

        // Log update
        $this->logAction('update_professor', [
            'professor_id' => $professor->id,
            'professor_name' => $professor->first_name . ' ' . $professor->last_name
        ]);

        return redirect()->route('professors.index')
            ->with('success', 'Professor updated successfully.');
    }

    public function destroy(Professor $professor)
    {
        $professorData = [
            'professor_id' => $professor->id,
            'professor_name' => $professor->first_name . ' ' . $professor->last_name
        ];

        $professor->delete();

        // Log deletion
        $this->logAction('delete_professor', $professorData);

        return redirect()->route('professors.index')
            ->with('success', 'Professor deleted successfully.');
    }

    /**
     * Logs user actions to user_logs table
     */
    protected function logAction(string $action, array $details = [])
    {
        if(auth()->check()) {
            \App\Models\UserLog::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'description' => json_encode($details),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }
    }
}
