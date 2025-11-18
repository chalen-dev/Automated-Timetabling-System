<?php

namespace App\Models\Records;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Model;

class UserLog extends Model
{
    protected $fillable = [
        'user_id', 'action', 'description', 'ip_address', 'user_agent'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
