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
        'position'
    ];
}
