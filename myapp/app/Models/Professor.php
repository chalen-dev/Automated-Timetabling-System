<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Professor extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'professor_type',
        'max_unit_load',
        'professor_age',
        'position',
        'academic_program_id' //foreign key
        //Add suffixes (jr, sr, etc.) soon
    ];

    //A professor can belong to a single academic program (many to one)
    public function academicProgram(){
        return $this->belongsTo(AcademicProgram::class);
    }

    //A professor can have many specializations (one to many)
    public function specializations(){
        return $this->hasMany(Specialization::class);
    }

    //Convenience accessor for courses yeah
    public function courses()
    {
        return $this->hasManyThrough(Course::class, Specialization::class, 'professor_id', 'id', 'id', 'course_id');
    }

    public function timetableProfessors(){
        return $this->hasMany(TimetableProfessor::class);
    }
}
