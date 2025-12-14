<?php

namespace App\Models\Records;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable =[
        'room_name',
        'room_type',
        'course_type_exclusive_to',
        'room_capacity',
    ];

    public function roomExclusiveDays(){
        return $this->hasMany(RoomExclusiveDay::class);
    }

    // Relationship to the pivot-like model, similar to roomExclusiveDays
    public function roomExclusiveAcademicPrograms()
    {
        return $this->hasMany(RoomExclusiveAcademicPrograms::class);
    }

    // Direct many-to-many to AcademicProgram via the pivot table
    public function exclusiveAcademicPrograms()
    {
        return $this->belongsToMany(
            AcademicProgram::class,
            'room_exclusive_academic_programs',
            'room_id',
            'academic_program_id'
        )->withTimestamps();
    }

    // Optional convenience method:
    public function canAccommodateProgram(AcademicProgram $program): bool
    {
        // If no exclusives are set, you can decide whether that means:
        //  - room is open to all programs (return true), or
        //  - room cannot be used until exclusives are set (return false).
        if ($this->exclusiveAcademicPrograms()->count() === 0) {
            return true; // or false, depending on your business rule
        }

        return $this->exclusiveAcademicPrograms()
            ->where('academic_program_id', $program->id)
            ->exists();
    }
}
