<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable =[
        'room_name',
        'room_type',
        'specific_days', //e.g. Monday, Wednesday, Friday
        'room_capacity'
    ];
}
