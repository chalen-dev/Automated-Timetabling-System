<?php

namespace App\Models\Records;

use App\Models\Records\AcademicProgram;
use App\Models\Records\Course;
use Illuminate\Database\Eloquent\Model;

class CourseAcademicPrograms extends Model
{
    protected $fillable = [
        'course_id',
        'academic_program_id',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function academicProgram()
    {
        return $this->belongsTo(AcademicProgram::class);
    }
}
