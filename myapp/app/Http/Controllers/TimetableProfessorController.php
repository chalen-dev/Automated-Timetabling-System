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
        $professors = Professor::all();
        return view('timetabling.timetable-professors.create', compact('timetable', 'professors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Timetable $timetable)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Timetable $timetable, TimetableProfessor $timetableProfessor)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Timetable $timetable, TimetableProfessor $timetableProfessor)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Timetable $timetable, TimetableProfessor $timetableProfessor)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Timetable $timetable, TimetableProfessor $timetableProfessor)
    {
        //
    }
}
