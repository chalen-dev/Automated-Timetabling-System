<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Timetable extends Model
{
    protected $fillable = [
        'timetable_name',

    ];
    public function user(){
        return $this->belongsTo(User::class);
    }
}
