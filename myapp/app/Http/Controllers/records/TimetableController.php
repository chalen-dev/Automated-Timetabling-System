<?php

namespace App\Http\Controllers\records;

use App\Http\Controllers\Controller;
use App\Models\records\Timetable;
use App\Models\records\UserLog;
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

        $this->logAction('viewed_timetables_list');

        return view('records.timetables.index', compact('timetables'));
    }

    public function create()
    {
        $semesterOptions = $this->semesterOptions;

        $this->logAction('accessed_create_timetable_form');

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

        $this->logAction('create_timetable', [
            'timetable_id' => $timetable->id,
            'timetable_name' => $timetable->timetable_name
        ]);

        return redirect()->route('timetables.index')
            ->with('success', 'Timetable created successfully.');
    }

    public function show(Timetable $timetable)
    {
        $this->logAction('viewed_timetable', [
            'timetable_id' => $timetable->id,
            'timetable_name' => $timetable->timetable_name
        ]);

        return view('records.timetables.show', compact('timetable'));
    }

    public function edit(Timetable $timetable)
    {
        $semesterOptions = $this->semesterOptions;

        $this->logAction('accessed_edit_timetable_form', [
            'timetable_id' => $timetable->id,
            'timetable_name' => $timetable->timetable_name
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

        $this->logAction('update_timetable', [
            'timetable_id' => $timetable->id,
            'timetable_name' => $timetable->timetable_name
        ]);

        return redirect()->route('timetables.index')
            ->with('success', 'Timetable updated successfully.');
    }

    public function destroy(Timetable $timetable)
    {
        $timetableData = [
            'timetable_id' => $timetable->id,
            'timetable_name' => $timetable->timetable_name
        ];

        // Delete the timetable from the database
        $timetable->delete();

        // Delete the corresponding XLSX file if it exists
        $filePath = "exports/timetables/{$timetable->id}.xlsx";
        if (Storage::exists($filePath)) {
            Storage::delete($filePath);
        }

        $this->logAction('delete_timetable', $timetableData);

        return redirect()->route('timetables.index')
            ->with('success', 'Timetable deleted successfully.');
    }

    /**
     * Log user actions.
     */
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
}
