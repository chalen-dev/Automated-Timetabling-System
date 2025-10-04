<?php

namespace App\Http\Controllers;

use App\Models\Professor;
use App\Models\Timetable;
use App\Models\TimetableProfessor;
use Illuminate\Http\Request;

class
TimetableProfessorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Timetable $timetable)
    {
        $professors = $timetable->professors;
        return view('timetabling.timetable-professors.index', compact('timetable', 'professors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Timetable $timetable)
    {
        $assignedProfessorIds = $timetable->professors->pluck('id');
        $professors = Professor::whereNotIn('id', $assignedProfessorIds)->get();
        return view('timetabling.timetable-professors.create', compact('timetable', 'professors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Timetable $timetable)
    {
        $validatedData = $request->validate([
            'professors' => 'array',
            'professors.*' => 'exists:professors,id'
        ]);
        $assignedProfessorIds = $timetable->professors->pluck('id');
        $professors = Professor::whereNotIn('id', $assignedProfessorIds)->get();
        //No selection
        if (empty($validatedData['professors'])) {
            return view('timetabling.timetable-professors.create', [
                'timetable' => $timetable,
                'professors' => $professors,
                'message' => 'Must select a professor.'
            ]);
        }

        foreach($validatedData['professors'] as $professorId){
            $timetable->professors()->attach($professorId);
        }

        return redirect()->route('timetables.timetable-professors.index', $timetable);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Timetable $timetable, TimetableProfessor $timetableProfessor)
    {
        $timetable->professors()->detach($timetableProfessor->id);

        return redirect()->route('timetables.timetable-professors.index', $timetable);
    }
}
