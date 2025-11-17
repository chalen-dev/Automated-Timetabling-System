<?php

namespace App\Models\timetabling;

use App\Models\records\AcademicProgram;
use App\Models\records\Professor;
use App\Models\records\Timetable;
use Illuminate\Database\Eloquent\Model;

class TimetableProfessor extends Model
{
    protected $fillable = [
        'professor_id',
        'timetable_id',
    ];

    public function professor(){
        return $this->belongsTo(Professor::class);
    }

    public function timetable(){
        return $this->belongsTo(Timetable::class);
    }

    //Convenience Relationships (nge)
    public function academicProgram()
    {
        return $this->hasOneThrough(
            AcademicProgram::class, // target
            Professor::class,       // middle
            'id',                   // FK on professors (to match timetable_professors.professor_id)
            'id',                   // PK on academic_programs
            'professor_id',         // FK on timetable_professors
            'academic_program_id'   // FK on professors
        );
    }

}
