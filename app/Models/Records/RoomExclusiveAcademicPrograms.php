<?php

namespace App\Models\Records;

use Illuminate\Database\Eloquent\Model;

class RoomExclusiveAcademicPrograms extends Model
{
    protected $fillable = [
        'room_id',
        'academic_program_id',
    ];

    public function room() {
        return $this->belongsTo(Room::class);
    }

    public function academicProgram() {
        return $this->belongsTo(AcademicProgram::class);
    }
}
