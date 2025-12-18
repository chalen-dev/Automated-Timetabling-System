<?php

namespace App\Models\Records;

use Illuminate\Database\Eloquent\Model;

class TimetableAcademicProgram extends Model
{
    protected $table = "timetable_academic_program";

    protected $fillable = [
        'timetable_id',
        'academic_program_id',
    ];
}
