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
    ];

    //A professor can belong to a single academic program (many to one)
    public function academicProgram(){
        return $this->belongsTo(AcademicProgram::class);
    }
}
