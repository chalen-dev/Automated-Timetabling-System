<?php

namespace App\Http\Controllers\Timetabling;

use App\Http\Controllers\Controller;
use App\Models\Records\AcademicProgram;
use App\Models\Records\Timetable;
use App\Models\Timetabling\SessionGroup;
use App\Models\Users\UserLog;
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

    // ---------- LOGGING FUNCTION ----------
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

    // ---------- INDEX ----------
    public function index(Timetable $timetable, Request $request, SessionGroup $sessionGroup)
    {
        $query = $timetable->sessionGroups()->with(['academicProgram', 'courseSessions.course']);

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

        $sessionGroupsByProgram = $sessionGroups->groupBy('academic_program_id')->map(function ($groups) {
            return $groups->sortBy(function ($g) {
                $map = ['1st' => 1, '2nd' => 2, '3rd' => 3, '4th' => 4];
                return $map[$g->year_level] ?? 99;
            });
        });

        $courseSessionsBySessionGroup = $sessionGroups->mapWithKeys(function ($sessionGroup) {
            $termOrder = ['1st' => 1, '2nd' => 2, 'semestral' => 3];
            $sorted = $sessionGroup->courseSessions->sortBy(function ($cs) use ($termOrder) {
                return $termOrder[$cs->academic_term] ?? 99;
            });
            return [$sessionGroup->id => $sorted];
        });

        $this->logAction('viewed_session_groups', ['timetable_id' => $timetable->id, 'search' => $request->input('search')]);

        return view(
            'timetabling.timetable-session-groups.index',
            compact('timetable', 'sessionGroupsByProgram', 'courseSessionsBySessionGroup')
        );
    }

    // ---------- CREATE ----------
    public function create(Timetable $timetable)
    {
        $academic_program_options = AcademicProgram::all()->pluck('program_abbreviation', 'id')->toArray();
        $year_level_options = $this->year_level_options;

        $this->logAction('accessed_create_session_group_form', ['timetable_id' => $timetable->id]);

        return view('timetabling.timetable-session-groups.create', compact('timetable', 'academic_program_options', 'year_level_options'));
    }

    // ---------- STORE ----------
    public function store(Request $request, Timetable $timetable)
    {
        $validatedData = $request->validate([
            'session_name' => [
                'required',
                'string',
                'max:4',
                Rule::unique('session_groups')->where(function ($query) use ($request, $timetable) {
                    return $query->where('timetable_id', $timetable->id)
                        ->where('academic_program_id', $request->academic_program_id)
                        ->where('year_level', $request->year_level);
                }),
            ],
            'year_level' => 'required|string',
            'academic_program_id' => 'required|exists:academic_programs,id',
            'short_description' => 'nullable|string',
        ]);

        $validatedData['timetable_id'] = $timetable->id;

        $sessionGroup = SessionGroup::create($validatedData);

        $this->logAction('create_session_group', [
            'session_group_id' => $sessionGroup->id,
            'session_name' => $sessionGroup->session_name,
            'timetable_id' => $timetable->id
        ]);

        return redirect()->route('timetables.session-groups.index', $timetable)
            ->with('success', 'Class Session created successfully.');
    }


    // ---------- EDIT ----------
    public function edit(Timetable $timetable, SessionGroup $sessionGroup)
    {
        $academic_program_options = AcademicProgram::all()->pluck('program_abbreviation', 'id')->toArray();
        $year_level_options = $this->year_level_options;

        $this->logAction('accessed_edit_session_group_form', [
            'session_group_id' => $sessionGroup->id
        ]);

        return view('timetabling.timetable-session-groups.edit', compact('sessionGroup', 'timetable', 'academic_program_options', 'year_level_options'));
    }

    // ---------- UPDATE ----------
    public function update(Request $request, Timetable $timetable, SessionGroup $sessionGroup)
    {
        $validatedData = $request->validate([
            'session_name' => [
                'required',
                'string',
                'max:4',
                Rule::unique('session_groups')
                    ->ignore($sessionGroup->id) // Ignore the current row
                    ->where(function ($query) use ($request, $timetable) {
                        return $query->where('timetable_id', $timetable->id)
                            ->where('academic_program_id', $request->academic_program_id)
                            ->where('year_level', $request->year_level);
                    }),
            ],
            'year_level' => 'required|string',
            'academic_program_id' => 'required|exists:academic_programs,id',
            'short_description' => 'nullable|string',
        ]);

        $sessionGroup->update($validatedData);

        $this->logAction('update_session_group', [
            'session_group_id' => $sessionGroup->id,
            'session_name' => $sessionGroup->session_name
        ]);

        return redirect()->route('timetables.session-groups.index', $timetable)
            ->with('success', 'Class Session updated successfully.');
    }


    // ---------- SHOW ----------
    public function show(Timetable $timetable, SessionGroup $sessionGroup)
    {
        $sessionGroup->load(['academicProgram', 'courseSessions.course']);

        $sessionFullName = trim(sprintf(
            '%s %s %s Year',
            $sessionGroup->academicProgram->program_abbreviation ?? 'Unknown',
            $sessionGroup->session_name,
            $sessionGroup->year_level
        ));

        $this->logAction('viewed_session_group', [
            'session_group_id' => $sessionGroup->id,
            'session_fullname' => $sessionFullName
        ]);

        return view('timetabling.timetable-session-groups.show', compact('timetable', 'sessionGroup', 'sessionFullName'));
    }

    // ---------- DESTROY ----------
    public function destroy(Timetable $timetable, SessionGroup $sessionGroup)
    {
        $sessionGroupId = $sessionGroup->id;
        $sessionGroupName = $sessionGroup->session_name;

        $sessionGroup->delete();

        $this->logAction('delete_session_group', [
            'session_group_id' => $sessionGroupId,
            'session_name' => $sessionGroupName
        ]);

        return redirect()->route('timetables.session-groups.index', $timetable)
            ->with('success', 'Class Session deleted successfully.');
    }
}
