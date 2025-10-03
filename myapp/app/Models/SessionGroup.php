<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionGroup extends Model
{
    protected $fillable = [
        'group_name',
        'year_level',
        'academic_program_id',
        'timetable_id',
    ];

    public function academicProgram()
    {
        return $this->belongsTo(AcademicProgram::class);
    }

    public function timetable()
    {
        return $this->belongsTo(Timetable::class);
    }
}
