<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimetableProfessor extends Model
{
    protected $fillable = [
        'professor_id',
        'timetable_id',
    ];

    public function professor(){
        return $this->belongsTo(Professor::class);
    }

    public function timetable(){
        return $this->belongsTo(Timetable::class);
    }
}
