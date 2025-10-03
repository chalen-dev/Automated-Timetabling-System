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

    //An academic program has many professors (one to many)
    public function professors(){
        return $this->hasMany(Professor::class);
    }

    public function sessionGroups(){
        return $this->hasMany(SessionGroup::class);
    }
}
