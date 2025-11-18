<?php

namespace App\Models\Records;

use App\Models\Timetabling\SessionGroup;
use App\Models\Users\User;
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

    public function sessionGroups(){
        return $this->hasMany(SessionGroup::class);
    }

    public function professors()
    {
        return $this->belongsToMany(
            Professor::class,
            'timetable_professors',
            'timetable_id',
            'professor_id'
        );
    }

    public function rooms(){
        return $this->belongsToMany(
            Room::class,
            'timetable_rooms',
            'timetable_id',
            'room_id'
        );
    }
}
