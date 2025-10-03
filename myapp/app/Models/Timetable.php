<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Timetable extends Model
{
    protected $fillable = [
        'timetable_name',
        //created_at          ------- NOTE: is already implemented automatically through LARAVEL TIMESTAMPS, just call it from the controller
        'semester',
        'academic_year',
        'timetable_description',
        'user_id',
    ];
    public function user(){
        return $this->belongsTo(User::class);
    }

    public function sessionGroup(){
        return $this->hasMany(SessionGroup::class);
    }

    public function timetableProfessor(){
        return $this->hasMany(TimetableProfessor::class);
    }
}
