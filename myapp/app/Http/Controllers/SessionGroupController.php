<?php

namespace App\Http\Controllers;

use App\Models\AcademicProgram;
use App\Models\SessionGroup;
use App\Models\Timetable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SessionGroupController extends Controller
{
    protected $year_level_options = [
        '1st' => '1st',
        '2nd' => '2nd',
        '3rd' => '3rd',
        '4th' => '4th',
    ];

    protected $academic_term_options = [
        '1st' => '1st',
        '2nd' => '2nd',
        'semestral' => 'Semestral'
    ];
    /**
     * Display a listing of the resource.
     */
    public function index(Timetable $timetable, Request $request)
    {
        // Start query for session groups of this timetable
        $query = $timetable->sessionGroups()->with(['academicProgram', 'courseSessions.course']);

        // Apply search filter
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('session_name', 'like', "%{$search}%")
                    ->orWhere('year_level', 'like', "%{$search}%")
                    ->orWhereHas('academicProgram', function($q2) use ($search) {
                        $q2->where('program_abbreviation', 'like', "%{$search}%");
                    });
            });
        }

        $sessionGroups = $query->get();

        // Group SessionGroups by program
        $sessionGroupsByProgram = $sessionGroups->groupBy('academic_program_id')->map(function ($groups) {
            return $groups->sortBy(function ($g) {
                $map = ['1st' => 1, '2nd' => 2, '3rd' => 3, '4th' => 4];
                return $map[$g->year_level] ?? 99;
            });
        });

        // Sort CourseSessions by term order (still inside each session group)
        $courseSessionsBySessionGroup = $sessionGroups->mapWithKeys(function ($sessionGroup) {
            $termOrder = ['1st' => 1, '2nd' => 2, 'semestral' => 3];
            $sorted = $sessionGroup->courseSessions->sortBy(function ($cs) use ($termOrder) {
                return $termOrder[$cs->academic_term] ?? 99;
            });
            return [$sessionGroup->id => $sorted];
        });

        return view(
            'timetabling.timetable-session-groups.index',
            compact('timetable', 'sessionGroupsByProgram', 'courseSessionsBySessionGroup')
        );
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
            'session_name' => [
                'required',
                'string',
                'max:4',
                Rule::unique('session_groups')->where(function ($query) use ($request) {
                    return $query
                        ->where('year_level', $request->year_level)
                        ->where('academic_program_id', $request->academic_program_id);
                }),
            ],
            'year_level' => 'required|string',
            'academic_program_id' => 'required|exists:academic_programs,id',
            'short_description' => 'string',
        ]);

        $validatedData['timetable_id'] = $timetable->id;
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
            'session_name' => [
                'required',
                'string',
                'max:4',
                Rule::unique('session_groups')
                    ->ignore($sessionGroup->id)
                    ->where(function ($query) use ($request) {
                        return $query
                            ->where('year_level', $request->year_level)
                            ->where('academic_program_id', $request->academic_program_id);
                    }),
            ],
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
