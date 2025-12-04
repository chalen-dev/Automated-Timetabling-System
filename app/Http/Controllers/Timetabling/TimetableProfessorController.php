<?php

namespace App\Http\Controllers\Timetabling;

use App\Helpers\Logger;
use App\Http\Controllers\Controller;
use App\Models\Records\Professor;
use App\Models\Records\Timetable;
use App\Models\Users\UserLog;
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

        Logger::log('index', 'timetable professor', [
            'timetable_id' => $timetable->id,
            'timetable_name' => $timetable->timetable_name,
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

        Logger::log('create', 'timetable professor', [
            'timetable_id' => $timetable->id,
            'timetable_name' => $timetable->timetable_name,
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

        $addedProfessors = [];

        foreach($validatedData['professors'] as $professorId){
            $timetable->professors()->attach($professorId);

            $professor = Professor::find($professorId);
            $addedProfessors[] = $professor->full_name;
        }

        Logger::log('store', 'timetable professor', [
            'timetable_id' => $timetable->id,
            'timetable_name' => $timetable->timetable_name,
            'added_professors' => $addedProfessors,
        ]);

        return redirect()->route('timetables.timetable-professors.index', $timetable);
    }

    public function destroy(Timetable $timetable, Professor $professor)
    {
        $timetable->professors()->detach($professor->id);

        Logger::log('delete', 'timetable professor', [
            'timetable_id' => $timetable->id,
            'timetable_name' => $timetable->timetable_name,
            'professor_id' => $professor->id,
            'professor_name' => $professor->full_name,
        ]);

        return redirect()->route('timetables.timetable-professors.index', $timetable);
    }
}
