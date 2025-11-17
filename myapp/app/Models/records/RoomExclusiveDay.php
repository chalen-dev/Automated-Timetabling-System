<?php

namespace App\Models\records;

use Illuminate\Database\Eloquent\Model;

class RoomExclusiveDay extends Model
{
    protected $fillable = [
        'room_id',
        'exclusive_day',
    ];

    public function room(){
        return $this->belongsTo(Room::class);
    }
}
