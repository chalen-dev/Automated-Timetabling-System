<?php

namespace App\Models\Timetabling;

use App\Models\Records\Course;
use Illuminate\Database\Eloquent\Model;

class CourseSession extends Model
{
    protected $fillable = [
        'session_group_id',
        'course_id',
        'academic_term' // 1st, 2nd, semestral
    ];

    public function course(){
        return $this->belongsTo(Course::class);
    }

    public function sessionGroup(){
        return $this->belongsTo(SessionGroup::class);
    }
}
