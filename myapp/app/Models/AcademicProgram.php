<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicProgram extends Model
{
    protected $fillable = [
        'program_name',
        'program_abbreviation',
        'program_description',
    ];
}
