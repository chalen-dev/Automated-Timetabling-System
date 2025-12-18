<?php

namespace App\Http\Controllers\Timetabling;

use App\Http\Controllers\Controller;
use App\Models\Records\AcademicProgram;
use App\Models\Records\Timetable;
use App\Models\Users\User;
use Illuminate\Http\Request;

class TimetableSettingsController extends Controller
{
    /**
     * Show timetable settings page
     */
    public function edit(Timetable $timetable)
    {
        $this->authorize('manageAccess', $timetable);

        return view('timetabling.timetable-settings.settings', [
            'timetable' => $timetable,
            'users' => User::where('role', '!=', 'admin')
                ->where('id', '!=', $timetable->user_id)
                ->orderBy('name')
                ->get(),
            'programs' => AcademicProgram::orderBy('program_name')->get(),
            'allow_non_owner_record_edit' => 'nullable|boolean',
        ]);
    }

    /**
     * Save timetable visibility & access rules
     */
    public function update(Request $request, Timetable $timetable)
    {
        $this->authorize('manageAccess', $timetable);

        $data = $request->validate([
            'visibility' => 'required|in:private,public,restricted',
            'user_ids' => 'array',
            'user_ids.*' => 'exists:users,id',
            'program_ids' => 'array',
            'program_ids.*' => 'exists:academic_programs,id',
        ]);

        // Save visibility
        $timetable->update([
            'visibility' => $data['visibility'],
            'allow_non_owner_record_edit' =>
                $request->boolean('allow_non_owner_record_edit'),
            'allow_non_owner_timetable_edit' =>
                $request->boolean('allow_non_owner_timetable_edit'),
        ]);

        // Sync access tables (ONLY if restricted)
        if ($data['visibility'] === 'restricted') {
            $timetable->allowedUsers()->sync($data['user_ids'] ?? []);
            $timetable->allowedPrograms()->sync($data['program_ids'] ?? []);
        } else {
            // Clear restrictions if not restricted
            $timetable->allowedUsers()->detach();
            $timetable->allowedPrograms()->detach();
        }

        return back()->with('success', 'Timetable settings updated.');
    }
}
