<?php

namespace App\Http\Controllers\Records;

use App\Helpers\Logger;
use App\Http\Controllers\Controller;
use App\Models\Records\Timetable;
use App\Models\Users\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TimetableController extends Controller
{
    private $semesterOptions = [
        '1st' => '1st',
        '2nd' => '2nd'
    ];

    public function index()
    {
        $timetables = Timetable::all();

        //Log
        Logger::log('index', 'timetable', null);

        return view('records.timetables.index', compact('timetables'));
    }

    public function create()
    {
        $semesterOptions = $this->semesterOptions;

        //Log
        Logger::log('create', 'timetable', null);

        return view('records.timetables.create', compact('semesterOptions'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'timetable_name' => 'required|string|max:30',
            'semester' => 'required|string|in:1st,2nd',
            'academic_year' => 'required|string',
            'timetable_description' => 'nullable|string',
        ]);

        $validatedData['user_id'] = auth()->id();

        $timetable = Timetable::create($validatedData);

        //Log
        Logger::log('store', 'timetable', [
            'timetable_id' => $timetable->id,
            'timetable_name' => $timetable->timetable_name,
            'semester' => $timetable->semester,
            'academic_year' => $timetable->academic_year,
            'timetable_description' => $timetable->timetable_description,
        ]);

        return redirect()->route('timetables.index')
            ->with('success', 'Timetable created successfully.');
    }

    public function show(Timetable $timetable)
    {
        //Log
        Logger::log('show', 'timetable', [
            'timetable_id' => $timetable->id,
            'timetable_name' => $timetable->timetable_name,
        ]);

        return view('records.timetables.show', compact('timetable'));
    }

    public function edit(Timetable $timetable)
    {
        $semesterOptions = $this->semesterOptions;

        //Log
        Logger::log('edit', 'timetable', [
            'timetable_id' => $timetable->id,
            'timetable_name' => $timetable->timetable_name,
        ]);

        return view('records.timetables.edit', compact('timetable', 'semesterOptions'));
    }

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

        //Log
        Logger::log('update', 'timetable', $validatedData);

        return redirect()->route('timetables.index')
            ->with('success', 'Timetable updated successfully.');
    }

    public function destroy(Timetable $timetable)
    {
        $timetableData = [
            'timetable_id' => $timetable->id,
            'timetable_name' => $timetable->timetable_name
        ];

        // Delete the corresponding XLSX file if it exists
        $filePath = "exports/timetables/{$timetable->id}.xlsx";

        // Prefer the local disk (storage/app root) — matches storage_path('app/...')
        if (Storage::disk('local')->exists($filePath)) {
            Storage::disk('local')->delete($filePath);
        } else {
            // fallback: try direct filesystem path (what the seeder cleans)
            $fullPath = storage_path("app/{$filePath}");
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
        }


        // Delete the timetable from the database
        $timetable->delete();

        //Log
        Logger::log('delete', 'timetable', $timetableData);

        return redirect()->route('timetables.index')
            ->with('success', 'Timetable deleted successfully.');
    }

}
