<?php

namespace App\Http\Controllers;

use App\Models\SessionGroup;
use App\Models\Timetable;
use Illuminate\Http\Request;
class SessionGroupController extends Controller
{
    protected $yearLevelOptions = [
        '1st' => '1st'
    ];
    /**
     * Display a listing of the resource.
     */
    public function index(Timetable $timetable)
    {
        $sessionGroups = SessionGroup::all();
        return view('timetabling.timetable-session-groups.index', compact('timetable', 'sessionGroups'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Timetable $timetable)
    {
        return view('timetabling.timetable-session-groups.create', compact('timetable'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Timetable $timetable)
    {
        $validatedData = $request->validate([
            'group_name' => 'required|string',
            'year_level' => 'required|string',
            'program_id' => 'required|exists:academic_programs,id',
        ]);

        $validatedData['timetable_id'] = $request->route('timetable')->id;
        SessionGroup::create($validatedData);

        SessionGroup::create($validatedData);
        return redirect()->route('timetables.session-groups.index', $timetable)
            ->with('success', 'Class Session created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SessionGroup $sessionGroup, Timetable $timetable)
    {
        return view('timetabling.timetable-session-groups.edit', compact('sessionGroup', 'timetable'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SessionGroup $sessionGroup, Timetable $timetable)
    {
        $validatedData = $request->validate([
            'group_name' => 'required|string',
            'year_level' => 'required|string',
            'program_id' => 'required|exists:academic_programs,id',
        ]);

        $sessionGroup->update($validatedData);

        return redirect()->route('timetables.session-groups.index', $timetable)
            ->with('success', 'Class Session updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SessionGroup $sessionGroup, Timetable $timetable)
    {
        $sessionGroup->delete();
        return redirect()->route('timetables.session-groups.index', $timetable)
            ->with('success', 'Class Session deleted successfully.');
    }
}
