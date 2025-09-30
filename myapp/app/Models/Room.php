<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable =[
        'room_name',
        'room_type',
        'course_type_exclusive_to',
        'room_capacity',
        'monday_exclusive',
        'tuesday_exclusive',
        'wednesday_exclusive',
        'thursday_exclusive',
        'friday_exclusive',
        'saturday_exclusive',
        'sunday_exclusive'
    ];
}
