<?php

namespace App\Http\Controllers\Records;

use App\Helpers\Logger;
use App\Http\Controllers\Controller;
use App\Models\Records\AcademicProgram;
use App\Models\Records\Course;
use App\Models\Records\Professor;
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

        // Count of academic programs
        $academicProgramsCount = AcademicProgram::count();

        // Log
        Logger::log('index', 'professor', null);

        return view('records.professors.index', compact('professors', 'search', 'academicProgramsCount'));
    }


    public function create()
    {
        $genderOptions = $this->genderOptions;
        $professorTypeOptions = $this->professorTypeOptions;
        $academic_program_options = AcademicProgram::all()->pluck('program_abbreviation', 'id')->toArray();

        // Log access to create form
        Logger::log('create', 'professor', null);

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
        Logger::log('store', 'professor', [
            'professor_id' => $professor->id,
            'professor_name' => $professor->first_name . ' ' . $professor->last_name,
            'professor_type' => $professor->professor_type,
            'max_unit_load' => $professor->max_unit_load,
            'gender' => $professor->gender,
            'professor_age' => $professor->professor_age,
            'position' => $professor->position,
            'academic_program_id' => $professor->academic_program_id
        ]);

        return redirect()->route('professors.index')
            ->with('success', 'Professor created successfully.');
    }

    public function show(Professor $professor)
    {
        $academic_program_options = AcademicProgram::all()->pluck('program_abbreviation', 'id')->toArray();

        // Log view
        Logger::log('show', 'professor', [
            'professor_id' => $professor->id,
            'professor_name' => $professor->first_name . ' ' . $professor->last_name,
            'academic_program_id' => $professor->academic_program_id
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
        Logger::log('edit', 'professor', [
            'professor_id' => $professor->id,
            'professor_name' => $professor->first_name . ' ' . $professor->last_name,
            'academic_program_id' => $professor->academic_program_id
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
        Logger::log('update', 'professor', $validatedData);

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
        Logger::log('delete', 'professor', $professorData);

        return redirect()->route('professors.index')
            ->with('success', 'Professor deleted successfully.');
    }

}
