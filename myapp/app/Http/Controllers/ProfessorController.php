<?php

namespace App\Http\Controllers;

use App\Models\Professor;
use Illuminate\Http\Request;

class ProfessorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $professors = Professor::all();
        return view('professors.index', compact('professors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('professors.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request -> validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'professor_type' => 'required|string',
            'max_unit_load' => 'required|numeric|min:1.0',
            'professor_age' => 'nullable|numeric',
            'position' => 'nullable|string',
        ]);

        Professor::create($validatedData);
        return redirect()->route('professors.index')
            ->with('success', 'Professor created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Professor $professor)
    {
        return view('professors.show', compact('professor'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Professor $professor)
    {
        return view('professors.edit', compact('professor'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Professor $professor)
    {
        $validatedData = $request -> validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'professor_type' => 'required|string',
            'max_unit_load' => 'required|numeric|min:1.0',
            'professor_age' => 'nullable|numeric',
            'position' => 'nullable|string',
        ]);

        $professor->update($validatedData);
        return redirect()->route('professors.index')
            ->with('success', 'Professor updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Professor $professor)
    {
        $professor->delete();
        return redirect()->route('professors.index')
            ->with('success', 'Professor deleted successfully.');
    }
}
