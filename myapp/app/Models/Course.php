<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'course_title',
        'course_name',
        'course_type',
        'class_hours',
        'total_lecture_class_days',
        'total_laboratory_class_days',
        'unit_load',
        'duration_type'
    ];
    
}
