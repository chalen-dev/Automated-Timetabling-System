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
        // 3) Return your exact view (as requested)
        return view('timetabling.timetable-editing-pane.editor', [
            'timetable' => $timetable,
        ]);
    }
}
