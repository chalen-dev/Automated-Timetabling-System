<?php

namespace App\Models\Records;

use App\Models\Timetabling\SessionGroup;
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

    public function roomExclusiveAcademicPrograms(){
        return $this->hasMany(RoomExclusiveAcademicPrograms::class);
    }

    // Direct many-to-many back to Room
    public function exclusiveRooms()
    {
        return $this->belongsToMany(
            Room::class,
            'room_exclusive_academic_programs',
            'academic_program_id',
            'room_id'
        )->withTimestamps();
    }
}
