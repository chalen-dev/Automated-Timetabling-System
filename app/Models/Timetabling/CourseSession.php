<?php

namespace App\Models\Timetabling;

use App\Models\Records\Course;
use Exception;
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

    protected static function booted()
    {
        static::creating(function ($model) {
            if (!$model->sessionGroup) {
                throw new Exception('Invalid session_group_id used in CourseSession');
            }
        });
    }
}
