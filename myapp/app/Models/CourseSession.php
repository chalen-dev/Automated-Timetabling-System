<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseSession extends Model
{
    protected $fillable = [
        'session_group_id',
        'course_id',
        'academic_term'
    ];

    public function course(){
        return $this->belongsTo(Course::class);
    }

    public function sessionGroup(){
        return $this->belongsTo(SessionGroup::class);
    }
}
