<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function index(){
        //Test Code Start
        $sessions = [
            ['session_name' => 'BSIT 1st Year A', 'id' => 1],
            ['session_name' => 'BSCS 1st Year B', 'id' => 2],
        ];
        //Test code end
        return view('ClassSections.index', compact('sessions'));
    }

    public function show($id){

        //Test Code Start
        $sessions = [
            ['session_name' => 'BSIT 1st Year A', 'id' => 1],
            ['session_name' => 'BSCS 1st Year B', 'id' => 2],
        ];
        //Test code end

        //Collect specific course details depending on the id
        $session = collect($sessions)->firstWhere('id', $id);

        return view('ClassSections.show', compact('session'));
    }
}
