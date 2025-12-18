<?php

namespace App\Models\Records;

use Illuminate\Database\Eloquent\Model;

class TimetableUserAccess extends Model
{
    protected $table = 'timetable_user_access';

    protected $fillable = [
        'timetable_id',
        'user_id',
    ];
}
