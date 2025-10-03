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
        return view('timetabling.timetable-professors.index', compact('timetable'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(TimetableProfessor $timetableProfessor)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TimetableProfessor $timetableProfessor)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TimetableProfessor $timetableProfessor)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TimetableProfessor $timetableProfessor)
    {
        //
    }
}
