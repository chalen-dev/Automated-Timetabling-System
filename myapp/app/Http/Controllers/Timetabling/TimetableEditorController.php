<?php

namespace App\Http\Controllers\Timetabling;

use App\Http\Controllers\Controller;
use App\Models\Records\Timetable;
use App\Models\Records\Room;
use App\Models\Timetabling\SessionGroup;
use App\Models\Timetabling\TimetableRoom;

class TimetableEditorController extends Controller
{
    public function editor(Timetable $timetable)
    {
        $sessionGroups = SessionGroup::where('timetable_id', $timetable->id)
            ->with('courseSessions')
            ->get();

        return view('timetabling.timetable-editing-pane.editor', [
            'timetable' => $timetable,
            'sessionGroups' => $sessionGroups,
        ]);
    }
}
