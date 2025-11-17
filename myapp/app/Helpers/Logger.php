<?php

namespace App\Helpers;

use App\Models\records\UserLog;

class Logger
{
    public static function log($action, $description = null)
    {
        if(auth()->check()) {
            UserLog::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'description' => $description,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }
    }
}
