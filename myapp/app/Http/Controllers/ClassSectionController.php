<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClassSectionController extends Controller
{
    public function index(){
        //Test Code Start
        $classSections = [
            ['section_name' => 'BSIT 1st Year A', 'id' => 1],
            ['section_name' => 'BSCS 1st Year B', 'id' => 2],
        ];
        //Test code end
        return view('ClassSections.index', compact('classSections'));
    }

    public function show($id){

        //Test Code Start
        $classSections = [
            ['section_name' => 'BSIT 1st Year A', 'id' => 1],
            ['section_name' => 'BSCS 1st Year B', 'id' => 2],
        ];
        //Test code end

        //Collect specific course details depending on the id
        $classSection = collect($classSections)->firstWhere('id', $id);

        return view('ClassSections.show', compact('classSection'));
    }
}
