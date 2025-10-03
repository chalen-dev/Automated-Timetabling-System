<?php

namespace App\Http\Controllers;

use App\Models\AcademicProgram;
use App\Models\SessionGroup;
use App\Models\Timetable;
use Illuminate\Http\Request;
class SessionGroupController extends Controller
{
    protected $year_level_options = [
        '1st' => '1st',
        '2nd' => '2nd',
        '3rd' => '3rd',
        '4th' => '4th',
    ];
    /**
     * Display a listing of the resource.
     */
    public function index(Timetable $timetable)
    {
        $sessionGroups = $timetable->sessionGroups()
            ->with('academicProgram')
            ->get()
            ->groupBy('program_id');

        return view('timetabling.timetable-session-groups.index', compact('timetable', 'sessionGroups'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Timetable $timetable)
    {
        $academic_program_options = AcademicProgram::all()->pluck('program_abbreviation', 'id')->toArray();
        $year_level_options = $this->year_level_options;
        return view('timetabling.timetable-session-groups.create', compact('timetable', 'academic_program_options', 'year_level_options'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Timetable $timetable)
    {
        $validatedData = $request->validate([
            'session_name' => 'required|string|unique:session_groups,session_name',
            'year_level' => 'required|string',
            'academic_program_id' => 'required|exists:academic_programs,id',
        ]);

        $validatedData['timetable_id'] = $request->route('timetable')->id;
        SessionGroup::create($validatedData);

        return redirect()->route('timetables.session-groups.index', $timetable)
            ->with('success', 'Class Session created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Timetable $timetable, SessionGroup $sessionGroup)
    {
        $academic_program_options = AcademicProgram::all()->pluck('program_abbreviation', 'id')->toArray();
        $year_level_options = $this->year_level_options;
        return view('timetabling.timetable-session-groups.edit', compact('sessionGroup', 'timetable', 'academic_program_options', 'year_level_options'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Timetable $timetable, SessionGroup $sessionGroup)
    {
        $validatedData = $request->validate([
            'session_name' => 'required|string|unique:session_groups, session_name' . $sessionGroup->id,
            'year_level' => 'required|string',
            'academic_program_id' => 'required|exists:academic_programs,id',
        ]);

        $sessionGroup->update($validatedData);

        return redirect()->route('timetables.session-groups.index', $timetable)
            ->with('success', 'Class Session updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Timetable $timetable, SessionGroup $sessionGroup)
    {
        $sessionGroup->delete();
        return redirect()->route('timetables.session-groups.index', $timetable)
            ->with('success', 'Class Session deleted successfully.');
    }
}
