<?php

namespace App\Http\Controllers\timetabling;

use App\Http\Controllers\Controller;
use App\Models\records\Professor;
use App\Models\records\Timetable;
use App\Models\records\UserLog;
use Illuminate\Http\Request;

class TimetableProfessorController extends Controller
{
    public function index(Timetable $timetable, Request $request)
    {
        $query = $timetable->professors();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhereHas('specializations.course', function ($q2) use ($search) {
                        $q2->where('course_title', 'like', "%{$search}%");
                    });
            });
        }
        $professors = $query->get();

        // Log view action
        $this->logAction('viewed_timetable_professors', [
            'timetable_id' => $timetable->id,
            'professors_count' => $professors->count()
        ]);

        return view('timetabling.timetable-professors.index', compact('timetable', 'professors'));
    }

    public function create(Timetable $timetable, Request $request)
    {
        $assignedProfessorIds = $timetable->professors->pluck('id');
        $query = Professor::whereNotIn('id', $assignedProfessorIds);

        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhereHas('specializations.course', function($q2) use ($search) {
                        $q2->where('course_title', 'like', "%{$search}%");
                    });
            });
        }

        $professors = $query->get();
        $selected = $request->input('professors', []);

        // Log access to create form
        $this->logAction('accessed_timetable_professor_create_form', [
            'timetable_id' => $timetable->id
        ]);

        return view('timetabling.timetable-professors.create', compact('timetable', 'professors', 'selected'));
    }

    public function store(Request $request, Timetable $timetable)
    {
        $validatedData = $request->validate([
            'professors' => 'array',
            'professors.*' => 'exists:professors,id'
        ]);

        $assignedProfessorIds = $timetable->professors->pluck('id');
        $professors = Professor::whereNotIn('id', $assignedProfessorIds)->get();

        if (empty($validatedData['professors'])) {
            return view('timetabling.timetable-professors.create', [
                'timetable' => $timetable,
                'professors' => $professors,
                'message' => 'Must select a professor.'
            ]);
        }

        foreach($validatedData['professors'] as $professorId){
            $timetable->professors()->attach($professorId);

            // Log each professor assignment
            $this->logAction('assigned_professor_to_timetable', [
                'timetable_id' => $timetable->id,
                'professor_id' => $professorId
            ]);
        }

        return redirect()->route('timetables.timetable-professors.index', $timetable);
    }

    public function destroy(Timetable $timetable, Professor $professor)
    {
        $timetable->professors()->detach($professor->id);

        // Log removal
        $this->logAction('removed_professor_from_timetable', [
            'timetable_id' => $timetable->id,
            'professor_id' => $professor->id
        ]);

        return redirect()->route('timetables.timetable-professors.index', $timetable);
    }

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
