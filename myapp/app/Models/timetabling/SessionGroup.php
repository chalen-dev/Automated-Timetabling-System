<?php

namespace App\Models\timetabling;

use App\Models\records\AcademicProgram;
use App\Models\records\Timetable;
use Illuminate\Database\Eloquent\Model;

class SessionGroup extends Model
{
    protected $fillable = [
        'session_name',
        'year_level',
        'academic_program_id',
        'timetable_id',
        'short_description'
    ];

    public function academicProgram()
    {
        return $this->belongsTo(AcademicProgram::class);
    }

    public function timetable()
    {
        return $this->belongsTo(Timetable::class);
    }

    public function courseSessions(){
        return $this->hasMany(CourseSession::class, 'session_group_id');
    }
}
