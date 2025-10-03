<?php

namespace App\Http\Controllers;

use App\Models\Timetable;
use Illuminate\Http\Request;

class TimetableEditingPaneController extends Controller
{
    public function index(Timetable $timetable){
        return view('timetabling.timetable-editing-pane.index', compact('timetable'));
    }
}
