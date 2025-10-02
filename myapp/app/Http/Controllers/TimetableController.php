<?php

namespace App\Http\Controllers;

use App\Models\Timetable;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    private $semesterOptions = [
        '1st' => '1st',
        '2nd' => '2nd'
    ];

    public function index()
    {
        $timetables = Timetable::all();
        return view('records.timetables.index', compact('timetables'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $semesterOptions = $this->semesterOptions;
        return view('records.timetables.create', compact('semesterOptions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'timetable_name' => 'required|string|max:30',
            'semester' => 'required|string|in:1st,2nd',
            'academic_year' => 'required|string',
            'timetable_description' => 'nullable|string',
        ]);

        $validatedData['user_id'] = auth()->id();

        Timetable::create($validatedData);

        return redirect()->route('timetables.index')
            ->with('success', 'Timetable created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Timetable $timetable)
    {
        return view('timetables.show', compact('timetable'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Timetable $timetable)
    {
        $semesterOptions = $this->semesterOptions;
        return view('records.timetables.edit', compact('timetable', 'semesterOptions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Timetable $timetable)
    {
        $validatedData = $request->validate([
            'timetable_name' => 'required|string|max:30',
            'semester' => 'required|string|in:1st,2nd',
            'academic_year' => 'required|string',
            'timetable_description' => 'nullable|string',
        ]);

        $validatedData['user_id'] = auth()->id();

        $timetable->update($validatedData);

        return redirect()->route('timetables.index')
            ->with('success', 'Timetable updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Timetable $timetable)
    {
        $timetable->delete();
        return redirect()->route('timetables.index')
            ->with('success', 'Timetable deleted successfully.');
    }
}
