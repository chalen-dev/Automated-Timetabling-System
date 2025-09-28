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

    ];
    public function user(){
        return $this->belongsTo(User::class);
    }
}
